import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import { Customer, CustomerGroup } from '../types';
import {
  Users,
  UserPlus,
  Sparkles,
  Phone,
  Mail,
  Tag,
  Coins,
  CreditCard,
  Plus,
  Pencil,
  Trash2,
  X,
  Search,
  Check,
  ChevronRight,
  History,
  DollarSign,
  Filter,
  ArrowDownCircle,
  ArrowUpCircle
} from 'lucide-react';

type Tab = 'pelanggan' | 'grup';

const emptyCustomerForm = {
  name: '',
  phone: '',
  email: '',
  type: 'regular' as 'regular' | 'member',
  customer_group_id: undefined as number | undefined,
  member_code: '',
};

const emptyGroupForm = {
  name: '',
  discount_percent: 0,
  description: '',
  is_active: true,
};

const fmtRp = (n: number) => `Rp ${n.toLocaleString('id-ID')}`;

export const CustomersView: React.FC = () => {
  const {
    customers, setCustomers,
    customerGroups, setCustomerGroups,
    customerPointLedgers,
    sales,
    redeemCustomerPoints,
    payCustomerCredit,
  } = useApp();

  const [tab, setTab] = useState<Tab>('pelanggan');
  const [search, setSearch] = useState('');
  const [filterGroup, setFilterGroup] = useState<number | 'all'>('all');
  const [filterType, setFilterType] = useState<'all' | 'regular' | 'member'>('all');

  // Customer modals
  const [showCustomerModal, setShowCustomerModal] = useState(false);
  const [editingCustomer, setEditingCustomer] = useState<Customer | null>(null);
  const [customerForm, setCustomerForm] = useState(emptyCustomerForm);

  // Point modal
  const [showPointModal, setShowPointModal] = useState(false);
  const [pointCustomer, setPointCustomer] = useState<Customer | null>(null);
  const [redeemPoints, setRedeemPoints] = useState('');
  const [redeemNotes, setRedeemNotes] = useState('');

  // Transaction modal
  const [showTxModal, setShowTxModal] = useState(false);
  const [txCustomer, setTxCustomer] = useState<Customer | null>(null);
  const [paySaleId, setPaySaleId] = useState<number | null>(null);
  const [payAmount, setPayAmount] = useState('');
  const [payMethod, setPayMethod] = useState<'cash' | 'transfer' | 'qris'>('cash');

  // Group modals
  const [showGroupModal, setShowGroupModal] = useState(false);
  const [editingGroup, setEditingGroup] = useState<CustomerGroup | null>(null);
  const [groupForm, setGroupForm] = useState(emptyGroupForm);

  const genMemberCode = () => {
    const seq = (customers.filter(c => c.type === 'member').length + 1).toString().padStart(4, '0');
    return `MBR-${seq}`;
  };

  // Filtered customers
  const filteredCustomers = useMemo(() => {
    return customers.filter(c => {
      const matchSearch = !search || c.name.toLowerCase().includes(search.toLowerCase()) || c.phone?.includes(search) || c.email?.toLowerCase().includes(search.toLowerCase()) || c.member_code?.toLowerCase().includes(search.toLowerCase());
      const matchGroup = filterGroup === 'all' || c.customer_group_id === filterGroup;
      const matchType = filterType === 'all' || c.type === filterType;
      return matchSearch && matchGroup && matchType;
    });
  }, [customers, search, filterGroup, filterType]);

  // Summary stats
  const totalMembers = customers.filter(c => c.type === 'member').length;
  const totalPoints = customers.filter(c => c.type === 'member').reduce((s, c) => s + c.points_balance, 0);
  const totalCredit = sales
    .filter(s => s.customer_id && s.credit_status && s.credit_status !== 'paid')
    .reduce((s, sale) => {
      const unpaid = sale.grand_total - (sale.paid_amount || 0);
      return s + Math.max(0, unpaid);
    }, 0);

  // --- Customer CRUD handlers ---
  const openAddCustomer = () => {
    setEditingCustomer(null);
    setCustomerForm({ ...emptyCustomerForm, member_code: '' });
    setShowCustomerModal(true);
  };

  const openEditCustomer = (c: Customer) => {
    setEditingCustomer(c);
    setCustomerForm({
      name: c.name,
      phone: c.phone || '',
      email: c.email || '',
      type: c.type,
      customer_group_id: c.customer_group_id,
      member_code: c.member_code || '',
    });
    setShowCustomerModal(true);
  };

  const saveCustomer = () => {
    if (!customerForm.name.trim()) return;
    const isMember = customerForm.type === 'member';
    const code = isMember && !editingCustomer
      ? genMemberCode()
      : editingCustomer
        ? (customerForm.member_code || editingCustomer.member_code || '')
        : '';

    if (editingCustomer) {
      setCustomers(prev => prev.map(c => c.id === editingCustomer.id ? {
        ...c,
        name: customerForm.name,
        phone: customerForm.phone || undefined,
        email: customerForm.email || undefined,
        type: customerForm.type,
        customer_group_id: customerForm.customer_group_id,
        member_code: code,
      } : c));
    } else {
      setCustomers(prev => [...prev, {
        id: Date.now(),
        name: customerForm.name,
        phone: customerForm.phone || undefined,
        email: customerForm.email || undefined,
        type: customerForm.type,
        points_balance: 0,
        is_active: true,
        customer_group_id: customerForm.customer_group_id,
        member_code: code,
      }]);
    }
    setShowCustomerModal(false);
  };

  const deleteCustomer = (id: number) => {
    if (!confirm('Hapus pelanggan ini?')) return;
    setCustomers(prev => prev.filter(c => c.id !== id));
  };

  const toggleActive = (c: Customer) => {
    setCustomers(prev => prev.map(x => x.id === c.id ? { ...x, is_active: !x.is_active } : x));
  };

  // --- Point modal ---
  const openPointModal = (c: Customer) => {
    setPointCustomer(c);
    setRedeemPoints('');
    setRedeemNotes('');
    setShowPointModal(true);
  };

  const handleRedeem = () => {
    if (!pointCustomer) return;
    const pts = parseInt(redeemPoints, 10);
    if (!pts || pts <= 0) return;
    const ok = redeemCustomerPoints(pointCustomer.id, pts, redeemNotes || undefined);
    if (!ok) {
      alert('Poin tidak cukup!');
      return;
    }
    setPointCustomer(prev => prev ? { ...prev, points_balance: prev.points_balance - pts } : prev);
    setRedeemPoints('');
    setRedeemNotes('');
  };

  const pointLedgerForCustomer = pointCustomer
    ? customerPointLedgers.filter(l => l.customer_id === pointCustomer.id).sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
    : [];

  // --- Transaction modal ---
  const openTxModal = (c: Customer) => {
    setTxCustomer(c);
    setPaySaleId(null);
    setPayAmount('');
    setPayMethod('cash');
    setShowTxModal(true);
  };

  const customerSales = txCustomer
    ? sales.filter(s => s.customer_id === txCustomer.id).sort((a, b) => new Date(b.sale_at).getTime() - new Date(a.sale_at).getTime())
    : [];

  const unpaidSales = customerSales.filter(s => s.credit_status && s.credit_status !== 'paid');

  const handlePayCredit = () => {
    if (!txCustomer || !paySaleId) return;
    const amt = parseInt(payAmount, 10);
    if (!amt || amt <= 0) return;
    payCustomerCredit(txCustomer.id, paySaleId, amt, payMethod);
    setPaySaleId(null);
    setPayAmount('');
  };

  // --- Group CRUD ---
  const openAddGroup = () => {
    setEditingGroup(null);
    setGroupForm({ ...emptyGroupForm });
    setShowGroupModal(true);
  };

  const openEditGroup = (g: CustomerGroup) => {
    setEditingGroup(g);
    setGroupForm({ name: g.name, discount_percent: g.discount_percent, description: g.description || '', is_active: g.is_active });
    setShowGroupModal(true);
  };

  const saveGroup = () => {
    if (!groupForm.name.trim()) return;
    if (editingGroup) {
      setCustomerGroups(prev => prev.map(g => g.id === editingGroup.id ? { ...g, ...groupForm } : g));
    } else {
      setCustomerGroups(prev => [...prev, { id: Date.now(), ...groupForm }]);
    }
    setShowGroupModal(false);
  };

  const deleteGroup = (id: number) => {
    if (!confirm('Hapus grup ini?')) return;
    setCustomerGroups(prev => prev.filter(g => g.id !== id));
  };

  const memberCount = (groupId: number) => customers.filter(c => c.customer_group_id === groupId).length;

  // ==================== RENDER ====================
  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-slate-900 tracking-tight">Pelanggan & Poin</h1>
          <p className="text-xs text-slate-500 mt-1">Kelola data pelanggan, grup, poin loyalitas, dan piutang.</p>
        </div>
      </div>

      {/* Tab Bar */}
      <div className="flex gap-1 bg-slate-100 rounded-xl p-1 w-fit">
        <button
          onClick={() => setTab('pelanggan')}
          className={`px-4 py-2 text-xs font-bold rounded-lg transition-all ${tab === 'pelanggan' ? 'bg-white shadow-sm text-primary-700' : 'text-slate-500 hover:text-slate-700'}`}
        >
          <Users className="w-3.5 h-3.5 inline mr-1.5" />
          Pelanggan
        </button>
        <button
          onClick={() => setTab('grup')}
          className={`px-4 py-2 text-xs font-bold rounded-lg transition-all ${tab === 'grup' ? 'bg-white shadow-sm text-primary-700' : 'text-slate-500 hover:text-slate-700'}`}
        >
          <Tag className="w-3.5 h-3.5 inline mr-1.5" />
          Grup Pelanggan
        </button>
      </div>

      {/* ================== TAB: PELANGGAN ================== */}
      {tab === 'pelanggan' && (
        <>
          {/* Summary Cards */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pelanggan</span>
                <div className="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><Users className="w-4 h-4" /></div>
              </div>
              <div className="mt-3 text-2xl font-black text-slate-900">{customers.length}</div>
              <div className="text-xs text-slate-500 mt-1">{customers.filter(c => c.is_active).length} aktif</div>
            </div>

            <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Member</span>
                <div className="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center"><Sparkles className="w-4 h-4" /></div>
              </div>
              <div className="mt-3 text-2xl font-black text-amber-600">{totalMembers}</div>
              <div className="text-xs text-slate-500 mt-1">Pelanggan terdaftar member</div>
            </div>

            <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Poin Beredar</span>
                <div className="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><Coins className="w-4 h-4" /></div>
              </div>
              <div className="mt-3 text-2xl font-black text-emerald-700">{totalPoints.toLocaleString('id-ID')}</div>
              <div className="text-xs text-slate-500 mt-1">Poin seluruh member</div>
            </div>

            <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Piutang Pelanggan</span>
                <div className="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center"><CreditCard className="w-4 h-4" /></div>
              </div>
              <div className="mt-3 text-2xl font-black text-red-600">{fmtRp(totalCredit)}</div>
              <div className="text-xs text-slate-500 mt-1">Tagihan tempo belum lunas</div>
            </div>
          </div>

          {/* Toolbar */}
          <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            <div className="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
              <div className="flex items-center gap-2 flex-wrap flex-1">
                <div className="relative flex-1 min-w-[200px]">
                  <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                  <input
                    type="text"
                    value={search}
                    onChange={e => setSearch(e.target.value)}
                    placeholder="Cari nama, telepon, email, kode member..."
                    className="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  />
                </div>
                <select
                  value={filterGroup}
                  onChange={e => setFilterGroup(e.target.value === 'all' ? 'all' : Number(e.target.value))}
                  className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="all">Semua Grup</option>
                  {customerGroups.filter(g => g.is_active).map(g => (
                    <option key={g.id} value={g.id}>{g.name}</option>
                  ))}
                </select>
                <select
                  value={filterType}
                  onChange={e => setFilterType(e.target.value as typeof filterType)}
                  className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="all">Semua Tipe</option>
                  <option value="member">Member</option>
                  <option value="regular">Regular</option>
                </select>
              </div>
              <button
                onClick={openAddCustomer}
                className="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/30 transition-all flex items-center gap-1.5 whitespace-nowrap"
              >
                <UserPlus className="w-4 h-4" />
                Tambah Pelanggan
              </button>
            </div>
          </div>

          {/* Customer Table */}
          <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
                  <tr>
                    <th className="px-5 py-3">Nama</th>
                    <th className="px-4 py-3">Kontak</th>
                    <th className="px-4 py-3">Grup</th>
                    <th className="px-4 py-3">Tipe</th>
                    <th className="px-4 py-3 text-right">Poin</th>
                    <th className="px-4 py-3 text-center">Aktif</th>
                    <th className="px-4 py-3">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 text-slate-700">
                  {filteredCustomers.map(c => (
                    <tr key={c.id} className="hover:bg-slate-50/70 transition-colors">
                      <td className="px-5 py-3">
                        <div className="font-bold text-slate-800">{c.name}</div>
                        {c.member_code && (
                          <div className="text-[10px] text-amber-600 font-mono mt-0.5">{c.member_code}</div>
                        )}
                      </td>
                      <td className="px-4 py-3">
                        {c.phone && <div className="flex items-center gap-1 text-slate-600"><Phone className="w-3 h-3" />{c.phone}</div>}
                        {c.email && <div className="flex items-center gap-1 text-slate-500"><Mail className="w-3 h-3" />{c.email}</div>}
                        {!c.phone && !c.email && <span className="text-slate-400">-</span>}
                      </td>
                      <td className="px-4 py-3 text-slate-600">
                        {customerGroups.find(g => g.id === c.customer_group_id)?.name || <span className="text-slate-400">-</span>}
                      </td>
                      <td className="px-4 py-3">
                        <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${c.type === 'member' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600'}`}>
                          {c.type === 'member' ? 'MEMBER' : 'REGULAR'}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-right font-bold text-slate-800">
                        {c.type === 'member' ? c.points_balance.toLocaleString('id-ID') : '-'}
                      </td>
                      <td className="px-4 py-3 text-center">
                        <button
                          onClick={() => toggleActive(c)}
                          className={`w-9 h-5 rounded-full transition-colors relative ${c.is_active ? 'bg-primary-600' : 'bg-slate-300'}`}
                        >
                          <span className={`absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform ${c.is_active ? 'left-[18px]' : 'left-0.5'}`} />
                        </button>
                      </td>
                      <td className="px-4 py-3">
                        <div className="flex items-center gap-1">
                          <button onClick={() => openEditCustomer(c)} title="Edit" className="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors"><Pencil className="w-3.5 h-3.5" /></button>
                          <button onClick={() => deleteCustomer(c.id)} title="Hapus" className="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors"><Trash2 className="w-3.5 h-3.5" /></button>
                          {c.type === 'member' && (
                            <button onClick={() => openPointModal(c)} title="Poin" className="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"><Coins className="w-3.5 h-3.5" /></button>
                          )}
                          <button onClick={() => openTxModal(c)} title="Transaksi" className="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"><History className="w-3.5 h-3.5" /></button>
                        </div>
                      </td>
                    </tr>
                  ))}
                  {filteredCustomers.length === 0 && (
                    <tr>
                      <td colSpan={7} className="px-5 py-12 text-center text-slate-400">
                        <Users className="w-10 h-10 mx-auto mb-2 text-slate-300" />
                        <p className="font-semibold text-sm">Belum ada pelanggan ditemukan</p>
                        <p className="text-xs mt-1">Klik "Tambah Pelanggan" untuk menambah data baru.</p>
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}

      {/* ================== TAB: GRUP PELANGGAN ================== */}
      {tab === 'grup' && (
        <>
          <div className="flex justify-end">
            <button
              onClick={openAddGroup}
              className="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/30 transition-all flex items-center gap-1.5"
            >
              <Plus className="w-4 h-4" />
              Tambah Grup
            </button>
          </div>

          <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
                  <tr>
                    <th className="px-5 py-3">Nama Grup</th>
                    <th className="px-4 py-3 text-center">Diskon %</th>
                    <th className="px-4 py-3">Deskripsi</th>
                    <th className="px-4 py-3 text-center">Jumlah Anggota</th>
                    <th className="px-4 py-3">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 text-slate-700">
                  {customerGroups.map(g => (
                    <tr key={g.id} className="hover:bg-slate-50/70 transition-colors">
                      <td className="px-5 py-3 font-bold text-slate-800">{g.name}</td>
                      <td className="px-4 py-3 text-center font-bold text-primary-700">{g.discount_percent}%</td>
                      <td className="px-4 py-3 text-slate-600">{g.description || <span className="text-slate-400">-</span>}</td>
                      <td className="px-4 py-3 text-center">
                        <span className="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold">{memberCount(g.id)}</span>
                      </td>
                      <td className="px-4 py-3">
                        <div className="flex items-center gap-1">
                          <button onClick={() => openEditGroup(g)} title="Edit" className="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors"><Pencil className="w-3.5 h-3.5" /></button>
                          <button onClick={() => deleteGroup(g.id)} title="Hapus" className="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors"><Trash2 className="w-3.5 h-3.5" /></button>
                        </div>
                      </td>
                    </tr>
                  ))}
                  {customerGroups.length === 0 && (
                    <tr>
                      <td colSpan={5} className="px-5 py-12 text-center text-slate-400">
                        <Tag className="w-10 h-10 mx-auto mb-2 text-slate-300" />
                        <p className="font-semibold text-sm">Belum ada grup pelanggan</p>
                        <p className="text-xs mt-1">Klik "Tambah Grup" untuk membuat grup baru.</p>
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}

      {/* ================== MODAL: ADD/EDIT CUSTOMER ================== */}
      {showCustomerModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onClick={() => setShowCustomerModal(false)}>
          <div className="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 border border-slate-200" onClick={e => e.stopPropagation()}>
            <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
              <h3 className="text-sm font-black text-slate-900">{editingCustomer ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru'}</h3>
              <button onClick={() => setShowCustomerModal(false)} className="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400"><X className="w-4 h-4" /></button>
            </div>
            <div className="p-6 space-y-4">
              <div>
                <label className="block text-[11px] font-bold text-slate-600 mb-1">Nama Lengkap *</label>
                <input
                  type="text"
                  value={customerForm.name}
                  onChange={e => setCustomerForm(p => ({ ...p, name: e.target.value }))}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  placeholder="Nama pelanggan"
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-[11px] font-bold text-slate-600 mb-1">Telepon</label>
                  <input
                    type="text"
                    value={customerForm.phone}
                    onChange={e => setCustomerForm(p => ({ ...p, phone: e.target.value }))}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="08xxx"
                  />
                </div>
                <div>
                  <label className="block text-[11px] font-bold text-slate-600 mb-1">Email</label>
                  <input
                    type="email"
                    value={customerForm.email}
                    onChange={e => setCustomerForm(p => ({ ...p, email: e.target.value }))}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="email@contoh.com"
                  />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-[11px] font-bold text-slate-600 mb-1">Tipe Pelanggan *</label>
                  <select
                    value={customerForm.type}
                    onChange={e => setCustomerForm(p => ({ ...p, type: e.target.value as 'regular' | 'member' }))}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="regular">Regular</option>
                    <option value="member">Member</option>
                  </select>
                </div>
                <div>
                  <label className="block text-[11px] font-bold text-slate-600 mb-1">Grup Pelanggan</label>
                  <select
                    value={customerForm.customer_group_id || ''}
                    onChange={e => setCustomerForm(p => ({ ...p, customer_group_id: e.target.value ? Number(e.target.value) : undefined }))}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Tanpa Grup</option>
                    {customerGroups.filter(g => g.is_active).map(g => (
                      <option key={g.id} value={g.id}>{g.name} ({g.discount_percent}% diskon)</option>
                    ))}
                  </select>
                </div>
              </div>
              {customerForm.type === 'member' && (
                <div>
                  <label className="block text-[11px] font-bold text-slate-600 mb-1">Kode Member</label>
                  <input
                    type="text"
                    value={editingCustomer ? customerForm.member_code : genMemberCode()}
                    readOnly
                    className="w-full px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-xs font-mono font-bold text-amber-700"
                  />
                  <p className="text-[10px] text-slate-400 mt-1">Kode member digenerate otomatis.</p>
                </div>
              )}
            </div>
            <div className="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
              <button onClick={() => setShowCustomerModal(false)} className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors">Batal</button>
              <button onClick={saveCustomer} className="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                <Check className="w-3.5 h-3.5" />
                {editingCustomer ? 'Simpan Perubahan' : 'Tambah Pelanggan'}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ================== MODAL: POINTS ================== */}
      {showPointModal && pointCustomer && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onClick={() => setShowPointModal(false)}>
          <div className="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 border border-slate-200 max-h-[80vh] flex flex-col" onClick={e => e.stopPropagation()}>
            <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
              <div>
                <h3 className="text-sm font-black text-slate-900">Poin Pelanggan</h3>
                <p className="text-[11px] text-slate-500 mt-0.5">{pointCustomer.name} {pointCustomer.member_code && `(${pointCustomer.member_code})`}</p>
              </div>
              <button onClick={() => setShowPointModal(false)} className="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400"><X className="w-4 h-4" /></button>
            </div>

            <div className="p-6 space-y-4 overflow-y-auto flex-1">
              {/* Balance */}
              <div className="p-4 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between">
                <div>
                  <div className="text-[11px] font-bold text-emerald-600 uppercase">Saldo Poin Saat Ini</div>
                  <div className="text-2xl font-black text-emerald-700 mt-1">{pointCustomer.points_balance.toLocaleString('id-ID')}</div>
                </div>
                <Coins className="w-8 h-8 text-emerald-400" />
              </div>

              {/* Redeem Form */}
              <div className="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                <div className="text-xs font-bold text-slate-700">Tukar Poin</div>
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block text-[10px] font-bold text-slate-500 mb-1">Jumlah Poin</label>
                    <input
                      type="number"
                      min={1}
                      max={pointCustomer.points_balance}
                      value={redeemPoints}
                      onChange={e => setRedeemPoints(e.target.value)}
                      className="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                      placeholder="0"
                    />
                  </div>
                  <div>
                    <label className="block text-[10px] font-bold text-slate-500 mb-1">Catatan</label>
                    <input
                      type="text"
                      value={redeemNotes}
                      onChange={e => setRedeemNotes(e.target.value)}
                      className="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                      placeholder="Opsional"
                    />
                  </div>
                </div>
                <button
                  onClick={handleRedeem}
                  disabled={!redeemPoints || parseInt(redeemPoints, 10) <= 0}
                  className="px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white font-bold text-xs rounded-xl transition-all flex items-center gap-1.5"
                >
                  <ArrowDownCircle className="w-3.5 h-3.5" />
                  Tukarkan Poin
                </button>
              </div>

              {/* Ledger History */}
              <div>
                <div className="text-xs font-bold text-slate-700 mb-2">Riwayat Poin</div>
                {pointLedgerForCustomer.length === 0 ? (
                  <p className="text-xs text-slate-400 text-center py-4">Belum ada riwayat poin.</p>
                ) : (
                  <div className="space-y-1.5 max-h-48 overflow-y-auto">
                    {pointLedgerForCustomer.map(l => (
                      <div key={l.id} className="flex items-center justify-between p-2.5 bg-white rounded-lg border border-slate-100">
                        <div className="flex items-center gap-2">
                          {l.points > 0 ? (
                            <ArrowUpCircle className="w-4 h-4 text-emerald-500 shrink-0" />
                          ) : (
                            <ArrowDownCircle className="w-4 h-4 text-red-500 shrink-0" />
                          )}
                          <div>
                            <div className="text-[11px] font-semibold text-slate-700">{l.notes || l.source}</div>
                            <div className="text-[10px] text-slate-400">{new Date(l.created_at).toLocaleString('id-ID')}</div>
                          </div>
                        </div>
                        <div className="text-right">
                          <div className={`text-xs font-bold ${l.points > 0 ? 'text-emerald-600' : 'text-red-600'}`}>
                            {l.points > 0 ? '+' : ''}{l.points}
                          </div>
                          <div className="text-[10px] text-slate-400">Saldo: {l.balance_after}</div>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* ================== MODAL: TRANSAKSI & PIUTANG ================== */}
      {showTxModal && txCustomer && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onClick={() => setShowTxModal(false)}>
          <div className="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 border border-slate-200 max-h-[80vh] flex flex-col" onClick={e => e.stopPropagation()}>
            <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
              <div>
                <h3 className="text-sm font-black text-slate-900">Transaksi Pelanggan</h3>
                <p className="text-[11px] text-slate-500 mt-0.5">{txCustomer.name}</p>
              </div>
              <button onClick={() => setShowTxModal(false)} className="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400"><X className="w-4 h-4" /></button>
            </div>

            <div className="p-6 space-y-4 overflow-y-auto flex-1">
              {/* Unpaid Credit Summary */}
              {unpaidSales.length > 0 && (
                <div className="p-4 bg-red-50 rounded-xl border border-red-200 space-y-3">
                  <div className="text-xs font-bold text-red-700">Piutang Belum Lunas ({unpaidSales.length})</div>
                  {unpaidSales.map(s => {
                    const unpaidAmt = s.grand_total - (s.paid_amount || 0);
                    return (
                      <div key={s.id} className="p-3 bg-white rounded-lg border border-red-100">
                        <div className="flex items-center justify-between mb-2">
                          <div>
                            <span className="text-xs font-mono font-bold text-primary-700">{s.sale_code}</span>
                            <span className="text-[10px] text-slate-500 ml-2">{new Date(s.sale_at).toLocaleDateString('id-ID')}</span>
                          </div>
                          <span className="text-xs font-black text-red-600">{fmtRp(unpaidAmt)}</span>
                        </div>
                        {paySaleId === s.id ? (
                          <div className="flex items-center gap-2 mt-2">
                            <input
                              type="number"
                              min={1}
                              max={unpaidAmt}
                              value={payAmount}
                              onChange={e => setPayAmount(e.target.value)}
                              className="w-32 px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-primary-500"
                              placeholder="Jumlah bayar"
                            />
                            <select
                              value={payMethod}
                              onChange={e => setPayMethod(e.target.value as typeof payMethod)}
                              className="px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                              <option value="cash">Tunai</option>
                              <option value="transfer">Transfer</option>
                              <option value="qris">QRIS</option>
                            </select>
                            <button
                              onClick={() => { handlePayCredit(); setPaySaleId(null); }}
                              className="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg"
                            >
                              Bayar
                            </button>
                            <button
                              onClick={() => setPaySaleId(null)}
                              className="px-2 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-lg"
                            >
                              Batal
                            </button>
                          </div>
                        ) : (
                          <button
                            onClick={() => { setPaySaleId(s.id); setPayAmount(unpaidAmt.toString()); }}
                            className="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-[10px] font-bold rounded-lg flex items-center gap-1"
                          >
                            <DollarSign className="w-3 h-3" />
                            Bayar Piutang
                          </button>
                        )}
                      </div>
                    );
                  })}
                </div>
              )}

              {/* All Sales */}
              <div>
                <div className="text-xs font-bold text-slate-700 mb-2">Riwayat Transaksi</div>
                {customerSales.length === 0 ? (
                  <p className="text-xs text-slate-400 text-center py-6">Belum ada transaksi.</p>
                ) : (
                  <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                      <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
                        <tr>
                          <th className="px-4 py-2">No. Nota</th>
                          <th className="px-4 py-2">Tanggal</th>
                          <th className="px-4 py-2 text-right">Total</th>
                          <th className="px-4 py-2 text-right">Bayar</th>
                          <th className="px-4 py-3">Metode</th>
                          <th className="px-4 py-2">Status</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-100 text-slate-700">
                        {customerSales.slice(0, 20).map(s => (
                          <tr key={s.id} className="hover:bg-slate-50/70">
                            <td className="px-4 py-2 font-mono font-bold text-primary-700">{s.sale_code}</td>
                            <td className="px-4 py-2">{new Date(s.sale_at).toLocaleDateString('id-ID')}</td>
                            <td className="px-4 py-2 text-right font-bold">{fmtRp(s.grand_total)}</td>
                            <td className="px-4 py-2 text-right text-slate-600">{fmtRp(s.paid_amount || 0)}</td>
                            <td className="px-4 py-2 uppercase font-semibold text-slate-600">{s.payment_method}</td>
                            <td className="px-4 py-2">
                              {s.credit_status ? (
                                <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                                  s.credit_status === 'paid' ? 'bg-emerald-100 text-emerald-800' :
                                  s.credit_status === 'partial' ? 'bg-amber-100 text-amber-800' :
                                  'bg-red-100 text-red-800'
                                }`}>
                                  {s.credit_status.toUpperCase()}
                                </span>
                              ) : (
                                <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">PAID</span>
                              )}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* ================== MODAL: ADD/EDIT GROUP ================== */}
      {showGroupModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onClick={() => setShowGroupModal(false)}>
          <div className="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 border border-slate-200" onClick={e => e.stopPropagation()}>
            <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
              <h3 className="text-sm font-black text-slate-900">{editingGroup ? 'Edit Grup' : 'Tambah Grup Baru'}</h3>
              <button onClick={() => setShowGroupModal(false)} className="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400"><X className="w-4 h-4" /></button>
            </div>
            <div className="p-6 space-y-4">
              <div>
                <label className="block text-[11px] font-bold text-slate-600 mb-1">Nama Grup *</label>
                <input
                  type="text"
                  value={groupForm.name}
                  onChange={e => setGroupForm(p => ({ ...p, name: e.target.value }))}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  placeholder="Contoh: VIP, Reseller"
                />
              </div>
              <div>
                <label className="block text-[11px] font-bold text-slate-600 mb-1">Diskon (%)</label>
                <input
                  type="number"
                  min={0}
                  max={100}
                  value={groupForm.discount_percent}
                  onChange={e => setGroupForm(p => ({ ...p, discount_percent: Number(e.target.value) }))}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>
              <div>
                <label className="block text-[11px] font-bold text-slate-600 mb-1">Deskripsi</label>
                <input
                  type="text"
                  value={groupForm.description}
                  onChange={e => setGroupForm(p => ({ ...p, description: e.target.value }))}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  placeholder="Deskripsi singkat (opsional)"
                />
              </div>
              <div className="flex items-center gap-3">
                <label className="text-[11px] font-bold text-slate-600">Aktif</label>
                <button
                  type="button"
                  onClick={() => setGroupForm(p => ({ ...p, is_active: !p.is_active }))}
                  className={`w-9 h-5 rounded-full transition-colors relative ${groupForm.is_active ? 'bg-primary-600' : 'bg-slate-300'}`}
                >
                  <span className={`absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform ${groupForm.is_active ? 'left-[18px]' : 'left-0.5'}`} />
                </button>
              </div>
            </div>
            <div className="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
              <button onClick={() => setShowGroupModal(false)} className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors">Batal</button>
              <button onClick={saveGroup} className="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                <Check className="w-3.5 h-3.5" />
                {editingGroup ? 'Simpan' : 'Tambah Grup'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
