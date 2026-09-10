<?php

namespace App\Http\Livewire;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProfitLossComponent extends Component
{
    public string $from = '';
    public string $to = '';

    public function mount(): void
    {
        $this->from = Carbon::now()->startOfYear()->toDateString();
        $this->to = Carbon::now()->endOfYear()->toDateString();
    }

    private function sumCategory(string $type): array
    {
        $postable = DB::table('journal_entry_lines')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('chart_of_accounts.type', $type)
            ->whereBetween('journal_entries.journal_date', [$this->from, $this->to])
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

    public function render()
    {
        $revenue = $this->sumCategory('revenue');
        $cogs = $this->sumCategory('cogs');
        $expenses = $this->sumCategory('expense');
        $totalRevenue = array_sum($revenue['postable']);
        $totalCogs = array_sum($cogs['postable']);
        $totalExpense = array_sum($expenses['postable']);
        $grossProfit = $totalRevenue - $totalCogs;
        $netProfit = $grossProfit - $totalExpense;

        return view('livewire.accounting.profit-loss-component', compact('revenue', 'cogs', 'expenses', 'totalRevenue', 'totalCogs', 'totalExpense', 'grossProfit', 'netProfit'));
    }
}