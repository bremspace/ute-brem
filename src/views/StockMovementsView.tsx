import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import clsx from 'clsx';
import {
  ArrowDownToLine,
  ArrowUpFromLine,
  Filter,
  Package,
  Hash,
  Layers,
  MapPin,
  User,
  FileText,
} from 'lucide-react';
import {
  Card,
  Badge,
  Button,
  Select,
  SearchInput,
  StatCard,
  TableContainer,
  TableHeader,
  TableBase,
  TableRow,
  TableEmpty,
} from '../components/ui';
import type { SelectOption } from '../components/ui';

type MovementTypeFilter = 'all' | 'in' | 'out' | 'transfer_in' | 'transfer_out' | 'adjustment_plus' | 'adjustment_minus';

const MOVEMENT_TYPE_OPTIONS: SelectOption[] = [
  { value: 'all', label: 'Semua' },
  { value: 'in', label: 'Masuk' },
  { value: 'out', label: 'Keluar' },
  { value: 'transfer_in', label: 'Transfer Masuk' },
  { value: 'transfer_out', label: 'Transfer Keluar' },
  { value: 'adjustment_plus', label: 'Penyesuaian +' },
  { value: 'adjustment_minus', label: 'Penyesuaian −' },
];

const BADGE_VARIANT: Record<string, 'success' | 'danger' | 'info' | 'primary' | 'amber'> = {
  in: 'success',
  out: 'danger',
  transfer_in: 'info',
  transfer_out: 'primary',
  adjustment_plus: 'success',
  adjustment_minus: 'amber',
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

  const locationOptions: SelectOption[] = useMemo(
    () => [{ value: 'all', label: 'Semua Lokasi' }, ...locations.map(l => ({ value: l.id, label: l.name }))],
    [locations],
  );

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

  const columns = useMemo(
    () => [
      { header: 'Waktu' },
      { header: 'Kode Referensi' },
      { header: 'Produk' },
      { header: 'Tipe Mutasi' },
      { header: 'Qty', align: 'right' as const },
      { header: 'Saldo', align: 'center' as const },
      { header: 'Lokasi' },
      { header: 'Petugas' },
      { header: 'Catatan' },
    ],
    [],
  );

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
      <Card noPadding>
        <div className="p-4 space-y-3">
          <div className="flex items-center gap-3">
            {/* Search */}
            <div className="relative flex-1">
              <SearchInput
                value={search}
                onChange={e => setSearch(e.target.value)}
                placeholder="Cari nama/kode produk atau kode referensi..."
              />
            </div>

            {/* Type Filter */}
            <Select
              options={MOVEMENT_TYPE_OPTIONS}
              value={typeFilter}
              onChange={e => setTypeFilter(e.target.value as MovementTypeFilter)}
              wrapperClassName="w-auto"
            />

            {/* Location Filter */}
            <Select
              options={locationOptions}
              value={locationFilter}
              onChange={e => setLocationFilter(e.target.value === 'all' ? 'all' : Number(e.target.value))}
              wrapperClassName="w-auto"
            />

            {/* Toggle Filters */}
            <Button
              variant={showFilters ? 'outline' : 'ghost'}
              size="sm"
              icon={<Filter className="w-4 h-4" />}
              onClick={() => setShowFilters(!showFilters)}
              className={clsx(showFilters && 'border-primary-300 text-primary-600 bg-primary-50')}
            />
          </div>
        </div>
      </Card>

      {/* Summary Cards */}
      {showFilters && (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard
            value={totalMasuk.toLocaleString('id-ID')}
            label="Total Masuk"
            description="unit diterima / masuk"
            icon={<ArrowDownToLine className="w-4 h-4" />}
            color="success"
          />
          <StatCard
            value={totalKeluar.toLocaleString('id-ID')}
            label="Total Keluar"
            description="unit keluar / terkirim"
            icon={<ArrowUpFromLine className="w-4 h-4" />}
            color="danger"
          />
          <StatCard
            value={filtered.length.toLocaleString('id-ID')}
            label="Total Transaksi"
            description="baris mutasi stok"
            icon={<Hash className="w-4 h-4" />}
            color="primary"
          />
          <StatCard
            value={distinctProducts.toLocaleString('id-ID')}
            label="Produk Terpengaruh"
            description="produk unik"
            icon={<Package className="w-4 h-4" />}
            color="warning"
          />
        </div>
      )}

      {/* Movements Table */}
      <TableContainer>
        <TableHeader
          icon={<Layers className="w-4 h-4 text-primary-600" />}
          count={filtered.length}
        >
          Daftar Mutasi Stok
        </TableHeader>

        <TableBase columns={columns} colSpan={columns.length}>
          {filtered.length === 0 ? (
            <TableEmpty
              colSpan={columns.length}
              message="Belum ada data mutasi stok"
              icon={<Package className="w-10 h-10 mx-auto mb-2 text-slate-300" />}
            />
          ) : (
            filtered.map(m => {
              const prod = products.find(p => p.id === m.product_id);
              const loc = locations.find(l => l.id === m.location_id);
              const isIncrease = INCREASE_TYPES.includes(m.movement_type);

              return (
                <TableRow key={m.id}>
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
                    <Badge variant={BADGE_VARIANT[m.movement_type] || 'neutral'}>
                      {BADGE_LABELS[m.movement_type] || m.movement_type}
                    </Badge>
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
                </TableRow>
              );
            })
          )}
        </TableBase>
      </TableContainer>
    </div>
  );
};
