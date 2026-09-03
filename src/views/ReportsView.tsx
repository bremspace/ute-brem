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
  ArrowDownRight
} from 'lucide-react';

type TabId = 'ringkasan' | 'penjualan' | 'pembelian' | 'labarugi' | 'kas' | 'servis';

const TABS: { id: TabId; label: string; icon: React.ReactNode }[] = [
  { id: 'ringkasan', label: 'Ringkasan', icon: <BarChart3 className="w-4 h-4" /> },
  { id: 'penjualan', label: 'Penjualan', icon: <ShoppingCart className="w-4 h-4" /> },
  { id: 'pembelian', label: 'Pembelian (PO)', icon: <Truck className="w-4 h-4" /> },
  { id: 'labarugi', label: 'Laba Rugi', icon: <TrendingUp className="w-4 h-4" /> },
  { id: 'kas', label: 'Kas', icon: <Wallet className="w-4 h-4" /> },
  { id: 'servis', label: 'Servis', icon: <Wrench className="w-4 h-4" /> }
];

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
    customers
  } = useApp();

  const [activeTab, setActiveTab] = useState<TabId>('ringkasan');
  const [dateFrom, setDateFrom] = useState(startOfMonth);
  const [dateTo, setDateTo] = useState(endOfMonth);

  const filteredSales = useMemo(
    () => sales.filter(s => inRange(s.sale_at, dateFrom, dateTo)),
    [sales, dateFrom, dateTo]
  );

  const filteredServices = useMemo(
    () => serviceTransactions.filter(s => inRange(s.service_at, dateFrom, dateTo)),
    [serviceTransactions, dateFrom, dateTo]
  );

  const filteredPOs = useMemo(
    () => purchaseOrders.filter(p => inRange(p.order_date, dateFrom, dateTo)),
    [purchaseOrders, dateFrom, dateTo]
  );

  const filteredCash = useMemo(
    () => cashTransactions.filter(c => inRange(c.transaction_date, dateFrom, dateTo)),
    [cashTransactions, dateFrom, dateTo]
  );

  const paidSales = useMemo(() => filteredSales.filter(s => s.status === 'paid'), [filteredSales]);
  const nonCancelledServices = useMemo(
    () => filteredServices.filter(s => s.status !== 'cancelled'),
    [filteredServices]
  );

  // KPI values
  const totalPenjualan = useMemo(() => paidSales.reduce((s, v) => s + v.grand_total, 0), [paidSales]);
  const totalTransaksi = paidSales.length;
  const labaKotor = useMemo(
    () => paidSales.reduce((s, v) => s + v.items.reduce((is2, it) => is2 + (it.unit_price - it.purchase_price) * it.quantity, 0), 0),
    [paidSales]
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
            String(s.grand_total), s.payment_method, s.status
          ]),
          ['', '', '', '', '', '', '', 'TOTAL', String(paidSales.reduce((a, b) => a + b.grand_total, 0)), '', '']
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
            p.status, p.payment_status
          ]),
          ['', '', '', 'TOTAL', String(filteredPOs.reduce((a, b) => a + b.total_amount, 0)),
           String(filteredPOs.reduce((a, b) => a + b.paid_amount, 0)), '', '', '']
        ];
        downloadCsv(`pembelian_${dateLabel}.csv`, rows);
        break;
      }
      case 'kas': {
        const rows: string[][] = [
          ['Tanggal', 'Kode', 'Tipe', 'Akun', 'Jumlah', 'Deskripsi'],
          ...filteredCash.map(c => [
            fmtDate(c.transaction_date), c.transaction_code, c.transaction_type,
            c.cash_account?.name || '-', String(c.amount), c.description || '-'
          ])
        ];
        downloadCsv(`kas_${dateLabel}.csv`, rows);
        break;
      }
      case 'servis': {
        const rows: string[][] = [
          ['No', 'Pelanggan', 'Device', 'Status', 'Estimasi', 'Total', 'Bayar'],
          ...filteredServices.map((s, i) => [
            String(i + 1), s.service_code, s.customer_name, `${s.device_brand} ${s.device_model}`,
            s.status, String(s.estimated_cost), String(s.grand_total), String(s.paid_amount)
          ])
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
          ['Laba Bersih', String(labaBersih)]
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
          ['Pendapatan Servis', String(pendapatanServis)]
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
        <button
          onClick={handleExport}
          className="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/30 transition-all flex items-center gap-1.5"
        >
          <Download className="w-4 h-4" />
          Export CSV
        </button>
      </div>

      {/* Date Range Filter */}
      <div className="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <div className="flex items-center gap-2 text-xs font-bold text-slate-600">
          <Calendar className="w-4 h-4 text-primary-600" />
          Rentang Waktu:
        </div>
        <div className="flex items-center gap-2">
          <input
            type="date"
            value={dateFrom}
            onChange={e => setDateFrom(e.target.value)}
            className="px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
          />
          <span className="text-xs font-bold text-slate-400">s/d</span>
          <input
            type="date"
            value={dateTo}
            onChange={e => setDateTo(e.target.value)}
            className="px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
          />
        </div>
      </div>

      {/* Tab Navigation */}
      <div className="flex gap-1.5 overflow-x-auto pb-1">
        {TABS.map(tab => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id)}
            className={`flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all ${
              activeTab === tab.id
                ? 'bg-primary-600 text-white shadow-sm shadow-primary-600/30'
                : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
            }`}
          >
            {tab.icon}
            {tab.label}
          </button>
        ))}
      </div>

      {/* === RINGKASAN === */}
      {activeTab === 'ringkasan' && (
        <div className="space-y-6">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <KpiCard
              title="Total Penjualan"
              value={rp(totalPenjualan)}
              subtitle={`${totalTransaksi} transaksi`}
              icon={<TrendingUp className="w-4 h-4" />}
              color="emerald"
            />
            <KpiCard
              title="Total Transaksi"
              value={String(totalTransaksi)}
              subtitle="transaksi tercatat"
              icon={<ShoppingCart className="w-4 h-4" />}
              color="primary"
            />
            <KpiCard
              title="Laba Kotor"
              value={rp(labaKotor)}
              subtitle={`Margin: ${totalPenjualan > 0 ? Math.round((labaKotor / totalPenjualan) * 100) : 0}%`}
              icon={<ArrowUpRight className="w-4 h-4" />}
              color="blue"
            />
            <KpiCard
              title="Pendapatan Servis"
              value={rp(pendapatanServis)}
              subtitle={`${nonCancelledServices.length} transaksi servis`}
              icon={<Wrench className="w-4 h-4" />}
              color="amber"
            />
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Top Produk */}
            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
              <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-2 font-bold text-slate-800 text-sm">
                <Package className="w-4 h-4 text-primary-600" />
                Top Produk Terlaris
              </div>
              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs">
                  <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
                    <tr>
                      <th className="px-5 py-3">#</th>
                      <th className="px-4 py-3">Produk</th>
                      <th className="px-4 py-3 text-right">Qty Terjual</th>
                      <th className="px-4 py-3 text-right">Omset</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100 text-slate-700">
                    {topProducts.map((p, i) => (
                      <tr key={i} className="hover:bg-slate-50/70 transition-colors">
                        <td className="px-5 py-2.5 font-bold text-slate-400">{i + 1}</td>
                        <td className="px-4 py-2.5 font-medium text-slate-800">{p.name}</td>
                        <td className="px-4 py-2.5 text-right font-bold">{p.qty}</td>
                        <td className="px-4 py-2.5 text-right font-bold text-primary-700">{rp(p.revenue)}</td>
                      </tr>
                    ))}
                    {topProducts.length === 0 && (
                      <tr>
                        <td colSpan={4} className="px-5 py-8 text-center text-slate-400">
                          Tidak ada data penjualan di rentang ini.
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
            </div>

            {/* Metode Pembayaran */}
            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
              <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-2 font-bold text-slate-800 text-sm">
                <CreditCard className="w-4 h-4 text-primary-600" />
                Transaksi per Metode
              </div>
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
            </div>
          </div>
        </div>
      )}

      {/* === PENJUALAN === */}
      {activeTab === 'penjualan' && (
        <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-2 font-bold text-slate-800 text-sm">
            <Receipt className="w-4 h-4 text-primary-600" />
            Daftar Penjualan ({filteredSales.length} transaksi)
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
                <tr>
                  <th className="px-5 py-3">#</th>
                  <th className="px-4 py-3">No Nota</th>
                  <th className="px-4 py-3">Tanggal</th>
                  <th className="px-4 py-3">Pelanggan</th>
                  <th className="px-4 py-3">Channel</th>
                  <th className="px-4 py-3 text-right">Items</th>
                  <th className="px-4 py-3 text-right">Subtotal</th>
                  <th className="px-4 py-3 text-right">Diskon</th>
                  <th className="px-4 py-3 text-right">Total</th>
                  <th className="px-4 py-3">Metode</th>
                  <th className="px-4 py-3">Status</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {filteredSales.map((s, i) => (
                  <tr key={s.id} className="hover:bg-slate-50/70 transition-colors">
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
                      <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                        s.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'
                      }`}>
                        {s.status.toUpperCase()}
                      </span>
                    </td>
                  </tr>
                ))}
                {filteredSales.length === 0 && (
                  <tr>
                    <td colSpan={11} className="px-5 py-8 text-center text-slate-400">
                      Tidak ada data penjualan di rentang waktu ini.
                    </td>
                  </tr>
                )}
              </tbody>
              {paidSales.length > 0 && (
                <tfoot className="bg-slate-50 border-t-2 border-slate-200 font-bold text-slate-800">
                  <tr>
                    <td colSpan={6} className="px-5 py-3 text-xs text-slate-500">TOTAL</td>
                    <td className="px-4 py-3 text-right">{filteredSales.reduce((a, b) => a + b.items_count, 0)}</td>
                    <td className="px-4 py-3 text-right">{rp(filteredSales.reduce((a, b) => a + b.subtotal, 0))}</td>
                    <td className="px-4 py-3 text-right text-red-600">{rp(filteredSales.reduce((a, b) => a + b.discount_total, 0))}</td>
                    <td className="px-4 py-3 text-right text-primary-700 font-black">{rp(paidSales.reduce((a, b) => a + b.grand_total, 0))}</td>
                    <td colSpan={2}></td>
                  </tr>
                </tfoot>
              )}
            </table>
          </div>
        </div>
      )}

      {/* === PEMBELIAN (PO) === */}
      {activeTab === 'pembelian' && (
        <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-2 font-bold text-slate-800 text-sm">
            <Truck className="w-4 h-4 text-primary-600" />
            Daftar Purchase Order ({filteredPOs.length} PO)
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
                <tr>
                  <th className="px-5 py-3">#</th>
                  <th className="px-4 py-3">No PO</th>
                  <th className="px-4 py-3">Supplier</th>
                  <th className="px-4 py-3">Tanggal</th>
                  <th className="px-4 py-3 text-right">Total</th>
                  <th className="px-4 py-3 text-right">Bayar</th>
                  <th className="px-4 py-3 text-right">Sisa</th>
                  <th className="px-4 py-3">Status PO</th>
                  <th className="px-4 py-3">Status Bayar</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {filteredPOs.map((p, i) => (
                  <tr key={p.id} className="hover:bg-slate-50/70 transition-colors">
                    <td className="px-5 py-2.5 font-bold text-slate-400">{i + 1}</td>
                    <td className="px-4 py-2.5 font-mono font-bold text-primary-700">{p.po_number}</td>
                    <td className="px-4 py-2.5 font-medium text-slate-800">{p.supplier?.name || '-'}</td>
                    <td className="px-4 py-2.5">{fmtDate(p.order_date)}</td>
                    <td className="px-4 py-2.5 text-right font-bold text-slate-900">{rp(p.total_amount)}</td>
                    <td className="px-4 py-2.5 text-right text-emerald-700 font-semibold">{rp(p.paid_amount)}</td>
                    <td className="px-4 py-2.5 text-right text-red-600 font-semibold">{rp(Math.max(0, p.total_amount - p.paid_amount))}</td>
                    <td className="px-4 py-2.5">
                      <PoStatusBadge status={p.status} />
                    </td>
                    <td className="px-4 py-2.5">
                      <PaymentStatusBadge status={p.payment_status} />
                    </td>
                  </tr>
                ))}
                {filteredPOs.length === 0 && (
                  <tr>
                    <td colSpan={9} className="px-5 py-8 text-center text-slate-400">
                      Tidak ada data purchase order di rentang waktu ini.
                    </td>
                  </tr>
                )}
              </tbody>
              {filteredPOs.length > 0 && (
                <tfoot className="bg-slate-50 border-t-2 border-slate-200 font-bold text-slate-800">
                  <tr>
                    <td colSpan={4} className="px-5 py-3 text-xs text-slate-500">TOTAL</td>
                    <td className="px-4 py-3 text-right">{rp(filteredPOs.reduce((a, b) => a + b.total_amount, 0))}</td>
                    <td className="px-4 py-3 text-right text-emerald-700">{rp(filteredPOs.reduce((a, b) => a + b.paid_amount, 0))}</td>
                    <td className="px-4 py-3 text-right text-red-600">{rp(filteredPOs.reduce((a, b) => a + Math.max(0, b.total_amount - b.paid_amount), 0))}</td>
                    <td colSpan={2}></td>
                  </tr>
                </tfoot>
              )}
            </table>
          </div>
        </div>
      )}

      {/* === LABA RUGI === */}
      {activeTab === 'labarugi' && (
        <div className="max-w-2xl mx-auto space-y-4">
          <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-2 font-bold text-slate-800 text-sm">
              <FileText className="w-4 h-4 text-primary-600" />
              Laporan Laba Rugi — {fmtDate(dateFrom)} s/d {fmtDate(dateTo)}
            </div>
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
              <div className={`flex justify-between items-center p-4 rounded-xl border-2 ${labaKotor >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200'}`}>
                <span className="text-sm font-bold text-slate-800">Laba Kotor</span>
                <span className={`text-lg font-black ${labaKotor >= 0 ? 'text-emerald-700' : 'text-red-700'}`}>{rp(labaKotor)}</span>
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
              <div className={`flex justify-between items-center p-5 rounded-xl border-2 ${labaBersih >= 0 ? 'bg-emerald-50 border-emerald-300' : 'bg-red-50 border-red-300'}`}>
                <div className="flex items-center gap-2">
                  {labaBersih >= 0 ? (
                    <TrendingUp className="w-5 h-5 text-emerald-600" />
                  ) : (
                    <TrendingDown className="w-5 h-5 text-red-600" />
                  )}
                  <span className="text-sm font-black text-slate-900">Laba Bersih</span>
                </div>
                <span className={`text-xl font-black ${labaBersih >= 0 ? 'text-emerald-700' : 'text-red-700'}`}>{rp(labaBersih)}</span>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* === KAS === */}
      {activeTab === 'kas' && (
        <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-2 font-bold text-slate-800 text-sm">
            <Wallet className="w-4 h-4 text-primary-600" />
            Mutasi Kas ({filteredCash.length} transaksi)
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
                <tr>
                  <th className="px-5 py-3">#</th>
                  <th className="px-4 py-3">Tanggal</th>
                  <th className="px-4 py-3">Kode</th>
                  <th className="px-4 py-3">Tipe</th>
                  <th className="px-4 py-3">Akun</th>
                  <th className="px-4 py-3 text-right">Jumlah</th>
                  <th className="px-4 py-3">Deskripsi</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {filteredCash.map((c, i) => (
                  <tr key={c.id} className="hover:bg-slate-50/70 transition-colors">
                    <td className="px-5 py-2.5 font-bold text-slate-400">{i + 1}</td>
                    <td className="px-4 py-2.5">{fmtDate(c.transaction_date)}</td>
                    <td className="px-4 py-2.5 font-mono font-bold text-slate-600">{c.transaction_code}</td>
                    <td className="px-4 py-2.5">
                      <CashTypeBadge type={c.transaction_type} />
                    </td>
                    <td className="px-4 py-2.5 font-medium text-slate-800">{c.cash_account?.name || '-'}</td>
                    <td className={`px-4 py-2.5 text-right font-bold ${c.transaction_type === 'income' ? 'text-emerald-700' : c.transaction_type === 'expense' ? 'text-red-600' : 'text-slate-600'}`}>
                      {c.transaction_type === 'expense' ? '-' : c.transaction_type === 'income' ? '+' : ''}{rp(c.amount)}
                    </td>
                    <td className="px-4 py-2.5 text-slate-600 max-w-[200px] truncate">{c.description || '-'}</td>
                  </tr>
                ))}
                {filteredCash.length === 0 && (
                  <tr>
                    <td colSpan={7} className="px-5 py-8 text-center text-slate-400">
                      Tidak ada transaksi kas di rentang waktu ini.
                    </td>
                  </tr>
                )}
              </tbody>
              {filteredCash.length > 0 && (
                <tfoot className="bg-slate-50 border-t-2 border-slate-200 font-bold text-slate-800">
                  <tr>
                    <td colSpan={5} className="px-5 py-3 text-xs text-slate-500">TOTAL</td>
                    <td className="px-4 py-3 text-right">
                      <span className="text-emerald-700">+{rp(totalIncomeOther)}</span>
                      <span className="mx-1 text-slate-300">/</span>
                      <span className="text-red-600">-{rp(totalExpense)}</span>
                    </td>
                    <td></td>
                  </tr>
                </tfoot>
              )}
            </table>
          </div>
        </div>
      )}

      {/* === SERVIS === */}
      {activeTab === 'servis' && (
        <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-2 font-bold text-slate-800 text-sm">
            <Wrench className="w-4 h-4 text-primary-600" />
            Daftar Servis ({filteredServices.length} transaksi)
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
                <tr>
                  <th className="px-5 py-3">#</th>
                  <th className="px-4 py-3">No</th>
                  <th className="px-4 py-3">Pelanggan</th>
                  <th className="px-4 py-3">Device</th>
                  <th className="px-4 py-3">Status</th>
                  <th className="px-4 py-3 text-right">Estimasi</th>
                  <th className="px-4 py-3 text-right">Total</th>
                  <th className="px-4 py-3 text-right">Bayar</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {filteredServices.map((s, i) => (
                  <tr key={s.id} className="hover:bg-slate-50/70 transition-colors">
                    <td className="px-5 py-2.5 font-bold text-slate-400">{i + 1}</td>
                    <td className="px-4 py-2.5 font-mono font-bold text-primary-700">{s.service_code}</td>
                    <td className="px-4 py-2.5 font-medium text-slate-800">{s.customer_name}</td>
                    <td className="px-4 py-2.5 text-slate-600">{s.device_brand} {s.device_model}</td>
                    <td className="px-4 py-2.5">
                      <ServiceStatusBadge status={s.status} />
                    </td>
                    <td className="px-4 py-2.5 text-right font-bold text-slate-600">{rp(s.estimated_cost)}</td>
                    <td className="px-4 py-2.5 text-right font-bold text-slate-900">{rp(s.grand_total)}</td>
                    <td className="px-4 py-2.5 text-right font-bold text-emerald-700">{rp(s.paid_amount)}</td>
                  </tr>
                ))}
                {filteredServices.length === 0 && (
                  <tr>
                    <td colSpan={8} className="px-5 py-8 text-center text-slate-400">
                      Tidak ada data servis di rentang waktu ini.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
};

/* ========== Sub-components ========== */

function KpiCard({ title, value, subtitle, icon, color }: {
  title: string; value: string; subtitle: string; icon: React.ReactNode; color: string;
}) {
  const colorMap: Record<string, string> = {
    emerald: 'bg-emerald-50 text-emerald-600',
    primary: 'bg-primary-50 text-primary-600',
    blue: 'bg-blue-50 text-blue-600',
    amber: 'bg-amber-50 text-amber-600'
  };
  return (
    <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
      <div className="flex items-center justify-between">
        <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">{title}</span>
        <div className={`w-8 h-8 rounded-lg flex items-center justify-center font-bold ${colorMap[color] || colorMap.primary}`}>
          {icon}
        </div>
      </div>
      <div className="mt-3">
        <div className="text-2xl font-black text-slate-900">{value}</div>
        <div className="text-xs text-slate-500 mt-1">{subtitle}</div>
      </div>
    </div>
  );
}

function PoStatusBadge({ status }: { status: string }) {
  const map: Record<string, string> = {
    draft: 'bg-slate-100 text-slate-700',
    ordered: 'bg-blue-100 text-blue-800',
    received: 'bg-amber-100 text-amber-800',
    completed: 'bg-emerald-100 text-emerald-800',
    cancelled: 'bg-red-100 text-red-800'
  };
  return (
    <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase ${map[status] || map.draft}`}>
      {status}
    </span>
  );
}

function PaymentStatusBadge({ status }: { status: string }) {
  const map: Record<string, string> = {
    unpaid: 'bg-red-100 text-red-800',
    partial: 'bg-amber-100 text-amber-800',
    paid: 'bg-emerald-100 text-emerald-800'
  };
  return (
    <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase ${map[status] || map.unpaid}`}>
      {status}
    </span>
  );
}

function CashTypeBadge({ type }: { type: string }) {
  const map: Record<string, string> = {
    income: 'bg-emerald-100 text-emerald-800',
    expense: 'bg-red-100 text-red-800',
    mutation: 'bg-blue-100 text-blue-800',
    employee_advance: 'bg-amber-100 text-amber-800'
  };
  const labels: Record<string, string> = {
    income: 'Pemasukan',
    expense: 'Pengeluaran',
    mutation: 'Mutasi',
    employee_advance: 'Kasbon'
  };
  return (
    <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase ${map[type] || map.mutation}`}>
      {labels[type] || type}
    </span>
  );
}

function ServiceStatusBadge({ status }: { status: string }) {
  const map: Record<string, string> = {
    pending: 'bg-slate-100 text-slate-700',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-emerald-100 text-emerald-800',
    delivered: 'bg-primary-100 text-primary-800',
    cancelled: 'bg-red-100 text-red-800'
  };
  const labels: Record<string, string> = {
    pending: 'Antri',
    in_progress: 'Dikerjakan',
    completed: 'Selesai',
    delivered: 'Diambil',
    cancelled: 'Batal'
  };
  return (
    <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase ${map[status] || map.pending}`}>
      {labels[status] || status}
    </span>
  );
}
