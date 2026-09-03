import React, { useState } from 'react';
import { useApp } from '../context/AppContext';
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
  Plus,
  X,
  Tag,
  Clock,
  TrendingUp,
  TrendingDown,
  Banknote,
  Boxes,
  Wrench
} from 'lucide-react';

type TabKey = 'kas' | 'biaya' | 'kasbon' | 'koreksi' | 'pemakaian';

interface ModalState {
  kind: 'income' | 'expense' | 'mutation' | 'kasbon' | 'koreksi' | 'pemakaian' | 'biaya' | null;
}

const fmt = (n: number) => 'Rp ' + n.toLocaleString('id-ID');

const typeBadge: Record<string, { label: string; cls: string }> = {
  income: { label: 'Pemasukan', cls: 'bg-emerald-100 text-emerald-800' },
  expense: { label: 'Pengeluaran', cls: 'bg-red-100 text-red-800' },
  mutation: { label: 'Mutasi', cls: 'bg-blue-100 text-blue-800' },
  employee_advance: { label: 'Kasbon', cls: 'bg-amber-100 text-amber-800' }
};

const accountTypeLabel: Record<string, string> = {
  cash: 'Kas',
  bank: 'Bank',
  ewallet: 'E-Wallet'
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
    currentUser
  } = useApp();

  const [activeTab, setActiveTab] = useState<TabKey>('kas');
  const [modal, setModal] = useState<ModalState>({ kind: null });

  const totalSaldo = cashAccounts.reduce((s, a) => s + a.current_balance, 0);
  const totalIncome = cashTransactions.filter(t => t.transaction_type === 'income').reduce((s, t) => s + t.amount, 0);
  const totalExpense = cashTransactions.filter(t => t.transaction_type === 'expense').reduce((s, t) => s + t.amount, 0);
  const totalAdvanceOpen = employeeAdvances.filter(a => a.status === 'open').reduce((s, a) => s + a.amount, 0);

  const close = () => setModal({ kind: null });

  const tabs: { key: TabKey; label: string; icon: React.ReactNode }[] = [
    { key: 'kas', label: 'Kas', icon: <Wallet className="w-4 h-4" /> },
    { key: 'biaya', label: 'Biaya', icon: <Tag className="w-4 h-4" /> },
    { key: 'kasbon', label: 'Kasbon Karyawan', icon: <Users className="w-4 h-4" /> },
    { key: 'koreksi', label: 'Koreksi Stok', icon: <PackagePlus className="w-4 h-4" /> },
    { key: 'pemakaian', label: 'Pemakaian Barang', icon: <PackageMinus className="w-4 h-4" /> }
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
        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Saldo Kas</span>
            <div className="w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center font-bold">
              <CircleDollarSign className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-slate-900">{fmt(totalSaldo)}</div>
            <div className="text-xs text-slate-500 mt-1">{cashAccounts.length} akun kas aktif</div>
          </div>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pemasukan</span>
            <div className="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
              <TrendingUp className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-emerald-600">{fmt(totalIncome)}</div>
            <div className="text-xs text-slate-500 mt-1">Transaksi pemasukan kas</div>
          </div>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pengeluaran</span>
            <div className="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center font-bold">
              <TrendingDown className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-red-600">{fmt(totalExpense)}</div>
            <div className="text-xs text-slate-500 mt-1">Transaksi pengeluaran kas</div>
          </div>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Kasbon Terbuka</span>
            <div className="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
              <Banknote className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3">
            <div className="text-2xl font-black text-amber-600">{fmt(totalAdvanceOpen)}</div>
            <div className="text-xs text-slate-500 mt-1">
              {employeeAdvances.filter(a => a.status === 'open').length} kasbon belum lunas
            </div>
          </div>
        </div>
      </div>

      {/* Tab Bar */}
      <div className="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs bg-white border border-slate-200 rounded-2xl p-1.5 shadow-sm">
        {tabs.map(t => (
          <button
            key={t.key}
            onClick={() => setActiveTab(t.key)}
            className={`px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-all flex items-center gap-1.5 ${
              activeTab === t.key
                ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30'
                : 'text-slate-600 hover:bg-slate-100'
            }`}
          >
            {t.icon}
            {t.label}
          </button>
        ))}
      </div>

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
      {modal.kind && (
        <ModalShell title={modalTitle(modal.kind)} onClose={close}>
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
        </ModalShell>
      )}
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

const inputCls = "w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500";
const labelCls = "block text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-1";
const btnPrimary = "px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/30 transition-all flex items-center gap-1.5";

function KasTab({ cashAccounts, cashTransactions, onOpen }: {
  cashAccounts: ReturnType<typeof useApp>['cashAccounts'];
  cashTransactions: ReturnType<typeof useApp>['cashTransactions'];
  onOpen: (k: 'income' | 'expense' | 'mutation') => void;
}) {
  const active = cashAccounts.filter(a => a.is_active);
  const rows = [...cashTransactions].sort((a, b) => (b.transaction_date || '').localeCompare(a.transaction_date || ''));

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
          <div key={a.id} className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm">
            <div className="flex items-center justify-between">
              <div>
                <div className="text-xs font-black text-slate-900">{a.name}</div>
                <div className="text-[10px] font-mono text-slate-400 mt-0.5">{a.code}</div>
              </div>
              <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 capitalize">
                {accountTypeLabel[a.type] || a.type}
              </span>
            </div>
            <div className="mt-4">
              <div className="text-2xl font-black text-primary-700">{fmt(a.current_balance)}</div>
              <div className="text-[10px] text-slate-400 mt-0.5">Saldo awal: {fmt(a.opening_balance)}</div>
            </div>
          </div>
        ))}
      </div>

      {/* Action buttons */}
      <div className="flex flex-wrap gap-2">
        <button className={btnPrimary} onClick={() => onOpen('income')}>
          <ArrowDownCircle className="w-4 h-4" /> + Pemasukan
        </button>
        <button className={btnPrimary} onClick={() => onOpen('expense')}>
          <ArrowUpCircle className="w-4 h-4" /> + Pengeluaran
        </button>
        <button className={btnPrimary} onClick={() => onOpen('mutation')}>
          <ArrowLeftRight className="w-4 h-4" /> + Mutasi Antar Akun
        </button>
      </div>

      {/* Transactions */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-2 font-bold text-slate-800 text-sm">
          <Clock className="w-4 h-4 text-primary-600" />
          Transaksi Kas Terkini
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
              <tr>
                <th className="px-5 py-3">Kode</th>
                <th className="px-4 py-3">Tanggal</th>
                <th className="px-4 py-3">Tipe</th>
                <th className="px-4 py-3">Akun</th>
                <th className="px-4 py-3">Deskripsi</th>
                <th className="px-4 py-3 text-right">Jumlah</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 text-slate-700">
              {rows.slice(0, 15).map(t => {
                const badge = typeBadge[t.transaction_type] || { label: t.transaction_type, cls: 'bg-slate-100 text-slate-700' };
                const isIn = t.transaction_type === 'income' || t.transaction_type === 'mutation';
                return (
                  <tr key={t.id} className="hover:bg-slate-50/70 transition-colors">
                    <td className="px-5 py-3 font-mono font-bold text-primary-700">{t.transaction_code}</td>
                    <td className="px-4 py-3">{t.transaction_date}</td>
                    <td className="px-4 py-3">
                      <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${badge.cls}`}>{badge.label}</span>
                    </td>
                    <td className="px-4 py-3">
                      {t.cash_account?.name || (t.transaction_type === 'mutation' && t.target_cash_account?.name ? `${t.cash_account?.name} → ${t.target_cash_account?.name}` : '')}
                    </td>
                    <td className="px-4 py-3 max-w-[220px] truncate">{t.description || t.cost_category?.name || '-'}</td>
                    <td className={`px-4 py-3 text-right font-bold ${t.transaction_type === 'expense' ? 'text-red-600' : isIn ? 'text-emerald-600' : 'text-slate-700'}`}>
                      {t.transaction_type === 'expense' ? '-' : ''}{fmt(t.amount)}
                    </td>
                  </tr>
                );
              })}
              {rows.length === 0 && (
                <tr>
                  <td colSpan={6} className="px-5 py-8 text-center text-slate-400">
                    Belum ada transaksi kas.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

function BiayaTab({ costCategories, onAdd }: {
  costCategories: ReturnType<typeof useApp>['costCategories'];
  onAdd: () => void;
}) {
  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
          <Tag className="w-4 h-4 text-primary-600" />
          Kategori Biaya
        </div>
        <button className={btnPrimary} onClick={onAdd}><Plus className="w-4 h-4" /> Tambah</button>
      </div>
      <div className="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        {costCategories.map(c => (
          <div key={c.id} className="p-3.5 rounded-xl bg-slate-50 border border-slate-200/90 flex items-center justify-between">
            <div>
              <div className="font-bold text-xs text-slate-800">{c.name}</div>
              <div className="text-[10px] font-mono text-slate-400 mt-0.5">{c.code}</div>
            </div>
            <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full capitalize ${
              c.type === 'income' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'
            }`}>
              {c.type}
            </span>
          </div>
        ))}
        {costCategories.length === 0 && (
          <div className="col-span-full text-center text-slate-400 py-8 border border-dashed border-slate-300 rounded-xl">
            Belum ada kategori biaya.
          </div>
        )}
      </div>
    </div>
  );
}

function KasbonTab({ employeeAdvances, onAdd }: {
  employeeAdvances: ReturnType<typeof useApp>['employeeAdvances'];
  onAdd: () => void;
}) {
  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
          <Users className="w-4 h-4 text-primary-600" />
          Kasbon Karyawan
        </div>
        <button className={btnPrimary} onClick={onAdd}><Plus className="w-4 h-4" /> + Kasbon</button>
      </div>
      <div className="overflow-x-auto">
        <table className="w-full text-left text-xs">
          <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
            <tr>
              <th className="px-5 py-3">Kode</th>
              <th className="px-4 py-3">Tanggal</th>
              <th className="px-4 py-3">Karyawan</th>
              <th className="px-4 py-3">Akun</th>
              <th className="px-4 py-3 text-right">Jumlah</th>
              <th className="px-4 py-3">Status</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 text-slate-700">
            {employeeAdvances.map(a => (
              <tr key={a.id} className="hover:bg-slate-50/70 transition-colors">
                <td className="px-5 py-3 font-mono font-bold text-primary-700">{a.advance_code}</td>
                <td className="px-4 py-3">{a.advance_date}</td>
                <td className="px-4 py-3 font-semibold text-slate-800">{a.employee_name}</td>
                <td className="px-4 py-3">{a.cash_account_name}</td>
                <td className="px-4 py-3 text-right font-bold text-slate-900">{fmt(a.amount)}</td>
                <td className="px-4 py-3">
                  <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                    a.status === 'open' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'
                  }`}>
                    {a.status.toUpperCase()}
                  </span>
                </td>
              </tr>
            ))}
            {employeeAdvances.length === 0 && (
              <tr>
                <td colSpan={6} className="px-5 py-8 text-center text-slate-400">
                  Belum ada kasbon karyawan.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}

function StockTab({ docs, title, icon, onAdd }: {
  docs: ReturnType<typeof useApp>['stockDocuments'];
  title: string;
  icon: React.ReactNode;
  onAdd: () => void;
}) {
  const rows = [...docs].sort((a, b) => (b.document_date || '').localeCompare(a.document_date || ''));
  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
          {icon}
          {title}
        </div>
        <button className={btnPrimary} onClick={onAdd}><Plus className="w-4 h-4" /> + Tambah</button>
      </div>
      <div className="overflow-x-auto">
        <table className="w-full text-left text-xs">
          <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
            <tr>
              <th className="px-5 py-3">Kode</th>
              <th className="px-4 py-3">Tanggal</th>
              <th className="px-4 py-3">Produk</th>
              <th className="px-4 py-3">Lokasi</th>
              <th className="px-4 py-3">Gerakan</th>
              <th className="px-4 py-3 text-right">Qty</th>
              <th className="px-4 py-3">Deskripsi</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 text-slate-700">
            {rows.map(d => (
              <tr key={d.id} className="hover:bg-slate-50/70 transition-colors">
                <td className="px-5 py-3 font-mono font-bold text-primary-700">{d.document_code}</td>
                <td className="px-4 py-3">{d.document_date}</td>
                <td className="px-4 py-3 font-semibold text-slate-800">{d.product?.name || d.product_id}</td>
                <td className="px-4 py-3">{d.location?.name || d.location_id}</td>
                <td className="px-4 py-3">
                  <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">
                    {d.movement_type}
                  </span>
                </td>
                <td className={`px-4 py-3 text-right font-bold ${
                  d.movement_type === 'adjustment_plus' ? 'text-emerald-600' : 'text-red-600'
                }`}>
                  {d.movement_type === 'adjustment_plus' ? '+' : '-'}{d.quantity}
                </td>
                <td className="px-4 py-3 max-w-[200px] truncate">{d.description || d.created_by_name}</td>
              </tr>
            ))}
            {rows.length === 0 && (
              <tr>
                <td colSpan={7} className="px-5 py-8 text-center text-slate-400">
                  Belum ada dokumen stok {title.toLowerCase()}.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}

/* ---------------- Modals ---------------- */

function ModalShell({ title, onClose, children }: {
  title: string;
  onClose: () => void;
  children: React.ReactNode;
}) {
  return (
    <div className="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4" onClick={onClose}>
      <div className="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto" onClick={e => e.stopPropagation()}>
        <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h3 className="text-sm font-black text-slate-900">{title}</h3>
          <button onClick={onClose} className="p-1.5 text-slate-400 hover:bg-slate-100 rounded-lg">
            <X className="w-4 h-4" />
          </button>
        </div>
        <div className="p-6">{children}</div>
      </div>
    </div>
  );
}

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
      reference: reference || undefined
    });
  };

  return (
    <form onSubmit={submit} className="space-y-4">
      <div>
        <label className={labelCls}>Akun Kas</label>
        <select value={accountId} onChange={e => setAccountId(Number(e.target.value))} className={inputCls}>
          {cashAccounts.filter(a => a.is_active).map(a => (
            <option key={a.id} value={a.id}>{a.name} ({fmt(a.current_balance)})</option>
          ))}
        </select>
      </div>
      <div>
        <label className={labelCls}>Jumlah ({type === 'income' ? 'Pemasukan' : 'Pengeluaran'})</label>
        <input type="number" min="0" value={amount} onChange={e => setAmount(e.target.value)} placeholder="0" required className={inputCls} />
      </div>
      {costCategories.length > 0 && (
        <div>
          <label className={labelCls}>Kategori</label>
          <select value={categoryId} onChange={e => setCategoryId(e.target.value)} className={inputCls}>
            <option value="">— Tanpa kategori —</option>
            {costCategories.map(c => (
              <option key={c.id} value={c.id}>{c.name}</option>
            ))}
          </select>
        </div>
      )}
      <div>
        <label className={labelCls}>Deskripsi</label>
        <input type="text" value={description} onChange={e => setDescription(e.target.value)} placeholder="Keterangan" className={inputCls} />
      </div>
      <div>
        <label className={labelCls}>Referensi</label>
        <input type="text" value={reference} onChange={e => setReference(e.target.value)} placeholder="No. referensi (opsional)" className={inputCls} />
      </div>
      <div className="flex justify-end gap-2 pt-2">
        <button type="submit" className={btnPrimary}>
          {type === 'income' ? <ArrowDownCircle className="w-4 h-4" /> : <ArrowUpCircle className="w-4 h-4" />}
          Simpan {type === 'income' ? 'Pemasukan' : 'Pengeluaran'}
        </button>
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

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    const amt = Number(amount);
    if (!sourceId || !targetId || sourceId === targetId || !amt || amt <= 0) return;
    onSubmit({ sourceAccountId: sourceId, targetAccountId: targetId, amount: amt, description: description || undefined });
  };

  return (
    <form onSubmit={submit} className="space-y-4">
      <div>
        <label className={labelCls}>Akun Sumber</label>
        <select value={sourceId} onChange={e => setSourceId(Number(e.target.value))} className={inputCls}>
          {active.map(a => (
            <option key={a.id} value={a.id} disabled={a.id === targetId}>{a.name} ({fmt(a.current_balance)})</option>
          ))}
        </select>
      </div>
      <div>
        <label className={labelCls}>Akun Tujuan</label>
        <select value={targetId} onChange={e => setTargetId(Number(e.target.value))} className={inputCls}>
          {active.map(a => (
            <option key={a.id} value={a.id} disabled={a.id === sourceId}>{a.name} ({fmt(a.current_balance)})</option>
          ))}
        </select>
      </div>
      {sourceId === targetId && (
        <p className="text-[11px] font-semibold text-red-600">Akun sumber dan tujuan harus berbeda.</p>
      )}
      <div>
        <label className={labelCls}>Jumlah</label>
        <input type="number" min="0" value={amount} onChange={e => setAmount(e.target.value)} placeholder="0" required className={inputCls} />
      </div>
      <div>
        <label className={labelCls}>Deskripsi</label>
        <input type="text" value={description} onChange={e => setDescription(e.target.value)} placeholder="Keterangan mutasi" className={inputCls} />
      </div>
      <div className="flex justify-end gap-2 pt-2">
        <button type="submit" className={btnPrimary} disabled={sourceId === targetId}>
          <ArrowLeftRight className="w-4 h-4" /> Simpan Mutasi
        </button>
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

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    const amt = Number(amount);
    if (!accountId || !amt || amt <= 0) return;
    onSubmit({ employeeId: currentUserId, cashAccountId: accountId, amount: amt, description: description || undefined });
  };

  return (
    <form onSubmit={submit} className="space-y-4">
      <div>
        <label className={labelCls}>Karyawan (kasir saat ini)</label>
        <input type="text" value={currentUserName} disabled className={`${inputCls} bg-slate-50 text-slate-500`} />
      </div>
      <div>
        <label className={labelCls}>Akun Kas</label>
        <select value={accountId} onChange={e => setAccountId(Number(e.target.value))} className={inputCls}>
          {cashAccounts.filter(a => a.is_active).map(a => (
            <option key={a.id} value={a.id}>{a.name} ({fmt(a.current_balance)})</option>
          ))}
        </select>
      </div>
      <div>
        <label className={labelCls}>Jumlah Kasbon</label>
        <input type="number" min="0" value={amount} onChange={e => setAmount(e.target.value)} placeholder="0" required className={inputCls} />
      </div>
      <div>
        <label className={labelCls}>Deskripsi</label>
        <input type="text" value={description} onChange={e => setDescription(e.target.value)} placeholder="Alasan kasbon" className={inputCls} />
      </div>
      <div className="flex justify-end gap-2 pt-2">
        <button type="submit" className={btnPrimary}><Users className="w-4 h-4" /> Simpan Kasbon</button>
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
      description: description || undefined
    });
  };

  return (
    <form onSubmit={submit} className="space-y-4">
      <div>
        <label className={labelCls}>Produk</label>
        <select value={productId} onChange={e => setProductId(Number(e.target.value))} className={inputCls}>
          {products.map(p => (
            <option key={p.id} value={p.id}>{p.name} (Stok: {p.stock_global || 0})</option>
          ))}
        </select>
      </div>
      <div>
        <label className={labelCls}>Lokasi</label>
        <select value={locationId} onChange={e => setLocationId(Number(e.target.value))} className={inputCls}>
          {locations.map(l => (
            <option key={l.id} value={l.id}>{l.name}</option>
          ))}
        </select>
      </div>
      {!isUsage && (
        <div>
          <label className={labelCls}>Jenis Koreksi</label>
          <select value={movementType} onChange={e => setMovementType(e.target.value)} className={inputCls}>
            <option value="adjustment_plus">Penambahan Stok (+)</option>
            <option value="adjustment_minus">Pengurangan Stok (-)</option>
          </select>
        </div>
      )}
      <div>
        <label className={labelCls}>Jumlah</label>
        <input type="number" min="0" value={quantity} onChange={e => setQuantity(e.target.value)} placeholder="0" required className={inputCls} />
      </div>
      <div>
        <label className={labelCls}>Deskripsi</label>
        <input type="text" value={description} onChange={e => setDescription(e.target.value)} placeholder="Keterangan" className={inputCls} />
      </div>
      <div className="flex justify-end gap-2 pt-2">
        <button type="submit" className={btnPrimary}>
          {isUsage ? <Wrench className="w-4 h-4" /> : <Boxes className="w-4 h-4" />}
          Simpan
        </button>
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

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim()) return;
    const maxId = costCategories.reduce((m, c) => Math.max(m, c.id), 0);
    const code = (type === 'income' ? 'INC' : 'EXP') + '-' + (maxId + 1);
    const next: ReturnType<typeof useApp>['costCategories'] = [
      { id: maxId + 1, code, name: name.trim(), type, is_active: true },
      ...costCategories
    ];
    onSubmit(next);
  };

  return (
    <form onSubmit={submit} className="space-y-4">
      <div>
        <label className={labelCls}>Nama Kategori</label>
        <input type="text" value={name} onChange={e => setName(e.target.value)} placeholder="contoh: Listrik, Gaji, Retur" required className={inputCls} />
      </div>
      <div>
        <label className={labelCls}>Tipe</label>
        <select value={type} onChange={e => setType(e.target.value as 'income' | 'expense')} className={inputCls}>
          <option value="expense">Pengeluaran</option>
          <option value="income">Pemasukan</option>
        </select>
      </div>
      <div className="flex justify-end gap-2 pt-2">
        <button type="submit" className={btnPrimary}><Tag className="w-4 h-4" /> Simpan Kategori</button>
      </div>
    </form>
  );
}
