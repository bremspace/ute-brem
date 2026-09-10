<?php

namespace App\Http\Livewire;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Livewire\Component;

class TrialBalanceComponent extends Component
{
    public string $from = '';
    public string $to = '';

    public function mount(): void
    {
        $this->from = Carbon::now()->startOfMonth()->toDateString();
        $this->to = Carbon::now()->endOfMonth()->toDateString();
    }

    public function getRowsProperty()
    {
        $accounts = ChartOfAccount::where('is_postable', true)->orderBy('code')->get();
        return $accounts->map(function ($acc) {
            $sum = JournalEntryLine::query()
                ->where('account_id', $acc->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('journal_date', [$this->from, $this->to]))
                ->selectRaw('COALESCE(SUM(debit),0) AS d, COALESCE(SUM(credit),0) AS c')
                ->first();
            $debit = (float) $sum->d;
            $credit = (float) $sum->c;
            $opening = (float) $acc->opening_balance;
            $balDebit = 0.0; $balCredit = 0.0;
            if ($acc->normal_balance === 'debit') {
                $net = $opening + $debit - $credit;
                $balDebit = $net > 0 ? $net : 0;
                $balCredit = $net < 0 ? abs($net) : 0;
            } else {
                $net = -$opening + $credit - $debit;
                $balCredit = $net > 0 ? $net : 0;
                $balDebit = $net < 0 ? abs($net) : 0;
            }
            return (object) ['code' => $acc->code, 'name' => $acc->name, 'debit' => $debit, 'credit' => $credit, 'balDebit' => $balDebit, 'balCredit' => $balCredit];
        });
    }

    public function getTotalsProperty(): array
    {
        return [
            'debit' => $this->rows->sum('debit'),
            'credit' => $this->rows->sum('credit'),
            'balDebit' => $this->rows->sum('balDebit'),
            'balCredit' => $this->rows->sum('balCredit'),
        ];
    }

    public function render()
    {
        return view('livewire.accounting.trial-balance-component');
    }
}