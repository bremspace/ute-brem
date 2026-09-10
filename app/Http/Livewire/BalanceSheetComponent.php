<?php

namespace App\Http\Livewire;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Livewire\Component;

class BalanceSheetComponent extends Component
{
    public string $asOf = '';

    public function mount(): void
    {
        $this->asOf = Carbon::now()->toDateString();
    }

    private function balanceAsOf(ChartOfAccount $acc): float
    {
        $opening = (float) $acc->opening_balance;
        $sum = JournalEntryLine::query()
            ->where('account_id', $acc->id)
            ->whereHas('journalEntry', fn ($q) => $q->whereDate('journal_date', '<=', $this->asOf))
            ->selectRaw('COALESCE(SUM(debit),0) AS d, COALESCE(SUM(credit),0) AS c')
            ->first();
        if ($acc->normal_balance === 'debit') {
            return $opening + (float) $sum->d - (float) $sum->c;
        }
        return -$opening + (float) $sum->c - (float) $sum->d;
    }

    private function balanceSheetGroups(array $categories): array
    {
        $parents = ChartOfAccount::whereIn('category', $categories)->whereNull('parent_id')->orderBy('code')->get();
        $groups = []; $total = 0.0;
        foreach ($parents as $parent) {
            $children = ChartOfAccount::where('parent_id', $parent->id)->where('is_postable', true)->orderBy('code')->get();
            $rows = []; $subtotal = 0.0;
            foreach ($children as $child) {
                $bal = $this->balanceAsOf($child);
                $rows[] = (object) ['code' => $child->code, 'name' => $child->name, 'balance' => $bal];
                $subtotal += $bal;
            }
            $groups[] = (object) ['name' => $parent->name, 'rows' => $rows, 'subtotal' => $subtotal];
            $total += $subtotal;
        }
        return ['groups' => $groups, 'total' => $total];
    }

    public function render()
    {
        $assetGroups = $this->balanceSheetGroups(['asset']);
        $liabilityGroups = $this->balanceSheetGroups(['liability']);
        $equityGroups = $this->balanceSheetGroups(['equity']);
        $totalAssets = $assetGroups['total'];
        $totalLiabilities = $liabilityGroups['total'];
        $totalEquity = $equityGroups['total'];

        return view('livewire.accounting.balance-sheet-component', compact('assetGroups', 'liabilityGroups', 'equityGroups', 'totalAssets', 'totalLiabilities', 'totalEquity'));
    }
}