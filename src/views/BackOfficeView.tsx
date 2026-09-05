import React, { useState } from 'react';
import { useApp } from '../context/AppContext';
import clsx from 'clsx';
import {
  Building2,
  CircleDollarSign,
  Wallet,
  ArrowDownCircle,
  ArrowUpCircle,
  ArrowLeftRight,
  Users,
  PackageMinus,
  PackagePlus,
  Tag,
  Clock,
  TrendingUp,
  TrendingDown,
  Banknote,
  Boxes,
  Wrench,
} from 'lucide-react';
import {
  Button,
  Card,
  CardHeader,
  Modal,
  Badge,
  Select,
  Input,
  StatCard,
  Tabs,
  TableContainer,
  TableHeader,
  TableBase,
  TableRow,
  TableEmpty,
} from '../components/ui';
import type { SelectOption } from '../components/ui';

type TabKey = 'kas' | 'biaya' | 'kasbon' | 'koreksi' | 'pemakaian';

interface ModalState {
  kind: 'income' | 'expense' | 'mutation' | 'kasbon' | 'koreksi' | 'pemakaian' | 'biaya' | null;
}

const fmt = (n: number) => 'Rp ' + n.toLocaleString('id-ID');

const typeBadge: Record<string, { label: string; variant: 'success' | 'danger' | 'info' | 'warning' }> = {
  income: { label: 'Pemasukan', variant: 'success' },
  expense: { label: 'Pengeluaran', variant: 'danger' },
  mutation: { label: 'Mutasi', variant: 'info' },
  employee_advance: { label: 'Kasbon', variant: 'warning' },
};

const accountTypeLabel: Record<string, string> = {
  cash: 'Kas',
  bank: 'Bank',
  ewallet: 'E-Wallet',
};

export const BackOfficeView: React.FC = () => {
  const {
    cashAccounts,
    setCashAccounts,
    costCategories,
    setCostCategories,
    cashTransactions,
    addCashTransaction,
    addCashMutation,
    employeeAdvances,
    addEmployeeAdvance,
    stockDocuments,
    addStockDocument,
    products,
    locations,
    currentUser,
  } = useApp();

  const [activeTab, setActiveTab] = useState<TabKey>('kas');
  const [modal, setModal] = useState<ModalState>({ kind: null });

  const totalSaldo = cashAccounts.reduce((s, a) => s + a.current_balance, 0);
  const totalIncome = cashTransactions.filter(t => t.transaction_type === 'income').reduce((s, t) => s + t.amount, 0);
  const totalExpense = cashTransactions.filter(t => t.transaction_type === 'expense').reduce((s, t) => s + t.amount, 0);
  const totalAdvanceOpen = employeeAdvances.filter(a => a.status === 'open').reduce((s, a) => s + a.amount, 0);

  const close = () => setModal({ kind: null });

  const tabs: { id: TabKey; label: string; icon: React.ReactNode }[] = [
    { id: 'kas', label: 'Kas', icon: <Wallet className="w-3.5 h-3.5" /> },
    { id: 'biaya', label: 'Biaya', icon: <Tag className="w-3.5 h-3.5" /> },
    { id: 'kasbon', label: 'Kasbon Karyawan', icon: <Users className="w-3.5 h-3.5" /> },
    { id: 'koreksi', label: 'Koreksi Stok', icon: <PackagePlus className="w-3.5 h-3.5" /> },
    { id: 'pemakaian', label: 'Pemakaian Barang', icon: <PackageMinus className="w-3.5 h-3.5" /> },
  ];

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Page Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-slate-900 tracking-tight">Back Office & Kas</h1>
          <p className="text-xs text-slate-500 mt-1">
            Kelola kas, biaya, kasbon karyawan, dan dokumen stok internal.
          </p>
        </div>
        <div className="flex items-center gap-2">
          <span className="px-3 py-1.5 bg-slate-100 border border-slate-200 rounded-xl text-[11px] font-bold text-slate-600 flex items-center gap-1.5">
            <Building2 className="w-3.5 h-3.5 text-primary-600" />
            {currentUser.name}
          </span>
        </div>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          value={fmt(totalSaldo)}
          label="Total Saldo Kas"
          description={`${cashAccounts.length} akun kas aktif`}
          icon={<CircleDollarSign className="w-4 h-4" />}
          color="primary"
        />
        <StatCard
          value={fmt(totalIncome)}
          label="Total Pemasukan"
          description="Transaksi pemasukan kas"
          icon={<TrendingUp className="w-4 h-4" />}
          color="success"
        />
        <StatCard
          value={fmt(totalExpense)}
          label="Total Pengeluaran"
          description="Transaksi pengeluaran kas"
          icon={<TrendingDown className="w-4 h-4" />}
          color="danger"
        />
        <StatCard
          value={fmt(totalAdvanceOpen)}
          label="Kasbon Terbuka"
          description={`${employeeAdvances.filter(a => a.status === 'open').length} kasbon belum lunas`}
          icon={<Banknote className="w-4 h-4" />}
          color="warning"
        />
      </div>

      {/* Tab Bar */}
      <Tabs
        tabs={tabs}
        activeTab={activeTab}
        onChange={tabId => setActiveTab(tabId as TabKey)}
        variant="segmented"
      />

      {/* Tab Content */}
      {activeTab === 'kas' && (
        <KasTab
          cashAccounts={cashAccounts}
          cashTransactions={cashTransactions}
          onOpen={k => setModal({ kind: k })}
        />
      )}
      {activeTab === 'biaya' && (
        <BiayaTab costCategories={costCategories} onAdd={() => setModal({ kind: 'biaya' })} />
      )}
      {activeTab === 'kasbon' && (
        <KasbonTab employeeAdvances={employeeAdvances} onAdd={() => setModal({ kind: 'kasbon' })} />
      )}
      {activeTab === 'koreksi' && (
        <StockTab
          docs={stockDocuments.filter(d => d.document_type === 'correction')}
          title="Koreksi Stok"
          icon={<PackagePlus className="w-4 h-4" />}
          onAdd={() => setModal({ kind: 'koreksi' })}
        />
      )}
      {activeTab === 'pemakaian' && (
        <StockTab
          docs={stockDocuments.filter(d => d.document_type === 'usage')}
          title="Pemakaian Barang"
          icon={<PackageMinus className="w-4 h-4" />}
          onAdd={() => setModal({ kind: 'pemakaian' })}
        />
      )}

      {/* Modals */}
      <Modal
        open={modal.kind !== null}
        onClose={close}
        title={modal.kind ? modalTitle(modal.kind) : ''}
        size="md"
      >
        {modal.kind === 'income' || modal.kind === 'expense' ? (
          <CashTxForm
            type={modal.kind}
            cashAccounts={cashAccounts}
            costCategories={costCategories.filter(c => c.type === modal.kind)}
            onSubmit={p => { addCashTransaction(p); close(); }}
          />
        ) : modal.kind === 'mutation' ? (
          <MutationForm cashAccounts={cashAccounts} onSubmit={p => { addCashMutation(p); close(); }} />
        ) : modal.kind === 'kasbon' ? (
          <KasbonForm
            cashAccounts={cashAccounts}
            currentUserId={currentUser.id}
            currentUserName={currentUser.name}
            onSubmit={p => { addEmployeeAdvance(p); close(); }}
          />
        ) : modal.kind === 'koreksi' || modal.kind === 'pemakaian' ? (
          <StockForm
            isUsage={modal.kind === 'pemakaian'}
            products={products}
            locations={locations}
            onSubmit={p => { addStockDocument(p); close(); }}
          />
        ) : (
          <BiayaForm onSubmit={p => { setCostCategories(p); close(); }} />
        )}
      </Modal>
    </div>
  );
};

function modalTitle(kind: NonNullable<ModalState['kind']>): string {
  switch (kind) {
    case 'income': return 'Tambah Pemasukan';
    case 'expense': return 'Tambah Pengeluaran';
    case 'mutation': return 'Mutasi Antar Akun';
    case 'kasbon': return 'Kasbon Karyawan';
    case 'koreksi': return 'Koreksi Stok';
    case 'pemakaian': return 'Pemakaian Barang';
    case 'biaya': return 'Tambah Kategori Biaya';
  }
}

function KasTab({ cashAccounts, cashTransactions, onOpen }: {
  cashAccounts: ReturnType<typeof useApp>['cashAccounts'];
  cashTransactions: ReturnType<typeof useApp>['cashTransactions'];
  onOpen: (k: 'income' | 'expense' | 'mutation') => void;
}) {
  const active = cashAccounts.filter(a => a.is_active);
  const rows = [...cashTransactions].sort((a, b) => (b.transaction_date || '').localeCompare(a.transaction_date || ''));

  const txColumns = [
    { header: 'Kode' },
    { header: 'Tanggal' },
    { header: 'Tipe' },
    { header: 'Akun' },
    { header: 'Deskripsi' },
    { header: 'Jumlah', align: 'right' as const },
  ];

  return (
    <div className="space-y-6">
      {/* Account cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {active.length === 0 && (
          <div className="col-span-full text-center text-slate-400 py-8 border border-dashed border-slate-300 rounded-2xl">
            Belum ada akun kas aktif.
          </div>
        )}
        {active.map(a => (
          <Card key={a.id}>
            <div className="flex items-center justify-between">
              <div>
                <div className="text-xs font-black text-slate-900">{a.name}</div>
                <div className="text-[10px] font-mono text-slate-400 mt-0.5">{a.code}</div>
              </div>
              <Badge variant="neutral">
                {accountTypeLabel[a.type] || a.type}
              </Badge>
            </div>
            <div className="mt-4">
              <div className="text-2xl font-black text-primary-700">{fmt(a.current_balance)}</div>
              <div className="text-[10px] text-slate-400 mt-0.5">Saldo awal: {fmt(a.opening_balance)}</div>
            </div>
          </Card>
        ))}
      </div>

      {/* Action buttons */}
      <div className="flex flex-wrap gap-2">
        <Button variant="primary" icon={<ArrowDownCircle className="w-4 h-4" />} onClick={() => onOpen('income')}>
          + Pemasukan
        </Button>
        <Button variant="primary" icon={<ArrowUpCircle className="w-4 h-4" />} onClick={() => onOpen('expense')}>
          + Pengeluaran
        </Button>
        <Button variant="primary" icon={<ArrowLeftRight className="w-4 h-4" />} onClick={() => onOpen('mutation')}>
          + Mutasi Antar Akun
        </Button>
      </div>

      {/* Transactions */}
      <TableContainer>
        <TableHeader icon={<Clock className="w-4 h-4 text-primary-600" />} count={rows.length}>
          Transaksi Kas Terkini
        </TableHeader>
        <TableBase columns={txColumns} colSpan={txColumns.length}>
          {rows.length === 0 ? (
            <TableEmpty colSpan={txColumns.length} message="Belum ada transaksi kas." />
          ) : (
            rows.slice(0, 15).map(t => {
              const badge = typeBadge[t.transaction_type] || { label: t.transaction_type, variant: 'neutral' as const };
              const isIn = t.transaction_type === 'income' || t.transaction_type === 'mutation';
              return (
                <TableRow key={t.id}>
                  <td className="px-5 py-3 font-mono font-bold text-primary-700">{t.transaction_code}</td>
                  <td className="px-4 py-3">{t.transaction_date}</td>
                  <td className="px-4 py-3">
                    <Badge variant={badge.variant}>{badge.label}</Badge>
                  </td>
                  <td className="px-4 py-3">
                    {t.cash_account?.name || (t.transaction_type === 'mutation' && t.target_cash_account?.name ? `${t.cash_account?.name} → ${t.target_cash_account?.name}` : '')}
                  </td>
                  <td className="px-4 py-3 max-w-[220px] truncate">{t.description || t.cost_category?.name || '-'}</td>
                  <td className={clsx(
                    'px-4 py-3 text-right font-bold',
                    t.transaction_type === 'expense' ? 'text-red-600' : isIn ? 'text-emerald-600' : 'text-slate-700',
                  )}>
                    {t.transaction_type === 'expense' ? '-' : ''}{fmt(t.amount)}
                  </td>
                </TableRow>
              );
            })
          )}
        </TableBase>
      </TableContainer>
    </div>
  );
}

function BiayaTab({ costCategories, onAdd }: {
  costCategories: ReturnType<typeof useApp>['costCategories'];
  onAdd: () => void;
}) {
  return (
    <Card noPadding>
      <CardHeader>
        <div className="flex items-center justify-between w-full">
          <div className="flex items-center gap-2">
            <Tag className="w-4 h-4 text-primary-600" />
            <span className="font-bold text-slate-800 text-sm">Kategori Biaya</span>
          </div>
          <Button variant="primary" size="sm" icon={<Tag className="w-4 h-4" />} onClick={onAdd}>
            Tambah
          </Button>
        </div>
      </CardHeader>
      <div className="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        {costCategories.map(c => (
          <div key={c.id} className="p-3.5 rounded-xl bg-slate-50 border border-slate-200/90 flex items-center justify-between">
            <div>
              <div className="font-bold text-xs text-slate-800">{c.name}</div>
              <div className="text-[10px] font-mono text-slate-400 mt-0.5">{c.code}</div>
            </div>
            <Badge variant={c.type === 'income' ? 'success' : 'danger'}>
              {c.type}
            </Badge>
          </div>
        ))}
        {costCategories.length === 0 && (
          <div className="col-span-full text-center text-slate-400 py-8 border border-dashed border-slate-300 rounded-xl">
            Belum ada kategori biaya.
          </div>
        )}
      </div>
    </Card>
  );
}

function KasbonTab({ employeeAdvances, onAdd }: {
  employeeAdvances: ReturnType<typeof useApp>['employeeAdvances'];
  onAdd: () => void;
}) {
  const columns = [
    { header: 'Kode' },
    { header: 'Tanggal' },
    { header: 'Karyawan' },
    { header: 'Akun' },
    { header: 'Jumlah', align: 'right' as const },
    { header: 'Status' },
  ];

  return (
    <TableContainer>
      <TableHeader
        icon={<Users className="w-4 h-4 text-primary-600" />}
        count={employeeAdvances.length}
        extra={
          <Button variant="primary" size="sm" icon={<Tag className="w-4 h-4" />} onClick={onAdd}>
            + Kasbon
          </Button>
        }
      >
        Kasbon Karyawan
      </TableHeader>
      <TableBase columns={columns} colSpan={columns.length}>
        {employeeAdvances.length === 0 ? (
          <TableEmpty colSpan={columns.length} message="Belum ada kasbon karyawan." />
        ) : (
          employeeAdvances.map(a => (
            <TableRow key={a.id}>
              <td className="px-5 py-3 font-mono font-bold text-primary-700">{a.advance_code}</td>
              <td className="px-4 py-3">{a.advance_date}</td>
              <td className="px-4 py-3 font-semibold text-slate-800">{a.employee_name}</td>
              <td className="px-4 py-3">{a.cash_account_name}</td>
              <td className="px-4 py-3 text-right font-bold text-slate-900">{fmt(a.amount)}</td>
              <td className="px-4 py-3">
                <Badge variant={a.status === 'open' ? 'warning' : 'success'}>
                  {a.status.toUpperCase()}
                </Badge>
              </td>
            </TableRow>
          ))
        )}
      </TableBase>
    </TableContainer>
  );
}

function StockTab({ docs, title, icon, onAdd }: {
  docs: ReturnType<typeof useApp>['stockDocuments'];
  title: string;
  icon: React.ReactNode;
  onAdd: () => void;
}) {
  const rows = [...docs].sort((a, b) => (b.document_date || '').localeCompare(a.document_date || ''));

  const columns = [
    { header: 'Kode' },
    { header: 'Tanggal' },
    { header: 'Produk' },
    { header: 'Lokasi' },
    { header: 'Gerakan' },
    { header: 'Qty', align: 'right' as const },
    { header: 'Deskripsi' },
  ];

  return (
    <TableContainer>
      <TableHeader
        icon={icon}
        count={rows.length}
        extra={
          <Button variant="primary" size="sm" icon={<Tag className="w-4 h-4" />} onClick={onAdd}>
            + Tambah
          </Button>
        }
      >
        {title}
      </TableHeader>
      <TableBase columns={columns} colSpan={columns.length}>
        {rows.length === 0 ? (
          <TableEmpty
            colSpan={columns.length}
            message={`Belum ada dokumen stok ${title.toLowerCase()}.`}
          />
        ) : (
          rows.map(d => (
            <TableRow key={d.id}>
              <td className="px-5 py-3 font-mono font-bold text-primary-700">{d.document_code}</td>
              <td className="px-4 py-3">{d.document_date}</td>
              <td className="px-4 py-3 font-semibold text-slate-800">{d.product?.name || d.product_id}</td>
              <td className="px-4 py-3">{d.location?.name || d.location_id}</td>
              <td className="px-4 py-3">
                <Badge variant="neutral">
                  {d.movement_type}
                </Badge>
              </td>
              <td className={clsx(
                'px-4 py-3 text-right font-bold',
                d.movement_type === 'adjustment_plus' ? 'text-emerald-600' : 'text-red-600',
              )}>
                {d.movement_type === 'adjustment_plus' ? '+' : '-'}{d.quantity}
              </td>
              <td className="px-4 py-3 max-w-[200px] truncate">{d.description || d.created_by_name}</td>
            </TableRow>
          ))
        )}
      </TableBase>
    </TableContainer>
  );
}

/* ---------------- Modals (forms) ---------------- */

function CashTxForm({ type, cashAccounts, costCategories, onSubmit }: {
  type: 'income' | 'expense';
  cashAccounts: ReturnType<typeof useApp>['cashAccounts'];
  costCategories: ReturnType<typeof useApp>['costCategories'];
  onSubmit: (p: { type: 'income' | 'expense'; cashAccountId: number; amount: number; costCategoryId?: number; description?: string; reference?: string }) => void;
}) {
  const [accountId, setAccountId] = useState<number>(cashAccounts[0]?.id || 0);
  const [amount, setAmount] = useState<string>('');
  const [categoryId, setCategoryId] = useState<string>('');
  const [description, setDescription] = useState('');
  const [reference, setReference] = useState('');

  const accountOpts: SelectOption[] = cashAccounts.filter(a => a.is_active).map(a => ({
    value: a.id,
    label: `${a.name} (${fmt(a.current_balance)})`,
  }));

  const categoryOpts: SelectOption[] = [
    { value: '', label: '— Tanpa kategori —' },
    ...costCategories.map(c => ({ value: c.id, label: c.name })),
  ];

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    const amt = Number(amount);
    if (!accountId || !amt || amt <= 0) return;
    onSubmit({
      type,
      cashAccountId: accountId,
      amount: amt,
      costCategoryId: categoryId ? Number(categoryId) : undefined,
      description: description || undefined,
      reference: reference || undefined,
    });
  };

  return (
    <form onSubmit={submit} className="space-y-4">
      <Select
        label="Akun Kas"
        options={accountOpts}
        value={accountId}
        onChange={e => setAccountId(Number(e.target.value))}
      />
      <Input
        label={`Jumlah (${type === 'income' ? 'Pemasukan' : 'Pengeluaran'})`}
        type="number"
        min={0}
        value={amount}
        onChange={e => setAmount(e.target.value)}
        placeholder="0"
        required
      />
      {costCategories.length > 0 && (
        <Select
          label="Kategori"
          options={categoryOpts}
          value={categoryId}
          onChange={e => setCategoryId(e.target.value)}
        />
      )}
      <Input
        label="Deskripsi"
        value={description}
        onChange={e => setDescription(e.target.value)}
        placeholder="Keterangan"
      />
      <Input
        label="Referensi"
        value={reference}
        onChange={e => setReference(e.target.value)}
        placeholder="No. referensi (opsional)"
      />
      <div className="flex justify-end gap-2 pt-2">
        <Button
          type="submit"
          variant="primary"
          icon={type === 'income' ? <ArrowDownCircle className="w-4 h-4" /> : <ArrowUpCircle className="w-4 h-4" />}
        >
          Simpan {type === 'income' ? 'Pemasukan' : 'Pengeluaran'}
        </Button>
      </div>
    </form>
  );
}

function MutationForm({ cashAccounts, onSubmit }: {
  cashAccounts: ReturnType<typeof useApp>['cashAccounts'];
  onSubmit: (p: { sourceAccountId: number; targetAccountId: number; amount: number; description?: string; reference?: string }) => void;
}) {
  const active = cashAccounts.filter(a => a.is_active);
  const [sourceId, setSourceId] = useState<number>(active[0]?.id || 0);
  const [targetId, setTargetId] = useState<number>(active[1]?.id || active[0]?.id || 0);
  const [amount, setAmount] = useState<string>('');
  const [description, setDescription] = useState('');

  const accountOpts: SelectOption[] = active.map(a => ({
    value: a.id,
    label: `${a.name} (${fmt(a.current_balance)})`,
  }));

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    const amt = Number(amount);
    if (!sourceId || !targetId || sourceId === targetId || !amt || amt <= 0) return;
    onSubmit({ sourceAccountId: sourceId, targetAccountId: targetId, amount: amt, description: description || undefined });
  };

  return (
    <form onSubmit={submit} className="space-y-4">
      <Select
        label="Akun Sumber"
        options={accountOpts}
        value={sourceId}
        onChange={e => setSourceId(Number(e.target.value))}
      />
      <Select
        label="Akun Tujuan"
        options={accountOpts}
        value={targetId}
        onChange={e => setTargetId(Number(e.target.value))}
      />
      {sourceId === targetId && (
        <p className="text-[11px] font-semibold text-red-600">Akun sumber dan tujuan harus berbeda.</p>
      )}
      <Input
        label="Jumlah"
        type="number"
        min={0}
        value={amount}
        onChange={e => setAmount(e.target.value)}
        placeholder="0"
        required
      />
      <Input
        label="Deskripsi"
        value={description}
        onChange={e => setDescription(e.target.value)}
        placeholder="Keterangan mutasi"
      />
      <div className="flex justify-end gap-2 pt-2">
        <Button
          type="submit"
          variant="primary"
          icon={<ArrowLeftRight className="w-4 h-4" />}
          disabled={sourceId === targetId}
        >
          Simpan Mutasi
        </Button>
      </div>
    </form>
  );
}

function KasbonForm({ cashAccounts, currentUserId, currentUserName, onSubmit }: {
  cashAccounts: ReturnType<typeof useApp>['cashAccounts'];
  currentUserId: number;
  currentUserName: string;
  onSubmit: (p: { employeeId: number; cashAccountId: number; amount: number; description?: string }) => void;
}) {
  const [accountId, setAccountId] = useState<number>(cashAccounts[0]?.id || 0);
  const [amount, setAmount] = useState<string>('');
  const [description, setDescription] = useState('');

  const accountOpts: SelectOption[] = cashAccounts.filter(a => a.is_active).map(a => ({
    value: a.id,
    label: `${a.name} (${fmt(a.current_balance)})`,
  }));

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    const amt = Number(amount);
    if (!accountId || !amt || amt <= 0) return;
    onSubmit({ employeeId: currentUserId, cashAccountId: accountId, amount: amt, description: description || undefined });
  };

  return (
    <form onSubmit={submit} className="space-y-4">
      <Input
        label="Karyawan (kasir saat ini)"
        value={currentUserName}
        disabled
      />
      <Select
        label="Akun Kas"
        options={accountOpts}
        value={accountId}
        onChange={e => setAccountId(Number(e.target.value))}
      />
      <Input
        label="Jumlah Kasbon"
        type="number"
        min={0}
        value={amount}
        onChange={e => setAmount(e.target.value)}
        placeholder="0"
        required
      />
      <Input
        label="Deskripsi"
        value={description}
        onChange={e => setDescription(e.target.value)}
        placeholder="Alasan kasbon"
      />
      <div className="flex justify-end gap-2 pt-2">
        <Button type="submit" variant="primary" icon={<Users className="w-4 h-4" />}>
          Simpan Kasbon
        </Button>
      </div>
    </form>
  );
}

function StockForm({ isUsage, products, locations, onSubmit }: {
  isUsage: boolean;
  products: ReturnType<typeof useApp>['products'];
  locations: ReturnType<typeof useApp>['locations'];
  onSubmit: (p: { documentType: 'correction' | 'usage'; movementType: string; productId: number; locationId: number; quantity: number; description?: string }) => void;
}) {
  const [productId, setProductId] = useState<number>(products[0]?.id || 0);
  const [locationId, setLocationId] = useState<number>(locations[0]?.id || 0);
  const [movementType, setMovementType] = useState<string>(isUsage ? 'adjustment_minus' : 'adjustment_plus');
  const [quantity, setQuantity] = useState<string>('');
  const [description, setDescription] = useState('');

  const productOpts: SelectOption[] = products.map(p => ({
    value: p.id,
    label: `${p.name} (Stok: ${p.stock_global || 0})`,
  }));

  const locationOpts: SelectOption[] = locations.map(l => ({
    value: l.id,
    label: l.name,
  }));

  const correctionTypeOpts: SelectOption[] = [
    { value: 'adjustment_plus', label: 'Penambahan Stok (+)' },
    { value: 'adjustment_minus', label: 'Pengurangan Stok (-)' },
  ];

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    const qty = Number(quantity);
    if (!productId || !locationId || !qty || qty <= 0) return;
    onSubmit({
      documentType: isUsage ? 'usage' : 'correction',
      movementType,
      productId,
      locationId,
      quantity: qty,
      description: description || undefined,
    });
  };

  return (
    <form onSubmit={submit} className="space-y-4">
      <Select
        label="Produk"
        options={productOpts}
        value={productId}
        onChange={e => setProductId(Number(e.target.value))}
      />
      <Select
        label="Lokasi"
        options={locationOpts}
        value={locationId}
        onChange={e => setLocationId(Number(e.target.value))}
      />
      {!isUsage && (
        <Select
          label="Jenis Koreksi"
          options={correctionTypeOpts}
          value={movementType}
          onChange={e => setMovementType(e.target.value)}
        />
      )}
      <Input
        label="Jumlah"
        type="number"
        min={0}
        value={quantity}
        onChange={e => setQuantity(e.target.value)}
        placeholder="0"
        required
      />
      <Input
        label="Deskripsi"
        value={description}
        onChange={e => setDescription(e.target.value)}
        placeholder="Keterangan"
      />
      <div className="flex justify-end gap-2 pt-2">
        <Button type="submit" variant="primary" icon={isUsage ? <Wrench className="w-4 h-4" /> : <Boxes className="w-4 h-4" />}>
          Simpan
        </Button>
      </div>
    </form>
  );
}

function BiayaForm({ onSubmit }: {
  onSubmit: (p: ReturnType<typeof useApp>['costCategories']) => void;
}) {
  const { costCategories } = useApp();
  const [name, setName] = useState('');
  const [type, setType] = useState<'income' | 'expense'>('expense');

  const typeOpts: SelectOption[] = [
    { value: 'expense', label: 'Pengeluaran' },
    { value: 'income', label: 'Pemasukan' },
  ];

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim()) return;
    const maxId = costCategories.reduce((m, c) => Math.max(m, c.id), 0);
    const code = (type === 'income' ? 'INC' : 'EXP') + '-' + (maxId + 1);
    const next: ReturnType<typeof useApp>['costCategories'] = [
      { id: maxId + 1, code, name: name.trim(), type, is_active: true },
      ...costCategories,
    ];
    onSubmit(next);
  };

  return (
    <form onSubmit={submit} className="space-y-4">
      <Input
        label="Nama Kategori"
        value={name}
        onChange={e => setName(e.target.value)}
        placeholder="contoh: Listrik, Gaji, Retur"
        required
      />
      <Select
        label="Tipe"
        options={typeOpts}
        value={type}
        onChange={e => setType(e.target.value as 'income' | 'expense')}
      />
      <div className="flex justify-end gap-2 pt-2">
        <Button type="submit" variant="primary" icon={<Tag className="w-4 h-4" />}>
          Simpan Kategori
        </Button>
      </div>
    </form>
  );
}
