import React, { useMemo, useState } from 'react';
import { useApp } from '../context/AppContext';
import { ServiceTransaction, Product, ServiceItem } from '../types';
import {
  Wrench,
  Smartphone,
  User,
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
  Trash2
} from 'lucide-react';
import { PatternLockInput } from '../components/services/PatternLockInput';

const STATUS_META: Record<ServiceTransaction['status'], { label: string; badge: string; dot: string }> = {
  pending: { label: 'Menunggu', badge: 'bg-amber-100 text-amber-800', dot: 'bg-amber-500' },
  in_progress: { label: 'Dikerjakan', badge: 'bg-blue-100 text-blue-800', dot: 'bg-blue-500' },
  completed: { label: 'Selesai', badge: 'bg-emerald-100 text-emerald-800', dot: 'bg-emerald-500' },
  delivered: { label: 'Diambil', badge: 'bg-slate-200 text-slate-700', dot: 'bg-slate-500' },
  cancelled: { label: 'Dibatalkan', badge: 'bg-red-100 text-red-800', dot: 'bg-red-500' }
};

const FILTERS: { key: string; label: string; match: (s: ServiceTransaction) => boolean }[] = [
  { key: 'all', label: 'Semua', match: () => true },
  { key: 'pending', label: 'Menunggu', match: s => s.status === 'pending' },
  { key: 'in_progress', label: 'Dikerjakan', match: s => s.status === 'in_progress' },
  { key: 'completed', label: 'Selesai', match: s => s.status === 'completed' },
  { key: 'delivered', label: 'Diambil', match: s => s.status === 'delivered' },
  { key: 'cancelled', label: 'Dibatalkan', match: s => s.status === 'cancelled' }
];

const LOCK_META: Record<string, string> = {
  none: 'Tanpa Kunci',
  pin: 'PIN',
  pattern: 'Pola',
  password: 'Password'
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

interface DraftLine {
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
    currentUser
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
    lockCode: ''
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
      diambil: by('delivered')
    };
  }, [serviceTransactions]);

  const resetForm = () => {
    setForm({
      customerName: '', customerPhone: '', deviceBrand: '', deviceModel: '',
      deviceImei: '', deviceColor: '', problemDescription: '',
      conditionNotes: '', completenessNotes: '', warrantyDays: 7,
      lockType: 'none', lockCode: ''
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
        purchasePrice: 0
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
        purchasePrice: draftPurchasePrice
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
        purchasePrice: i.purchasePrice
      }))
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

        <button
          onClick={() => { resetForm(); setShowCreate(true); }}
          className="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/30 transition-all inline-flex items-center gap-1.5"
        >
          <Plus className="w-4 h-4" />
          Buat Order Servis Baru
        </button>
      </div>

      {/* Summary Kanban Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <SummaryCard icon={<ClipboardList className="w-5 h-5" />} iconClass="bg-amber-50 text-amber-600" label="Total Antrean" value={counts.antrean} sub="Menunggu + Dikerjakan" />
        <SummaryCard icon={<Wrench className="w-5 h-5" />} iconClass="bg-blue-50 text-blue-600" label="Sedang Dikerjakan" value={counts.dikerjakan} sub="Unit dalam proses servis" />
        <SummaryCard icon={<Check className="w-5 h-5" />} iconClass="bg-emerald-50 text-emerald-600" label="Selesai Siap Ambil" value={counts.selesai} sub="Unit selesai perbaikan" />
        <SummaryCard icon={<Package className="w-5 h-5" />} iconClass="bg-slate-100 text-slate-600" label="Sudah Diambil" value={counts.diambil} sub="Unit telah diserahkan" />
      </div>

      {/* Status Filter */}
      <div className="flex items-center gap-2 overflow-x-auto pb-1">
        {FILTERS.map(f => (
          <button
            key={f.key}
            onClick={() => setFilter(f.key)}
            className={`px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all border ${
              filter === f.key
                ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-100'
            }`}
          >
            {f.label}
            <span className={`ml-1.5 text-[10px] px-1.5 py-0.5 rounded-full ${
              filter === f.key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'
            }`}>
              {serviceTransactions.filter(f.match).length}
            </span>
          </button>
        ))}
      </div>

      {/* Order Cards */}
      {filtered.length === 0 ? (
        <div className="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center justify-center py-16 text-center text-slate-400">
          <Smartphone className="w-12 h-12 stroke-[1.2] mb-2 text-slate-300" />
          <p className="text-sm font-semibold">Tidak ada order servis</p>
          <p className="text-xs text-slate-400 mt-0.5">Buat order servis baru atau ubah filter status</p>
        </div>
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
      {showCreate && (
        <Modal onClose={() => setShowCreate(false)} title="Buat Order Servis Baru">
          <div className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <Field label="Nama Pelanggan" required>
                <input value={form.customerName} onChange={e => setField('customerName', e.target.value)} placeholder="Nama pelanggan" className={inputCls} />
              </Field>
              <Field label="No. Telepon">
                <input value={form.customerPhone} onChange={e => setField('customerPhone', e.target.value)} placeholder="08xxxxxxxxxx" className={inputCls} />
              </Field>
            </div>

            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <Field label="Brand Device" required>
                <input value={form.deviceBrand} onChange={e => setField('deviceBrand', e.target.value)} placeholder="Samsung" className={inputCls} />
              </Field>
              <Field label="Model" required>
                <input value={form.deviceModel} onChange={e => setField('deviceModel', e.target.value)} placeholder="A52" className={inputCls} />
              </Field>
              <Field label="IMEI (opsional)">
                <input value={form.deviceImei} onChange={e => setField('deviceImei', e.target.value)} placeholder="IMEI" className={inputCls} />
              </Field>
              <Field label="Warna (opsional)">
                <input value={form.deviceColor} onChange={e => setField('deviceColor', e.target.value)} placeholder="Hitam" className={inputCls} />
              </Field>
            </div>

            <Field label="Deskripsi Masalah" required>
              <textarea value={form.problemDescription} onChange={e => setField('problemDescription', e.target.value)} rows={3} placeholder="Jelaskan kerusakan/keluhan..." className={inputCls} />
            </Field>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <Field label="Catatan Kondisi (opsional)">
                <textarea value={form.conditionNotes} onChange={e => setField('conditionNotes', e.target.value)} rows={2} placeholder="Kondisi fisik device saat diterima" className={inputCls} />
              </Field>
              <Field label="Catatan Kelengkapan (opsional)">
                <textarea value={form.completenessNotes} onChange={e => setField('completenessNotes', e.target.value)} rows={2} placeholder="Kelengkapan saat diterima (jok, sim card, dsb)" className={inputCls} />
              </Field>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <Field label="Masa Garansi (hari)">
                <input type="number" min="0" value={form.warrantyDays} onChange={e => setField('warrantyDays', Number(e.target.value))} className={inputCls} />
              </Field>
              <Field label="Jenis Kunci Device">
                <select value={form.lockType} onChange={e => setField('lockType', e.target.value as any)} className={inputCls}>
                  <option value="none">Tanpa Kunci</option>
                  <option value="pin">PIN</option>
                  <option value="pattern">Pola (Pattern)</option>
                  <option value="password">Password</option>
                </select>
              </Field>
            </div>

            {form.lockType !== 'none' && (
              <div className={form.lockType === 'pattern' ? '' : 'grid grid-cols-1'}>
                <Field label={`Kode Kunci ${LOCK_META[form.lockType] || ''}`}>
                  {form.lockType === 'pattern' ? (
                    <PatternLockInput value={form.lockCode} onChange={v => setField('lockCode', v)} />
                  ) : (
                    <input
                      type="password"
                      value={form.lockCode}
                      onChange={e => setField('lockCode', e.target.value)}
                      placeholder="Masukkan kode kunci"
                      className={inputCls}
                    />
                  )}
                </Field>
              </div>
            )}

            {/* Line Items Builder */}
            <div className="rounded-2xl border border-slate-200 p-3 space-y-3 bg-slate-50/50">
              <div className="text-xs font-bold text-slate-700">Tambah Item Servis / Sparepart</div>

              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => handleDraftTypeChange('service')}
                  className={`flex-1 py-2 rounded-xl text-xs font-bold transition-all ${
                    draftType === 'service' ? 'bg-primary-600 text-white' : 'bg-white border border-slate-200 text-slate-600'
                  }`}
                >
                  Servis
                </button>
                <button
                  type="button"
                  onClick={() => handleDraftTypeChange('product')}
                  className={`flex-1 py-2 rounded-xl text-xs font-bold transition-all ${
                    draftType === 'product' ? 'bg-primary-600 text-white' : 'bg-white border border-slate-200 text-slate-600'
                  }`}
                >
                  Sparepart
                </button>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-12 gap-2 items-end">
                {draftType === 'service' ? (
                  <div className="sm:col-span-7">
                    <label className="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Pilih Jenis Servis</label>
                    <select value={draftServiceId} onChange={e => handleDraftServiceSelect(e.target.value === '' ? '' : Number(e.target.value))} className={inputCls}>
                      <option value="">-- Pilih servis --</option>
                      {serviceItems.filter(s => s.is_active).map(s => (
                        <option key={s.id} value={s.id}>{s.name} ({fmt(s.estimated_price)})</option>
                      ))}
                    </select>
                  </div>
                ) : (
                  <div className="sm:col-span-7">
                    <label className="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Pilih Sparepart</label>
                    <select value={draftProductId} onChange={e => handleDraftProductSelect(e.target.value === '' ? '' : Number(e.target.value))} className={inputCls}>
                      <option value="">-- Pilih sparepart --</option>
                      {products.filter(p => p.is_active).map(p => (
                        <option key={p.id} value={p.id}>{p.name} (Jual {fmt(p.selling_price)})</option>
                      ))}
                    </select>
                  </div>
                )}

                <div className="sm:col-span-2">
                  <label className="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Qty</label>
                  <input type="number" min="1" value={draftQty} onChange={e => setDraftQty(Math.max(1, Number(e.target.value)))} className={inputCls} />
                </div>

                <div className="sm:col-span-3 space-y-1">
                  <label className="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Harga (Rp)</label>
                  <input type="number" min="0" value={draftPrice} onChange={e => setDraftPrice(Number(e.target.value))} className={inputCls} />
                </div>
              </div>

              <button
                type="button"
                onClick={addDraftItem}
                disabled={draftType === 'service' ? draftServiceId === '' : draftProductId === ''}
                className="w-full py-2.5 bg-slate-800 hover:bg-slate-900 disabled:opacity-40 text-white font-bold text-xs rounded-xl flex items-center justify-center gap-1.5"
              >
                <Plus className="w-4 h-4" />
                Tambah Item
              </button>

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
                        <button type="button" onClick={() => removeDraftItem(it.key)} className="p-1 text-slate-400 hover:text-red-600 rounded-lg">
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
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

          <div className="flex gap-2 mt-5 pt-4 border-t border-slate-100">
            <button type="button" onClick={() => setShowCreate(false)} className="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl flex items-center gap-1.5">
              <X className="w-4 h-4" /> Batal
            </button>
            <button type="button" onClick={handleSubmit} className="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-primary-600/30 flex items-center justify-center gap-2">
              <Check className="w-4 h-4" /> Simpan Order Servis
            </button>
          </div>
        </Modal>
      )}

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

const inputCls =
  'w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm';

const Field: React.FC<{ label: string; required?: boolean; children: React.ReactNode }> = ({ label, required, children }) => (
  <label className="block">
    <span className="block text-[10px] font-bold text-slate-500 mb-1 uppercase">
      {label} {required && <span className="text-red-500">*</span>}
    </span>
    {children}
  </label>
);

const Modal: React.FC<{ title: string; onClose: () => void; children: React.ReactNode }> = ({ title, onClose, children }) => (
  <div className="fixed inset-0 z-50 flex items-start sm:items-center justify-center p-4 bg-black/40 overflow-y-auto">
    <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl my-8">
      <div className="sticky top-0 bg-white border-b border-slate-100 px-5 py-4 rounded-t-2xl flex items-center justify-between">
        <h2 className="text-base font-black text-slate-900 flex items-center gap-2">
          <Wrench className="w-5 h-5 text-primary-600" />
          {title}
        </h2>
        <button type="button" onClick={onClose} className="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg">
          <X className="w-5 h-5" />
        </button>
      </div>
      <div className="px-5 py-5">{children}</div>
    </div>
  </div>
);

const SummaryCard: React.FC<{ icon: React.ReactNode; iconClass: string; label: string; value: number; sub: string }> = ({ icon, iconClass, label, value, sub }) => (
  <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm">
    <div className="flex items-center justify-between">
      <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">{label}</span>
      <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${iconClass}`}>{icon}</div>
    </div>
    <div className="mt-3">
      <div className="text-2xl font-black text-slate-900">{value} Unit</div>
      <div className="text-xs text-slate-500 mt-1">{sub}</div>
    </div>
  </div>
);

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
    <div className={`bg-white rounded-2xl border shadow-sm transition-all ${expanded ? 'border-primary-400 ring-1 ring-primary-100' : 'border-slate-200'}`}>
      {/* Card main row */}
      <div className="p-4 flex items-start gap-3 cursor-pointer" onClick={onToggle}>
        <div className="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-500 shrink-0">
          <Smartphone className="w-5 h-5" />
        </div>

        <div className="flex-1 min-w-0">
          <div className="flex flex-wrap items-center gap-2">
            <span className="font-mono font-bold text-primary-700 text-xs">{order.service_code}</span>
            <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${meta.badge}`}>{meta.label}</span>
            {order.credit_status === 'unpaid' && (
              <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">Belum Lunas</span>
            )}
            {order.credit_status === 'partial' && (
              <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">Cicil</span>
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
            <button type="button" onClick={e => { e.stopPropagation(); onStart(); }} className="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm shadow-blue-600/30 flex items-center gap-1.5">
              <Wrench className="w-3.5 h-3.5" /> Mulai Kerjakan
            </button>
            <button type="button" onClick={e => { e.stopPropagation(); if (confirm('Batalkan order servis ini?')) onCancel(); }} className="px-3.5 py-2 bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs rounded-xl border border-red-200 flex items-center gap-1.5">
              <X className="w-3.5 h-3.5" /> Batal
            </button>
          </>
        )}

        {order.status === 'in_progress' && (
          <button type="button" onClick={e => { e.stopPropagation(); onMarkComplete(); }} className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm shadow-emerald-600/30 flex items-center gap-1.5">
            <Check className="w-3.5 h-3.5" /> Tandai Selesai
          </button>
        )}

        {order.status === 'completed' && (
          <button type="button" onClick={e => { e.stopPropagation(); onPay(); }} className="px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-sm shadow-primary-600/30 flex items-center gap-1.5">
            <Banknote className="w-3.5 h-3.5" /> Ambil & Bayar
          </button>
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
    { key: 'tempo', label: 'Tempo / Kredit' }
  ];

  return (
    <Modal title="Ambil & Bayar" onClose={onClose}>
      <div className="space-y-4">
        <div className="rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-1.5 text-xs">
          <div className="flex justify-between items-center">
            <span className="font-bold text-slate-500">{order.service_code}</span>
            <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${STATUS_META[order.status].badge}`}>{STATUS_META[order.status].label}</span>
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
              <button
                key={m.key}
                type="button"
                onClick={() => setMethod(m.key)}
                className={`px-3 py-2.5 rounded-xl text-xs font-bold transition-all border ${
                  method === m.key ? 'bg-primary-600 text-white border-primary-600 shadow-sm' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-100'
                }`}
              >
                {m.label}
              </button>
            ))}
          </div>
        </div>

        <Field label={`Jumlah Dibayar (Rp)`}>
          <input type="number" min="0" value={amount} onChange={e => setAmount(Number(e.target.value))} className={inputCls} />
        </Field>

        {method === 'tempo' && (
          <div className="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
            Tempo = serahkan unit tanpa pelunasan penuh. Sisa tagihan tercatat sebagai piutang ({fmt(Math.max(0, remaining - amount))}).
          </div>
        )}

        <div className="flex gap-2 pt-2">
          <button type="button" onClick={onClose} className="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl flex items-center gap-1.5">
            <X className="w-4 h-4" /> Batal
          </button>
          <button type="button" onClick={onConfirm} className="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-primary-600/30 flex items-center justify-center gap-2">
            <Check className="w-4 h-4" /> Konfirmasi Penyerahan & Pembayaran
          </button>
        </div>
      </div>
    </Modal>
  );
};
