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
  Check,
  History,
  DollarSign,
  ArrowDownCircle,
  ArrowUpCircle,
} from 'lucide-react';
import clsx from 'clsx';
import {
  Button,
  Card,
  Input,
  Select,
  SearchInput,
  StatCard,
  Tabs,
  Toggle,
  Modal,
  TableContainer,
  TableHeader,
  TableBase,
  TableRow,
  TableEmpty,
  StatusBadge,
} from '../components/ui';
import type { SelectOption, TabItem } from '../components/ui';

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

const TAB_ITEMS: TabItem[] = [
  { id: 'pelanggan', label: 'Pelanggan', icon: <Users className="w-3.5 h-3.5" /> },
  { id: 'grup', label: 'Grup Pelanggan', icon: <Tag className="w-3.5 h-3.5" /> },
];

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

  const groupOptions: SelectOption[] = [
    { value: 'all', label: 'Semua Grup' },
    ...customerGroups.filter(g => g.is_active).map(g => ({ value: g.id, label: g.name })),
  ];

  const typeOptions: SelectOption[] = [
    { value: 'all', label: 'Semua Tipe' },
    { value: 'member', label: 'Member' },
    { value: 'regular', label: 'Regular' },
  ];

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
      <Tabs
        tabs={TAB_ITEMS}
        activeTab={tab}
        onChange={(id) => setTab(id as Tab)}
        variant="segmented"
      />

      {/* ================== TAB: PELANGGAN ================== */}
      {tab === 'pelanggan' && (
        <>
          {/* Summary Cards */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <StatCard
              label="Total Pelanggan"
              value={customers.length}
              description={`${customers.filter(c => c.is_active).length} aktif`}
              icon={<Users className="w-4 h-4" />}
              color="info"
            />
            <StatCard
              label="Total Member"
              value={totalMembers}
              description="Pelanggan terdaftar member"
              icon={<Sparkles className="w-4 h-4" />}
              color="warning"
            />
            <StatCard
              label="Total Poin Beredar"
              value={totalPoints.toLocaleString('id-ID')}
              description="Poin seluruh member"
              icon={<Coins className="w-4 h-4" />}
              color="success"
            />
            <StatCard
              label="Piutang Pelanggan"
              value={fmtRp(totalCredit)}
              description="Tagihan tempo belum lunas"
              icon={<CreditCard className="w-4 h-4" />}
              color="danger"
            />
          </div>

          {/* Toolbar */}
          <Card className="p-4">
            <div className="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
              <div className="flex items-center gap-2 flex-wrap flex-1">
                <div className="flex-1 min-w-[200px]">
                  <SearchInput
                    value={search}
                    onChange={e => setSearch(e.target.value)}
                    placeholder="Cari nama, telepon, email, kode member..."
                  />
                </div>
                <Select
                  value={filterGroup}
                  onChange={e => setFilterGroup(e.target.value === 'all' ? 'all' : Number(e.target.value))}
                  options={groupOptions}
                  wrapperClassName="w-auto"
                />
                <Select
                  value={filterType}
                  onChange={e => setFilterType(e.target.value as typeof filterType)}
                  options={typeOptions}
                  wrapperClassName="w-auto"
                />
              </div>
              <Button onClick={openAddCustomer} icon={<UserPlus className="w-4 h-4" />}>
                Tambah Pelanggan
              </Button>
            </div>
          </Card>

          {/* Customer Table */}
          <TableContainer>
            <TableBase
              columns={[
                { header: 'Nama', className: 'px-5' },
                { header: 'Kontak' },
                { header: 'Grup' },
                { header: 'Tipe' },
                { header: 'Poin', align: 'right' },
                { header: 'Aktif', align: 'center' },
                { header: 'Aksi' },
              ]}
              emptyMessage="Belum ada pelanggan ditemukan"
              emptyIcon={<Users className="w-10 h-10 mx-auto text-slate-300" />}
              colSpan={7}
            >
              {filteredCustomers.map(c => (
                <TableRow key={c.id}>
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
                    <StatusBadge status={c.type}>
                      {c.type === 'member' ? 'MEMBER' : 'REGULAR'}
                    </StatusBadge>
                  </td>
                  <td className="px-4 py-3 text-right font-bold text-slate-800">
                    {c.type === 'member' ? c.points_balance.toLocaleString('id-ID') : '-'}
                  </td>
                  <td className="px-4 py-3 text-center">
                    <Toggle
                      checked={c.is_active}
                      onChange={() => toggleActive(c)}
                    />
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-1">
                      <Button variant="ghost" size="xs" onClick={() => openEditCustomer(c)} title="Edit">
                        <Pencil className="w-3.5 h-3.5" />
                      </Button>
                      <Button variant="ghost" size="xs" onClick={() => deleteCustomer(c.id)} title="Hapus" className="hover:!text-red-600 hover:!bg-red-50">
                        <Trash2 className="w-3.5 h-3.5" />
                      </Button>
                      {c.type === 'member' && (
                        <Button variant="ghost" size="xs" onClick={() => openPointModal(c)} title="Poin" className="hover:!text-emerald-600 hover:!bg-emerald-50">
                          <Coins className="w-3.5 h-3.5" />
                        </Button>
                      )}
                      <Button variant="ghost" size="xs" onClick={() => openTxModal(c)} title="Transaksi" className="hover:!text-blue-600 hover:!bg-blue-50">
                        <History className="w-3.5 h-3.5" />
                      </Button>
                    </div>
                  </td>
                </TableRow>
              ))}
              {filteredCustomers.length === 0 && (
                <TableEmpty
                  colSpan={7}
                  message="Belum ada pelanggan ditemukan. Klik 'Tambah Pelanggan' untuk menambah data baru."
                  icon={<Users className="w-10 h-10 mx-auto mb-2 text-slate-300" />}
                />
              )}
            </TableBase>
          </TableContainer>
        </>
      )}

      {/* ================== TAB: GRUP PELANGGAN ================== */}
      {tab === 'grup' && (
        <>
          <div className="flex justify-end">
            <Button onClick={openAddGroup} icon={<Plus className="w-4 h-4" />}>
              Tambah Grup
            </Button>
          </div>

          <TableContainer>
            <TableBase
              columns={[
                { header: 'Nama Grup', className: 'px-5' },
                { header: 'Diskon %', align: 'center' },
                { header: 'Deskripsi' },
                { header: 'Jumlah Anggota', align: 'center' },
                { header: 'Aksi' },
              ]}
              emptyMessage="Belum ada grup pelanggan"
              emptyIcon={<Tag className="w-10 h-10 mx-auto text-slate-300" />}
              colSpan={5}
            >
              {customerGroups.map(g => (
                <TableRow key={g.id}>
                  <td className="px-5 py-3 font-bold text-slate-800">{g.name}</td>
                  <td className="px-4 py-3 text-center font-bold text-primary-700">{g.discount_percent}%</td>
                  <td className="px-4 py-3 text-slate-600">{g.description || <span className="text-slate-400">-</span>}</td>
                  <td className="px-4 py-3 text-center">
                    <span className="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold">{memberCount(g.id)}</span>
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-1">
                      <Button variant="ghost" size="xs" onClick={() => openEditGroup(g)} title="Edit">
                        <Pencil className="w-3.5 h-3.5" />
                      </Button>
                      <Button variant="ghost" size="xs" onClick={() => deleteGroup(g.id)} title="Hapus" className="hover:!text-red-600 hover:!bg-red-50">
                        <Trash2 className="w-3.5 h-3.5" />
                      </Button>
                    </div>
                  </td>
                </TableRow>
              ))}
              {customerGroups.length === 0 && (
                <TableEmpty
                  colSpan={5}
                  message="Belum ada grup pelanggan. Klik 'Tambah Grup' untuk membuat grup baru."
                  icon={<Tag className="w-10 h-10 mx-auto mb-2 text-slate-300" />}
                />
              )}
            </TableBase>
          </TableContainer>
        </>
      )}

      {/* ================== MODAL: ADD/EDIT CUSTOMER ================== */}
      <Modal
        open={showCustomerModal}
        onClose={() => setShowCustomerModal(false)}
        title={editingCustomer ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru'}
        footer={
          <>
            <Button variant="secondary" onClick={() => setShowCustomerModal(false)}>Batal</Button>
            <Button onClick={saveCustomer} icon={<Check className="w-3.5 h-3.5" />}>
              {editingCustomer ? 'Simpan Perubahan' : 'Tambah Pelanggan'}
            </Button>
          </>
        }
      >
        <div className="space-y-4">
          <Input
            label="Nama Lengkap"
            required
            value={customerForm.name}
            onChange={e => setCustomerForm(p => ({ ...p, name: e.target.value }))}
            placeholder="Nama pelanggan"
          />
          <div className="grid grid-cols-2 gap-4">
            <Input
              label="Telepon"
              value={customerForm.phone}
              onChange={e => setCustomerForm(p => ({ ...p, phone: e.target.value }))}
              placeholder="08xxx"
            />
            <Input
              label="Email"
              type="email"
              value={customerForm.email}
              onChange={e => setCustomerForm(p => ({ ...p, email: e.target.value }))}
              placeholder="email@contoh.com"
            />
          </div>
          <div className="grid grid-cols-2 gap-4">
            <Select
              label="Tipe Pelanggan"
              required
              value={customerForm.type}
              onChange={e => setCustomerForm(p => ({ ...p, type: e.target.value as 'regular' | 'member' }))}
              options={[
                { value: 'regular', label: 'Regular' },
                { value: 'member', label: 'Member' },
              ]}
            />
            <Select
              label="Grup Pelanggan"
              value={customerForm.customer_group_id || ''}
              onChange={e => setCustomerForm(p => ({ ...p, customer_group_id: e.target.value ? Number(e.target.value) : undefined }))}
              placeholder="Tanpa Grup"
              options={customerGroups.filter(g => g.is_active).map(g => ({
                value: g.id,
                label: `${g.name} (${g.discount_percent}% diskon)`,
              }))}
            />
          </div>
          {customerForm.type === 'member' && (
            <div>
              <Input
                label="Kode Member"
                value={editingCustomer ? customerForm.member_code : genMemberCode()}
                readOnly
                className="!bg-amber-50 !border-amber-200 !font-mono !font-bold !text-amber-700"
              />
              <p className="text-[10px] text-slate-400 mt-1">Kode member digenerate otomatis.</p>
            </div>
          )}
        </div>
      </Modal>

      {/* ================== MODAL: POINTS ================== */}
      <Modal
        open={showPointModal}
        onClose={() => setShowPointModal(false)}
        title="Poin Pelanggan"
        subtitle={pointCustomer ? `${pointCustomer.name} ${pointCustomer.member_code ? `(${pointCustomer.member_code})` : ''}` : undefined}
        size="lg"
      >
        {pointCustomer && (
          <div className="space-y-4">
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
                <Input
                  label="Jumlah Poin"
                  type="number"
                  min={1}
                  max={pointCustomer.points_balance}
                  value={redeemPoints}
                  onChange={e => setRedeemPoints(e.target.value)}
                  placeholder="0"
                />
                <Input
                  label="Catatan"
                  value={redeemNotes}
                  onChange={e => setRedeemNotes(e.target.value)}
                  placeholder="Opsional"
                />
              </div>
              <Button
                onClick={handleRedeem}
                disabled={!redeemPoints || parseInt(redeemPoints, 10) <= 0}
                icon={<ArrowDownCircle className="w-3.5 h-3.5" />}
                variant="primary"
              >
                Tukarkan Poin
              </Button>
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
                        <div className={clsx('text-xs font-bold', l.points > 0 ? 'text-emerald-600' : 'text-red-600')}>
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
        )}
      </Modal>

      {/* ================== MODAL: TRANSAKSI & PIUTANG ================== */}
      <Modal
        open={showTxModal}
        onClose={() => setShowTxModal(false)}
        title="Transaksi Pelanggan"
        subtitle={txCustomer?.name}
        size="xl"
      >
        {txCustomer && (
          <div className="space-y-4">
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
                          <Input
                            type="number"
                            min={1}
                            max={unpaidAmt}
                            value={payAmount}
                            onChange={e => setPayAmount(e.target.value)}
                            placeholder="Jumlah bayar"
                            className="!w-32"
                          />
                          <Select
                            value={payMethod}
                            onChange={e => setPayMethod(e.target.value as typeof payMethod)}
                            options={[
                              { value: 'cash', label: 'Tunai' },
                              { value: 'transfer', label: 'Transfer' },
                              { value: 'qris', label: 'QRIS' },
                            ]}
                            wrapperClassName="!w-auto"
                          />
                          <Button
                            variant="success"
                            size="xs"
                            onClick={() => { handlePayCredit(); setPaySaleId(null); }}
                          >
                            Bayar
                          </Button>
                          <Button
                            variant="secondary"
                            size="xs"
                            onClick={() => setPaySaleId(null)}
                          >
                            Batal
                          </Button>
                        </div>
                      ) : (
                        <Button
                          size="xs"
                          onClick={() => { setPaySaleId(s.id); setPayAmount(unpaidAmt.toString()); }}
                          icon={<DollarSign className="w-3 h-3" />}
                        >
                          Bayar Piutang
                        </Button>
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
                <TableContainer>
                  <TableBase
                    columns={[
                      { header: 'No. Nota' },
                      { header: 'Tanggal' },
                      { header: 'Total', align: 'right' },
                      { header: 'Bayar', align: 'right' },
                      { header: 'Metode' },
                      { header: 'Status' },
                    ]}
                  >
                    {customerSales.slice(0, 20).map(s => (
                      <TableRow key={s.id}>
                        <td className="px-4 py-2 font-mono font-bold text-primary-700">{s.sale_code}</td>
                        <td className="px-4 py-2">{new Date(s.sale_at).toLocaleDateString('id-ID')}</td>
                        <td className="px-4 py-2 text-right font-bold">{fmtRp(s.grand_total)}</td>
                        <td className="px-4 py-2 text-right text-slate-600">{fmtRp(s.paid_amount || 0)}</td>
                        <td className="px-4 py-2 uppercase font-semibold text-slate-600">{s.payment_method}</td>
                        <td className="px-4 py-2">
                          <StatusBadge status={s.credit_status || 'paid'}>
                            {(s.credit_status || 'paid').toUpperCase()}
                          </StatusBadge>
                        </td>
                      </TableRow>
                    ))}
                  </TableBase>
                </TableContainer>
              )}
            </div>
          </div>
        )}
      </Modal>

      {/* ================== MODAL: ADD/EDIT GROUP ================== */}
      <Modal
        open={showGroupModal}
        onClose={() => setShowGroupModal(false)}
        title={editingGroup ? 'Edit Grup' : 'Tambah Grup Baru'}
        size="md"
        footer={
          <>
            <Button variant="secondary" onClick={() => setShowGroupModal(false)}>Batal</Button>
            <Button onClick={saveGroup} icon={<Check className="w-3.5 h-3.5" />}>
              {editingGroup ? 'Simpan' : 'Tambah Grup'}
            </Button>
          </>
        }
      >
        <div className="space-y-4">
          <Input
            label="Nama Grup"
            required
            value={groupForm.name}
            onChange={e => setGroupForm(p => ({ ...p, name: e.target.value }))}
            placeholder="Contoh: VIP, Reseller"
          />
          <Input
            label="Diskon (%)"
            type="number"
            min={0}
            max={100}
            value={groupForm.discount_percent}
            onChange={e => setGroupForm(p => ({ ...p, discount_percent: Number(e.target.value) }))}
          />
          <Input
            label="Deskripsi"
            value={groupForm.description}
            onChange={e => setGroupForm(p => ({ ...p, description: e.target.value }))}
            placeholder="Deskripsi singkat (opsional)"
          />
          <div className="flex items-center gap-3">
            <Toggle
              checked={groupForm.is_active}
              onChange={() => setGroupForm(p => ({ ...p, is_active: !p.is_active }))}
              label="Aktif"
            />
          </div>
        </div>
      </Modal>
    </div>
  );
};
