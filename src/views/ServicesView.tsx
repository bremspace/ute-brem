import React, { useMemo, useState } from 'react';
import { useApp } from '../context/AppContext';
import { ServiceTransaction } from '../types';
import {
  Wrench,
  Smartphone,
  Phone,
  Calendar,
  Plus,
  Check,
  X,
  Banknote,
  ShieldQuestion,
  Package,
  ClipboardList,
  ScanLine,
  Trash2,
} from 'lucide-react';
import clsx from 'clsx';
import { PatternLockInput } from '../components/services/PatternLockInput';
import {
  Button,
  Card,
  Input,
  Select,
  StatCard,
  Tabs,
  Modal,
  StatusBadge,
} from '../components/ui';
import type { TabItem, SelectOption } from '../components/ui';

const STATUS_META: Record<ServiceTransaction['status'], { label: string; badge: string; dot: string }> = {
  pending: { label: 'Menunggu', badge: 'bg-amber-100 text-amber-800', dot: 'bg-amber-500' },
  in_progress: { label: 'Dikerjakan', badge: 'bg-blue-100 text-blue-800', dot: 'bg-blue-500' },
  completed: { label: 'Selesai', badge: 'bg-emerald-100 text-emerald-800', dot: 'bg-emerald-500' },
  delivered: { label: 'Diambil', badge: 'bg-slate-200 text-slate-700', dot: 'bg-slate-500' },
  cancelled: { label: 'Dibatalkan', badge: 'bg-red-100 text-red-800', dot: 'bg-red-500' },
};

const FILTERS: { key: string; label: string; match: (s: ServiceTransaction) => boolean }[] = [
  { key: 'all', label: 'Semua', match: () => true },
  { key: 'pending', label: 'Menunggu', match: s => s.status === 'pending' },
  { key: 'in_progress', label: 'Dikerjakan', match: s => s.status === 'in_progress' },
  { key: 'completed', label: 'Selesai', match: s => s.status === 'completed' },
  { key: 'delivered', label: 'Diambil', match: s => s.status === 'delivered' },
  { key: 'cancelled', label: 'Dibatalkan', match: s => s.status === 'cancelled' },
];

const LOCK_META: Record<string, string> = {
  none: 'Tanpa Kunci',
  pin: 'PIN',
  pattern: 'Pola',
  password: 'Password',
};

const fmt = (n: number) => `Rp ${n.toLocaleString('id-ID')}`;
const fmtDate = (iso?: string) => {
  if (!iso) return '-';
  const d = new Date(iso);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) +
    ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

interface DraftItem {
  key: number;
  itemType: 'service' | 'product';
  productId?: number;
  name: string;
  quantity: number;
  price: number;
  purchasePrice: number;
}

export const ServicesView: React.FC = () => {
  const {
    serviceTransactions,
    createServiceTransaction,
    updateServiceStatus,
    completeServicePayment,
    serviceItems,
    products,
    currentUser,
  } = useApp();

  const [filter, setFilter] = useState('all');
  const [expandedId, setExpandedId] = useState<number | null>(null);
  const [showCreate, setShowCreate] = useState(false);
  const [payingOrder, setPayingOrder] = useState<ServiceTransaction | null>(null);

  // Create form state
  const [form, setForm] = useState({
    customerName: '',
    customerPhone: '',
    deviceBrand: '',
    deviceModel: '',
    deviceImei: '',
    deviceColor: '',
    problemDescription: '',
    conditionNotes: '',
    completenessNotes: '',
    warrantyDays: 7,
    lockType: 'none' as 'none' | 'pin' | 'pattern' | 'password',
    lockCode: '',
  });

  // Line item builder state
  const [draftType, setDraftType] = useState<'service' | 'product'>('service');
  const [draftServiceId, setDraftServiceId] = useState<number | ''>('');
  const [draftProductId, setDraftProductId] = useState<number | ''>('');
  const [draftQty, setDraftQty] = useState(1);
  const [draftPrice, setDraftPrice] = useState(0);
  const [draftPurchasePrice, setDraftPurchasePrice] = useState(0);
  const [draftItems, setDraftItems] = useState<DraftItem[]>([]);

  // Payment modal state
  const [payMethod, setPayMethod] = useState<'cash' | 'transfer' | 'qris' | 'tempo'>('cash');
  const [paidAmount, setPaidAmount] = useState(0);

  const filtered = useMemo(() => {
    const f = FILTERS.find(x => x.key === filter) || FILTERS[0];
    return serviceTransactions.filter(f.match);
  }, [serviceTransactions, filter]);

  const counts = useMemo(() => {
    const by = (st: ServiceTransaction['status']) => serviceTransactions.filter(s => s.status === st).length;
    return {
      antrean: by('pending') + by('in_progress'),
      dikerjakan: by('in_progress'),
      selesai: by('completed'),
      diambil: by('delivered'),
    };
  }, [serviceTransactions]);

  const resetForm = () => {
    setForm({
      customerName: '', customerPhone: '', deviceBrand: '', deviceModel: '',
      deviceImei: '', deviceColor: '', problemDescription: '',
      conditionNotes: '', completenessNotes: '', warrantyDays: 7,
      lockType: 'none', lockCode: '',
    });
    setDraftType('service');
    setDraftServiceId('');
    setDraftProductId('');
    setDraftQty(1);
    setDraftPrice(0);
    setDraftPurchasePrice(0);
    setDraftItems([]);
  };

  const setField = (k: keyof typeof form, v: string | number | 'none' | 'pin' | 'pattern' | 'password') =>
    setForm(prev => ({ ...prev, [k]: v }));

  // When draft type or selection changes, auto-fill name/price
  const handleDraftTypeChange = (t: 'service' | 'product') => {
    setDraftType(t);
    setDraftServiceId('');
    setDraftProductId('');
    setDraftPrice(0);
    setDraftPurchasePrice(0);
  };

  const handleDraftServiceSelect = (id: number | '') => {
    setDraftServiceId(id);
    if (id === '') { setDraftPrice(0); setDraftPurchasePrice(0); return; }
    const si = serviceItems.find(s => s.id === id);
    setDraftPrice(si?.estimated_price || 0);
    setDraftPurchasePrice(0);
  };

  const handleDraftProductSelect = (id: number | '') => {
    setDraftProductId(id);
    if (id === '') { setDraftPrice(0); setDraftPurchasePrice(0); return; }
    const p = products.find(p => p.id === id);
    setDraftPrice(p?.selling_price || 0);
    setDraftPurchasePrice(p?.purchase_price || 0);
  };

  const addDraftItem = () => {
    if (draftType === 'service') {
      const si = serviceItems.find(s => s.id === draftServiceId);
      if (!si || draftQty <= 0) return;
      const item: DraftItem = {
        key: Date.now(),
        itemType: 'service',
        productId: undefined,
        name: si.name,
        quantity: draftQty,
        price: draftPrice,
        purchasePrice: 0,
      };
      setDraftItems(prev => [...prev, item]);
      setDraftQty(1);
    } else {
      const p = products.find(p => p.id === draftProductId);
      if (!p || draftQty <= 0) return;
      const item: DraftItem = {
        key: Date.now(),
        itemType: 'product',
        productId: p.id,
        name: p.name,
        quantity: draftQty,
        price: draftPrice,
        purchasePrice: draftPurchasePrice,
      };
      setDraftItems(prev => [...prev, item]);
      setDraftQty(1);
    }
  };

  const removeDraftItem = (key: number) => setDraftItems(prev => prev.filter(i => i.key !== key));

  const estimatedTotal = draftItems.reduce((sum, i) => sum + i.price * i.quantity, 0);

  const handleSubmit = () => {
    if (!form.customerName.trim() || !form.deviceBrand.trim() || !form.deviceModel.trim() || !form.problemDescription.trim()) {
      alert('Lengkapi nama pelanggan, brand, model, dan deskripsi masalah.');
      return;
    }
    if (draftItems.length === 0) {
      alert('Tambahkan minimal satu item servis/sparepart.');
      return;
    }
    if (form.lockType !== 'none' && !form.lockCode.trim()) {
      alert('Isi kode kunci perangkat terlebih dahulu.');
      return;
    }

    createServiceTransaction({
      customerName: form.customerName,
      customerPhone: form.customerPhone,
      deviceBrand: form.deviceBrand,
      deviceModel: form.deviceModel,
      problemDescription: form.problemDescription,
      deviceLockType: form.lockType,
      deviceLockCode: form.lockType === 'none' ? undefined : form.lockCode,
      technicianId: currentUser.id,
      warranty_days: form.warrantyDays,
      condition_notes: form.conditionNotes || undefined,
      completeness_notes: form.completenessNotes || undefined,
      device_imei: form.deviceImei || undefined,
      device_color: form.deviceColor || undefined,
      estimated_cost: estimatedTotal,
      items: draftItems.map(i => ({
        itemType: i.itemType,
        productId: i.productId,
        name: i.name,
        quantity: i.quantity,
        price: i.price,
        purchasePrice: i.purchasePrice,
      })),
    });

    setShowCreate(false);
    resetForm();
  };

  const handlePayConfirm = () => {
    if (!payingOrder) return;
    if (payMethod === 'tempo' ? paidAmount < 0 : paidAmount < 0) {
      // allow any non-negative amount
    }
    completeServicePayment(payingOrder.id, payMethod, paidAmount);
    setPayingOrder(null);
  };

  const filterTabItems: TabItem[] = FILTERS.map(f => ({
    id: f.key,
    label: `${f.label} (${serviceTransactions.filter(f.match).length})`,
  }));

  const serviceItemOptions: SelectOption[] = serviceItems
    .filter(s => s.is_active)
    .map(s => ({ value: s.id, label: `${s.name} (${fmt(s.estimated_price)})` }));

  const productOptions: SelectOption[] = products
    .filter(p => p.is_active)
    .map(p => ({ value: p.id, label: `${p.name} (Jual ${fmt(p.selling_price)})` }));

  const lockTypeOptions: SelectOption[] = [
    { value: 'none', label: 'Tanpa Kunci' },
    { value: 'pin', label: 'PIN' },
    { value: 'pattern', label: 'Pola (Pattern)' },
    { value: 'password', label: 'Password' },
  ];

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Page Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div className="flex items-center gap-3">
          <div className="w-11 h-11 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center shadow-sm">
            <Wrench className="w-6 h-6" />
          </div>
          <div>
            <h1 className="text-2xl font-black text-slate-900 tracking-tight">Service Center HP</h1>
            <p className="text-xs text-slate-500 mt-0.5">
              Kelola work order perbaikan HP, antrean, dan pembayaran servis.
            </p>
          </div>
        </div>

        <Button
          onClick={() => { resetForm(); setShowCreate(true); }}
          icon={<Plus className="w-4 h-4" />}
        >
          Buat Order Servis Baru
        </Button>
      </div>

      {/* Summary Kanban Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          label="Total Antrean"
          value={`${counts.antrean} Unit`}
          description="Menunggu + Dikerjakan"
          icon={<ClipboardList className="w-5 h-5" />}
          color="warning"
        />
        <StatCard
          label="Sedang Dikerjakan"
          value={`${counts.dikerjakan} Unit`}
          description="Unit dalam proses servis"
          icon={<Wrench className="w-5 h-5" />}
          color="info"
        />
        <StatCard
          label="Selesai Siap Ambil"
          value={`${counts.selesai} Unit`}
          description="Unit selesai perbaikan"
          icon={<Check className="w-5 h-5" />}
          color="success"
        />
        <StatCard
          label="Sudah Diambil"
          value={`${counts.diambil} Unit`}
          description="Unit telah diserahkan"
          icon={<Package className="w-5 h-5" />}
          color="primary"
        />
      </div>

      {/* Status Filter */}
      <Tabs
        tabs={filterTabItems}
        activeTab={filter}
        onChange={setFilter}
        variant="pill"
      />

      {/* Order Cards */}
      {filtered.length === 0 ? (
        <Card className="flex flex-col items-center justify-center py-16 text-center text-slate-400">
          <Smartphone className="w-12 h-12 stroke-[1.2] mb-2 text-slate-300" />
          <p className="text-sm font-semibold">Tidak ada order servis</p>
          <p className="text-xs text-slate-400 mt-0.5">Buat order servis baru atau ubah filter status</p>
        </Card>
      ) : (
        <div className="space-y-3">
          {filtered.map(s => (
            <OrderCard
              key={s.id}
              order={s}
              expanded={expandedId === s.id}
              onToggle={() => setExpandedId(expandedId === s.id ? null : s.id)}
              onStart={() => updateServiceStatus(s.id, 'in_progress')}
              onCancel={() => updateServiceStatus(s.id, 'cancelled')}
              onMarkComplete={() => updateServiceStatus(s.id, 'completed')}
              onPay={() => { setPayingOrder(s); setPayMethod('cash'); setPaidAmount(s.grand_total - s.paid_amount); }}
            />
          ))}
        </div>
      )}

      {/* Create Modal */}
      <Modal
        open={showCreate}
        onClose={() => setShowCreate(false)}
        title="Buat Order Servis Baru"
        size="xl"
        headerIcon={<Wrench className="w-5 h-5" />}
        footer={
          <>
            <Button variant="secondary" onClick={() => setShowCreate(false)} icon={<X className="w-4 h-4" />}>
              Batal
            </Button>
            <Button onClick={handleSubmit} icon={<Check className="w-4 h-4" />} className="flex-1">
              Simpan Order Servis
            </Button>
          </>
        }
      >
        <div className="space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <Input label="Nama Pelanggan" required value={form.customerName} onChange={e => setField('customerName', e.target.value)} placeholder="Nama pelanggan" />
            <Input label="No. Telepon" value={form.customerPhone} onChange={e => setField('customerPhone', e.target.value)} placeholder="08xxxxxxxxxx" />
          </div>

          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <Input label="Brand Device" required value={form.deviceBrand} onChange={e => setField('deviceBrand', e.target.value)} placeholder="Samsung" />
            <Input label="Model" required value={form.deviceModel} onChange={e => setField('deviceModel', e.target.value)} placeholder="A52" />
            <Input label="IMEI (opsional)" value={form.deviceImei} onChange={e => setField('deviceImei', e.target.value)} placeholder="IMEI" />
            <Input label="Warna (opsional)" value={form.deviceColor} onChange={e => setField('deviceColor', e.target.value)} placeholder="Hitam" />
          </div>

          <div>
            <label className="block text-[10px] font-bold text-slate-500 mb-1 uppercase">
              Deskripsi Masalah <span className="text-red-500">*</span>
            </label>
            <textarea
              value={form.problemDescription}
              onChange={e => setField('problemDescription', e.target.value)}
              rows={3}
              placeholder="Jelaskan kerusakan/keluhan..."
              className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm"
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label className="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Catatan Kondisi (opsional)</label>
              <textarea
                value={form.conditionNotes}
                onChange={e => setField('conditionNotes', e.target.value)}
                rows={2}
                placeholder="Kondisi fisik device saat diterima"
                className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm"
              />
            </div>
            <div>
              <label className="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Catatan Kelengkapan (opsional)</label>
              <textarea
                value={form.completenessNotes}
                onChange={e => setField('completenessNotes', e.target.value)}
                rows={2}
                placeholder="Kelengkapan saat diterima (jok, sim card, dsb)"
                className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm"
              />
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <Input
              label="Masa Garansi (hari)"
              type="number"
              min="0"
              value={form.warrantyDays}
              onChange={e => setField('warrantyDays', Number(e.target.value))}
            />
            <Select
              label="Jenis Kunci Device"
              value={form.lockType}
              onChange={e => setField('lockType', e.target.value as any)}
              options={lockTypeOptions}
            />
          </div>

          {form.lockType !== 'none' && (
            <div className={form.lockType === 'pattern' ? '' : 'grid grid-cols-1'}>
              <label className="block text-[10px] font-bold text-slate-500 mb-1 uppercase">
                Kode Kunci {LOCK_META[form.lockType] || ''}
              </label>
              {form.lockType === 'pattern' ? (
                <PatternLockInput value={form.lockCode} onChange={v => setField('lockCode', v)} />
              ) : (
                <Input
                  type="password"
                  value={form.lockCode}
                  onChange={e => setField('lockCode', e.target.value)}
                  placeholder="Masukkan kode kunci"
                />
              )}
            </div>
          )}

          {/* Line Items Builder */}
          <div className="rounded-2xl border border-slate-200 p-3 space-y-3 bg-slate-50/50">
            <div className="text-xs font-bold text-slate-700">Tambah Item Servis / Sparepart</div>

            <div className="flex gap-2">
              <Button
                type="button"
                onClick={() => handleDraftTypeChange('service')}
                variant={draftType === 'service' ? 'primary' : 'secondary'}
                className="flex-1"
              >
                Servis
              </Button>
              <Button
                type="button"
                onClick={() => handleDraftTypeChange('product')}
                variant={draftType === 'product' ? 'primary' : 'secondary'}
                className="flex-1"
              >
                Sparepart
              </Button>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-12 gap-2 items-end">
              {draftType === 'service' ? (
                <div className="sm:col-span-7">
                  <Select
                    label="Pilih Jenis Servis"
                    value={draftServiceId}
                    onChange={e => handleDraftServiceSelect(e.target.value === '' ? '' : Number(e.target.value))}
                    placeholder="-- Pilih servis --"
                    options={serviceItemOptions}
                  />
                </div>
              ) : (
                <div className="sm:col-span-7">
                  <Select
                    label="Pilih Sparepart"
                    value={draftProductId}
                    onChange={e => handleDraftProductSelect(e.target.value === '' ? '' : Number(e.target.value))}
                    placeholder="-- Pilih sparepart --"
                    options={productOptions}
                  />
                </div>
              )}

              <div className="sm:col-span-2">
                <Input
                  label="Qty"
                  type="number"
                  min="1"
                  value={draftQty}
                  onChange={e => setDraftQty(Math.max(1, Number(e.target.value)))}
                />
              </div>

              <div className="sm:col-span-3">
                <Input
                  label="Harga (Rp)"
                  type="number"
                  min="0"
                  value={draftPrice}
                  onChange={e => setDraftPrice(Number(e.target.value))}
                />
              </div>
            </div>

            <Button
              type="button"
              onClick={addDraftItem}
              disabled={draftType === 'service' ? draftServiceId === '' : draftProductId === ''}
              icon={<Plus className="w-4 h-4" />}
              variant="secondary"
              className="w-full"
            >
              Tambah Item
            </Button>

            {draftItems.length > 0 && (
              <div className="space-y-2">
                {draftItems.map(it => (
                  <div key={it.key} className="flex items-center justify-between gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2">
                    <div className="min-w-0">
                      <div className="text-xs font-bold text-slate-800 truncate">{it.name}</div>
                      <div className="text-[10px] text-slate-500">
                        {it.itemType === 'product' ? 'Sparepart' : 'Servis'} × {it.quantity} • {fmt(it.price)}/unit
                      </div>
                    </div>
                    <div className="flex items-center gap-2 shrink-0">
                      <span className="text-xs font-black text-primary-700">{fmt(it.price * it.quantity)}</span>
                      <Button type="button" variant="ghost" size="xs" onClick={() => removeDraftItem(it.key)} className="hover:!text-red-600">
                        <Trash2 className="w-3.5 h-3.5" />
                      </Button>
                    </div>
                  </div>
                ))}
              </div>
            )}

            <div className="pt-2 border-t border-slate-200 flex items-center justify-between">
              <span className="text-xs font-bold text-slate-600">Estimasi Total</span>
              <span className="text-lg font-black text-primary-700">{fmt(estimatedTotal)}</span>
            </div>
          </div>
        </div>
      </Modal>

      {/* Payment Modal */}
      {payingOrder && (
        <PayModal
          order={payingOrder}
          method={payMethod}
          setMethod={setPayMethod}
          amount={paidAmount}
          setAmount={setPaidAmount}
          onConfirm={handlePayConfirm}
          onClose={() => setPayingOrder(null)}
        />
      )}
    </div>
  );
};

const OrderCard: React.FC<{
  order: ServiceTransaction;
  expanded: boolean;
  onToggle: () => void;
  onStart: () => void;
  onCancel: () => void;
  onMarkComplete: () => void;
  onPay: () => void;
}> = ({ order, expanded, onToggle, onStart, onCancel, onMarkComplete, onPay }) => {
  const meta = STATUS_META[order.status];
  const tech = order.technician_name || '-';

  return (
    <div className={clsx(
      'bg-white rounded-2xl border shadow-sm transition-all',
      expanded ? 'border-primary-400 ring-1 ring-primary-100' : 'border-slate-200',
    )}>
      {/* Card main row */}
      <div className="p-4 flex items-start gap-3 cursor-pointer" onClick={onToggle}>
        <div className="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-500 shrink-0">
          <Smartphone className="w-5 h-5" />
        </div>

        <div className="flex-1 min-w-0">
          <div className="flex flex-wrap items-center gap-2">
            <span className="font-mono font-bold text-primary-700 text-xs">{order.service_code}</span>
            <StatusBadge status={order.status}>{meta.label}</StatusBadge>
            {order.credit_status === 'unpaid' && (
              <StatusBadge status="unpaid">Belum Lunas</StatusBadge>
            )}
            {order.credit_status === 'partial' && (
              <StatusBadge status="partial">Cicil</StatusBadge>
            )}
            <span className="text-[10px] text-slate-400 font-mono">{fmtDate(order.service_at)}</span>
          </div>

          <div className="mt-1.5 text-sm font-black text-slate-900 truncate">{order.customer_name}</div>

          <div className="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-[11px] text-slate-500">
            <span className="inline-flex items-center gap-1"><Smartphone className="w-3 h-3" /> {order.device_brand} {order.device_model}</span>
            {order.device_imei && <span className="inline-flex items-center gap-1 font-mono"><ScanLine className="w-3 h-3" /> {order.device_imei}</span>}
            <span className="inline-flex items-center gap-1"><Phone className="w-3 h-3" /> {order.customer_phone || '-'}</span>
          </div>

          <div className="text-[11px] text-slate-600 mt-1 line-clamp-1">{order.problem_description}</div>
        </div>

        <div className="flex flex-col items-end gap-1.5 shrink-0">
          <span className="text-sm font-black text-primary-700">{fmt(order.grand_total)}</span>
          <span className="text-[10px] text-slate-500 font-semibold">Teknisi: {tech}</span>
          <span className="text-[10px] text-slate-400">{order.items.length} item</span>
        </div>
      </div>

      {/* Action bar */}
      <div className="px-4 pb-4 flex items-center gap-2">
        {order.status === 'pending' && (
          <>
            <Button
              size="sm"
              onClick={e => { e.stopPropagation(); onStart(); }}
              icon={<Wrench className="w-3.5 h-3.5" />}
              className="!bg-blue-600 hover:!bg-blue-700 !shadow-blue-600/30"
            >
              Mulai Kerjakan
            </Button>
            <Button
              size="sm"
              variant="outline"
              onClick={e => { e.stopPropagation(); if (confirm('Batalkan order servis ini?')) onCancel(); }}
              icon={<X className="w-3.5 h-3.5" />}
              className="!border-red-200 !text-red-600 hover:!bg-red-100"
            >
              Batal
            </Button>
          </>
        )}

        {order.status === 'in_progress' && (
          <Button
            size="sm"
            variant="success"
            onClick={e => { e.stopPropagation(); onMarkComplete(); }}
            icon={<Check className="w-3.5 h-3.5" />}
          >
            Tandai Selesai
          </Button>
        )}

        {order.status === 'completed' && (
          <Button
            size="sm"
            onClick={e => { e.stopPropagation(); onPay(); }}
            icon={<Banknote className="w-3.5 h-3.5" />}
          >
            Ambil & Bayar
          </Button>
        )}

        <span className="ml-auto text-[10px] text-slate-400 font-semibold flex items-center gap-1">
          <Calendar className="w-3 h-3" />
          {expanded ? 'Sembunyikan detail' : 'Lihat detail'}
        </span>
      </div>

      {/* Expanded detail */}
      {expanded && (
        <div className="px-4 pb-4 pt-1 border-t border-slate-100 space-y-4">
          {/* Items */}
          <div>
            <div className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Item Servis / Sparepart</div>
            <div className="space-y-1.5">
              {order.items.map((it, idx) => (
                <div key={idx} className="flex items-center justify-between bg-slate-50 rounded-xl px-3 py-2 border border-slate-100">
                  <div className="min-w-0">
                    <div className="text-xs font-bold text-slate-800">
                      {it.item_type === 'product' ? '🔧 ' : '⚙ '}{it.name}
                    </div>
                    <div className="text-[10px] text-slate-500">
                      {it.item_type === 'product' ? 'Sparepart' : 'Servis'} × {it.quantity} • {fmt(it.price)}/unit
                    </div>
                  </div>
                  <span className="text-xs font-black text-primary-700">{fmt(it.subtotal)}</span>
                </div>
              ))}
              {order.items.length === 0 && <div className="text-xs text-slate-400">Tidak ada item</div>}
            </div>
          </div>

          {/* Notes */}
          {(order.condition_notes || order.completeness_notes) && (
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              {order.condition_notes && (
                <div className="rounded-xl bg-amber-50/60 border border-amber-100 p-3">
                  <div className="text-[10px] font-bold text-amber-700 uppercase tracking-wider mb-1">Catatan Kondisi</div>
                  <div className="text-xs text-slate-700">{order.condition_notes}</div>
                </div>
              )}
              {order.completeness_notes && (
                <div className="rounded-xl bg-blue-50/60 border border-blue-100 p-3">
                  <div className="text-[10px] font-bold text-blue-700 uppercase tracking-wider mb-1">Catatan Kelengkapan</div>
                  <div className="text-xs text-slate-700">{order.completeness_notes}</div>
                </div>
              )}
            </div>
          )}

          {/* Warranty & Lock */}
          <div className="grid grid-cols-2 gap-3">
            <div className="flex items-center gap-2 text-xs text-slate-600">
              <ShieldQuestion className="w-4 h-4 text-emerald-600" />
              <span>Garansi: <span className="font-bold text-slate-800">{order.warranty_days} hari</span></span>
            </div>
            {order.device_lock_type && order.device_lock_type !== 'none' && (
              <div className="flex items-center gap-2 text-xs text-slate-600">
                <ShieldQuestion className="w-4 h-4 text-slate-400" />
                <span>Kunci: <span className="font-bold text-slate-800">{LOCK_META[order.device_lock_type]}</span></span>
              </div>
            )}
          </div>

          {/* Totals */}
          <div className="rounded-xl border border-slate-200 p-3 space-y-1.5 text-xs">
            <div className="flex justify-between text-slate-600"><span>Biaya Sparepart</span><span className="font-semibold">{fmt(order.total_sparepart_cost)}</span></div>
            <div className="flex justify-between text-slate-600"><span>Biaya Servis</span><span className="font-semibold">{fmt(order.total_service_cost)}</span></div>
            <div className="pt-2 border-t border-slate-100 flex justify-between font-black text-slate-900">
              <span>Grand Total</span>
              <span className="text-primary-700">{fmt(order.grand_total)}</span>
            </div>
            <div className="flex justify-between text-emerald-700 font-semibold"><span>Dibayar</span><span>{fmt(order.paid_amount)}</span></div>
          </div>
        </div>
      )}
    </div>
  );
};

const PayModal: React.FC<{
  order: ServiceTransaction;
  method: 'cash' | 'transfer' | 'qris' | 'tempo';
  setMethod: (m: 'cash' | 'transfer' | 'qris' | 'tempo') => void;
  amount: number;
  setAmount: (n: number) => void;
  onConfirm: () => void;
  onClose: () => void;
}> = ({ order, method, setMethod, amount, setAmount, onConfirm, onClose }) => {
  const remaining = order.grand_total - order.paid_amount;
  const methods: { key: 'cash' | 'transfer' | 'qris' | 'tempo'; label: string }[] = [
    { key: 'cash', label: 'Tunai' },
    { key: 'transfer', label: 'Transfer' },
    { key: 'qris', label: 'QRIS' },
    { key: 'tempo', label: 'Tempo / Kredit' },
  ];

  const payMethodOptions: SelectOption[] = methods.map(m => ({ value: m.key, label: m.label }));

  return (
    <Modal
      open
      onClose={onClose}
      title="Ambil & Bayar"
      size="lg"
      footer={
        <>
          <Button variant="secondary" onClick={onClose} icon={<X className="w-4 h-4" />}>
            Batal
          </Button>
          <Button onClick={onConfirm} icon={<Check className="w-4 h-4" />} className="flex-1">
            Konfirmasi Penyerahan & Pembayaran
          </Button>
        </>
      }
    >
      <div className="space-y-4">
        <div className="rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-1.5 text-xs">
          <div className="flex justify-between items-center">
            <span className="font-bold text-slate-500">{order.service_code}</span>
            <StatusBadge status={order.status}>{STATUS_META[order.status].label}</StatusBadge>
          </div>
          <div className="flex justify-between text-slate-600"><span>Pelanggan</span><span className="font-semibold text-slate-800">{order.customer_name}</span></div>
          <div className="flex justify-between text-slate-600"><span>Device</span><span className="font-semibold text-slate-800">{order.device_brand} {order.device_model}</span></div>
          <div className="pt-2 border-t border-slate-100 flex justify-between items-baseline text-slate-600">
            <span>Grand Total</span>
            <span className="text-lg font-black text-slate-900">{fmt(order.grand_total)}</span>
          </div>
          <div className="flex justify-between text-emerald-700 font-semibold"><span>Sudah Dibayar</span><span>{fmt(order.paid_amount)}</span></div>
          <div className="flex justify-between text-red-600 font-bold"><span>Sisa Tagihan</span><span>{fmt(remaining)}</span></div>
        </div>

        <div>
          <div className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Metode Pembayaran</div>
          <div className="grid grid-cols-2 gap-2">
            {methods.map(m => (
              <Button
                key={m.key}
                type="button"
                onClick={() => setMethod(m.key)}
                variant={method === m.key ? 'primary' : 'secondary'}
              >
                {m.label}
              </Button>
            ))}
          </div>
        </div>

        <Input label="Jumlah Dibayar (Rp)" type="number" min="0" value={amount} onChange={e => setAmount(Number(e.target.value))} />

        {method === 'tempo' && (
          <div className="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
            Tempo = serahkan unit tanpa pelunasan penuh. Sisa tagihan tercatat sebagai piutang ({fmt(Math.max(0, remaining - amount))}).
          </div>
        )}
      </div>
    </Modal>
  );
};
