import React from 'react';
import { useApp } from '../context/AppContext';
import {
  TrendingUp,
  ShoppingCart,
  Package,
  Wrench,
  AlertTriangle,
  ArrowUpRight,
  ArrowDownRight,
  Calendar,
  Users,
  Store,
  Layers
} from 'lucide-react';

export const DashboardView: React.FC = () => {
  const { sales, products, serviceTransactions, branchTransfers, purchaseOrders, setCurrentView } = useApp();

  const today = new Date().toISOString().split('T')[0];
  const todaySales = sales.filter(s => s.sale_at.startsWith(today) && s.status === 'paid');

  const totalOmsetToday = todaySales.reduce((sum, s) => sum + s.grand_total, 0);
  const totalItemsSoldToday = todaySales.reduce((sum, s) => sum + s.items_count, 0);
  const totalHppToday = todaySales.reduce((sum, s) => {
    return sum + s.items.reduce((itemSum, it) => itemSum + (it.purchase_price * it.quantity), 0);
  }, 0);
  const grossProfitToday = totalOmsetToday - totalHppToday;

  const lowStockProducts = products.filter(p => (p.stock_global || 0) <= (p.stock_min || 0));
  const activeServices = serviceTransactions.filter(s => s.status === 'pending' || s.status === 'in_progress');
  const inTransitTransfers = branchTransfers.filter(t => t.status === 'in_transit');

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

        <div className="flex items-center gap-2">
          <button
            onClick={() => setCurrentView('pos')}
            className="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/30 transition-all flex items-center gap-1.5"
          >
            <ShoppingCart className="w-4 h-4" />
            Buka Kasir POS
          </button>
        </div>
      </div>

      {/* KPI Stats Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {/* Omset Hari Ini */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Omset Hari Ini</span>
            <div className="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
              <TrendingUp className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-slate-900">
              Rp {totalOmsetToday.toLocaleString('id-ID')}
            </div>
            <div className="text-xs text-slate-500 mt-1">
              Dari <span className="font-bold text-slate-800">{todaySales.length} transaksi</span> ({totalItemsSoldToday} item)
            </div>
          </div>
        </div>

        {/* Laba Kotor Hari Ini */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Laba Kotor Penjualan</span>
            <div className="w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center font-bold">
              <ArrowUpRight className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-primary-700">
              Rp {grossProfitToday.toLocaleString('id-ID')}
            </div>
            <div className="text-xs text-slate-500 mt-1">
              Margin kotor: {totalOmsetToday > 0 ? Math.round((grossProfitToday / totalOmsetToday) * 100) : 0}%
            </div>
          </div>
        </div>

        {/* Antrean Servis */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Antrean Servis HP</span>
            <div className="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
              <Wrench className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-slate-900">{activeServices.length} Unit</div>
            <div className="text-xs text-slate-500 mt-1">
              {serviceTransactions.filter(s => s.status === 'completed').length} unit selesai siap ambil
            </div>
          </div>
        </div>

        {/* Peringatan Stok Menipis */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Stok Perlu Restock</span>
            <div className="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
              <AlertTriangle className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-amber-600">{lowStockProducts.length} SKU</div>
            <div className="text-xs text-slate-500 mt-1">Stok di bawah batas minimum</div>
          </div>
        </div>
      </div>

      {/* Main Grid: Recent Transactions & Low Stock Alerts */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left Column: Recent Sales */}
        <div className="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
              <ShoppingCart className="w-4 h-4 text-primary-600" />
              Transaksi Penjualan Terkini
            </div>
            <button
              onClick={() => setCurrentView('reports')}
              className="text-xs font-semibold text-primary-600 hover:text-primary-700"
            >
              Lihat Laporan Lengkap →
            </button>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
                <tr>
                  <th className="px-5 py-3">No. Nota</th>
                  <th className="px-4 py-3">Pelanggan</th>
                  <th className="px-4 py-3">Items</th>
                  <th className="px-4 py-3">Total</th>
                  <th className="px-4 py-3">Metode</th>
                  <th className="px-4 py-3">Status</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {sales.slice(0, 5).map(s => (
                  <tr key={s.id} className="hover:bg-slate-50/70 transition-colors">
                    <td className="px-5 py-3 font-mono font-bold text-primary-700">{s.sale_code}</td>
                    <td className="px-4 py-3 font-medium text-slate-800">{s.customer?.name || 'Walk-in'}</td>
                    <td className="px-4 py-3">{s.items_count} pcs</td>
                    <td className="px-4 py-3 font-bold text-slate-900">Rp {s.grand_total.toLocaleString('id-ID')}</td>
                    <td className="px-4 py-3 uppercase font-semibold text-slate-600">{s.payment_method}</td>
                    <td className="px-4 py-3">
                      <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                        s.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'
                      }`}>
                        {s.status.toUpperCase()}
                      </span>
                    </td>
                  </tr>
                ))}
                {sales.length === 0 && (
                  <tr>
                    <td colSpan={6} className="px-5 py-8 text-center text-slate-400">
                      Belum ada transaksi hari ini. Silakan buka modul POS Kasir.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>

        {/* Right Column: Low Stock Warnings */}
        <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
          <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
              <AlertTriangle className="w-4 h-4 text-amber-500" />
              Peringatan Stok Kritis
            </div>
            <button
              onClick={() => setCurrentView('products')}
              className="text-xs font-semibold text-primary-600 hover:text-primary-700"
            >
              Master Produk →
            </button>
          </div>

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
                  <span className="text-xs font-black text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-lg">
                    Sisa: {p.stock_global || 0}
                  </span>
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
        </div>
      </div>
    </div>
  );
};
