import React from 'react';
import { useApp } from '../context/AppContext';
import {
  TrendingUp,
  ShoppingCart,
  Package,
  Wrench,
  AlertTriangle,
  ArrowUpRight,
} from 'lucide-react';
import clsx from 'clsx';
import {
  StatCard,
  Card,
  CardHeader,
  Button,
  TableContainer,
  TableHeader,
  TableBase,
  TableRow,
  TableEmpty,
  Badge,
} from '../components/ui';

export const DashboardView: React.FC = () => {
  const { sales, products, serviceTransactions, branchTransfers, setCurrentView } = useApp();

  const today = new Date().toISOString().split('T')[0];
  const todaySales = sales.filter(s => s.sale_at.startsWith(today) && s.status === 'paid');

  const totalOmsetToday = todaySales.reduce((sum, s) => sum + s.grand_total, 0);
  const totalItemsSoldToday = todaySales.reduce((sum, s) => sum + s.items_count, 0);
  const totalHppToday = todaySales.reduce((sum, s) => {
    return sum + s.items.reduce((itemSum, it) => itemSum + (it.purchase_price * it.quantity), 0);
  }, 0);
  const grossProfitToday = totalOmsetToday - totalHppToday;

  const lowStockProducts = products.filter(p => (p.stock_global || 0) <= (p.stock_min || 0));
  const activeServices = serviceTransactions.filter(s => s.status === 'process');

  const transactionColumns = [
    { header: 'No. Nota', className: 'px-5' },
    { header: 'Pelanggan' },
    { header: 'Items' },
    { header: 'Total', align: 'right' as const },
    { header: 'Metode' },
    { header: 'Status' },
  ];

  const recentSales = sales.slice(0, 5);

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Page Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-slate-900 tracking-tight">Dashboard Ringkasan Bisnis</h1>
          <p className="text-xs text-slate-500 mt-1">
            Monitoring performa penjualan kasir, stok sparepart, dan antrean servis hari ini.
          </p>
        </div>

        <Button
          variant="primary"
          size="md"
          icon={<ShoppingCart className="w-4 h-4" />}
          onClick={() => setCurrentView('pos')}
        >
          Buka Kasir POS
        </Button>
      </div>

      {/* KPI Stats Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {/* Omset Hari Ini */}
        <StatCard
          label="Omset Hari Ini"
          value={`Rp ${totalOmsetToday.toLocaleString('id-ID')}`}
          description={`Dari ${todaySales.length} transaksi (${totalItemsSoldToday} item)`}
          icon={<TrendingUp className="w-4 h-4" />}
          color="success"
        />

        {/* Laba Kotor Hari Ini */}
        <StatCard
          label="Laba Kotor Penjualan"
          value={`Rp ${grossProfitToday.toLocaleString('id-ID')}`}
          description={`Margin kotor: ${totalOmsetToday > 0 ? Math.round((grossProfitToday / totalOmsetToday) * 100) : 0}%`}
          icon={<ArrowUpRight className="w-4 h-4" />}
          color="primary"
        />

        {/* Antrean Servis */}
        <StatCard
          label="Antrean Servis HP"
          value={`${activeServices.length} Unit`}
          description={`${serviceTransactions.filter(s => s.status === 'done').length} unit selesai siap ambil`}
          icon={<Wrench className="w-4 h-4" />}
          color="info"
        />

        {/* Peringatan Stok Menipis */}
        <StatCard
          label="Stok Perlu Restock"
          value={`${lowStockProducts.length} SKU`}
          description="Stok di bawah batas minimum"
          icon={<AlertTriangle className="w-4 h-4" />}
          color="warning"
        />
      </div>

      {/* Main Grid: Recent Transactions & Low Stock Alerts */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left Column: Recent Sales */}
        <TableContainer className="lg:col-span-2">
          <TableHeader
            icon={<ShoppingCart className="w-4 h-4 text-primary-600" />}
            count={recentSales.length}
            extra={
              <Button
                variant="ghost"
                size="sm"
                onClick={() => setCurrentView('reports')}
              >
                Lihat Laporan Lengkap &rarr;
              </Button>
            }
          >
            Transaksi Penjualan Terkini
          </TableHeader>

          <TableBase
            columns={transactionColumns}
            emptyMessage="Belum ada transaksi hari ini"
            emptyIcon={<ShoppingCart className="w-6 h-6 text-slate-300" />}
          >
            {recentSales.map(s => (
              <TableRow key={s.id}>
                <td className="px-5 py-3 font-mono font-bold text-primary-700">{s.sale_code}</td>
                <td className="px-4 py-3 font-medium text-slate-800">{s.customer?.name || 'Walk-in'}</td>
                <td className="px-4 py-3">{s.items_count} pcs</td>
                <td className="px-4 py-3 font-bold text-slate-900 text-right">Rp {s.grand_total.toLocaleString('id-ID')}</td>
                <td className="px-4 py-3 uppercase font-semibold text-slate-600">{s.payment_method}</td>
                <td className="px-4 py-3">
                  <Badge variant={s.status === 'paid' ? 'success' : 'danger'}>
                    {s.status.toUpperCase()}
                  </Badge>
                </td>
              </TableRow>
            ))}

            {recentSales.length === 0 && (
              <TableEmpty
                colSpan={transactionColumns.length}
                message="Belum ada transaksi hari ini. Silakan buka modul POS Kasir."
                icon={<ShoppingCart className="w-6 h-6 text-slate-300" />}
              />
            )}
          </TableBase>
        </TableContainer>

        {/* Right Column: Low Stock Warnings */}
        <Card noPadding>
          <TableHeader
            icon={<AlertTriangle className="w-4 h-4 text-amber-500" />}
            count={lowStockProducts.length}
            extra={
              <Button
                variant="ghost"
                size="sm"
                onClick={() => setCurrentView('products')}
              >
                Master Produk &rarr;
              </Button>
            }
          >
            Peringatan Stok Kritis
          </TableHeader>

          <div className="p-4 flex-1 overflow-y-auto space-y-3">
            {lowStockProducts.slice(0, 6).map(p => (
              <div
                key={p.id}
                className="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between"
              >
                <div>
                  <div className="font-bold text-xs text-slate-800 line-clamp-1">{p.name}</div>
                  <div className="text-[10px] text-slate-500 font-mono mt-0.5">{p.product_code}</div>
                </div>
                <div className="text-right">
                  <Badge
                    variant={(p.stock_global || 0) === 0 ? 'danger' : 'warning'}
                    className="text-xs font-black"
                  >
                    Sisa: {p.stock_global || 0}
                  </Badge>
                  <div className="text-[10px] text-slate-400 mt-0.5">Min: {p.stock_min}</div>
                </div>
              </div>
            ))}

            {lowStockProducts.length === 0 && (
              <div className="h-40 flex flex-col items-center justify-center text-center text-slate-400">
                <Package className="w-8 h-8 text-emerald-400 mb-1" />
                <p className="text-xs font-semibold text-emerald-700">Semua stok produk aman!</p>
              </div>
            )}
          </div>
        </Card>
      </div>
    </div>
  );
};
