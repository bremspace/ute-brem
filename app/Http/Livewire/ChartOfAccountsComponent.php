<?php

namespace App\Http\Livewire;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Livewire\Component;

class ChartOfAccountsComponent extends Component
{
    public function getAccountsProperty()
    {
        return ChartOfAccount::with('children')->orderBy('code')->get();
    }

    public function getBalancesProperty(): array
    {
        $map = [];
        ChartOfAccount::where('is_postable', true)->orderBy('code')->get()->each(function ($acc) use (&$map) {
            $opening = (float) $acc->opening_balance;
            $sum = JournalEntryLine::query()
                ->where('account_id', $acc->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereDate('journal_date', '<=', now()->toDateString()))
                ->selectRaw('COALESCE(SUM(debit),0) AS d, COALESCE(SUM(credit),0) AS c')
                ->first();
            if ($acc->normal_balance === 'debit') {
                $map[$acc->code] = $opening + (float) $sum->d - (float) $sum->c;
            } else {
                $map[$acc->code] = -$opening + (float) $sum->c - (float) $sum->d;
            }
        });
        return $map;
    }

    public function render()
    {
        return view('livewire.accounting.chart-of-accounts-component');
    }
}