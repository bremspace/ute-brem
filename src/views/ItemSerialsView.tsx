import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import clsx from 'clsx';
import {
  Hash,
  Package,
  MapPin,
  Filter,
  Search,
  Check,
  XCircle,
  AlertCircle,
  Tag,
} from 'lucide-react';
import {
  Badge,
  Button,
  Card,
  SearchInput,
  Select,
  StatCard,
  TableContainer,
  TableHeader,
  TableBase,
  TableRow,
  TableEmpty,
  StatusBadge,
} from '../components/ui';
import type { SelectOption } from '../components/ui';

type SerialStatus = 'available' | 'sold' | 'returned' | 'damaged' | 'reserved';

interface ItemSerial {
  id: number;
  serial_number: string;
  product_id: number;
  product_code: string;
  product_name: string;
  location_name: string;
  status: SerialStatus;
  sale_code?: string;
  customer_name?: string;
  notes: string;
  created_at: string;
}

const STATUS_LABELS: Record<SerialStatus, string> = {
  available: 'Tersedia',
  sold: 'Terjual',
  returned: 'Dikembalikan',
  damaged: 'Rusak',
  reserved: 'Direservasi',
};

const STATUS_VARIANT: Record<SerialStatus, 'success' | 'info' | 'warning' | 'danger' | 'primary'> = {
  available: 'success',
  sold: 'info',
  returned: 'warning',
  damaged: 'danger',
  reserved: 'primary',
};

function generateMockSerialData(
  products: ReturnType<typeof useApp>['products'],
  sales: ReturnType<typeof useApp>['sales'],
): ItemSerial[] {
  if (products.length === 0) return [];

  const serialProducts = products.filter(p => p.has_serial_number).slice(0, 10);
  if (serialProducts.length === 0) return [];

  const statuses: SerialStatus[] = ['available', 'sold', 'returned', 'damaged', 'reserved'];
  const now = new Date();

  const serials: ItemSerial[] = [];
  let seq = 1;

  serialProducts.forEach((p, pi) => {
    const count = Math.min(3, pi + 1);
    for (let i = 0; i < count; i++) {
      const status = statuses[(pi + i) % statuses.length];
      const serialNum = `SN-${p.product_code}-${(seq).toString().padStart(4, '0')}`;
      const paidSales = sales.filter(s => s.status === 'paid');
      const relatedSale = status === 'sold' && paidSales.length > 0 ? paidSales[pi % paidSales.length] : undefined;

      serials.push({
        id: seq,
        serial_number: serialNum,
        product_id: p.id,
        product_code: p.product_code,
        product_name: p.name,
        location_name: 'Gudang Pusat',
        status,
        sale_code: relatedSale?.sale_code,
        customer_name: relatedSale?.customer?.name,
        notes: '',
        created_at: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(Math.max(1, now.getDate() - seq)).padStart(2, '0')}T10:00:00`,
      });
      seq++;
    }
  });

  return serials;
}

function formatDate(iso: string): string {
  return new Date(iso).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

export const ItemSerialsView: React.FC = () => {
  const { products, sales, locations } = useApp();

  const [statusFilter, setStatusFilter] = useState<SerialStatus | 'all'>('all');
  const [search, setSearch] = useState('');

  const serials = useMemo(() => generateMockSerialData(products, sales), [products, sales]);

  const filtered = useMemo(() => {
    const q = search.toLowerCase().trim();
    return serials.filter(s => {
      if (statusFilter !== 'all' && s.status !== statusFilter) return false;
      if (q) {
        return (
          s.serial_number.toLowerCase().includes(q) ||
          s.product_name.toLowerCase().includes(q) ||
          s.product_code.toLowerCase().includes(q) ||
          (s.customer_name && s.customer_name.toLowerCase().includes(q)) ||
          (s.sale_code && s.sale_code.toLowerCase().includes(q))
        );
      }
      return true;
    });
  }, [serials, statusFilter, search]);

  const totalAvailable = serials.filter(s => s.status === 'available').length;
  const totalSold = serials.filter(s => s.status === 'sold').length;
  const totalReturned = serials.filter(s => s.status === 'returned').length;
  const totalDamaged = serials.filter(s => s.status === 'damaged').length;

  const statusOptions: SelectOption[] = [
    { value: 'all', label: 'Semua Status' },
    { value: 'available', label: 'Tersedia' },
    { value: 'sold', label: 'Terjual' },
    { value: 'returned', label: 'Dikembalikan' },
    { value: 'damaged', label: 'Rusak' },
    { value: 'reserved', label: 'Direservasi' },
  ];

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Header */}
      <div>
        <div className="flex items-center gap-2">
          <Hash className="w-5 h-5 text-primary-600" />
          <h1 className="text-2xl font-black text-slate-900 tracking-tight">Serial Number</h1>
        </div>
        <p className="text-xs text-slate-500 mt-1">
          Lacak nomor seri produk untuk setiap item yang memiliki serial number.
        </p>
      </div>

      {/* Filter */}
      <Card noPadding>
        <div className="p-4 space-y-3">
          <div className="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <div className="relative flex-1 w-full sm:w-auto">
              <SearchInput
                value={search}
                onChange={e => setSearch(e.target.value)}
                placeholder="Cari serial number, nama produk, atau pelanggan..."
              />
            </div>
            <Select
              options={statusOptions}
              value={statusFilter}
              onChange={e => setStatusFilter(e.target.value as SerialStatus | 'all')}
              wrapperClassName="w-auto"
            />
          </div>
        </div>
      </Card>

      {/* Stats */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          value={serials.length.toLocaleString('id-ID')}
          label="Total Serial"
          icon={<Hash className="w-4 h-4" />}
          color="primary"
        />
        <StatCard
          value={totalAvailable.toLocaleString('id-ID')}
          label="Tersedia"
          description="Siap dijual"
          icon={<Check className="w-4 h-4" />}
          color="success"
        />
        <StatCard
          value={totalSold.toLocaleString('id-ID')}
          label="Terjual"
          description="Sudah terjual"
          icon={<Tag className="w-4 h-4" />}
          color="info"
        />
        <StatCard
          value={totalReturned.toLocaleString('id-ID') + totalDamaged.toLocaleString('id-ID')}
          label="Retur / Rusak"
          description="Perlu perhatian"
          icon={<AlertCircle className="w-4 h-4" />}
          color="danger"
        />
      </div>

      {/* Table */}
      <TableContainer>
        <TableHeader
          icon={<Hash className="w-4 h-4 text-primary-600" />}
          count={filtered.length}
        >
          Daftar Serial Number
        </TableHeader>

        <TableBase
          columns={[
            { header: 'Serial Number' },
            { header: 'Produk' },
            { header: 'Lokasi' },
            { header: 'Status' },
            { header: 'No. Penjualan' },
            { header: 'Pelanggan' },
            { header: 'Tanggal' },
          ]}
          colSpan={7}
        >
          {filtered.length === 0 ? (
            <TableEmpty
              colSpan={7}
              message="Belum ada data serial number"
              icon={<Package className="w-10 h-10 mx-auto mb-2 text-slate-300" />}
            />
          ) : (
            filtered.map(s => (
              <TableRow key={s.id}>
                <td className="px-5 py-3">
                  <span className="font-mono font-bold text-primary-700 text-[13px]">{s.serial_number}</span>
                </td>
                <td className="px-4 py-3">
                  <div className="font-bold text-slate-800 line-clamp-1">{s.product_name}</div>
                  <div className="text-[10px] text-slate-500 font-mono">{s.product_code}</div>
                </td>
                <td className="px-4 py-3">
                  <div className="flex items-center gap-1 text-slate-700">
                    <MapPin className="w-3 h-3 text-slate-400" />
                    <span className="line-clamp-1">{s.location_name}</span>
                  </div>
                </td>
                <td className="px-4 py-3">
                  <Badge variant={STATUS_VARIANT[s.status]}>
                    {STATUS_LABELS[s.status]}
                  </Badge>
                </td>
                <td className="px-4 py-3 font-mono font-bold text-slate-600">{s.sale_code || '-'}</td>
                <td className="px-4 py-3 text-slate-700">{s.customer_name || '-'}</td>
                <td className="px-4 py-3 whitespace-nowrap text-slate-600">{formatDate(s.created_at)}</td>
              </TableRow>
            ))
          )}
        </TableBase>
      </TableContainer>
    </div>
  );
};
