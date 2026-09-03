<?php

namespace App\Services;

use App\Models\BackOfficeCashAccount;
use App\Models\BackOfficeCashTransaction;
use App\Models\ChartOfAccount;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\ServiceTransaction;

/**
 * Posting jurnal khusus (special journals) — pola double-entry per peristiwa
 * operasional. Dipanggil oleh controller di dalam DB::transaction sehingga
 * jurnal komit/rollback atomik dengan data operasional.
 */
class AccountingPostingService
{
    public function __construct(private readonly LedgerService $ledger) {}

    /**
     * Penjualan POS (kredit/tunai). Mencatat pendapatan + HPP (COGS).
     */
    public function postSale(Sale $sale, ?int $createdBy = null): void
    {
        $date = $sale->sale_at?->toDateString() ?? now()->toDateString();
        $channel = $sale->sale_channel ?? 'toko';
        $revCode = match ($channel) {
            'cabang' => '4102',
            'partai' => '4103',
            default => '4101',
        };

        $paid = (float) $sale->paid_amount;
        $total = (float) $sale->grand_total;
        $receivable = max(0, $total - $paid);

        $cashCoa = $this->resolveCashCoa($sale->payment_method, $createdBy);

        $lines = [];
        $memoCash = $sale->payment_method === 'tempo'
            ? 'Penerimaan tunai penjualan (tempo) ' . $sale->sale_code
            : 'Penerimaan penjualan ' . $sale->sale_code;
        if ($paid > 0) {
            $lines[] = ['account' => $cashCoa, 'debit' => $paid, 'credit' => 0, 'memo' => $memoCash];
        }
        if ($receivable > 0) {
            $lines[] = ['account' => '1201', 'debit' => $receivable, 'credit' => 0, 'memo' => 'Piutang penjualan ' . $sale->sale_code];
        }
        $lines[] = ['account' => $revCode, 'debit' => 0, 'credit' => $total, 'memo' => 'Pendapatan ' . $sale->sale_code];

        // HPP / COGS
        $hpp = 0.0;
        foreach ($sale->items as $item) {
            $hpp += (float) $item->purchase_price * (float) ($item->base_quantity ?: $item->quantity);
        }
        if ($hpp > 0) {
            $lines[] = ['account' => '5101', 'debit' => $hpp, 'credit' => 0, 'memo' => 'HPP ' . $sale->sale_code];
            $lines[] = ['account' => '1301', 'debit' => 0, 'credit' => $hpp, 'memo' => 'Persediaan keluar ' . $sale->sale_code];
        }

        $this->ledger->post('sale', $sale->id, $sale->sale_code, $date, 'Penjualan ' . $sale->sale_code, $lines, $createdBy);
    }

    /**
     * Pelunasan piutang penjualan (tempo).
     */
    public function postSalePayment(Sale $sale, float $amount, string $method, $createdBy = null): void
    {
        $cashCoa = $this->resolveCashCoa($method, $createdBy);
        $this->ledger->post(
            'sale_payment',
            $sale->id . '-' . time(),
            'PAY-' . $sale->sale_code,
            now()->toDateString(),
            'Pelunasan piutang ' . $sale->sale_code,
            [
                ['account' => $cashCoa, 'debit' => round($amount, 2), 'credit' => 0, 'memo' => 'Cash masuk'],
                ['account' => '1201', 'debit' => 0, 'credit' => round($amount, 2), 'memo' => 'Kurang piutang'],
            ],
            $createdBy
        );
    }

    /**
     * Void penjualan — balik jurnal.
     */
    public function reverseSale(Sale $sale, $createdBy = null): void
    {
        $this->ledger->reverse('sale', $sale->id, $sale->sale_code, now()->toDateString(), 'Pembalikan void ' . $sale->sale_code, $createdBy);
    }

    /**
     * Pembelian PO. Persediaan masuk; kas keluar; selisih jadi hutang supplier.
     */
    public function postPurchase(PurchaseOrder $po, $createdBy = null): void
    {
        $date = $po->ordered_at?->toDateString() ?? now()->toDateString();
        $total = (float) $po->total_price;
        $paid = (float) $po->paid_amount;
        $payable = max(0, $total - $paid);

        $cashCoa = $this->resolveCashCoa($po->payment_method, $createdBy);

        $lines = [
            ['account' => '1301', 'debit' => $total, 'credit' => 0, 'memo' => 'Persediaan masuk ' . $po->po_number],
        ];
        if ($paid > 0) {
            $lines[] = ['account' => $cashCoa, 'debit' => 0, 'credit' => $paid, 'memo' => 'Pembayaran ' . $po->po_number];
        }
        if ($payable > 0) {
            $lines[] = ['account' => '2101', 'debit' => 0, 'credit' => $payable, 'memo' => 'Hutang supplier ' . $po->po_number];
        }

        $this->ledger->post('purchase', $po->id, $po->po_number, $date, 'Pembelian PO ' . $po->po_number, $lines, $createdBy);
    }

    /**
     * Bayar hutang supplier (tempo PO).
     */
    public function postPurchasePayment(PurchaseOrder $po, float $amount, string $method, $createdBy = null): void
    {
        $cashCoa = $this->resolveCashCoa($method, $createdBy);
        $this->ledger->post(
            'purchase_payment',
            $po->id . '-' . time(),
            'PO-PAY-' . $po->po_number,
            now()->toDateString(),
            'Pelunasan hutang ' . $po->po_number,
            [
                ['account' => '2101', 'debit' => round($amount, 2), 'credit' => 0, 'memo' => 'Kurang hutang'],
                ['account' => $cashCoa, 'debit' => 0, 'credit' => round($amount, 2), 'memo' => 'Kas keluar'],
            ],
            $createdBy
        );
    }

    /**
     * Kas masuk/keluar manual (back office pemasukan/pengeluaran).
     */
    public function postCash(BackOfficeCashTransaction $trx, $createdBy = null): void
    {
        $late = null;
        if ($trx->cashAccount) {
            $late = $this->ledger->cashAccountToCoa($trx->cashAccount)->code ?? null;
        }
        $cashCoa = $late ?: '1101';

        if ($trx->transaction_type === 'income') {
            $lines = [
                ['account' => $cashCoa, 'debit' => (float) $trx->amount, 'credit' => 0, 'memo' => $trx->description ?: 'Pemasukan kas'],
                ['account' => '4301', 'debit' => 0, 'credit' => (float) $trx->amount, 'memo' => 'Pendapatan lain ' . $trx->transaction_code],
            ];
        } else {
            $lines = [
                ['account' => '6101', 'debit' => (float) $trx->amount, 'credit' => 0, 'memo' => $trx->description ?: 'Pengeluaran kas'],
                ['account' => $cashCoa, 'debit' => 0, 'credit' => (float) $trx->amount, 'memo' => 'Kas keluar ' . $trx->transaction_code],
            ];
        }

        $this->ledger->post('cash', $trx->id, $trx->transaction_code, $trx->transaction_date->toDateString(), 'Transaksi kas ' . $trx->transaction_code, $lines, $createdBy);
    }

    /**
     * Mutasi kas antar akun (aset -> aset). Jumlah tetap seimbang (net zero).
     */
    public function postCashMutation(BackOfficeCashTransaction $trx, $createdBy = null): void
    {
        $from = $trx->cashAccount ? $this->ledger->cashAccountToCoa($trx->cashAccount)->code ?? null : null;
        $to = $trx->targetCashAccount ? $this->ledger->cashAccountToCoa($trx->targetCashAccount)->code ?? null : null;
        if (! $from || ! $to || $from === $to) {
            return; // mutasi internal tak mengubah total aset
        }

        $this->ledger->post(
            'cash_mutation',
            $trx->id,
            $trx->transaction_code,
            $trx->transaction_date->toDateString(),
            'Mutasi kas ' . $trx->transaction_code,
            [
                ['account' => $to, 'debit' => (float) $trx->amount, 'credit' => 0, 'memo' => 'Pindah kas masuk'],
                ['account' => $from, 'debit' => 0, 'credit' => (float) $trx->amount, 'memo' => 'Pindah kas keluar'],
            ],
            $createdBy
        );
    }

    /**
     * Kasbon karyawan (advance) — piutang karyawan.
     */
    public function postEmployeeAdvance(BackOfficeCashTransaction $trx, $createdBy = null): void
    {
        $late = $trx->cashAccount ? $this->ledger->cashAccountToCoa($trx->cashAccount)->code ?? null : null;
        $cashCoa = $late ?: '1101';

        $this->ledger->post(
            'kasbon',
            $trx->id,
            $trx->transaction_code,
            $trx->transaction_date->toDateString(),
            'Kasbon karyawan ' . $trx->transaction_code,
            [
                ['account' => '1400', 'debit' => (float) $trx->amount, 'credit' => 0, 'memo' => 'Piutang karyawan'],
                ['account' => $cashCoa, 'debit' => 0, 'credit' => (float) $trx->amount, 'memo' => 'Kas keluar kasbon'],
            ],
            $createdBy
        );
    }

    /**
     * Pendapatan jasa servis (termasuk HPP sparepart servis).
     */
    public function postService(ServiceTransaction $svc, $createdBy = null): void
    {
        $date = $svc->service_at?->toDateString() ?? now()->toDateString();
        $paid = (float) $svc->paid_amount;
        $total = (float) $svc->grand_total;
        $receivable = max(0, $total - $paid);

        $cashCoa = $this->resolveCashCoa($svc->payment_method, $createdBy);

        $lines = [];
        if ($paid > 0) {
            $lines[] = ['account' => $cashCoa, 'debit' => $paid, 'credit' => 0, 'memo' => 'Penerimaan jasa ' . $svc->service_code];
        }
        if ($receivable > 0) {
            $lines[] = ['account' => '1202', 'debit' => $receivable, 'credit' => 0, 'memo' => 'Piutang jasa ' . $svc->service_code];
        }
        $lines[] = ['account' => '4201', 'debit' => 0, 'credit' => $total, 'memo' => 'Pendapatan jasa ' . $svc->service_code];

        $hpp = 0.0;
        foreach ($svc->items as $item) {
            if ($item->item_type === 'product') {
                $hpp += (float) $item->purchase_price * (float) $item->quantity;
            }
        }
        if ($hpp > 0) {
            $lines[] = ['account' => '5201', 'debit' => $hpp, 'credit' => 0, 'memo' => 'HPP servis ' . $svc->service_code];
            $lines[] = ['account' => '1301', 'debit' => 0, 'credit' => $hpp, 'memo' => 'Persediaan sparepart servis keluar'];
        }

        $this->ledger->post('service', $svc->id, $svc->service_code, $date, 'Penjualan jasa ' . $svc->service_code, $lines, $createdBy);
    }

    /**
     * Pelunasan piutang jasa servis.
     */
    public function postServicePayment(ServiceTransaction $svc, float $amount, string $method, $createdBy = null): void
    {
        $cashCoa = $this->resolveCashCoa($method, $createdBy);
        $this->ledger->post(
            'service_payment',
            $svc->id . '-' . time(),
            'PAY-JASA-' . $svc->service_code,
            now()->toDateString(),
            'Pelunasan piutang jasa ' . $svc->service_code,
            [
                ['account' => $cashCoa, 'debit' => round($amount, 2), 'credit' => 0, 'memo' => 'Cash masuk'],
                ['account' => '1202', 'debit' => 0, 'credit' => round($amount, 2), 'memo' => 'Kurang piutang'],
            ],
            $createdBy
        );
    }

    public function reverseService(ServiceTransaction $svc, $createdBy = null): void
    {
        $this->ledger->reverse('service', $svc->id, $svc->service_code, now()->toDateString(), 'Pembalikan void ' . $svc->service_code, $createdBy);
    }

    /**
     * Penyesuaian stok hasil stock opname. Selisih mengubah nilai persediaan.
     * shortage (hitung-real < 0)  -> Dr Beban Selisih Stok, Cr Persediaan
     * surplus  (hitung-real > 0)  -> Dr Persediaan, Cr Pendapatan Lain
     */
    public function postStockAdjustment(string $referenceCode, \App\Models\StockOpnameItem $item, float $differenceValue, $createdBy = null): void
    {
        if (abs($differenceValue) < 0.009) {
            return;
        }
        $shortage = $differenceValue < 0;
        $this->ledger->post(
            'stock_opname',
            $item->id,
            $referenceCode,
            now()->toDateString(),
            ($shortage ? 'Selisih kurang stok' : 'Selisih lebih stok') . ' ' . $referenceCode,
            $shortage
                ? [
                    ['account' => '6101', 'debit' => abs($differenceValue), 'credit' => 0, 'memo' => 'Beban selisih stok'],
                    ['account' => '1301', 'debit' => 0, 'credit' => abs($differenceValue), 'memo' => 'Kurang persediaan'],
                ]
                : [
                    ['account' => '1301', 'debit' => abs($differenceValue), 'credit' => 0, 'memo' => 'Lebih persediaan'],
                    ['account' => '4301', 'debit' => 0, 'credit' => abs($differenceValue), 'memo' => 'Pendapatan selisih'],
                ],
            $createdBy
        );
    }

    private function resolveCashCoa(?string $method, $createdBy = null): string
    {
        $cashAccount = $this->ledger->cashAccountForMethod($method ?: 'cash');
        if ($cashAccount) {
            return $this->ledger->cashAccountToCoa($cashAccount)->code;
        }
        return '1101';
    }
}
