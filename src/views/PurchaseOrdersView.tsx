import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import { PurchaseOrder } from '../types';
import clsx from 'clsx';
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
  Store,
} from 'lucide-react';
import {
  Button,
  Card,
  CardHeader,
  Modal,
  Select,
  Input,
  Badge,
  StatCard,
  TableContainer,
  TableHeader,
  TableBase,
  TableRow,
  TableEmpty,
} from '../components/ui';
import type { SelectOption } from '../components/ui';

interface LineItemDraft {
  productId: number;
  quantity: number;
  unitPrice: number;
}

const STATUS_BADGE: Record<string, { variant: 'info' | 'primary' | 'success' | 'danger' | 'neutral'; label: string }> = {
  ordered: { variant: 'info', label: 'Dipesan' },
  received: { variant: 'primary', label: 'Diterima' },
  completed: { variant: 'success', label: 'Selesai' },
  cancelled: { variant: 'danger', label: 'Dibatalkan' },
  draft: { variant: 'neutral', label: 'Draft' },
};

const PAYMENT_BADGE: Record<string, { variant: 'danger' | 'warning' | 'success'; label: string }> = {
  unpaid: { variant: 'danger', label: 'Belum Bayar' },
  partial: { variant: 'warning', label: 'Sebagian' },
  paid: { variant: 'success', label: 'Lunas' },
};

export const PurchaseOrdersView: React.FC = () => {
  const {
    purchaseOrders,
    createPurchaseOrder,
    receivePurchaseOrder,
    payPurchaseOrder,
    suppliers,
    locations,
    products,
    cashAccounts,
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
      { productId: prod ? prod.id : 0, quantity: 1, unitPrice: prod ? prod.purchase_price : 0 },
    ]);
  };

  const updateLineItem = (idx: number, patch: Partial<LineItemDraft>) => {
    setLineItems(prev =>
      prev.map((it, i) => {
        if (i !== idx) return it;
        const next = { ...it, ...patch };
        if (patch.productId !== undefined) {
          const prod = products.find(p => p.id === patch.productId);
          next.unitPrice = prod ? prod.purchase_price : it.unitPrice;
        }
        return next;
      })
    );
  };

  const removeLineItem = (idx: number) => {
    setLineItems(prev => prev.filter((_, i) => i !== idx));
  };

  const computeDraftTotal = () => lineItems.reduce((sum, it) => sum + it.quantity * it.unitPrice, 0);

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
        unitPrice: it.unitPrice,
      })),
      notes: notes || undefined,
    });
    setShowCreate(false);
    resetCreateForm();
  };

  const openReceive = (po: PurchaseOrder) => {
    setReceiveFor(po);
    const init: Record<number, number> = {};
    po.items.forEach(it => {
      init[it.product_id] = it.quantity;
    });
    setReceiveQty(init);
  };

  const submitReceive = () => {
    if (!receiveFor) return;
    const receivedItems = receiveFor.items.map(it => ({
      productId: it.product_id,
      receivedQuantity: receiveQty[it.product_id] ?? 0,
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

  const supplierOptions: SelectOption[] = [
    { value: 0, label: 'Pilih supplier...' },
    ...suppliers.map(s => ({ value: s.id, label: s.name })),
  ];

  const locationOptions: SelectOption[] = [
    { value: 0, label: 'Pilih lokasi...' },
    ...locations.map(l => ({ value: l.id, label: l.name })),
  ];

  const cashAccountOptions: SelectOption[] = [
    { value: 0, label: 'Pilih akun...' },
    ...activeCashAccounts.map(a => ({
      value: a.id,
      label: `${a.name} (Saldo: ${formatRupiah(a.current_balance)})`,
    })),
  ];

  const columns = useMemo(
    () => [
      { header: 'No. PO' },
      { header: 'Supplier' },
      { header: 'Status' },
      { header: 'Pembayaran' },
      { header: 'Total', align: 'right' as const },
      { header: 'Terbayar', align: 'right' as const },
      { header: 'Sisa', align: 'right' as const },
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
          <h1 className="text-2xl font-black text-slate-900 tracking-tight">Purchase Order (PO)</h1>
          <p className="text-xs text-slate-500 mt-1">
            Pengadaan barang dari supplier untuk menjaga stok tetap tersedia.
          </p>
        </div>
        <Button
          variant="primary"
          icon={<Plus className="w-4 h-4" />}
          onClick={openCreate}
        >
          Buat PO Baru
        </Button>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          value={totalPO.toLocaleString('id-ID')}
          label="Total PO"
          description="Jumlah pesanan pembelian"
          icon={<FileText className="w-4 h-4" />}
          color="primary"
        />
        <StatCard
          value={menunggu.toLocaleString('id-ID')}
          label="Menunggu"
          description="PO dipesan belum selesai"
          icon={<Truck className="w-4 h-4" />}
          color="info"
        />
        <StatCard
          value={formatRupiah(hutang)}
          label="Hutang / Belum Bayar"
          description="Sisa pembayaran belum lunas"
          icon={<Banknote className="w-4 h-4" />}
          color="danger"
        />
        <StatCard
          value={formatRupiah(totalNilai)}
          label="Nilai Total PO"
          description="Total seluruh pesanan"
          icon={<ShoppingCart className="w-4 h-4" />}
          color="success"
        />
      </div>

      {/* PO Table */}
      <TableContainer>
        <TableHeader
          icon={<PackagePlus className="w-4 h-4 text-primary-600" />}
          count={purchaseOrders.length}
        >
          Daftar Purchase Order
        </TableHeader>

        <TableBase columns={columns} colSpan={columns.length}>
          {purchaseOrders.length === 0 ? (
            <TableEmpty
              colSpan={columns.length}
              message="Belum ada Purchase Order"
              icon={<PackagePlus className="w-12 h-12 mx-auto mb-2 text-slate-300" />}
            />
          ) : (
            purchaseOrders.map(po => {
              const statusInfo = STATUS_BADGE[po.status] || STATUS_BADGE.draft;
              const payInfo = PAYMENT_BADGE[po.payment_status] || PAYMENT_BADGE.unpaid;

              return (
                <React.Fragment key={po.id}>
                  <TableRow>
                    <td className="px-5 py-3 font-mono font-bold text-primary-700">{po.po_number}</td>
                    <td className="px-4 py-3 font-medium text-slate-800">
                      {po.supplier?.name || `#${po.supplier_id}`}
                      <div className="text-[10px] text-slate-400 mt-0.5">{po.location?.name || ''}</div>
                    </td>
                    <td className="px-4 py-3">
                      <Badge variant={statusInfo.variant}>{statusInfo.label}</Badge>
                    </td>
                    <td className="px-4 py-3">
                      <Badge variant={payInfo.variant}>{payInfo.label}</Badge>
                    </td>
                    <td className="px-4 py-3 font-bold text-slate-900 text-right">{formatRupiah(po.total_amount)}</td>
                    <td className="px-4 py-3 font-semibold text-emerald-700 text-right">{formatRupiah(po.paid_amount)}</td>
                    <td className="px-4 py-3 font-semibold text-red-600 text-right">
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
                          <Button
                            variant="outline"
                            size="xs"
                            icon={<Truck className="w-3.5 h-3.5" />}
                            onClick={() => openReceive(po)}
                            className="!border-purple-200 !text-purple-700 !bg-purple-50 hover:!bg-purple-100"
                          >
                            Terima
                          </Button>
                        )}
                        {po.payment_status !== 'paid' && po.status !== 'cancelled' && (
                          <Button
                            variant="outline"
                            size="xs"
                            icon={<Banknote className="w-3.5 h-3.5" />}
                            onClick={() => openPay(po)}
                            className="!border-emerald-200 !text-emerald-700 !bg-emerald-50 hover:!bg-emerald-100"
                          >
                            Bayar
                          </Button>
                        )}
                      </div>
                    </td>
                  </TableRow>
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
              );
            })
          )}
        </TableBase>
      </TableContainer>

      {/* ===== CREATE MODAL ===== */}
      <Modal
        open={showCreate}
        onClose={() => setShowCreate(false)}
        title="Buat PO Baru"
        size="xl"
        footer={
          <>
            <div className="flex-1">
              <div className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total PO</div>
              <div className="text-lg font-black text-primary-700">{formatRupiah(computeDraftTotal())}</div>
            </div>
            <Button variant="secondary" onClick={() => setShowCreate(false)}>
              Batal
            </Button>
            <Button
              variant="primary"
              icon={<Check className="w-4 h-4" />}
              onClick={submitCreate}
              disabled={!validCreate}
            >
              Simpan PO
            </Button>
          </>
        }
      >
        <div className="space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <Select
              label="Supplier"
              options={supplierOptions}
              value={supplierId}
              onChange={e => setSupplierId(Number(e.target.value))}
            />
            <Select
              label="Lokasi"
              options={locationOptions}
              value={locationId}
              onChange={e => setLocationId(Number(e.target.value))}
            />
            <Input
              label="Tanggal PO"
              type="date"
              value={orderDate}
              onChange={e => setOrderDate(e.target.value)}
            />
            <Input
              label="Tanggal Tiba (opsional)"
              type="date"
              value={expectedDate}
              onChange={e => setExpectedDate(e.target.value)}
            />
          </div>

          <div>
            <div className="flex items-center justify-between mb-2">
              <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Item Barang</label>
              <Button
                variant="outline"
                size="xs"
                icon={<Plus className="w-3.5 h-3.5" />}
                onClick={addLineItem}
              >
                Tambah Item
              </Button>
            </div>

            <div className="space-y-2">
              {lineItems.map((it, idx) => {
                const prod = products.find(p => p.id === it.productId);
                const prodOpts: SelectOption[] = [
                  { value: 0, label: 'Pilih produk...' },
                  ...products.filter(p => p.is_active).map(p => ({
                    value: p.id,
                    label: `${p.name} (Rp ${p.purchase_price.toLocaleString('id-ID')})`,
                  })),
                ];

                return (
                  <div key={idx} className="p-3 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2">
                    <div className="grid grid-cols-2 sm:grid-cols-5 gap-2">
                      <div className="col-span-2 sm:col-span-3">
                        <Select
                          options={prodOpts}
                          value={it.productId}
                          onChange={e => updateLineItem(idx, { productId: Number(e.target.value) })}
                        />
                      </div>
                      <Input
                        type="number"
                        min={1}
                        value={it.quantity}
                        onChange={e => updateLineItem(idx, { quantity: Number(e.target.value) })}
                        placeholder="Qty"
                      />
                      <Input
                        type="number"
                        min={0}
                        value={it.unitPrice}
                        onChange={e => updateLineItem(idx, { unitPrice: Number(e.target.value) })}
                        placeholder="Harga"
                      />
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
              className="w-full px-3 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none mt-1"
              placeholder="Catatan tambahan untuk PO..."
            />
          </div>
        </div>
      </Modal>

      {/* ===== RECEIVE MODAL ===== */}
      <Modal
        open={receiveFor !== null}
        onClose={() => setReceiveFor(null)}
        title={receiveFor ? `Terima Barang - ${receiveFor.po_number}` : ''}
        size="md"
        footer={
          <>
            <Button variant="secondary" onClick={() => setReceiveFor(null)}>
              Batal
            </Button>
            <Button
              variant="primary"
              icon={<Check className="w-4 h-4" />}
              onClick={submitReceive}
            >
              Konfirmasi Terima
            </Button>
          </>
        }
      >
        {receiveFor && (
          <>
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
                    <Input
                      type="number"
                      min={0}
                      value={receiveQty[it.product_id] ?? 0}
                      onChange={e => setReceiveQty(prev => ({ ...prev, [it.product_id]: Number(e.target.value) }))}
                    />
                  </div>
                </div>
              ))}
            </div>
          </>
        )}
      </Modal>

      {/* ===== PAY MODAL ===== */}
      <Modal
        open={payFor !== null}
        onClose={() => setPayFor(null)}
        title={payFor ? `Bayar PO - ${payFor.po_number}` : ''}
        size="md"
        footer={
          <>
            <Button variant="secondary" onClick={() => setPayFor(null)}>
              Batal
            </Button>
            <Button
              variant="primary"
              icon={<Banknote className="w-4 h-4" />}
              onClick={submitPay}
              disabled={payAmount <= 0 || payAccountId === 0}
            >
              Konfirmasi Bayar
            </Button>
          </>
        }
      >
        {payFor && (
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

            <Input
              label="Jumlah Bayar"
              type="number"
              min={1}
              max={payFor.total_amount - payFor.paid_amount}
              value={payAmount}
              onChange={e => setPayAmount(Number(e.target.value))}
            />

            <Select
              label="Akun Kas"
              options={cashAccountOptions}
              value={payAccountId}
              onChange={e => setPayAccountId(Number(e.target.value))}
            />
            <div className="flex items-center gap-1 text-[10px] text-slate-400">
              <Store className="w-3 h-3" />
              Pembayaran akan dicatat sebagai pengeluaran dari akun terpilih.
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};
