<?php

namespace App\Http\Livewire;

use App\Models\BranchTransfer;
use Livewire\Component;

class TransferPrintComponent extends Component
{
    public function render()
    {
        return view('livewire.branches.transfer-print-component', [
            'title' => 'Surat Jalan Transfer Stok',
            'date' => now()->format('d/m/Y'),
        ]);
    }
}