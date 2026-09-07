import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import {
  BarChart3,
  TrendingUp,
  TrendingDown,
  ShoppingCart,
  Truck,
  Wallet,
  Wrench,
  Calendar,
  Download,
  Receipt,
  CreditCard,
  Package,
  FileText,
  ArrowUpRight,
} from 'lucide-react';
import clsx from 'clsx';
import {
  Button,
  Card,
  CardHeader,
  Input,
  StatCard,
  Tabs,
  TableContainer,
  TableHeader,
  TableBase,
  TableRow,
  TableEmpty,
  StatusBadge,
} from '../components/ui';
import type { TabItem } from '../components/ui';

type TabId = 'ringkasan' | 'penjualan' | 'pembelian' | 'labarugi' | 'kas' | 'servis';

const TABS: TabItem[] = [
  { id: 'ringkasan', label: 'Ringkasan', icon: <BarChart3 className="w-4 h-4" /> },
  { id: 'penjualan', label: 'Penjualan', icon: <ShoppingCart className="w-4 h-4" /> },
  { id: 'pembelian', label: 'Pembelian (PO)', icon: <Truck className="w-4 h-4" /> },
  { id: 'labarugi', label: 'Laba Rugi', icon: <TrendingUp className="w-4 h-4" /> },
  { id: 'kas', label: 'Kas', icon: <Wallet className="w-4 h-4" /> },
  { id: 'servis', label: 'Servis', icon: <Wrench className="w-4 h-4" /> },
];

const CASH_TYPE_LABELS: Record<string, string> = {
  income: 'Pemasukan',
  expense: 'Pengeluaran',
  mutation: 'Mutasi',
  employee_advance: 'Kasbon',
};

const SERVICE_STATUS_LABELS: Record<string, string> = {
  process: 'Dikerjakan',
  done: 'Selesai',
  taken: 'Diambil',
  cancelled: 'Batal',
};

function rp(n: number): string {
  return `Rp ${n.toLocaleString('id-ID')}`;
}

function fmtDate(iso: string): string {
  const d = new Date(iso);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

function startOfMonth(): string {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-01`;
}

function endOfMonth(): string {
  const d = new Date();
  const last = new Date(d.getFullYear(), d.getMonth() + 1, 0);
  return `${last.getFullYear()}-${String(last.getMonth() + 1).padStart(2, '0')}-${String(last.getDate()).padStart(2, '0')}`;
}

function inRange(dateStr: string, from: string, to: string): boolean {
  const d = dateStr.slice(0, 10);
  return d >= from && d <= to;
}

function downloadCsv(filename: string, rows: string[][]) {
  const csv = rows.map(r => r.map(c => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\n');
  const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
}

export const ReportsView: React.FC = () => {
  const {
    sales,
    serviceTransactions,
    purchaseOrders,
    cashTransactions,
    products,
    customers,
  } = useApp();

  const [activeTab, setActiveTab] = useState<TabId>('ringkasan');
  const [dateFrom, setDateFrom] = useState(startOfMonth);
  const [dateTo, setDateTo] = useState(endOfMonth);

  const filteredSales = useMemo(
    () => sales.filter(s => inRange(s.sale_at, dateFrom, dateTo)),
    [sales, dateFrom, dateTo],
  );

  const filteredServices = useMemo(
    () => serviceTransactions.filter(s => inRange(s.created_at, dateFrom, dateTo)),
    [serviceTransactions, dateFrom, dateTo],
  );

  const filteredPOs = useMemo(
    () => purchaseOrders.filter(p => inRange(p.order_date, dateFrom, dateTo)),
    [purchaseOrders, dateFrom, dateTo],
  );

  const filteredCash = useMemo(
    () => cashTransactions.filter(c => inRange(c.transaction_date, dateFrom, dateTo)),
    [cashTransactions, dateFrom, dateTo],
  );

  const paidSales = useMemo(() => filteredSales.filter(s => s.status === 'paid'), [filteredSales]);
  const nonCancelledServices = useMemo(
    () => filteredServices.filter(s => s.status !== 'cancelled'),
    [filteredServices],
  );

  // KPI values
  const totalPenjualan = useMemo(() => paidSales.reduce((s, v) => s + v.grand_total, 0), [paidSales]);
  const totalTransaksi = paidSales.length;
  const labaKotor = useMemo(
    () => paidSales.reduce((s, v) => s + v.items.reduce((is2, it) => is2 + (it.unit_price - it.purchase_price) * it.quantity, 0), 0),
    [paidSales],
  );
  const pendapatanServis = useMemo(() => nonCancelledServices.reduce((s, v) => s + v.grand_total, 0), [nonCancelledServices]);

  // Top products
  const topProducts = useMemo(() => {
    const map = new Map<string, { name: string; qty: number; revenue: number }>();
    paidSales.forEach(s => {
      s.items.forEach(it => {
        const existing = map.get(it.product_name) || { name: it.product_name, qty: 0, revenue: 0 };
        existing.qty += it.quantity;
        existing.revenue += it.subtotal;
        map.set(it.product_name, existing);
      });
    });
    return Array.from(map.values()).sort((a, b) => b.qty - a.qty).slice(0, 8);
  }, [paidSales]);

  // Payment method breakdown
  const methodBreakdown = useMemo(() => {
    const map = new Map<string, number>();
    paidSales.forEach(s => {
      map.set(s.payment_method, (map.get(s.payment_method) || 0) + s.grand_total);
    });
    return Array.from(map.entries()).sort((a, b) => b[1] - a[1]);
  }, [paidSales]);

  // P&L
  const pendapatan = totalPenjualan + pendapatanServis;
  const hpp = useMemo(() => paidSales.reduce((s, v) => s + v.items.reduce((is2, it) => is2 + it.purchase_price * it.quantity, 0), 0), [paidSales]);
  const grossProfit = pendapatan - hpp;
  const totalExpense = useMemo(() => filteredCash.filter(c => c.transaction_type === 'expense').reduce((s, c) => s + c.amount, 0), [filteredCash]);
  const totalIncomeOther = useMemo(() => filteredCash.filter(c => c.transaction_type === 'income').reduce((s, c) => s + c.amount, 0), [filteredCash]);
  const labaBersih = grossProfit - totalExpense + totalIncomeOther;

  const handleExport = () => {
    const dateLabel = `${dateFrom}_sampai_${dateTo}`;
    switch (activeTab) {
      case 'penjualan': {
        const rows: string[][] = [
          ['No Nota', 'Tanggal', 'Pelanggan', 'Channel', 'Items', 'Subtotal', 'Diskon', 'Total', 'Metode', 'Status'],
          ...filteredSales.map((s, i) => [
            String(i + 1), s.sale_code, fmtDate(s.sale_at), s.customer?.name || 'Walk-in',
            s.sale_channel, String(s.items_count), String(s.subtotal), String(s.discount_total),
            String(s.grand_total), s.payment_method, s.status,
          ]),
          ['', '', '', '', '', '', '', 'TOTAL', String(paidSales.reduce((a, b) => a + b.grand_total, 0)), '', ''],
        ];
        downloadCsv(`penjualan_${dateLabel}.csv`, rows);
        break;
      }
      case 'pembelian': {
        const rows: string[][] = [
          ['No PO', 'Supplier', 'Tanggal', 'Total', 'Bayar', 'Sisa', 'Status PO', 'Status Bayar'],
          ...filteredPOs.map((p, i) => [
            String(i + 1), p.po_number, p.supplier?.name || '-', fmtDate(p.order_date),
            String(p.total_amount), String(p.paid_amount), String(p.total_amount - p.paid_amount),
            p.status, p.payment_status,
          ]),
          ['', '', '', 'TOTAL', String(filteredPOs.reduce((a, b) => a + b.total_amount, 0)),
           String(filteredPOs.reduce((a, b) => a + b.paid_amount, 0)), '', '', ''],
        ];
        downloadCsv(`pembelian_${dateLabel}.csv`, rows);
        break;
      }
      case 'kas': {
        const rows: string[][] = [
          ['Tanggal', 'Kode', 'Tipe', 'Akun', 'Jumlah', 'Deskripsi'],
          ...filteredCash.map(c => [
            fmtDate(c.transaction_date), c.transaction_code, c.transaction_type,
            c.cash_account?.name || '-', String(c.amount), c.description || '-',
          ]),
        ];
        downloadCsv(`kas_${dateLabel}.csv`, rows);
        break;
      }
      case 'servis': {
        const rows: string[][] = [
          ['No', 'Kode', 'Tipe Device', 'Status', 'Subtotal', 'Total', 'Pajak'],
          ...filteredServices.map((s, i) => [
            String(i + 1), s.service_code, `${s.device_brand} ${s.device_type}`,
            s.status, String(s.subtotal), String(s.grand_total), String(s.tax_total),
          ]),
        ];
        downloadCsv(`servis_${dateLabel}.csv`, rows);
        break;
      }
      case 'labarugi': {
        const rows: string[][] = [
          ['Laporan Laba Rugi', `${dateFrom} s/d ${dateTo}`],
          ['Pendapatan Penjualan', String(totalPenjualan)],
          ['Pendapatan Servis', String(pendapatanServis)],
          ['Total Pendapatan', String(pendapatan)],
          ['HPP', String(hpp)],
          ['Laba Kotor', String(grossProfit)],
          ['Beban (Pengeluaran)', String(totalExpense)],
          ['Pemasukan Lain', String(totalIncomeOther)],
          ['Laba Bersih', String(labaBersih)],
        ];
        downloadCsv(`laba_rugi_${dateLabel}.csv`, rows);
        break;
      }
      default: {
        const rows: string[][] = [
          ['Metrik', 'Nilai'],
          ['Total Penjualan', String(totalPenjualan)],
          ['Total Transaksi', String(totalTransaksi)],
          ['Laba Kotor', String(labaKotor)],
          ['Pendapatan Servis', String(pendapatanServis)],
        ];
        downloadCsv(`ringkasan_${dateLabel}.csv`, rows);
      }
    }
  };

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-slate-900 tracking-tight">Laporan & Laba Rugi</h1>
          <p className="text-xs text-slate-500 mt-1">
            Analisis penjualan, pembelian, servis, dan laba-rugi bisnis pada rentang waktu tertentu.
          </p>
        </div>
        <Button
          onClick={handleExport}
          variant="success"
          icon={<Download className="w-4 h-4" />}
        >
          Export CSV
        </Button>
      </div>

      {/* Date Range Filter */}
      <Card className="p-4">
        <div className="flex flex-col sm:flex-row items-start sm:items-center gap-4">
          <div className="flex items-center gap-2 text-xs font-bold text-slate-600">
            <Calendar className="w-4 h-4 text-primary-600" />
            Rentang Waktu:
          </div>
          <div className="flex items-center gap-2">
            <Input
              type="date"
              value={dateFrom}
              onChange={e => setDateFrom(e.target.value)}
              className="!w-auto"
            />
            <span className="text-xs font-bold text-slate-400">s/d</span>
            <Input
              type="date"
              value={dateTo}
              onChange={e => setDateTo(e.target.value)}
              className="!w-auto"
            />
          </div>
        </div>
      </Card>

      {/* Tab Navigation */}
      <Tabs
        tabs={TABS}
        activeTab={activeTab}
        onChange={(id) => setActiveTab(id as TabId)}
        variant="pill"
      />

      {/* === RINGKASAN === */}
      {activeTab === 'ringkasan' && (
        <div className="space-y-6">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <StatCard
              label="Total Penjualan"
              value={rp(totalPenjualan)}
              description={`${totalTransaksi} transaksi`}
              icon={<TrendingUp className="w-4 h-4" />}
              color="success"
            />
            <StatCard
              label="Total Transaksi"
              value={String(totalTransaksi)}
              description="transaksi tercatat"
              icon={<ShoppingCart className="w-4 h-4" />}
              color="primary"
            />
            <StatCard
              label="Laba Kotor"
              value={rp(labaKotor)}
              description={`Margin: ${totalPenjualan > 0 ? Math.round((labaKotor / totalPenjualan) * 100) : 0}%`}
              icon={<ArrowUpRight className="w-4 h-4" />}
              color="info"
            />
            <StatCard
              label="Pendapatan Servis"
              value={rp(pendapatanServis)}
              description={`${nonCancelledServices.length} transaksi servis`}
              icon={<Wrench className="w-4 h-4" />}
              color="warning"
            />
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Top Produk */}
            <TableContainer>
              <TableHeader icon={<Package className="w-4 h-4 text-primary-600" />}>
                Top Produk Terlaris
              </TableHeader>
              <TableBase
                columns={[
                  { header: '#', className: 'px-5' },
                  { header: 'Produk' },
                  { header: 'Qty Terjual', align: 'right' },
                  { header: 'Omset', align: 'right' },
                ]}
                emptyMessage="Tidak ada data penjualan di rentang ini."
                colSpan={4}
              >
                {topProducts.map((p, i) => (
                  <TableRow key={i}>
                    <td className="px-5 py-2.5 font-bold text-slate-400">{i + 1}</td>
                    <td className="px-4 py-2.5 font-medium text-slate-800">{p.name}</td>
                    <td className="px-4 py-2.5 text-right font-bold">{p.qty}</td>
                    <td className="px-4 py-2.5 text-right font-bold text-primary-700">{rp(p.revenue)}</td>
                  </TableRow>
                ))}
              </TableBase>
            </TableContainer>

            {/* Metode Pembayaran */}
            <Card noPadding>
              <CardHeader>
                <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
                  <CreditCard className="w-4 h-4 text-primary-600" />
                  Transaksi per Metode
                </div>
              </CardHeader>
              <div className="p-5 space-y-3">
                {methodBreakdown.map(([method, amount]) => (
                  <div key={method} className="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span className="text-xs font-bold uppercase text-slate-700">{method}</span>
                    <span className="text-xs font-black text-slate-900">{rp(amount)}</span>
                  </div>
                ))}
                {methodBreakdown.length === 0 && (
                  <div className="h-24 flex items-center justify-center text-slate-400 text-xs font-semibold">
                    Belum ada data metode pembayaran.
                  </div>
                )}
              </div>
            </Card>
          </div>
        </div>
      )}

      {/* === PENJUALAN === */}
      {activeTab === 'penjualan' && (
        <TableContainer>
          <TableHeader icon={<Receipt className="w-4 h-4 text-primary-600" />} count={filteredSales.length}>
            Daftar Penjualan
          </TableHeader>
          <TableBase
            columns={[
              { header: '#', className: 'px-5' },
              { header: 'No Nota' },
              { header: 'Tanggal' },
              { header: 'Pelanggan' },
              { header: 'Channel' },
              { header: 'Items', align: 'right' },
              { header: 'Subtotal', align: 'right' },
              { header: 'Diskon', align: 'right' },
              { header: 'Total', align: 'right' },
              { header: 'Metode' },
              { header: 'Status' },
            ]}
            emptyMessage="Tidak ada data penjualan di rentang waktu ini."
            colSpan={11}
          >
            {filteredSales.map((s, i) => (
              <TableRow key={s.id}>
                <td className="px-5 py-2.5 font-bold text-slate-400">{i + 1}</td>
                <td className="px-4 py-2.5 font-mono font-bold text-primary-700">{s.sale_code}</td>
                <td className="px-4 py-2.5">{fmtDate(s.sale_at)}</td>
                <td className="px-4 py-2.5 font-medium text-slate-800">{s.customer?.name || 'Walk-in'}</td>
                <td className="px-4 py-2.5 uppercase font-semibold text-slate-600">{s.sale_channel}</td>
                <td className="px-4 py-2.5 text-right">{s.items_count}</td>
                <td className="px-4 py-2.5 text-right">{rp(s.subtotal)}</td>
                <td className="px-4 py-2.5 text-right text-red-600">{s.discount_total > 0 ? `-${rp(s.discount_total)}` : '-'}</td>
                <td className="px-4 py-2.5 text-right font-bold text-slate-900">{rp(s.grand_total)}</td>
                <td className="px-4 py-2.5 uppercase font-semibold text-slate-600">{s.payment_method}</td>
                <td className="px-4 py-2.5">
                  <StatusBadge status={s.status} />
                </td>
              </TableRow>
            ))}
            {filteredSales.length === 0 && (
              <TableEmpty
                colSpan={11}
                message="Tidak ada data penjualan di rentang waktu ini."
              />
            )}
            {paidSales.length > 0 && (
              <tr className="bg-slate-50 border-t-2 border-slate-200 font-bold text-slate-800">
                <td colSpan={6} className="px-5 py-3 text-xs text-slate-500">TOTAL</td>
                <td className="px-4 py-3 text-right">{filteredSales.reduce((a, b) => a + b.items_count, 0)}</td>
                <td className="px-4 py-3 text-right">{rp(filteredSales.reduce((a, b) => a + b.subtotal, 0))}</td>
                <td className="px-4 py-3 text-right text-red-600">{rp(filteredSales.reduce((a, b) => a + b.discount_total, 0))}</td>
                <td className="px-4 py-3 text-right text-primary-700 font-black">{rp(paidSales.reduce((a, b) => a + b.grand_total, 0))}</td>
                <td colSpan={2}></td>
              </tr>
            )}
          </TableBase>
        </TableContainer>
      )}

      {/* === PEMBELIAN (PO) === */}
      {activeTab === 'pembelian' && (
        <TableContainer>
          <TableHeader icon={<Truck className="w-4 h-4 text-primary-600" />} count={filteredPOs.length}>
            Daftar Purchase Order
          </TableHeader>
          <TableBase
            columns={[
              { header: '#', className: 'px-5' },
              { header: 'No PO' },
              { header: 'Supplier' },
              { header: 'Tanggal' },
              { header: 'Total', align: 'right' },
              { header: 'Bayar', align: 'right' },
              { header: 'Sisa', align: 'right' },
              { header: 'Status PO' },
              { header: 'Status Bayar' },
            ]}
            emptyMessage="Tidak ada data purchase order di rentang waktu ini."
            colSpan={9}
          >
            {filteredPOs.map((p, i) => (
              <TableRow key={p.id}>
                <td className="px-5 py-2.5 font-bold text-slate-400">{i + 1}</td>
                <td className="px-4 py-2.5 font-mono font-bold text-primary-700">{p.po_number}</td>
                <td className="px-4 py-2.5 font-medium text-slate-800">{p.supplier?.name || '-'}</td>
                <td className="px-4 py-2.5">{fmtDate(p.order_date)}</td>
                <td className="px-4 py-2.5 text-right font-bold text-slate-900">{rp(p.total_amount)}</td>
                <td className="px-4 py-2.5 text-right text-emerald-700 font-semibold">{rp(p.paid_amount)}</td>
                <td className="px-4 py-2.5 text-right text-red-600 font-semibold">{rp(Math.max(0, p.total_amount - p.paid_amount))}</td>
                <td className="px-4 py-2.5">
                  <StatusBadge status={p.status} />
                </td>
                <td className="px-4 py-2.5">
                  <StatusBadge status={p.payment_status} />
                </td>
              </TableRow>
            ))}
            {filteredPOs.length === 0 && (
              <TableEmpty
                colSpan={9}
                message="Tidak ada data purchase order di rentang waktu ini."
              />
            )}
            {filteredPOs.length > 0 && (
              <tr className="bg-slate-50 border-t-2 border-slate-200 font-bold text-slate-800">
                <td colSpan={4} className="px-5 py-3 text-xs text-slate-500">TOTAL</td>
                <td className="px-4 py-3 text-right">{rp(filteredPOs.reduce((a, b) => a + b.total_amount, 0))}</td>
                <td className="px-4 py-3 text-right text-emerald-700">{rp(filteredPOs.reduce((a, b) => a + b.paid_amount, 0))}</td>
                <td className="px-4 py-3 text-right text-red-600">{rp(filteredPOs.reduce((a, b) => a + Math.max(0, b.total_amount - b.paid_amount), 0))}</td>
                <td colSpan={2}></td>
              </tr>
            )}
          </TableBase>
        </TableContainer>
      )}

      {/* === LABA RUGI === */}
      {activeTab === 'labarugi' && (
        <div className="max-w-2xl mx-auto space-y-4">
          <TableContainer>
            <TableHeader icon={<FileText className="w-4 h-4 text-primary-600" />}>
              Laporan Laba Rugi — {fmtDate(dateFrom)} s/d {fmtDate(dateTo)}
            </TableHeader>
            <div className="p-6 space-y-4">
              {/* Pendapatan */}
              <div className="space-y-2">
                <h4 className="text-xs font-bold text-slate-500 uppercase tracking-wider">Pendapatan</h4>
                <div className="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                  <span className="text-xs font-semibold text-slate-700">Penjualan (non-void)</span>
                  <span className="text-xs font-bold text-slate-900">{rp(totalPenjualan)}</span>
                </div>
                <div className="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                  <span className="text-xs font-semibold text-slate-700">Pendapatan Servis</span>
                  <span className="text-xs font-bold text-slate-900">{rp(pendapatanServis)}</span>
                </div>
                <div className="flex justify-between items-center p-3 rounded-xl bg-primary-50 border border-primary-200">
                  <span className="text-xs font-bold text-primary-800">Total Pendapatan</span>
                  <span className="text-sm font-black text-primary-700">{rp(pendapatan)}</span>
                </div>
              </div>

              {/* HPP */}
              <div className="space-y-2">
                <h4 className="text-xs font-bold text-slate-500 uppercase tracking-wider">Harga Pokok Penjualan (HPP)</h4>
                <div className="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                  <span className="text-xs font-semibold text-slate-700">HPP (purchase_price × qty)</span>
                  <span className="text-xs font-bold text-red-600">({rp(hpp)})</span>
                </div>
              </div>

              {/* Laba Kotor */}
              <div className={clsx(
                'flex justify-between items-center p-4 rounded-xl border-2',
                labaKotor >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200',
              )}>
                <span className="text-sm font-bold text-slate-800">Laba Kotor</span>
                <span className={clsx('text-lg font-black', labaKotor >= 0 ? 'text-emerald-700' : 'text-red-700')}>{rp(labaKotor)}</span>
              </div>

              {/* Beban & Pemasukan Lain */}
              <div className="space-y-2">
                <h4 className="text-xs font-bold text-slate-500 uppercase tracking-wider">Komponen Lain</h4>
                <div className="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                  <span className="text-xs font-semibold text-slate-700">Beban (Pengeluaran)</span>
                  <span className="text-xs font-bold text-red-600">({rp(totalExpense)})</span>
                </div>
                <div className="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                  <span className="text-xs font-semibold text-slate-700">Pemasukan Lain</span>
                  <span className="text-xs font-bold text-emerald-700">+{rp(totalIncomeOther)}</span>
                </div>
              </div>

              {/* Laba Bersih */}
              <div className={clsx(
                'flex justify-between items-center p-5 rounded-xl border-2',
                labaBersih >= 0 ? 'bg-emerald-50 border-emerald-300' : 'bg-red-50 border-red-300',
              )}>
                <div className="flex items-center gap-2">
                  {labaBersih >= 0 ? (
                    <TrendingUp className="w-5 h-5 text-emerald-600" />
                  ) : (
                    <TrendingDown className="w-5 h-5 text-red-600" />
                  )}
                  <span className="text-sm font-black text-slate-900">Laba Bersih</span>
                </div>
                <span className={clsx('text-xl font-black', labaBersih >= 0 ? 'text-emerald-700' : 'text-red-700')}>{rp(labaBersih)}</span>
              </div>
            </div>
          </TableContainer>
        </div>
      )}

      {/* === KAS === */}
      {activeTab === 'kas' && (
        <TableContainer>
          <TableHeader icon={<Wallet className="w-4 h-4 text-primary-600" />} count={filteredCash.length}>
            Mutasi Kas
          </TableHeader>
          <TableBase
            columns={[
              { header: '#', className: 'px-5' },
              { header: 'Tanggal' },
              { header: 'Kode' },
              { header: 'Tipe' },
              { header: 'Akun' },
              { header: 'Jumlah', align: 'right' },
              { header: 'Deskripsi' },
            ]}
            emptyMessage="Tidak ada transaksi kas di rentang waktu ini."
            colSpan={7}
          >
            {filteredCash.map((c, i) => (
              <TableRow key={c.id}>
                <td className="px-5 py-2.5 font-bold text-slate-400">{i + 1}</td>
                <td className="px-4 py-2.5">{fmtDate(c.transaction_date)}</td>
                <td className="px-4 py-2.5 font-mono font-bold text-slate-600">{c.transaction_code}</td>
                <td className="px-4 py-2.5">
                  <StatusBadge status={c.transaction_type}>
                    {CASH_TYPE_LABELS[c.transaction_type] || c.transaction_type}
                  </StatusBadge>
                </td>
                <td className="px-4 py-2.5 font-medium text-slate-800">{c.cash_account?.name || '-'}</td>
                <td className={clsx(
                  'px-4 py-2.5 text-right font-bold',
                  c.transaction_type === 'income' ? 'text-emerald-700' : c.transaction_type === 'expense' ? 'text-red-600' : 'text-slate-600',
                )}>
                  {c.transaction_type === 'expense' ? '-' : c.transaction_type === 'income' ? '+' : ''}{rp(c.amount)}
                </td>
                <td className="px-4 py-2.5 text-slate-600 max-w-[200px] truncate">{c.description || '-'}</td>
              </TableRow>
            ))}
            {filteredCash.length === 0 && (
              <TableEmpty
                colSpan={7}
                message="Tidak ada transaksi kas di rentang waktu ini."
              />
            )}
            {filteredCash.length > 0 && (
              <tr className="bg-slate-50 border-t-2 border-slate-200 font-bold text-slate-800">
                <td colSpan={5} className="px-5 py-3 text-xs text-slate-500">TOTAL</td>
                <td className="px-4 py-3 text-right">
                  <span className="text-emerald-700">+{rp(totalIncomeOther)}</span>
                  <span className="mx-1 text-slate-300">/</span>
                  <span className="text-red-600">-{rp(totalExpense)}</span>
                </td>
                <td></td>
              </tr>
            )}
          </TableBase>
        </TableContainer>
      )}

      {/* === SERVIS === */}
      {activeTab === 'servis' && (
        <TableContainer>
          <TableHeader icon={<Wrench className="w-4 h-4 text-primary-600" />} count={filteredServices.length}>
            Daftar Servis
          </TableHeader>
          <TableBase
            columns={[
              { header: '#', className: 'px-5' },
              { header: 'No' },
              { header: 'Pelanggan' },
              { header: 'Device' },
              { header: 'Status' },
              { header: 'Estimasi', align: 'right' },
              { header: 'Total', align: 'right' },
              { header: 'Bayar', align: 'right' },
            ]}
            emptyMessage="Tidak ada data servis di rentang waktu ini."
            colSpan={8}
          >
            {filteredServices.map((s, i) => (
              <TableRow key={s.id}>
                <td className="px-5 py-2.5 font-bold text-slate-400">{i + 1}</td>
                <td className="px-4 py-2.5 font-mono font-bold text-primary-700">{s.service_code}</td>
                <td className="px-4 py-2.5 text-slate-600">{s.device_brand} {s.device_type}</td>
                <td className="px-4 py-2.5">
                  <StatusBadge status={s.status}>
                    {SERVICE_STATUS_LABELS[s.status] || s.status}
                  </StatusBadge>
                </td>
                <td className="px-4 py-2.5 text-right font-bold text-slate-600">{rp(s.subtotal)}</td>
                <td className="px-4 py-2.5 text-right font-bold text-slate-900">{rp(s.grand_total)}</td>
                <td className="px-4 py-2.5 text-right font-bold text-slate-600">{rp(s.tax_total)}</td>
              </TableRow>
            ))}
            {filteredServices.length === 0 && (
              <TableEmpty
                colSpan={8}
                message="Tidak ada data servis di rentang waktu ini."
              />
            )}
          </TableBase>
        </TableContainer>
      )}
    </div>
  );
};
