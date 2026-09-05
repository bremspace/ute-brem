import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import clsx from 'clsx';
import {
  ArrowLeftRight,
  Plus,
  Truck,
  Package,
  Check,
  X,
  ChevronDown,
  ChevronUp,
  Store,
  AlertCircle,
} from 'lucide-react';
import {
  Button,
  Card,
  CardHeader,
  Modal,
  Select,
  Input,
  StatCard,
  StatusBadge,
  TableContainer,
  TableHeader,
  TableBase,
  TableRow,
  TableEmpty,
} from '../components/ui';
import type { SelectOption } from '../components/ui';

export const BranchTransfersView: React.FC = () => {
  const {
    branchTransfers,
    branches,
    locations,
    products,
    createBranchTransfer,
    shipBranchTransfer,
    receiveBranchTransfer,
    cancelBranchTransfer,
  } = useApp();

  const [showCreateModal, setShowCreateModal] = useState(false);
  const [receiveModalTransfer, setReceiveModalTransfer] = useState<number | null>(null);
  const [expandedId, setExpandedId] = useState<number | null>(null);

  // Create modal state
  const [sourceBranchId, setSourceBranchId] = useState<number>(0);
  const [targetBranchId, setTargetBranchId] = useState<number>(0);
  const [targetLocationId, setTargetLocationId] = useState<number>(0);
  const [lineItems, setLineItems] = useState<{ productId: number; quantity: number; notes: string }[]>([]);
  const [transferNotes, setTransferNotes] = useState('');

  // Receive modal state
  const [receiveItems, setReceiveItems] = useState<{ productId: number; receivedQuantity: number }[]>([]);

  const activeBranches = branches.filter(b => b.is_active);

  const sourceLocations = sourceBranchId
    ? locations.filter(l => l.branch_id === sourceBranchId && l.is_active)
    : [];

  const targetLocations = targetBranchId
    ? locations.filter(l => l.branch_id === targetBranchId && l.is_active)
    : [];

  const totalDraft = branchTransfers.filter(t => t.status === 'draft').length;
  const totalInTransit = branchTransfers.filter(t => t.status === 'in_transit').length;
  const totalCompleted = branchTransfers.filter(t => t.status === 'completed').length;
  const totalCancelled = branchTransfers.filter(t => t.status === 'cancelled').length;

  const resetCreateModal = () => {
    setSourceBranchId(0);
    setTargetBranchId(0);
    setTargetLocationId(0);
    setLineItems([]);
    setTransferNotes('');
    setShowCreateModal(false);
  };

  const handleAddLineItem = () => {
    setLineItems([...lineItems, { productId: 0, quantity: 1, notes: '' }]);
  };

  const handleUpdateLineItem = (index: number, field: string, value: string | number) => {
    const updated = [...lineItems];
    (updated[index] as any)[field] = value;
    setLineItems(updated);
  };

  const handleRemoveLineItem = (index: number) => {
    setLineItems(lineItems.filter((_, i) => i !== index));
  };

  const handleCreateSubmit = () => {
    const validItems = lineItems.filter(li => li.productId > 0 && li.quantity > 0);

    if (validItems.length === 0) {
      alert('Minimal harus ada 1 item dengan jumlah > 0');
      return;
    }

    const sourceLocation = sourceLocations[0];
    if (!sourceLocation || !targetLocationId) {
      alert('Lokasi sumber dan lokasi tujuan harus dipilih');
      return;
    }

    createBranchTransfer({
      sourceBranchId,
      targetBranchId,
      sourceLocationId: sourceLocation.id,
      targetLocationId,
      items: validItems.map(li => ({
        productId: li.productId,
        quantity: li.quantity,
        notes: li.notes || undefined,
      })),
      notes: transferNotes || undefined,
    });

    resetCreateModal();
  };

  const handleShip = (transferId: number) => {
    if (confirm('Konfirmasi kirim transfer ini? Stok akan dikurangi dari lokasi sumber.')) {
      shipBranchTransfer(transferId);
    }
  };

  const handleCancel = (transferId: number) => {
    if (confirm('Konfirmasi batalkan transfer ini?')) {
      cancelBranchTransfer(transferId);
    }
  };

  const handleOpenReceiveModal = (transferId: number) => {
    const transfer = branchTransfers.find(t => t.id === transferId);
    if (!transfer) return;

    setReceiveItems(
      transfer.items.map(item => ({
        productId: item.product_id,
        receivedQuantity: item.quantity_sent,
      }))
    );
    setReceiveModalTransfer(transferId);
  };

  const handleConfirmReceive = () => {
    if (receiveModalTransfer === null) return;
    receiveBranchTransfer(receiveModalTransfer, receiveItems);
    setReceiveModalTransfer(null);
    setReceiveItems([]);
  };

  const formatDate = (isoString?: string) => {
    if (!isoString) return '-';
    const d = new Date(isoString);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
  };

  const sourceBranchOptions: SelectOption[] = [
    { value: 0, label: 'Pilih cabang sumber' },
    ...activeBranches.map(b => ({ value: b.id, label: b.name })),
  ];

  const targetBranchOptions: SelectOption[] = [
    { value: 0, label: 'Pilih cabang tujuan' },
    ...activeBranches
      .filter(b => b.id !== sourceBranchId)
      .map(b => ({ value: b.id, label: b.name })),
  ];

  const targetLocationOptions: SelectOption[] = [
    { value: 0, label: 'Pilih lokasi tujuan' },
    ...targetLocations.map(l => ({ value: l.id, label: l.name })),
  ];

  const productOptions: SelectOption[] = [
    { value: 0, label: 'Pilih produk' },
    ...products.filter(p => p.is_active).map(p => ({ value: p.id, label: `${p.product_code} - ${p.name}` })),
  ];

  const columns = useMemo(
    () => [
      { header: 'No Transfer' },
      { header: 'Dari → Ke' },
      { header: 'Status' },
      { header: 'Item' },
      { header: 'Total Qty', align: 'right' as const },
      { header: 'Dibuat oleh' },
      { header: 'Tanggal' },
      { header: 'Aksi' },
    ],
    [],
  );

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Page Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div className="flex items-center gap-2">
            <Store className="w-5 h-5 text-primary-600" />
            <h1 className="text-2xl font-black text-slate-900 tracking-tight">Transfer Cabang</h1>
          </div>
          <p className="text-xs text-slate-500 mt-1">
            Transfer stok antar cabang untuk memindahkan barang dari satu lokasi ke lokasi lain.
          </p>
        </div>
        <Button
          variant="primary"
          icon={<Plus className="w-4 h-4" />}
          onClick={() => setShowCreateModal(true)}
        >
          Buat Transfer Baru
        </Button>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          value={branchTransfers.length.toLocaleString('id-ID')}
          label="Total Transfer"
          icon={<ArrowLeftRight className="w-4 h-4" />}
          color="primary"
        />
        <StatCard
          value={totalInTransit.toLocaleString('id-ID')}
          label="Dalam Perjalanan"
          description="Transfer aktif"
          icon={<Truck className="w-4 h-4" />}
          color="info"
        />
        <StatCard
          value={totalCompleted.toLocaleString('id-ID')}
          label="Selesai"
          description="Berhasil diterima"
          icon={<Check className="w-4 h-4" />}
          color="success"
        />
        <StatCard
          value={totalCancelled.toLocaleString('id-ID')}
          label="Dibatalkan"
          description="Transfer batal"
          icon={<X className="w-4 h-4" />}
          color="danger"
        />
      </div>

      {/* Transfers Table */}
      <TableContainer>
        <TableHeader
          icon={<Truck className="w-4 h-4 text-primary-600" />}
          count={branchTransfers.length}
        >
          Daftar Transfer
        </TableHeader>

        <TableBase columns={columns} colSpan={columns.length}>
          {branchTransfers.length === 0 ? (
            <TableEmpty
              colSpan={columns.length}
              message="Belum ada transfer"
              icon={<Package className="w-10 h-10 mx-auto mb-2 text-slate-300" />}
            />
          ) : (
            branchTransfers.map(transfer => (
              <React.Fragment key={transfer.id}>
                <TableRow
                  className="cursor-pointer"
                  onClick={() => setExpandedId(expandedId === transfer.id ? null : transfer.id)}
                >
                  <td className="px-5 py-3 font-mono font-bold text-primary-700">{transfer.transfer_code}</td>
                  <td className="px-4 py-3 font-medium text-slate-800">
                    {transfer.source_branch?.name || 'N/A'}
                    <span className="text-slate-400 mx-1">→</span>
                    {transfer.target_branch?.name || 'N/A'}
                  </td>
                  <td className="px-4 py-3">
                    <StatusBadge status={transfer.status}>
                      {transfer.status === 'draft'
                        ? 'DRAFT'
                        : transfer.status === 'in_transit'
                        ? 'DALAM PERJALANAN'
                        : transfer.status === 'completed'
                        ? 'SELESAI'
                        : 'DIBATALKAN'}
                    </StatusBadge>
                  </td>
                  <td className="px-4 py-3">{transfer.items.length} item</td>
                  <td className="px-4 py-3 font-bold text-slate-900 text-right">
                    {transfer.items.reduce((sum, it) => sum + it.quantity_sent, 0)} pcs
                  </td>
                  <td className="px-4 py-3">{transfer.created_by_name}</td>
                  <td className="px-4 py-3">{formatDate(transfer.created_at)}</td>
                  <td className="px-4 py-3" onClick={e => e.stopPropagation()}>
                    <div className="flex items-center gap-1.5">
                      {transfer.status === 'draft' && (
                        <>
                          <Button
                            variant="secondary"
                            size="xs"
                            icon={<Truck className="w-3 h-3" />}
                            onClick={() => handleShip(transfer.id)}
                          >
                            Kirim
                          </Button>
                          <Button
                            variant="danger"
                            size="xs"
                            icon={<X className="w-3 h-3" />}
                            onClick={() => handleCancel(transfer.id)}
                          >
                            Batal
                          </Button>
                        </>
                      )}
                      {transfer.status === 'in_transit' && (
                        <Button
                          variant="primary"
                          size="xs"
                          icon={<Check className="w-3 h-3" />}
                          onClick={() => handleOpenReceiveModal(transfer.id)}
                        >
                          Terima
                        </Button>
                      )}
                      <button
                        onClick={() => setExpandedId(expandedId === transfer.id ? null : transfer.id)}
                        className="p-1 text-slate-400 hover:text-slate-600 rounded"
                      >
                        {expandedId === transfer.id ? (
                          <ChevronUp className="w-4 h-4" />
                        ) : (
                          <ChevronDown className="w-4 h-4" />
                        )}
                      </button>
                    </div>
                  </td>
                </TableRow>
                {/* Expanded Items Detail */}
                {expandedId === transfer.id && (
                  <tr>
                    <td colSpan={8} className="px-5 py-3 bg-slate-50/80">
                      <div className="text-xs font-bold text-slate-600 mb-2 uppercase tracking-wider">
                        Detail Item
                      </div>
                      <table className="w-full text-left text-[11px]">
                        <thead className="text-slate-500 font-semibold">
                          <tr>
                            <th className="py-1.5">Kode</th>
                            <th className="py-1.5">Nama Produk</th>
                            <th className="py-1.5 text-right">Qty Kirim</th>
                            <th className="py-1.5 text-right">Qty Diterima</th>
                            <th className="py-1.5 text-right">Hilang/Rusak</th>
                            <th className="py-1.5">Catatan</th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-200">
                          {transfer.items.map(item => (
                            <tr key={item.id} className="text-slate-700">
                              <td className="py-1.5 font-mono font-semibold">{item.product_code}</td>
                              <td className="py-1.5">{item.product_name}</td>
                              <td className="py-1.5 text-right font-bold">{item.quantity_sent}</td>
                              <td className="py-1.5 text-right font-bold">
                                {item.quantity_received !== undefined ? item.quantity_received : '-'}
                              </td>
                              <td className="py-1.5 text-right font-bold">
                                {item.quantity_lost !== undefined && item.quantity_lost > 0 ? (
                                  <span className="text-red-600">{item.quantity_lost}</span>
                                ) : (
                                  '-'
                                )}
                              </td>
                              <td className="py-1.5 text-slate-500">{item.notes || '-'}</td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                      {transfer.notes && (
                        <div className="mt-2 text-[11px] text-slate-500">
                          <span className="font-semibold">Catatan Transfer:</span> {transfer.notes}
                        </div>
                      )}
                    </td>
                  </tr>
                )}
              </React.Fragment>
            ))
          )}
        </TableBase>
      </TableContainer>

      {/* Create Modal */}
      <Modal
        open={showCreateModal}
        onClose={resetCreateModal}
        title="Buat Transfer Baru"
        size="xl"
        footer={
          <>
            <Button variant="secondary" onClick={resetCreateModal}>
              Batal
            </Button>
            <Button
              variant="primary"
              icon={<Check className="w-3.5 h-3.5" />}
              onClick={handleCreateSubmit}
              disabled={sourceBranchId === 0 || targetBranchId === 0 || targetLocationId === 0 || lineItems.length === 0}
            >
              Simpan Transfer
            </Button>
          </>
        }
      >
        <div className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <Select
              label="Cabang Sumber"
              options={sourceBranchOptions}
              value={sourceBranchId}
              onChange={e => {
                setSourceBranchId(Number(e.target.value));
                setTargetBranchId(0);
                setTargetLocationId(0);
              }}
            />
            <Select
              label="Cabang Tujuan"
              options={targetBranchOptions}
              value={targetBranchId}
              onChange={e => {
                setTargetBranchId(Number(e.target.value));
                setTargetLocationId(0);
              }}
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <Input
              label="Lokasi Sumber (Otomatis)"
              value={
                sourceLocations.length > 0
                  ? sourceLocations[0].name
                  : sourceBranchId
                  ? 'Tidak ada lokasi'
                  : '-'
              }
              disabled
            />
            <Select
              label="Lokasi Tujuan"
              options={targetLocationOptions}
              value={targetLocationId}
              onChange={e => setTargetLocationId(Number(e.target.value))}
            />
          </div>

          {/* Line Items */}
          <div>
            <div className="flex items-center justify-between mb-2">
              <label className="text-xs font-bold text-slate-700 uppercase tracking-wider">Item Barang</label>
              <Button
                variant="outline"
                size="xs"
                icon={<Plus className="w-3 h-3" />}
                onClick={handleAddLineItem}
              >
                Tambah Item
              </Button>
            </div>
            <div className="space-y-2">
              {lineItems.map((li, idx) => (
                <div key={idx} className="flex items-center gap-2 p-2 bg-slate-50 border border-slate-200 rounded-xl">
                  <Select
                    options={productOptions}
                    value={li.productId}
                    onChange={e => handleUpdateLineItem(idx, 'productId', Number(e.target.value))}
                    wrapperClassName="flex-1"
                  />
                  <div className="w-20">
                    <Input
                      type="number"
                      min={1}
                      value={li.quantity}
                      onChange={e => handleUpdateLineItem(idx, 'quantity', Math.max(1, Number(e.target.value)))}
                      placeholder="Qty"
                    />
                  </div>
                  <div className="w-32">
                    <Input
                      type="text"
                      value={li.notes}
                      onChange={e => handleUpdateLineItem(idx, 'notes', e.target.value)}
                      placeholder="Catatan"
                    />
                  </div>
                  <button
                    onClick={() => handleRemoveLineItem(idx)}
                    className="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50"
                  >
                    <X className="w-3.5 h-3.5" />
                  </button>
                </div>
              ))}
            </div>
            {lineItems.length === 0 && (
              <div className="text-center py-4 text-slate-400 text-xs">
                <AlertCircle className="w-5 h-5 mx-auto mb-1" />
                Klik "Tambah Item" untuk menambah barang
              </div>
            )}
          </div>

          {/* Notes */}
          <div>
            <label className="block text-xs font-bold text-slate-700 mb-1">Catatan</label>
            <textarea
              value={transferNotes}
              onChange={e => setTransferNotes(e.target.value)}
              className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
              rows={2}
              placeholder="Catatan transfer (opsional)"
            />
          </div>
        </div>
      </Modal>

      {/* Receive Modal */}
      <Modal
        open={receiveModalTransfer !== null}
        onClose={() => setReceiveModalTransfer(null)}
        title="Terima Transfer"
        subtitle={
          receiveModalTransfer !== null
            ? branchTransfers.find(t => t.id === receiveModalTransfer)?.transfer_code
            : undefined
        }
        size="md"
        footer={
          <>
            <Button variant="secondary" onClick={() => setReceiveModalTransfer(null)}>
              Batal
            </Button>
            <Button
              variant="primary"
              icon={<Check className="w-3.5 h-3.5" />}
              onClick={handleConfirmReceive}
            >
              Konfirmasi Penerimaan
            </Button>
          </>
        }
      >
        {receiveModalTransfer !== null && (() => {
          const transfer = branchTransfers.find(t => t.id === receiveModalTransfer);
          if (!transfer) return null;

          return (
            <div className="space-y-3">
              <div className="text-xs text-slate-500 mb-3">
                <span className="font-semibold text-slate-700">Dari:</span> {transfer.source_branch?.name} →
                <span className="font-semibold text-slate-700"> Ke:</span> {transfer.target_branch?.name}
              </div>

              {transfer.items.map(item => {
                const ri = receiveItems.find(r => r.productId === item.product_id);
                const sent = ri?.receivedQuantity ?? item.quantity_sent;
                const lost = item.quantity_sent - sent;

                return (
                  <div key={item.id} className="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <div className="flex items-center justify-between mb-2">
                      <div>
                        <div className="text-xs font-bold text-slate-800">{item.product_name}</div>
                        <div className="text-[10px] font-mono text-slate-500">{item.product_code}</div>
                      </div>
                      <div className="text-[10px] font-bold text-slate-500">
                        Dikirim: {item.quantity_sent}
                      </div>
                    </div>
                    <div className="flex items-center gap-2">
                      <label className="text-[11px] font-semibold text-slate-600 whitespace-nowrap">
                        Diterima:
                      </label>
                      <div className="flex-1">
                        <Input
                          type="number"
                          min={0}
                          max={item.quantity_sent}
                          value={sent}
                          onChange={e => {
                            const val = Math.max(0, Math.min(item.quantity_sent, Number(e.target.value)));
                            setReceiveItems(prev =>
                              prev.map(ri =>
                                ri.productId === item.product_id
                                  ? { ...ri, receivedQuantity: val }
                                  : ri
                              )
                            );
                          }}
                        />
                      </div>
                      {lost > 0 && (
                        <span className="text-[10px] font-bold text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-lg whitespace-nowrap">
                          Hilang: {lost}
                        </span>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          );
        })()}
      </Modal>
    </div>
  );
};
