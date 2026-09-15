<?php

namespace App\Http\Livewire;

use App\Models\BackOfficeCashAccount;
use App\Models\BackOfficeCashTransaction;
use App\Models\BackOfficeStockDocument;
use Livewire\Component;

class BackOfficeDashboardComponent extends Component
{
    public function getCashAccountsProperty()
    {
        return BackOfficeCashAccount::orderBy('name')->get();
    }

    public function getRecentCashTransactionsProperty()
    {
        return BackOfficeCashTransaction::with(['cashAccount', 'targetCashAccount', 'costCategory', 'employee'])
            ->latest('transaction_date')
            ->latest('id')
            ->limit(12)
            ->get();
    }

    public function getRecentStockDocumentsProperty()
    {
        return BackOfficeStockDocument::with(['product', 'location'])
            ->latest('document_date')
            ->latest('id')
            ->limit(12)
            ->get();
    }

    public function render()
    {
        return view('livewire.back-office.back-office-dashboard-component');
    }
}