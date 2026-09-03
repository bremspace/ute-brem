<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    public function index()
    {
        return $this->ledger(new Request());
    }

    /** Buku Besar (General Ledger). */
    public function ledger(Request $request)
    {
        $accounts = ChartOfAccount::query()
            ->where('is_postable', true)
            ->orderBy('code')
            ->get();

        $accountId = $request->integer('account');
        $from = $request->query('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->query('to', Carbon::now()->endOfMonth()->toDateString());

        $selected = $accountId ? ChartOfAccount::where('is_postable', true)->findOrFail($accountId) : $accounts->first();

        $opening = $this->openingBalance($selected, $from);
        $lines = collect();

        if ($selected) {
            $lines = JournalEntryLine::query()
                ->with('journalEntry')
                ->where('account_id', $selected->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('journal_date', [$from, $to]))
                ->orderBy('journal_entry_id')
                ->get();
        }

        $running = $opening;
        $rows = $lines->map(function ($l) use (&$running, $selected) {
            $debit = (float) $l->debit;
            $credit = (float) $l->credit;
            $running += $selected->normal_balance === 'debit' ? $debit - $credit : $credit - $debit;
            return (object) [
                'code' => $l->journalEntry->journal_code,
                'date' => $l->journalEntry->journal_date->toDateString(),
                'description' => $l->memo ?: $l->journalEntry->description,
                'reference' => $l->journalEntry->source_reference,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $running,
            ];
        });

        return view('accounting.ledger', compact('accounts', 'accountId', 'selected', 'from', 'to', 'opening', 'rows', 'running'));
    }

    /** Jurnal Umum (General Journal). */
    public function journal(Request $request)
    {
        $from = $request->query('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->query('to', Carbon::now()->endOfMonth()->toDateString());

        $entries = JournalEntry::with(['lines.account', 'creator'])
            ->whereBetween('journal_date', [$from, $to])
            ->orderByDesc('journal_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn ($e) => $e->journal_date->toDateString());

        $totalDebit = JournalEntry::whereBetween('journal_date', [$from, $to])->sum('debit_total');
        $totalCredit = JournalEntry::whereBetween('journal_date', [$from, $to])->sum('credit_total');

        return view('accounting.journal', compact('entries', 'from', 'to', 'totalDebit', 'totalCredit'));
    }

    /** Bagan Akun (Chart of Accounts) + saldo saat ini. */
    public function chart()
    {
        $accounts = ChartOfAccount::with('children')->orderBy('code')->get();

        $balance = $this->currentBalances();
        $assets = $accounts->where('category', 'asset');
        $liabilities = $accounts->where('category', 'liability');
        $equity = $accounts->where('category', 'equity');
        $revenue = $accounts->where('category', 'revenue');
        $expenses = $accounts->where('category', 'expense');

        return view('accounting.chart', compact('accounts', 'balance', 'assets', 'liabilities', 'equity', 'revenue', 'expenses'));
    }

    /** Neraca Saldo (Trial Balance). */
    public function trialBalance(Request $request)
    {
        $from = $request->query('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->query('to', Carbon::now()->endOfMonth()->toDateString());

        $accounts = ChartOfAccount::where('is_postable', true)->orderBy('code')->get();

        $rows = $accounts->map(function ($acc) use ($from, $to) {
            $sum = JournalEntryLine::query()
                ->where('account_id', $acc->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('journal_date', [$from, $to]))
                ->selectRaw('COALESCE(SUM(debit),0) AS d, COALESCE(SUM(credit),0) AS c')
                ->first();
            $debit = (float) $sum->d;
            $credit = (float) $sum->c;
            $opening = (float) $acc->opening_balance;
            $balDebit = 0.0;
            $balCredit = 0.0;
            if ($acc->normal_balance === 'debit') {
                $net = $opening + $debit - $credit;
                $balDebit = $net > 0 ? $net : 0;
                $balCredit = $net < 0 ? abs($net) : 0;
            } else {
                $net = -$opening + $credit - $debit;
                $balCredit = $net > 0 ? $net : 0;
                $balDebit = $net < 0 ? abs($net) : 0;
            }
            return (object) [
                'code' => $acc->code,
                'name' => $acc->name,
                'category' => $acc->category,
                'debit' => $debit,
                'credit' => $credit,
                'balDebit' => $balDebit,
                'balCredit' => $balCredit,
            ];
        });

        $totals = [
            'debit' => $rows->sum('debit'),
            'credit' => $rows->sum('credit'),
            'balDebit' => $rows->sum('balDebit'),
            'balCredit' => $rows->sum('balCredit'),
        ];

        return view('accounting.trial-balance', compact('rows', 'from', 'to', 'totals'));
    }

    /** Laba Rugi (Income Statement). */
    public function profitLoss(Request $request)
    {
        $from = $request->query('from', Carbon::now()->startOfYear()->toDateString());
        $to = $request->query('to', Carbon::now()->endOfYear()->toDateString());

        $revenue = $this->sumCategory('revenue', $from, $to);
        $cogs = $this->sumCategory('cogs', $from, $to);
        $expenses = $this->sumCategory('expense', $from, $to);

        $totalRevenue = array_sum($revenue['postable']);
        $totalCogs = array_sum($cogs['postable']);
        $totalExpense = array_sum($expenses['postable']);

        $grossProfit = $totalRevenue - $totalCogs;
        $netProfit = $grossProfit - $totalExpense;

        return view('accounting.profit-loss', compact('from', 'to', 'revenue', 'cogs', 'expenses', 'totalRevenue', 'totalCogs', 'totalExpense', 'grossProfit', 'netProfit'));
    }

    /** Neraca (Balance Sheet). */
    public function balanceSheet(Request $request)
    {
        $asOf = $request->query('as_of', Carbon::now()->toDateString());

        $assetGroups = $this->balanceSheetGroups(['asset'], $asOf);
        $liabilityGroups = $this->balanceSheetGroups(['liability'], $asOf);
        $equityGroups = $this->balanceSheetGroups(['equity'], $asOf);

        $totalAssets = $assetGroups['total'];
        $totalLiabilities = $liabilityGroups['total'];
        // Laba berjalan (period to date) ditambahkan ke ekuitas
        $retained = $this->currentPeriodProfit($asOf);
        $totalEquity = $equityGroups['total'] + $retained;

        return view('accounting.balance-sheet', compact('asOf', 'assetGroups', 'liabilityGroups', 'equityGroups', 'totalAssets', 'totalLiabilities', 'totalEquity', 'retained'));
    }

    private function balanceSheetGroups(array $categories, string $asOf): array
    {
        $parents = ChartOfAccount::whereIn('category', $categories)->whereNull('parent_id')->orderBy('code')->get();
        $groups = [];
        $total = 0.0;
        foreach ($parents as $parent) {
            $children = ChartOfAccount::where('parent_id', $parent->id)->where('is_postable', true)->orderBy('code')->get();
            $rows = [];
            $subtotal = 0.0;
            foreach ($children as $child) {
                $bal = $this->balanceAsOf($child, $asOf);
                $rows[] = (object) ['code' => $child->code, 'name' => $child->name, 'balance' => $bal];
                $subtotal += $bal;
            }
            $groups[] = (object) ['name' => $parent->name, 'rows' => $rows, 'subtotal' => $subtotal];
            $total += $subtotal;
        }
        return ['groups' => $groups, 'total' => $total];
    }

    private function sumCategory(string $type, string $from, string $to): array
    {
        $postable = DB::table('journal_entry_lines')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('chart_of_accounts.type', $type)
            ->whereBetween('journal_entries.journal_date', [$from, $to])
            ->select('chart_of_accounts.code', 'chart_of_accounts.name')
            ->selectRaw('COALESCE(SUM(COALESCE(journal_entry_lines.credit,0) - COALESCE(journal_entry_lines.debit,0)),0) AS amount')
            ->groupBy('chart_of_accounts.code', 'chart_of_accounts.name')
            ->orderBy('chart_of_accounts.code')
            ->get();

        return [
            'postable' => $postable->pluck('amount', 'code')->map(fn ($v) => (float) $v)->toArray(),
            'rows' => $postable->map(fn ($r) => (object) ['code' => $r->code, 'name' => $r->name, 'amount' => (float) $r->amount]),
        ];
    }

    private function openingBalance(ChartOfAccount $acc, string $from): float
    {
        $opening = (float) $acc->opening_balance;
        $sum = JournalEntryLine::query()
            ->where('account_id', $acc->id)
            ->whereHas('journalEntry', fn ($q) => $q->whereDate('journal_date', '<', $from))
            ->selectRaw('COALESCE(SUM(debit),0) AS d, COALESCE(SUM(credit),0) AS c')
            ->first();
        if ($acc->normal_balance === 'debit') {
            return $opening + (float) $sum->d - (float) $sum->c;
        }
        return -$opening + (float) $sum->c - (float) $sum->d;
    }

    private function balanceAsOf(ChartOfAccount $acc, string $asOf): float
    {
        $opening = (float) $acc->opening_balance;
        $sum = JournalEntryLine::query()
            ->where('account_id', $acc->id)
            ->whereHas('journalEntry', fn ($q) => $q->whereDate('journal_date', '<=', $asOf))
            ->selectRaw('COALESCE(SUM(debit),0) AS d, COALESCE(SUM(credit),0) AS c')
            ->first();
        if ($acc->normal_balance === 'debit') {
            return $opening + (float) $sum->d - (float) $sum->c;
        }
        return -$opening + (float) $sum->c - (float) $sum->d;
    }

    private function currentBalances(): array
    {
        $map = [];
        ChartOfAccount::where('is_postable', true)->orderBy('code')->get()->each(function ($acc) use (&$map) {
            $map[$acc->code] = $this->balanceAsOf($acc, now()->toDateString());
        });
        return $map;
    }

    private function currentPeriodProfit(string $asOf): float
    {
        $from = Carbon::now()->startOfYear()->toDateString();
        $to = $asOf;
        $revenue = array_sum($this->sumCategory('revenue', $from, $to)['postable'] ?? []);
        $cogs = array_sum($this->sumCategory('cogs', $from, $to)['postable'] ?? []);
        $expenses = array_sum($this->sumCategory('expense', $from, $to)['postable'] ?? []);
        return $revenue - $cogs - $expenses;
    }
}
