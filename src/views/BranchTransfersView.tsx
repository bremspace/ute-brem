import React, { useState } from 'react';
import { useApp } from '../context/AppContext';
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
  AlertCircle
} from 'lucide-react';

export const BranchTransfersView: React.FC = () => {
  const {
    branchTransfers,
    branches,
    locations,
    products,
    createBranchTransfer,
    shipBranchTransfer,
    receiveBranchTransfer,
    cancelBranchTransfer
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
        notes: li.notes || undefined
      })),
      notes: transferNotes || undefined
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
        receivedQuantity: item.quantity_sent
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
        <button
          onClick={() => setShowCreateModal(true)}
          className="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/30 transition-all flex items-center gap-1.5"
        >
          <Plus className="w-4 h-4" />
          Buat Transfer Baru
        </button>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Transfer</span>
            <div className="w-8 h-8 rounded-lg bg-slate-50 text-slate-600 flex items-center justify-center">
              <ArrowLeftRight className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-slate-900">{branchTransfers.length}</div>
          </div>
        </div>
        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Dalam Perjalanan</span>
            <div className="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
              <Truck className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-blue-600">{totalInTransit}</div>
          </div>
        </div>
        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Selesai</span>
            <div className="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <Check className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-emerald-600">{totalCompleted}</div>
          </div>
        </div>
        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Dibatalkan</span>
            <div className="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
              <X className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-red-600">{totalCancelled}</div>
          </div>
        </div>
      </div>

      {/* Transfers Table */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
          <Truck className="w-4 h-4 text-primary-600" />
          <span className="font-bold text-slate-800 text-sm">Daftar Transfer</span>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
              <tr>
                <th className="px-5 py-3">No Transfer</th>
                <th className="px-4 py-3">Dari → Ke</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Item</th>
                <th className="px-4 py-3">Total Qty</th>
                <th className="px-4 py-3">Dibuat oleh</th>
                <th className="px-4 py-3">Tanggal</th>
                <th className="px-4 py-3">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 text-slate-700">
              {branchTransfers.length === 0 ? (
                <tr>
                  <td colSpan={8} className="px-5 py-12 text-center text-slate-400">
                    <div className="flex flex-col items-center">
                      <Package className="w-10 h-10 stroke-[1.2] mb-2 text-slate-300" />
                      <p className="text-sm font-semibold">Belum ada transfer</p>
                      <p className="text-xs text-slate-400 mt-0.5">Klik "Buat Transfer Baru" untuk memulai</p>
                    </div>
                  </td>
                </tr>
              ) : (
                branchTransfers.map(transfer => (
                  <React.Fragment key={transfer.id}>
                    <tr
                      className="hover:bg-slate-50/70 transition-colors cursor-pointer"
                      onClick={() => setExpandedId(expandedId === transfer.id ? null : transfer.id)}
                    >
                      <td className="px-5 py-3 font-mono font-bold text-primary-700">{transfer.transfer_code}</td>
                      <td className="px-4 py-3 font-medium text-slate-800">
                        {transfer.source_branch?.name || 'N/A'}
                        <span className="text-slate-400 mx-1">→</span>
                        {transfer.target_branch?.name || 'N/A'}
                      </td>
                      <td className="px-4 py-3">
                        <span
                          className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                            transfer.status === 'draft'
                              ? 'bg-slate-100 text-slate-700'
                              : transfer.status === 'in_transit'
                              ? 'bg-blue-100 text-blue-800'
                              : transfer.status === 'completed'
                              ? 'bg-emerald-100 text-emerald-800'
                              : 'bg-red-100 text-red-800'
                          }`}
                        >
                          {transfer.status === 'draft'
                            ? 'DRAFT'
                            : transfer.status === 'in_transit'
                            ? 'DALAM PERJALANAN'
                            : transfer.status === 'completed'
                            ? 'SELESAI'
                            : 'DIBATALKAN'}
                        </span>
                      </td>
                      <td className="px-4 py-3">{transfer.items.length} item</td>
                      <td className="px-4 py-3 font-bold text-slate-900">
                        {transfer.items.reduce((sum, it) => sum + it.quantity_sent, 0)} pcs
                      </td>
                      <td className="px-4 py-3">{transfer.created_by_name}</td>
                      <td className="px-4 py-3">{formatDate(transfer.created_at)}</td>
                      <td className="px-4 py-3" onClick={e => e.stopPropagation()}>
                        <div className="flex items-center gap-1.5">
                          {transfer.status === 'draft' && (
                            <>
                              <button
                                onClick={() => handleShip(transfer.id)}
                                className="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] rounded-lg transition-colors flex items-center gap-1"
                              >
                                <Truck className="w-3 h-3" />
                                Kirim
                              </button>
                              <button
                                onClick={() => handleCancel(transfer.id)}
                                className="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-600 font-bold text-[10px] rounded-lg transition-colors flex items-center gap-1"
                              >
                                <X className="w-3 h-3" />
                                Batal
                              </button>
                            </>
                          )}
                          {transfer.status === 'in_transit' && (
                            <button
                              onClick={() => handleOpenReceiveModal(transfer.id)}
                              className="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] rounded-lg transition-colors flex items-center gap-1"
                            >
                              <Check className="w-3 h-3" />
                              Terima
                            </button>
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
                    </tr>
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
            </tbody>
          </table>
        </div>
      </div>

      {/* Create Modal */}
      {showCreateModal && (
        <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div className="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
              <h2 className="text-base font-black text-slate-900">Buat Transfer Baru</h2>
              <button onClick={resetCreateModal} className="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-600">
                <X className="w-4 h-4" />
              </button>
            </div>
            <div className="px-6 py-5 space-y-4">
              {/* Source Branch */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Cabang Sumber</label>
                  <select
                    value={sourceBranchId || ''}
                    onChange={e => {
                      setSourceBranchId(Number(e.target.value));
                      setTargetBranchId(0);
                      setTargetLocationId(0);
                    }}
                    className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Pilih cabang sumber</option>
                    {activeBranches.map(b => (
                      <option key={b.id} value={b.id}>
                        {b.name}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Cabang Tujuan</label>
                  <select
                    value={targetBranchId || ''}
                    onChange={e => {
                      setTargetBranchId(Number(e.target.value));
                      setTargetLocationId(0);
                    }}
                    className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Pilih cabang tujuan</option>
                    {activeBranches
                      .filter(b => b.id !== sourceBranchId)
                      .map(b => (
                        <option key={b.id} value={b.id}>
                          {b.name}
                        </option>
                      ))}
                  </select>
                </div>
              </div>

              {/* Location info & target location */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Lokasi Sumber (Otomatis)</label>
                  <div className="px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600">
                    {sourceLocations.length > 0
                      ? sourceLocations[0].name
                      : sourceBranchId
                      ? 'Tidak ada lokasi'
                      : '-'}
                  </div>
                </div>
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Lokasi Tujuan</label>
                  <select
                    value={targetLocationId || ''}
                    onChange={e => setTargetLocationId(Number(e.target.value))}
                    className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Pilih lokasi tujuan</option>
                    {targetLocations.map(l => (
                      <option key={l.id} value={l.id}>
                        {l.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              {/* Line Items */}
              <div>
                <div className="flex items-center justify-between mb-2">
                  <label className="text-xs font-bold text-slate-700 uppercase tracking-wider">Item Barang</label>
                  <button
                    onClick={handleAddLineItem}
                    className="px-2.5 py-1 bg-primary-50 hover:bg-primary-100 text-primary-600 font-bold text-[10px] rounded-lg flex items-center gap-1"
                  >
                    <Plus className="w-3 h-3" />
                    Tambah Item
                  </button>
                </div>
                <div className="space-y-2">
                  {lineItems.map((li, idx) => (
                    <div key={idx} className="flex items-center gap-2 p-2 bg-slate-50 border border-slate-200 rounded-xl">
                      <select
                        value={li.productId || ''}
                        onChange={e => handleUpdateLineItem(idx, 'productId', Number(e.target.value))}
                        className="flex-1 px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
                      >
                        <option value="">Pilih produk</option>
                        {products
                          .filter(p => p.is_active)
                          .map(p => (
                            <option key={p.id} value={p.id}>
                              {p.product_code} - {p.name}
                            </option>
                          ))}
                      </select>
                      <input
                        type="number"
                        min="1"
                        value={li.quantity}
                        onChange={e => handleUpdateLineItem(idx, 'quantity', Math.max(1, Number(e.target.value)))}
                        className="w-20 px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 text-center focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="Qty"
                      />
                      <input
                        type="text"
                        value={li.notes}
                        onChange={e => handleUpdateLineItem(idx, 'notes', e.target.value)}
                        className="w-32 px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs text-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="Catatan"
                      />
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
            <div className="px-6 py-4 border-t border-slate-200 flex justify-end gap-2">
              <button
                onClick={resetCreateModal}
                className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl"
              >
                Batal
              </button>
              <button
                onClick={handleCreateSubmit}
                disabled={sourceBranchId === 0 || targetBranchId === 0 || targetLocationId === 0 || lineItems.length === 0}
                className="px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/30 transition-all flex items-center gap-1.5"
              >
                <Check className="w-3.5 h-3.5" />
                Simpan Transfer
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Receive Modal */}
      {receiveModalTransfer !== null && (() => {
        const transfer = branchTransfers.find(t => t.id === receiveModalTransfer);
        if (!transfer) return null;

        return (
          <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div className="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
              <div className="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                  <h2 className="text-base font-black text-slate-900">Terima Transfer</h2>
                  <p className="text-[11px] text-slate-500 mt-0.5 font-mono">{transfer.transfer_code}</p>
                </div>
                <button
                  onClick={() => setReceiveModalTransfer(null)}
                  className="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-600"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>
              <div className="px-6 py-5 space-y-3">
                <div className="text-xs text-slate-500 mb-3">
                  <span className="font-semibold text-slate-700">Dari:</span> {transfer.source_branch?.name} →
                  <span className="font-semibold text-slate-700"> Ke:</span> {transfer.target_branch?.name}
                </div>

                {transfer.items.map((item, idx) => {
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
                        <input
                          type="number"
                          min="0"
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
                          className="flex-1 px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 text-center focus:outline-none focus:ring-2 focus:ring-primary-500"
                        />
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
              <div className="px-6 py-4 border-t border-slate-200 flex justify-end gap-2">
                <button
                  onClick={() => setReceiveModalTransfer(null)}
                  className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl"
                >
                  Batal
                </button>
                <button
                  onClick={handleConfirmReceive}
                  className="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/30 transition-all flex items-center gap-1.5"
                >
                  <Check className="w-3.5 h-3.5" />
                  Konfirmasi Penerimaan
                </button>
              </div>
            </div>
          </div>
        );
      })()}
    </div>
  );
};
