<?php

namespace App\Http\Livewire;

use App\Models\JournalEntry;
use Carbon\Carbon;
use Livewire\Component;

class JournalComponent extends Component
{
    public string $from = '';
    public string $to = '';

    public function mount(): void
    {
        $this->from = Carbon::now()->startOfMonth()->toDateString();
        $this->to = Carbon::now()->endOfMonth()->toDateString();
    }

    public function getEntriesProperty()
    {
        return JournalEntry::with(['lines.account', 'creator'])
            ->whereBetween('journal_date', [$this->from, $this->to])
            ->orderByDesc('journal_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn ($e) => $e->journal_date->toDateString());
    }

    public function getTotalDebitProperty(): float
    {
        return (float) JournalEntry::whereBetween('journal_date', [$this->from, $this->to])->sum('debit_total');
    }

    public function getTotalCreditProperty(): float
    {
        return (float) JournalEntry::whereBetween('journal_date', [$this->from, $this->to])->sum('credit_total');
    }

    public function render()
    {
        return view('livewire.accounting.journal-component');
    }
}