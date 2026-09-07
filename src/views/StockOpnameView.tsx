import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import clsx from 'clsx';
import {
  ClipboardCheck,
  Filter,
  Package,
  MapPin,
  Check,
  AlertCircle,
  Hash,
  ChevronDown,
  ChevronUp,
  Search,
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

type OpnameStatus = 'draft' | 'in_progress' | 'completed' | 'adjusted';

interface StockOpnameItem {
  id: number;
  product_id: number;
  product_code: string;
  product_name: string;
  system_count: number;
  physical_count: number | null;
  difference: number;
  notes: string;
}

interface StockOpnameRecord {
  id: number;
  opname_code: string;
  location_id: number;
  location_name: string;
  status: OpnameStatus;
  opname_date: string;
  counted_by: string;
  notes: string;
  items: StockOpnameItem[];
  created_at: string;
}

const STATUS_LABELS: Record<OpnameStatus, string> = {
  draft: 'Draft',
  in_progress: 'Di Hitung',
  completed: 'Selesai',
  adjusted: 'Disesuaikan',
};

const STATUS_BADGE: Record<OpnameStatus, 'info' | 'warning' | 'success' | 'primary'> = {
  draft: 'info',
  in_progress: 'warning',
  completed: 'success',
  adjusted: 'primary',
};

const LOCATION_OPTIONS: SelectOption[] = [
  { value: 'all', label: 'Semua Lokasi' },
  { value: '1', label: 'Gudang Pusat' },
  { value: '2', label: 'Toko Kasir Pusat' },
  { value: '3', label: 'Gudang Cabang' },
  { value: '4', label: 'Toko Kasir Cabang' },
];

function generateMockOpnameData(products: ReturnType<typeof useApp>['products']): StockOpnameRecord[] {
  if (products.length === 0) return [];

  const now = new Date();
  const records: StockOpnameRecord[] = [
    {
      id: 1,
      opname_code: 'OPN-2609-0001',
      location_id: 1,
      location_name: 'Gudang Pusat',
      status: 'completed',
      opname_date: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`,
      counted_by: 'Manager Utama',
      notes: 'Opname bulanan September',
      items: products.slice(0, 5).map((p, i) => ({
        id: i + 1,
        product_id: p.id,
        product_code: p.product_code,
        product_name: p.name,
        system_count: p.stock_global || 0,
        physical_count: (p.stock_global || 0) + (i % 3 === 0 ? 2 : i % 3 === 1 ? -1 : 0),
        difference: i % 3 === 0 ? 2 : i % 3 === 1 ? -1 : 0,
        notes: '',
      })),
      created_at: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01T08:00:00`,
    },
    {
      id: 2,
      opname_code: 'OPN-2609-0002',
      location_id: 2,
      location_name: 'Toko Kasir Pusat',
      status: 'in_progress',
      opname_date: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-05`,
      counted_by: 'Kasir Utama',
      notes: 'Opname mingguan',
      items: products.slice(2, 6).map((p, i) => ({
        id: i + 10,
        product_id: p.id,
        product_code: p.product_code,
        product_name: p.name,
        system_count: p.stock_global || 0,
        physical_count: i < 2 ? (p.stock_global || 0) : null,
        difference: i < 2 ? 0 : 0,
        notes: '',
      })),
      created_at: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-05T09:00:00`,
    },
    {
      id: 3,
      opname_code: 'OPN-2608-0003',
      location_id: 1,
      location_name: 'Gudang Pusat',
      status: 'adjusted',
      opname_date: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`,
      counted_by: 'Manager Utama',
      notes: 'Opname bulanan Agustus - sudah disesuaikan',
      items: products.slice(0, 3).map((p, i) => ({
        id: i + 20,
        product_id: p.id,
        product_code: p.product_code,
        product_name: p.name,
        system_count: p.stock_global || 0,
        physical_count: (p.stock_global || 0) + (i === 0 ? -3 : i === 1 ? 1 : 0),
        difference: i === 0 ? -3 : i === 1 ? 1 : 0,
        notes: '',
      })),
      created_at: `${now.getFullYear()}-08-01T08:00:00`,
    },
  ];

  return records;
}

function formatDate(iso: string): string {
  return new Date(iso).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

export const StockOpnameView: React.FC = () => {
  const { products, locations } = useApp();

  const [locationFilter, setLocationFilter] = useState<number | 'all'>('all');
  const [statusFilter, setStatusFilter] = useState<OpnameStatus | 'all'>('all');
  const [search, setSearch] = useState('');
  const [expandedId, setExpandedId] = useState<number | null>(null);

  const records = useMemo(() => generateMockOpnameData(products), [products]);

  const filtered = useMemo(() => {
    const q = search.toLowerCase().trim();
    return records.filter(r => {
      if (locationFilter !== 'all' && r.location_id !== locationFilter) return false;
      if (statusFilter !== 'all' && r.status !== statusFilter) return false;
      if (q) {
        return (
          r.opname_code.toLowerCase().includes(q) ||
          r.location_name.toLowerCase().includes(q) ||
          r.counted_by.toLowerCase().includes(q)
        );
      }
      return true;
    });
  }, [records, locationFilter, statusFilter, search]);

  const totalDraft = records.filter(r => r.status === 'draft').length;
  const totalInProgress = records.filter(r => r.status === 'in_progress').length;
  const totalCompleted = records.filter(r => r.status === 'completed' || r.status === 'adjusted').length;
  const totalDiscrepancies = records.reduce((sum, r) =>
    sum + r.items.filter(it => it.physical_count !== null && it.difference !== 0).length, 0,
  );

  const statusOptions: SelectOption[] = [
    { value: 'all', label: 'Semua Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'in_progress', label: 'Di Hitung' },
    { value: 'completed', label: 'Selesai' },
    { value: 'adjusted', label: 'Disesuaikan' },
  ];

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Header */}
      <div>
        <div className="flex items-center gap-2">
          <ClipboardCheck className="w-5 h-5 text-primary-600" />
          <h1 className="text-2xl font-black text-slate-900 tracking-tight">Stock Opname</h1>
        </div>
        <p className="text-xs text-slate-500 mt-1">
          Kelola stock opname untuk membandingkan stok sistem dengan stok fisik di lapangan.
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
                placeholder="Cari kode opname, lokasi, atau petugas..."
              />
            </div>
            <Select
              options={LOCATION_OPTIONS}
              value={locationFilter}
              onChange={e => setLocationFilter(e.target.value === 'all' ? 'all' : Number(e.target.value))}
              wrapperClassName="w-auto"
            />
            <Select
              options={statusOptions}
              value={statusFilter}
              onChange={e => setStatusFilter(e.target.value as OpnameStatus | 'all')}
              wrapperClassName="w-auto"
            />
          </div>
        </div>
      </Card>

      {/* Stats */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          value={records.length.toLocaleString('id-ID')}
          label="Total Opname"
          icon={<ClipboardCheck className="w-4 h-4" />}
          color="primary"
        />
        <StatCard
          value={totalInProgress.toLocaleString('id-ID')}
          label="Dalam Proses"
          description="Sedang dihitung"
          icon={<Search className="w-4 h-4" />}
          color="warning"
        />
        <StatCard
          value={totalCompleted.toLocaleString('id-ID')}
          label="Selesai"
          description="Sudah diverifikasi"
          icon={<Check className="w-4 h-4" />}
          color="success"
        />
        <StatCard
          value={totalDiscrepancies.toLocaleString('id-ID')}
          label="Selisih Ditemukan"
          description="Item dengan selisih"
          icon={<AlertCircle className="w-4 h-4" />}
          color="danger"
        />
      </div>

      {/* Table */}
      <TableContainer>
        <TableHeader
          icon={<ClipboardCheck className="w-4 h-4 text-primary-600" />}
          count={filtered.length}
        >
          Daftar Stock Opname
        </TableHeader>

        <TableBase
          columns={[
            { header: 'Kode Opname' },
            { header: 'Lokasi' },
            { header: 'Tanggal' },
            { header: 'Status' },
            { header: 'Item', align: 'right' as const },
            { header: 'Selisih', align: 'center' as const },
            { header: 'Petugas' },
          ]}
          colSpan={7}
        >
          {filtered.length === 0 ? (
            <TableEmpty
              colSpan={7}
              message="Belum ada data stock opname"
              icon={<Package className="w-10 h-10 mx-auto mb-2 text-slate-300" />}
            />
          ) : (
            filtered.map(record => {
              const diffCount = record.items.filter(it => it.physical_count !== null && it.difference !== 0).length;
              return (
                <React.Fragment key={record.id}>
                  <TableRow
                    className="cursor-pointer"
                    onClick={() => setExpandedId(expandedId === record.id ? null : record.id)}
                  >
                    <td className="px-5 py-3 font-mono font-bold text-primary-700">{record.opname_code}</td>
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-1 text-slate-700">
                        <MapPin className="w-3 h-3 text-slate-400" />
                        <span className="font-medium">{record.location_name}</span>
                      </div>
                    </td>
                    <td className="px-4 py-3 whitespace-nowrap">{formatDate(record.opname_date)}</td>
                    <td className="px-4 py-3">
                      <StatusBadge status={record.status}>
                        {STATUS_LABELS[record.status]}
                      </StatusBadge>
                    </td>
                    <td className="px-4 py-3 text-right font-bold">{record.items.length}</td>
                    <td className="px-4 py-3 text-center">
                      {diffCount > 0 ? (
                        <span className="inline-flex items-center gap-1 text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                          <AlertCircle className="w-3 h-3" />
                          {diffCount}
                        </span>
                      ) : (
                        <span className="text-xs text-slate-400">-</span>
                      )}
                    </td>
                    <td className="px-4 py-3">{record.counted_by}</td>
                  </TableRow>
                  {expandedId === record.id && (
                    <tr>
                      <td colSpan={7} className="px-5 py-3 bg-slate-50/80">
                        <div className="text-xs font-bold text-slate-600 mb-2 uppercase tracking-wider">
                          Detail Item Opname
                        </div>
                        <table className="w-full text-left text-[11px]">
                          <thead className="text-slate-500 font-semibold">
                            <tr>
                              <th className="py-1.5">Kode</th>
                              <th className="py-1.5">Nama Produk</th>
                              <th className="py-1.5 text-right">Stok Sistem</th>
                              <th className="py-1.5 text-right">Stok Fisik</th>
                              <th className="py-1.5 text-right">Selisih</th>
                              <th className="py-1.5">Catatan</th>
                            </tr>
                          </thead>
                          <tbody className="divide-y divide-slate-200">
                            {record.items.map(item => (
                              <tr key={item.id} className="text-slate-700">
                                <td className="py-1.5 font-mono font-semibold">{item.product_code}</td>
                                <td className="py-1.5">{item.product_name}</td>
                                <td className="py-1.5 text-right font-bold">{item.system_count}</td>
                                <td className="py-1.5 text-right font-bold">
                                  {item.physical_count !== null ? item.physical_count : (
                                    <span className="text-slate-400 italic">Belum dihitung</span>
                                  )}
                                </td>
                                <td className="py-1.5 text-right font-bold">
                                  {item.physical_count !== null ? (
                                    item.difference === 0 ? (
                                      <span className="text-emerald-600">= 0</span>
                                    ) : (
                                      <span className={item.difference > 0 ? 'text-emerald-600' : 'text-red-600'}>
                                        {item.difference > 0 ? '+' : ''}{item.difference}
                                      </span>
                                    )
                                  ) : '-'}
                                </td>
                                <td className="py-1.5 text-slate-500">{item.notes || '-'}</td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                        {record.notes && (
                          <div className="mt-2 text-[11px] text-slate-500">
                            <span className="font-semibold">Catatan:</span> {record.notes}
                          </div>
                        )}
                      </td>
                    </tr>
                  )}
                </React.Fragment>
              );
            })
          )}
        </TableBase>
      </TableContainer>
    </div>
  );
};
