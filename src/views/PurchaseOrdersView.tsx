import React, { useState } from 'react';
import { useApp } from '../context/AppContext';
import { PurchaseOrder } from '../types';
import {
  PackagePlus,
  Truck,
  Banknote,
  Plus,
  Check,
  X,
  Eye,
  ChevronDown,
  ChevronUp,
  ShoppingCart,
  Trash2,
  FileText,
  Wallet,
  Store
} from 'lucide-react';

interface LineItemDraft {
  productId: number;
  quantity: number;
  unitPrice: number;
}

export const PurchaseOrdersView: React.FC = () => {
  const {
    purchaseOrders,
    createPurchaseOrder,
    receivePurchaseOrder,
    payPurchaseOrder,
    suppliers,
    locations,
    products,
    cashAccounts
  } = useApp();

  const [showCreate, setShowCreate] = useState(false);
  const [showItemsFor, setShowItemsFor] = useState<number | null>(null);
  const [receiveFor, setReceiveFor] = useState<PurchaseOrder | null>(null);
  const [payFor, setPayFor] = useState<PurchaseOrder | null>(null);

  // Create form state
  const [supplierId, setSupplierId] = useState<number>(0);
  const [locationId, setLocationId] = useState<number>(0);
  const [orderDate, setOrderDate] = useState<string>(() => new Date().toISOString().split('T')[0]);
  const [expectedDate, setExpectedDate] = useState<string>('');
  const [notes, setNotes] = useState<string>('');
  const [lineItems, setLineItems] = useState<LineItemDraft[]>([]);
  const [receiveQty, setReceiveQty] = useState<Record<number, number>>({});
  const [payAmount, setPayAmount] = useState<number>(0);
  const [payAccountId, setPayAccountId] = useState<number>(0);

  const activeCashAccounts = cashAccounts.filter(a => a.is_active);

  const totalPO = purchaseOrders.length;
  const menunggu = purchaseOrders.filter(p => p.status === 'ordered' || p.status === 'received').length;
  const hutang = purchaseOrders
    .filter(p => p.payment_status === 'unpaid' || p.payment_status === 'partial')
    .reduce((sum, p) => sum + (p.total_amount - p.paid_amount), 0);
  const totalNilai = purchaseOrders.reduce((sum, p) => sum + p.total_amount, 0);

  const formatRupiah = (v: number) => `Rp ${v.toLocaleString('id-ID')}`;

  const statusBadge = (status: PurchaseOrder['status']) => {
    const map: Record<string, string> = {
      ordered: 'bg-blue-100 text-blue-800',
      received: 'bg-purple-100 text-purple-800',
      completed: 'bg-emerald-100 text-emerald-800',
      cancelled: 'bg-red-100 text-red-800',
      draft: 'bg-slate-100 text-slate-700'
    };
    const label: Record<string, string> = {
      ordered: 'Dipesan',
      received: 'Diterima',
      completed: 'Selesai',
      cancelled: 'Dibatalkan',
      draft: 'Draft'
    };
    return (
      <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${map[status]}`}>
        {label[status]}
      </span>
    );
  };

  const paymentBadge = (status: PurchaseOrder['payment_status']) => {
    const map: Record<string, string> = {
      unpaid: 'bg-red-100 text-red-800',
      partial: 'bg-amber-100 text-amber-800',
      paid: 'bg-emerald-100 text-emerald-800'
    };
    const label: Record<string, string> = {
      unpaid: 'Belum Bayar',
      partial: 'Sebagian',
      paid: 'Lunas'
    };
    return (
      <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${map[status]}`}>
        {label[status]}
      </span>
    );
  };

  const resetCreateForm = () => {
    setSupplierId(0);
    setLocationId(0);
    setOrderDate(new Date().toISOString().split('T')[0]);
    setExpectedDate('');
    setNotes('');
    setLineItems([]);
  };

  const openCreate = () => {
    resetCreateForm();
    setShowCreate(true);
  };

  const addLineItem = () => {
    const prod = products.find(p => p.is_active);
    setLineItems(prev => [
      ...prev,
      { productId: prod ? prod.id : 0, quantity: 1, unitPrice: prod ? prod.purchase_price : 0 }
    ]);
  };

  const updateLineItem = (idx: number, patch: Partial<LineItemDraft>) => {
    setLineItems(prev => prev.map((it, i) => {
      if (i !== idx) return it;
      const next = { ...it, ...patch };
      if (patch.productId !== undefined) {
        const prod = products.find(p => p.id === patch.productId);
        next.unitPrice = prod ? prod.purchase_price : it.unitPrice;
      }
      return next;
    }));
  };

  const removeLineItem = (idx: number) => {
    setLineItems(prev => prev.filter((_, i) => i !== idx));
  };

  const computeDraftTotal = () =>
    lineItems.reduce((sum, it) => sum + (it.quantity * it.unitPrice), 0);

  const validCreate =
    supplierId > 0 &&
    locationId > 0 &&
    orderDate &&
    lineItems.length > 0 &&
    lineItems.every(it => it.productId > 0 && it.quantity > 0);

  const submitCreate = () => {
    if (!validCreate) return;
    createPurchaseOrder({
      supplierId,
      locationId,
      orderDate,
      expectedDate: expectedDate || undefined,
      items: lineItems.map(it => ({
        productId: it.productId,
        quantity: it.quantity,
        unitPrice: it.unitPrice
      })),
      notes: notes || undefined
    });
    setShowCreate(false);
    resetCreateForm();
  };

  const openReceive = (po: PurchaseOrder) => {
    setReceiveFor(po);
    const init: Record<number, number> = {};
    po.items.forEach(it => { init[it.product_id] = it.quantity; });
    setReceiveQty(init);
  };

  const submitReceive = () => {
    if (!receiveFor) return;
    const receivedItems = receiveFor.items.map(it => ({
      productId: it.product_id,
      receivedQuantity: receiveQty[it.product_id] ?? 0
    }));
    receivePurchaseOrder(receiveFor.id, receivedItems);
    setReceiveFor(null);
  };

  const openPay = (po: PurchaseOrder) => {
    setPayFor(po);
    setPayAmount(po.total_amount - po.paid_amount);
    setPayAccountId(activeCashAccounts[0]?.id || 0);
  };

  const submitPay = () => {
    if (!payFor || payAmount <= 0 || payAccountId === 0) return;
    payPurchaseOrder(payFor.id, payAmount, payAccountId);
    setPayFor(null);
  };

  const selectClasses =
    'w-full px-3 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500';
  const inputClasses =
    'w-full px-3 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500';

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Page Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-slate-900 tracking-tight">Purchase Order (PO)</h1>
          <p className="text-xs text-slate-500 mt-1">
            Pengadaan barang dari supplier untuk menjaga stok tetap tersedia.
          </p>
        </div>
        <button
          onClick={openCreate}
          className="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/30 transition-all flex items-center gap-1.5"
        >
          <Plus className="w-4 h-4" />
          Buat PO Baru
        </button>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Total PO</span>
            <div className="w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center font-bold">
              <FileText className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-slate-900">{totalPO}</div>
            <div className="text-xs text-slate-500 mt-1">Jumlah pesanan pembelian</div>
          </div>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Menunggu</span>
            <div className="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
              <Truck className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-blue-700">{menunggu}</div>
            <div className="text-xs text-slate-500 mt-1">PO dipesan belum selesai</div>
          </div>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Hutang / Belum Bayar</span>
            <div className="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center font-bold">
              <Banknote className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-red-600">{formatRupiah(hutang)}</div>
            <div className="text-xs text-slate-500 mt-1">Sisa pembayaran belum lunas</div>
          </div>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Nilai Total PO</span>
            <div className="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
              <ShoppingCart className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-slate-900">{formatRupiah(totalNilai)}</div>
            <div className="text-xs text-slate-500 mt-1">Total seluruh pesanan</div>
          </div>
        </div>
      </div>

      {/* PO Table */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-2 font-bold text-slate-800 text-sm">
          <PackagePlus className="w-4 h-4 text-primary-600" />
          Daftar Purchase Order
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
              <tr>
                <th className="px-5 py-3">No. PO</th>
                <th className="px-4 py-3">Supplier</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Pembayaran</th>
                <th className="px-4 py-3">Total</th>
                <th className="px-4 py-3">Terbayar</th>
                <th className="px-4 py-3">Sisa</th>
                <th className="px-4 py-3">Tanggal</th>
                <th className="px-4 py-3">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 text-slate-700">
              {purchaseOrders.map(po => (
                <React.Fragment key={po.id}>
                  <tr className="hover:bg-slate-50/70 transition-colors">
                    <td className="px-5 py-3 font-mono font-bold text-primary-700">{po.po_number}</td>
                    <td className="px-4 py-3 font-medium text-slate-800">
                      {po.supplier?.name || `#${po.supplier_id}`}
                      <div className="text-[10px] text-slate-400 mt-0.5">{po.location?.name || ''}</div>
                    </td>
                    <td className="px-4 py-3">{statusBadge(po.status)}</td>
                    <td className="px-4 py-3">{paymentBadge(po.payment_status)}</td>
                    <td className="px-4 py-3 font-bold text-slate-900">{formatRupiah(po.total_amount)}</td>
                    <td className="px-4 py-3 font-semibold text-emerald-700">{formatRupiah(po.paid_amount)}</td>
                    <td className="px-4 py-3 font-semibold text-red-600">
                      {formatRupiah(po.total_amount - po.paid_amount)}
                    </td>
                    <td className="px-4 py-3 text-slate-500">{po.order_date}</td>
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-1.5">
                        <button
                          onClick={() => setShowItemsFor(showItemsFor === po.id ? null : po.id)}
                          className="p-1.5 rounded-lg text-slate-500 hover:text-primary-600 hover:bg-primary-50 transition-colors"
                          title="Detail item"
                        >
                          {showItemsFor === po.id ? <ChevronUp className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                        </button>
                        {(po.status === 'ordered' || po.status === 'received') && (
                          <button
                            onClick={() => openReceive(po)}
                            className="flex items-center gap-1 px-2 py-1.5 rounded-lg text-[10px] font-bold text-purple-700 bg-purple-50 border border-purple-200 hover:bg-purple-100 transition-colors"
                          >
                            <Truck className="w-3.5 h-3.5" />
                            Terima
                          </button>
                        )}
                        {po.payment_status !== 'paid' && po.status !== 'cancelled' && (
                          <button
                            onClick={() => openPay(po)}
                            className="flex items-center gap-1 px-2 py-1.5 rounded-lg text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition-colors"
                          >
                            <Banknote className="w-3.5 h-3.5" />
                            Bayar
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                  {showItemsFor === po.id && (
                    <tr>
                      <td colSpan={9} className="px-5 py-4 bg-slate-50/70">
                        <div className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                          Item {po.po_number}
                        </div>
                        <div className="overflow-x-auto">
                          <table className="w-full text-left text-xs">
                            <thead className="text-slate-400 uppercase font-semibold border-b border-slate-200">
                              <tr>
                                <th className="px-3 py-1.5">Produk</th>
                                <th className="px-3 py-1.5">Qty</th>
                                <th className="px-3 py-1.5">Diterima</th>
                                <th className="px-3 py-1.5">Harga</th>
                                <th className="px-3 py-1.5">Subtotal</th>
                              </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 text-slate-700">
                              {po.items.map(it => (
                                <tr key={it.id}>
                                  <td className="px-3 py-2 font-semibold text-slate-800">
                                    {it.product_name}
                                    <div className="text-[10px] text-slate-400 font-mono">{it.product_code}</div>
                                  </td>
                                  <td className="px-3 py-2">{it.quantity}</td>
                                  <td className="px-3 py-2 text-purple-700 font-bold">{it.received_quantity}</td>
                                  <td className="px-3 py-2">{formatRupiah(it.unit_price)}</td>
                                  <td className="px-3 py-2 font-bold text-slate-900">{formatRupiah(it.subtotal)}</td>
                                </tr>
                              ))}
                            </tbody>
                          </table>
                        </div>
                      </td>
                    </tr>
                  )}
                </React.Fragment>
              ))}
              {purchaseOrders.length === 0 && (
                <tr>
                  <td colSpan={9} className="px-5 py-12 text-center text-slate-400">
                    <div className="flex flex-col items-center justify-center">
                      <PackagePlus className="w-12 h-12 stroke-[1.2] mb-2 text-slate-300" />
                      <p className="text-sm font-semibold">Belum ada Purchase Order</p>
                      <p className="text-xs text-slate-400 mt-0.5">
                        Klik "Buat PO Baru" untuk memesan barang dari supplier
                      </p>
                    </div>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* ===== CREATE MODAL ===== */}
      {showCreate && (
        <ModalShell title="Buat PO Baru" onClose={() => setShowCreate(false)}>
          <div className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Supplier</label>
                <select value={supplierId} onChange={e => setSupplierId(Number(e.target.value))} className={`${selectClasses} mt-1`}>
                  <option value={0}>Pilih supplier...</option>
                  {suppliers.map(s => (
                    <option key={s.id} value={s.id}>{s.name}</option>
                  ))}
                </select>
              </div>
              <div>
                <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Lokasi</label>
                <select value={locationId} onChange={e => setLocationId(Number(e.target.value))} className={`${selectClasses} mt-1`}>
                  <option value={0}>Pilih lokasi...</option>
                  {locations.map(l => (
                    <option key={l.id} value={l.id}>{l.name}</option>
                  ))}
                </select>
              </div>
              <div>
                <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tanggal PO</label>
                <input type="date" value={orderDate} onChange={e => setOrderDate(e.target.value)} className={`${inputClasses} mt-1`} />
              </div>
              <div>
                <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                  Tanggal Tiba <span className="normal-case font-normal text-slate-400">(opsional)</span>
                </label>
                <input type="date" value={expectedDate} onChange={e => setExpectedDate(e.target.value)} className={`${inputClasses} mt-1`} />
              </div>
            </div>

            <div>
              <div className="flex items-center justify-between mb-2">
                <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Item Barang</label>
                <button
                  onClick={addLineItem}
                  className="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-bold text-primary-700 bg-primary-50 border border-primary-200 hover:bg-primary-100 transition-colors"
                >
                  <Plus className="w-3.5 h-3.5" />
                  Tambah Item
                </button>
              </div>

              <div className="space-y-2">
                {lineItems.map((it, idx) => {
                  const prod = products.find(p => p.id === it.productId);
                  return (
                    <div key={idx} className="p-3 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2">
                      <div className="grid grid-cols-2 sm:grid-cols-5 gap-2">
                        <div className="col-span-2 sm:col-span-3">
                          <select
                            value={it.productId}
                            onChange={e => updateLineItem(idx, { productId: Number(e.target.value) })}
                            className={selectClasses}
                          >
                            <option value={0}>Pilih produk...</option>
                            {products.filter(p => p.is_active).map(p => (
                              <option key={p.id} value={p.id}>
                                {p.name} (Rp {p.purchase_price.toLocaleString('id-ID')})
                              </option>
                            ))}
                          </select>
                        </div>
                        <div>
                          <input
                            type="number"
                            min="1"
                            value={it.quantity}
                            onChange={e => updateLineItem(idx, { quantity: Number(e.target.value) })}
                            className={inputClasses}
                            placeholder="Qty"
                          />
                        </div>
                        <div>
                          <input
                            type="number"
                            min="0"
                            value={it.unitPrice}
                            onChange={e => updateLineItem(idx, { unitPrice: Number(e.target.value) })}
                            className={inputClasses}
                            placeholder="Harga"
                          />
                        </div>
                        <div className="flex items-center justify-between col-span-2 sm:col-span-1">
                          <span className="text-xs font-black text-primary-700">
                            {formatRupiah(it.quantity * it.unitPrice)}
                          </span>
                          <button
                            onClick={() => removeLineItem(idx)}
                            className="p-1 text-slate-400 hover:text-red-600 rounded-lg"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </div>
                      </div>
                      {prod && (
                        <div className="text-[10px] text-slate-400">
                          Stok saat ini: {prod.stock_global || 0} • Harga beli: {formatRupiah(prod.purchase_price)}
                        </div>
                      )}
                    </div>
                  );
                })}
                {lineItems.length === 0 && (
                  <div className="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-300 text-center text-slate-400">
                    <p className="text-xs font-semibold">Belum ada item</p>
                    <p className="text-[10px] mt-0.5">Klik "Tambah Item" untuk menambahkan barang</p>
                  </div>
                )}
              </div>
            </div>

            <div>
              <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                Catatan <span className="normal-case font-normal text-slate-400">(opsional)</span>
              </label>
              <textarea
                value={notes}
                onChange={e => setNotes(e.target.value)}
                rows={2}
                className={`${inputClasses} mt-1 resize-none`}
                placeholder="Catatan tambahan untuk PO..."
              />
            </div>

            <div className="flex items-center justify-between pt-3 border-t border-slate-100">
              <div>
                <div className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total PO</div>
                <div className="text-lg font-black text-primary-700">{formatRupiah(computeDraftTotal())}</div>
              </div>
              <button
                onClick={submitCreate}
                disabled={!validCreate}
                className="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-40 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/30 transition-all flex items-center gap-1.5"
              >
                <Check className="w-4 h-4" />
                Simpan PO
              </button>
            </div>
          </div>
        </ModalShell>
      )}

      {/* ===== RECEIVE MODAL ===== */}
      {receiveFor && (
        <ModalShell title={`Terima Barang - ${receiveFor.po_number}`} onClose={() => setReceiveFor(null)}>
          <p className="text-xs text-slate-500 mb-3">
            Masukkan jumlah barang yang diterima dari {receiveFor.supplier?.name}. Stok akan ditambahkan ke {receiveFor.location?.name}.
          </p>
          <div className="space-y-2 max-h-80 overflow-y-auto pr-1">
            {receiveFor.items.map(it => (
              <div key={it.id} className="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between gap-3">
                <div>
                  <div className="text-xs font-bold text-slate-800">{it.product_name}</div>
                  <div className="text-[10px] text-slate-400 font-mono">{it.product_code}</div>
                  <div className="text-[10px] text-slate-500 mt-0.5">
                    Pesanan: {it.quantity} • Sudah diterima: {it.received_quantity}
                  </div>
                </div>
                <div className="w-28">
                  <input
                    type="number"
                    min="0"
                    value={receiveQty[it.product_id] ?? 0}
                    onChange={e => setReceiveQty(prev => ({ ...prev, [it.product_id]: Number(e.target.value) }))}
                    className={inputClasses}
                  />
                </div>
              </div>
            ))}
          </div>
          <div className="flex justify-end gap-2 pt-4 border-t border-slate-100 mt-4">
            <button
              onClick={() => setReceiveFor(null)}
              className="px-3.5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-xl"
            >
              Batal
            </button>
            <button
              onClick={submitReceive}
              className="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow-md shadow-purple-600/30 transition-all flex items-center gap-1.5"
            >
              <Check className="w-4 h-4" />
              Konfirmasi Terima
            </button>
          </div>
        </ModalShell>
      )}

      {/* ===== PAY MODAL ===== */}
      {payFor && (
        <ModalShell title={`Bayar PO - ${payFor.po_number}`} onClose={() => setPayFor(null)}>
          <div className="space-y-4">
            <div className="grid grid-cols-3 gap-2">
              <div className="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-center">
                <div className="text-[10px] font-bold text-slate-500 uppercase">Total</div>
                <div className="text-sm font-black text-slate-900 mt-1">{formatRupiah(payFor.total_amount)}</div>
              </div>
              <div className="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-center">
                <div className="text-[10px] font-bold text-slate-500 uppercase">Terbayar</div>
                <div className="text-sm font-black text-emerald-700 mt-1">{formatRupiah(payFor.paid_amount)}</div>
              </div>
              <div className="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-center">
                <div className="text-[10px] font-bold text-emerald-500 uppercase">Sisa</div>
                <div className="text-sm font-black text-emerald-700 mt-1">
                  {formatRupiah(payFor.total_amount - payFor.paid_amount)}
                </div>
              </div>
            </div>

            <div>
              <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Jumlah Bayar</label>
              <input
                type="number"
                min="1"
                max={payFor.total_amount - payFor.paid_amount}
                value={payAmount}
                onChange={e => setPayAmount(Number(e.target.value))}
                className={`${inputClasses} mt-1`}
              />
            </div>

            <div>
              <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Akun Kas</label>
              <select value={payAccountId} onChange={e => setPayAccountId(Number(e.target.value))} className={`${selectClasses} mt-1`}>
                <option value={0}>Pilih akun...</option>
                {activeCashAccounts.map(a => (
                  <option key={a.id} value={a.id}>
                    {a.name} (Saldo: {formatRupiah(a.current_balance)})
                  </option>
                ))}
              </select>
              <div className="flex items-center gap-1 text-[10px] text-slate-400 mt-1">
                <Store className="w-3 h-3" />
                Pembayaran akan dicatat sebagai pengeluaran dari akun terpilih.
              </div>
            </div>

            <div className="flex justify-end gap-2 pt-3 border-t border-slate-100">
              <button
                onClick={() => setPayFor(null)}
                className="px-3.5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-xl"
              >
                Batal
              </button>
              <button
                onClick={submitPay}
                disabled={payAmount <= 0 || payAccountId === 0}
                className="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/30 transition-all flex items-center gap-1.5"
              >
                <Banknote className="w-4 h-4" />
                Konfirmasi Bayar
              </button>
            </div>
          </div>
        </ModalShell>
      )}
    </div>
  );
};

const ModalShell: React.FC<{ title: string; onClose: () => void; children: React.ReactNode }> = ({ title, onClose, children }) => {
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onClick={onClose} />
      <div className="relative bg-white w-full max-w-2xl rounded-2xl shadow-2xl max-h-[90vh] flex flex-col">
        <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h3 className="font-black text-slate-900 flex items-center gap-2">
            <Wallet className="w-4 h-4 text-primary-600" />
            {title}
          </h3>
          <button onClick={onClose} className="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100">
            <X className="w-4 h-4" />
          </button>
        </div>
        <div className="p-6 overflow-y-auto">{children}</div>
      </div>
    </div>
  );
};
