<?php

namespace App\Http\Livewire;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Livewire\Component;

class LedgerComponent extends Component
{
    public ?int $accountId = null;
    public string $from = '';
    public string $to = '';

    public function mount(): void
    {
        $this->from = Carbon::now()->startOfMonth()->toDateString();
        $this->to = Carbon::now()->endOfMonth()->toDateString();
    }

    public function getAccountsProperty()
    {
        return ChartOfAccount::where('is_postable', true)->orderBy('code')->get();
    }

    public function getSelectedProperty()
    {
        if ($this->accountId) {
            return ChartOfAccount::where('is_postable', true)->find($this->accountId);
        }
        return $this->accounts->first();
    }

    public function getOpeningProperty(): float
    {
        if (! $this->selected) return 0;
        $opening = (float) $this->selected->opening_balance;
        $sum = JournalEntryLine::query()
            ->where('account_id', $this->selected->id)
            ->whereHas('journalEntry', fn ($q) => $q->whereDate('journal_date', '<', $this->from))
            ->selectRaw('COALESCE(SUM(debit),0) AS d, COALESCE(SUM(credit),0) AS c')
            ->first();
        if ($this->selected->normal_balance === 'debit') {
            return $opening + (float) $sum->d - (float) $sum->c;
        }
        return -$opening + (float) $sum->c - (float) $sum->d;
    }

    public function getRowsProperty()
    {
        if (! $this->selected) return collect();
        $lines = JournalEntryLine::query()
            ->with('journalEntry')
            ->where('account_id', $this->selected->id)
            ->whereHas('journalEntry', fn ($q) => $q->whereBetween('journal_date', [$this->from, $this->to]))
            ->orderBy('journal_entry_id')
            ->get();
        $running = $this->opening;
        return $lines->map(function ($l) use (&$running) {
            $debit = (float) $l->debit;
            $credit = (float) $l->credit;
            $running += $this->selected->normal_balance === 'debit' ? $debit - $credit : $credit - $debit;
            return (object) [
                'code' => $l->journalEntry->journal_code,
                'date' => $l->journalEntry->journal_date->toDateString(),
                'description' => $l->memo ?: $l->journalEntry->description,
                'reference' => $l->journalEntry->source_reference,
                'debit' => $debit, 'credit' => $credit, 'balance' => $running,
            ];
        });
    }

    public function render()
    {
        return view('livewire.accounting.ledger-component');
    }
}