import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import {
  ArrowDownToLine,
  ArrowUpFromLine,
  ArrowLeftRight,
  Search,
  Filter,
  Package,
  TrendingUp,
  TrendingDown,
  Hash,
  Layers,
  MapPin,
  User,
  FileText,
  ChevronDown
} from 'lucide-react';

type MovementTypeFilter = 'all' | 'in' | 'out' | 'transfer_in' | 'transfer_out' | 'adjustment_plus' | 'adjustment_minus';

const MOVEMENT_TYPE_OPTIONS: { value: MovementTypeFilter; label: string }[] = [
  { value: 'all', label: 'Semua' },
  { value: 'in', label: 'Masuk' },
  { value: 'out', label: 'Keluar' },
  { value: 'transfer_in', label: 'Transfer Masuk' },
  { value: 'transfer_out', label: 'Transfer Keluar' },
  { value: 'adjustment_plus', label: 'Penyesuaian +' },
  { value: 'adjustment_minus', label: 'Penyesuaian −' },
];

const BADGE_CLASSES: Record<string, string> = {
  in: 'bg-emerald-100 text-emerald-800',
  out: 'bg-red-100 text-red-800',
  transfer_in: 'bg-blue-100 text-blue-800',
  transfer_out: 'bg-purple-100 text-purple-800',
  adjustment_plus: 'bg-emerald-100 text-emerald-800',
  adjustment_minus: 'bg-amber-100 text-amber-800',
};

const BADGE_LABELS: Record<string, string> = {
  in: 'Masuk',
  out: 'Keluar',
  transfer_in: 'Transfer In',
  transfer_out: 'Transfer Out',
  adjustment_plus: 'Penyesuaian +',
  adjustment_minus: 'Penyesuaian −',
};

const INCREASE_TYPES = ['in', 'transfer_in', 'adjustment_plus'];

function formatDate(iso: string): string {
  return new Date(iso).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

export const StockMovementsView: React.FC = () => {
  const { stockMovements, products, locations } = useApp();

  const [search, setSearch] = useState('');
  const [typeFilter, setTypeFilter] = useState<MovementTypeFilter>('all');
  const [locationFilter, setLocationFilter] = useState<number | 'all'>('all');
  const [showFilters, setShowFilters] = useState(true);

  const filtered = useMemo(() => {
    const q = search.toLowerCase().trim();
    return stockMovements.filter(m => {
      if (typeFilter !== 'all' && m.movement_type !== typeFilter) return false;
      if (locationFilter !== 'all' && m.location_id !== locationFilter) return false;
      if (q) {
        const prod = products.find(p => p.id === m.product_id);
        const matchProduct =
          prod?.name.toLowerCase().includes(q) ||
          prod?.product_code.toLowerCase().includes(q);
        const matchRef = m.reference_code.toLowerCase().includes(q);
        if (!matchProduct && !matchRef) return false;
      }
      return true;
    });
  }, [stockMovements, products, search, typeFilter, locationFilter]);

  const totalMasuk = filtered
    .filter(m => INCREASE_TYPES.includes(m.movement_type))
    .reduce((s, m) => s + m.quantity, 0);

  const totalKeluar = filtered
    .filter(m => !INCREASE_TYPES.includes(m.movement_type))
    .reduce((s, m) => s + m.quantity, 0);

  const distinctProducts = new Set(filtered.map(m => m.product_id)).size;

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Page Header */}
      <div>
        <h1 className="text-2xl font-black text-slate-900 tracking-tight">Mutasi Stok (Ledger)</h1>
        <p className="text-xs text-slate-500 mt-1">
          Lacak setiap pergerakan stok masuk, keluar, transfer, dan penyesuaian untuk semua produk.
        </p>
      </div>

      {/* Filter Bar */}
      <div className="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-4 space-y-3">
        <div className="flex items-center gap-3">
          {/* Search */}
          <div className="relative flex-1">
            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <Search className="w-4 h-4" />
            </div>
            <input
              type="text"
              value={search}
              onChange={e => setSearch(e.target.value)}
              placeholder="Cari nama/kode produk atau kode referensi..."
              className="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm"
            />
          </div>

          {/* Type Filter */}
          <div className="relative">
            <select
              value={typeFilter}
              onChange={e => setTypeFilter(e.target.value as MovementTypeFilter)}
              className="appearance-none pl-3 pr-8 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              {MOVEMENT_TYPE_OPTIONS.map(opt => (
                <option key={opt.value} value={opt.value}>{opt.label}</option>
              ))}
            </select>
            <ChevronDown className="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" />
          </div>

          {/* Location Filter */}
          <div className="relative">
            <select
              value={locationFilter}
              onChange={e => setLocationFilter(e.target.value === 'all' ? 'all' : Number(e.target.value))}
              className="appearance-none pl-3 pr-8 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="all">Semua Lokasi</option>
              {locations.map(l => (
                <option key={l.id} value={l.id}>{l.name}</option>
              ))}
            </select>
            <ChevronDown className="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" />
          </div>

          {/* Toggle Filters */}
          <button
            onClick={() => setShowFilters(!showFilters)}
            className={`p-2.5 rounded-xl border transition-all ${
              showFilters
                ? 'bg-primary-50 border-primary-300 text-primary-600'
                : 'bg-white border-slate-300 text-slate-500 hover:bg-slate-50'
            }`}
          >
            <Filter className="w-4 h-4" />
          </button>
        </div>
      </div>

      {/* Summary Cards */}
      {showFilters && (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {/* Total Masuk */}
          <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Masuk</span>
              <div className="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <ArrowDownToLine className="w-4 h-4" />
              </div>
            </div>
            <div className="mt-3">
              <div className="text-2xl font-black text-emerald-700">{totalMasuk.toLocaleString('id-ID')}</div>
              <div className="text-xs text-slate-500 mt-1">unit diterima / masuk</div>
            </div>
          </div>

          {/* Total Keluar */}
          <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Keluar</span>
              <div className="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center font-bold">
                <ArrowUpFromLine className="w-4 h-4" />
              </div>
            </div>
            <div className="mt-3">
              <div className="text-2xl font-black text-red-700">{totalKeluar.toLocaleString('id-ID')}</div>
              <div className="text-xs text-slate-500 mt-1">unit keluar / terkirim</div>
            </div>
          </div>

          {/* Total Transaksi */}
          <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Transaksi</span>
              <div className="w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center font-bold">
                <Hash className="w-4 h-4" />
              </div>
            </div>
            <div className="mt-3">
              <div className="text-2xl font-black text-slate-900">{filtered.length.toLocaleString('id-ID')}</div>
              <div className="text-xs text-slate-500 mt-1">baris mutasi stok</div>
            </div>
          </div>

          {/* Distinct Produk */}
          <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Produk Terpengaruh</span>
              <div className="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <Package className="w-4 h-4" />
              </div>
            </div>
            <div className="mt-3">
              <div className="text-2xl font-black text-amber-700">{distinctProducts.toLocaleString('id-ID')}</div>
              <div className="text-xs text-slate-500 mt-1">produk unik</div>
            </div>
          </div>
        </div>
      )}

      {/* Movements Table */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
            <Layers className="w-4 h-4 text-primary-600" />
            Daftar Mutasi Stok
            <span className="text-xs font-semibold text-slate-400 ml-1">({filtered.length})</span>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
              <tr>
                <th className="px-5 py-3">Waktu</th>
                <th className="px-4 py-3">Kode Referensi</th>
                <th className="px-4 py-3">Produk</th>
                <th className="px-4 py-3">Tipe Mutasi</th>
                <th className="px-4 py-3 text-right">Qty</th>
                <th className="px-4 py-3 text-center">Saldo</th>
                <th className="px-4 py-3">Lokasi</th>
                <th className="px-4 py-3">Petugas</th>
                <th className="px-4 py-3">Catatan</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 text-slate-700">
              {filtered.map(m => {
                const prod = products.find(p => p.id === m.product_id);
                const loc = locations.find(l => l.id === m.location_id);
                const isIncrease = INCREASE_TYPES.includes(m.movement_type);

                return (
                  <tr key={m.id} className="hover:bg-slate-50/70 transition-colors">
                    <td className="px-5 py-3 whitespace-nowrap text-slate-600">
                      {formatDate(m.movement_at)}
                    </td>
                    <td className="px-4 py-3 font-mono font-bold text-primary-700">
                      {m.reference_code}
                    </td>
                    <td className="px-4 py-3">
                      <div className="font-bold text-slate-800 line-clamp-1">{prod?.name || `#${m.product_id}`}</div>
                      <div className="text-[10px] text-slate-500 font-mono">{prod?.product_code || '-'}</div>
                    </td>
                    <td className="px-4 py-3">
                      <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${BADGE_CLASSES[m.movement_type] || 'bg-slate-100 text-slate-800'}`}>
                        {BADGE_LABELS[m.movement_type] || m.movement_type}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-right font-black whitespace-nowrap">
                      <span className={isIncrease ? 'text-emerald-600' : 'text-red-600'}>
                        {isIncrease ? '+' : '-'}{m.quantity.toLocaleString('id-ID')}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-center">
                      <span className="text-slate-600 font-semibold">{m.balance_before.toLocaleString('id-ID')}</span>
                      <span className="text-slate-400 mx-1">&rarr;</span>
                      <span className="text-slate-900 font-bold">{m.balance_after.toLocaleString('id-ID')}</span>
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-1 text-slate-700">
                        <MapPin className="w-3 h-3 text-slate-400" />
                        <span className="line-clamp-1">{loc?.name || `#${m.location_id}`}</span>
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-1 text-slate-600">
                        <User className="w-3 h-3 text-slate-400" />
                        <span className="line-clamp-1">{m.created_by_name}</span>
                      </div>
                    </td>
                    <td className="px-4 py-3 max-w-[200px]">
                      <div className="flex items-center gap-1 text-slate-500 line-clamp-1">
                        {m.notes ? <FileText className="w-3 h-3 text-slate-400 flex-shrink-0" /> : null}
                        <span className="line-clamp-1">{m.notes || '-'}</span>
                      </div>
                    </td>
                  </tr>
                );
              })}

              {filtered.length === 0 && (
                <tr>
                  <td colSpan={9} className="px-5 py-12 text-center">
                    <div className="flex flex-col items-center justify-center text-slate-400">
                      <Package className="w-10 h-10 stroke-[1.2] mb-2 text-slate-300" />
                      <p className="text-sm font-semibold">Belum ada data mutasi stok</p>
                      <p className="text-xs text-slate-400 mt-0.5">Mutasi akan muncul setelah ada transaksi penjualan, penerimaan, atau transfer.</p>
                    </div>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
