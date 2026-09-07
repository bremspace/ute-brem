import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import clsx from 'clsx';
import {
  ListChecks,
  Package,
  Filter,
  User,
  MapPin,
  ChevronDown,
  ChevronUp,
  Check,
  Clock,
  Play,
  Hash,
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

type PickingStatus = 'pending' | 'in_progress' | 'completed' | 'cancelled';

interface PickingRequestItem {
  id: number;
  product_id: number;
  product_code: string;
  product_name: string;
  requested_qty: number;
  picked_qty: number | null;
  notes: string;
}

interface PickingRequest {
  id: number;
  picking_code: string;
  source_location: string;
  technician_name: string;
  status: PickingStatus;
  request_date: string;
  completed_date?: string;
  notes: string;
  items: PickingRequestItem[];
  created_at: string;
}

const STATUS_LABELS: Record<PickingStatus, string> = {
  pending: 'Menunggu',
  in_progress: 'Diambil',
  completed: 'Selesai',
  cancelled: 'Dibatalkan',
};

function generateMockPickingData(
  products: ReturnType<typeof useApp>['products'],
  serviceTransactions: ReturnType<typeof useApp>['serviceTransactions'],
): PickingRequest[] {
  if (products.length === 0) return [];

  const now = new Date();
  const technicians = ['Andi Teknisi', 'Budi Teknisi', 'Citra Mekanik'];
  const locations = ['Gudang Pusat', 'Toko Kasir Pusat'];

  const requests: PickingRequest[] = [
    {
      id: 1,
      picking_code: 'PCK-2609-0001',
      source_location: locations[0],
      technician_name: technicians[0],
      status: 'pending',
      request_date: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`,
      notes: 'Untuk servis LCD Samsung',
      items: products.slice(0, 3).map((p, i) => ({
        id: i + 1,
        product_id: p.id,
        product_code: p.product_code,
        product_name: p.name,
        requested_qty: i + 1,
        picked_qty: null,
        notes: '',
      })),
      created_at: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01T09:00:00`,
    },
    {
      id: 2,
      picking_code: 'PCK-2609-0002',
      source_location: locations[1],
      technician_name: technicians[1],
      status: 'in_progress',
      request_date: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-03`,
      notes: 'Servis baterai iPhone',
      items: products.slice(1, 4).map((p, i) => ({
        id: i + 10,
        product_id: p.id,
        product_code: p.product_code,
        product_name: p.name,
        requested_qty: i + 1,
        picked_qty: i < 2 ? i + 1 : null,
        notes: '',
      })),
      created_at: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-03T10:30:00`,
    },
    {
      id: 3,
      picking_code: 'PCK-2609-0003',
      source_location: locations[0],
      technician_name: technicians[2],
      status: 'completed',
      request_date: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-02`,
      completed_date: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-02`,
      notes: 'Sudah diambil semua',
      items: products.slice(0, 2).map((p, i) => ({
        id: i + 20,
        product_id: p.id,
        product_code: p.product_code,
        product_name: p.name,
        requested_qty: 2,
        picked_qty: 2,
        notes: '',
      })),
      created_at: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-02T08:00:00`,
    },
  ];

  return requests;
}

function formatDate(iso: string): string {
  return new Date(iso).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

export const PickingRequestsView: React.FC = () => {
  const { products, serviceTransactions } = useApp();

  const [statusFilter, setStatusFilter] = useState<PickingStatus | 'all'>('all');
  const [search, setSearch] = useState('');
  const [expandedId, setExpandedId] = useState<number | null>(null);

  const requests = useMemo(
    () => generateMockPickingData(products, serviceTransactions),
    [products, serviceTransactions],
  );

  const filtered = useMemo(() => {
    const q = search.toLowerCase().trim();
    return requests.filter(r => {
      if (statusFilter !== 'all' && r.status !== statusFilter) return false;
      if (q) {
        return (
          r.picking_code.toLowerCase().includes(q) ||
          r.technician_name.toLowerCase().includes(q) ||
          r.source_location.toLowerCase().includes(q)
        );
      }
      return true;
    });
  }, [requests, statusFilter, search]);

  const totalPending = requests.filter(r => r.status === 'pending').length;
  const totalInProgress = requests.filter(r => r.status === 'in_progress').length;
  const totalCompleted = requests.filter(r => r.status === 'completed').length;
  const totalItems = requests.reduce((sum, r) => sum + r.items.length, 0);

  const statusOptions: SelectOption[] = [
    { value: 'all', label: 'Semua Status' },
    { value: 'pending', label: 'Menunggu' },
    { value: 'in_progress', label: 'Diambil' },
    { value: 'completed', label: 'Selesai' },
    { value: 'cancelled', label: 'Dibatalkan' },
  ];

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Header */}
      <div>
        <div className="flex items-center gap-2">
          <ListChecks className="w-5 h-5 text-primary-600" />
          <h1 className="text-2xl font-black text-slate-900 tracking-tight">Picking Request</h1>
        </div>
        <p className="text-xs text-slate-500 mt-1">
          Kelola permintaan pengambilan barang untuk kebutuhan servis atau teknisi.
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
                placeholder="Cari kode picking, teknisi, atau lokasi..."
              />
            </div>
            <Select
              options={statusOptions}
              value={statusFilter}
              onChange={e => setStatusFilter(e.target.value as PickingStatus | 'all')}
              wrapperClassName="w-auto"
            />
          </div>
        </div>
      </Card>

      {/* Stats */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          value={requests.length.toLocaleString('id-ID')}
          label="Total Request"
          icon={<ListChecks className="w-4 h-4" />}
          color="primary"
        />
        <StatCard
          value={totalPending.toLocaleString('id-ID')}
          label="Menunggu"
          description="Belum diambil"
          icon={<Clock className="w-4 h-4" />}
          color="warning"
        />
        <StatCard
          value={totalInProgress.toLocaleString('id-ID')}
          label="Di Proses"
          description="Sedang diambil"
          icon={<Play className="w-4 h-4" />}
          color="info"
        />
        <StatCard
          value={totalCompleted.toLocaleString('id-ID')}
          label="Selesai"
          description="Sudah diambil"
          icon={<Check className="w-4 h-4" />}
          color="success"
        />
      </div>

      {/* Table */}
      <TableContainer>
        <TableHeader
          icon={<ListChecks className="w-4 h-4 text-primary-600" />}
          count={filtered.length}
        >
          Daftar Picking Request
        </TableHeader>

        <TableBase
          columns={[
            { header: 'Kode' },
            { header: 'Teknisi' },
            { header: 'Lokasi' },
            { header: 'Tanggal' },
            { header: 'Status' },
            { header: 'Item', align: 'right' as const },
          ]}
          colSpan={6}
        >
          {filtered.length === 0 ? (
            <TableEmpty
              colSpan={6}
              message="Belum ada picking request"
              icon={<Package className="w-10 h-10 mx-auto mb-2 text-slate-300" />}
            />
          ) : (
            filtered.map(req => {
              const allPicked = req.items.every(it => it.picked_qty !== null && it.picked_qty >= it.requested_qty);
              const partialPicked = req.items.some(it => it.picked_qty !== null && it.picked_qty > 0);

              return (
                <React.Fragment key={req.id}>
                  <TableRow
                    className="cursor-pointer"
                    onClick={() => setExpandedId(expandedId === req.id ? null : req.id)}
                  >
                    <td className="px-5 py-3 font-mono font-bold text-primary-700">{req.picking_code}</td>
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-1 text-slate-700">
                        <User className="w-3 h-3 text-slate-400" />
                        <span className="font-medium">{req.technician_name}</span>
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-1 text-slate-600">
                        <MapPin className="w-3 h-3 text-slate-400" />
                        <span className="line-clamp-1">{req.source_location}</span>
                      </div>
                    </td>
                    <td className="px-4 py-3 whitespace-nowrap">{formatDate(req.request_date)}</td>
                    <td className="px-4 py-3">
                      <StatusBadge status={req.status}>
                        {STATUS_LABELS[req.status]}
                      </StatusBadge>
                    </td>
                    <td className="px-4 py-3 text-right">
                      <span className="font-bold">{req.items.length}</span>
                      {allPicked && req.status !== 'completed' && (
                        <span className="ml-1 text-[10px] font-bold text-emerald-600">✓</span>
                      )}
                      {partialPicked && !allPicked && (
                        <span className="ml-1 text-[10px] font-bold text-amber-600">Partial</span>
                      )}
                    </td>
                  </TableRow>
                  {expandedId === req.id && (
                    <tr>
                      <td colSpan={6} className="px-5 py-3 bg-slate-50/80">
                        <div className="text-xs font-bold text-slate-600 mb-2 uppercase tracking-wider">
                          Detail Item
                        </div>
                        <table className="w-full text-left text-[11px]">
                          <thead className="text-slate-500 font-semibold">
                            <tr>
                              <th className="py-1.5">Kode</th>
                              <th className="py-1.5">Nama Produk</th>
                              <th className="py-1.5 text-right">Qty Diminta</th>
                              <th className="py-1.5 text-right">Qty Diambil</th>
                              <th className="py-1.5">Status</th>
                              <th className="py-1.5">Catatan</th>
                            </tr>
                          </thead>
                          <tbody className="divide-y divide-slate-200">
                            {req.items.map(item => {
                              const picked = item.picked_qty ?? 0;
                              const isComplete = picked >= item.requested_qty;
                              return (
                                <tr key={item.id} className="text-slate-700">
                                  <td className="py-1.5 font-mono font-semibold">{item.product_code}</td>
                                  <td className="py-1.5">{item.product_name}</td>
                                  <td className="py-1.5 text-right font-bold">{item.requested_qty}</td>
                                  <td className="py-1.5 text-right font-bold">
                                    {item.picked_qty !== null ? item.picked_qty : (
                                      <span className="text-slate-400 italic">-</span>
                                    )}
                                  </td>
                                  <td className="py-1.5">
                                    {item.picked_qty !== null ? (
                                      isComplete ? (
                                        <Badge variant="success">Selesai</Badge>
                                      ) : (
                                        <Badge variant="warning">Sebagian</Badge>
                                      )
                                    ) : (
                                      <Badge variant="info">Menunggu</Badge>
                                    )}
                                  </td>
                                  <td className="py-1.5 text-slate-500">{item.notes || '-'}</td>
                                </tr>
                              );
                            })}
                          </tbody>
                        </table>
                        {req.notes && (
                          <div className="mt-2 text-[11px] text-slate-500">
                            <span className="font-semibold">Catatan:</span> {req.notes}
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
