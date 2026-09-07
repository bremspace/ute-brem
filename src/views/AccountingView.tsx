import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import clsx from 'clsx';
import {
  BookOpen,
  FileText,
  Landmark,
  Scale,
  TrendingUp,
  TrendingDown,
  Calendar,
  Download,
  CreditCard,
  Wallet,
  ArrowUpRight,
  ArrowDownRight,
} from 'lucide-react';
import {
  Badge,
  Button,
  Card,
  CardHeader,
  Input,
  StatCard,
  Tabs,
  TableContainer,
  TableHeader,
  TableBase,
  TableRow,
  TableEmpty,
  StatusBadge,
} from '../components/ui';
import type { TabItem } from '../components/ui';

type TabId = 'jurnal' | 'bukubesar' | 'neracasaldo' | 'labarugi' | 'neraca';

const TABS: TabItem[] = [
  { id: 'jurnal', label: 'Jurnal Umum', icon: <FileText className="w-4 h-4" /> },
  { id: 'bukubesar', label: 'Buku Besar', icon: <BookOpen className="w-4 h-4" /> },
  { id: 'neracasaldo', label: 'Neraca Saldo', icon: <Scale className="w-4 h-4" /> },
  { id: 'labarugi', label: 'Laba Rugi', icon: <TrendingUp className="w-4 h-4" /> },
  { id: 'neraca', label: 'Neraca', icon: <Landmark className="w-4 h-4" /> },
];

function rp(n: number): string {
  return `Rp ${n.toLocaleString('id-ID')}`;
}

function fmtDate(iso: string): string {
  const d = new Date(iso);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

function startOfMonth(): string {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-01`;
}

function endOfMonth(): string {
  const d = new Date();
  const last = new Date(d.getFullYear(), d.getMonth() + 1, 0);
  return `${last.getFullYear()}-${String(last.getMonth() + 1).padStart(2, '0')}-${String(last.getDate()).padStart(2, '0')}`;
}

function inRange(dateStr: string, from: string, to: string): boolean {
  const d = dateStr.slice(0, 10);
  return d >= from && d <= to;
}

interface JournalEntry {
  id: number;
  date: string;
  code: string;
  description: string;
  accountCode: string;
  accountName: string;
  debit: number;
  credit: number;
  source: string;
}

interface AccountBalance {
  code: string;
  name: string;
  type: 'asset' | 'liability' | 'equity' | 'revenue' | 'expense';
  debit: number;
  credit: number;
  balance: number;
  balanceType: 'debit' | 'credit';
}

// Build journal entries from existing data
function buildJournalEntries(
  sales: ReturnType<typeof useApp>['sales'],
  cashTransactions: ReturnType<typeof useApp>['cashTransactions'],
  purchaseOrders: ReturnType<typeof useApp>['purchaseOrders'],
  serviceTransactions: ReturnType<typeof useApp>['serviceTransactions'],
): JournalEntry[] {
  const entries: JournalEntry[] = [];
  let seq = 1;

  // Sales entries
  sales.filter(s => s.status === 'paid').forEach(s => {
    const code = `JRN-${seq.toString().padStart(4, '0')}`;
    entries.push({
      id: seq++,
      date: s.sale_at,
      code,
      description: `Penjualan ${s.sale_code}`,
      accountCode: '1101',
      accountName: 'Kas',
      debit: s.grand_total,
      credit: 0,
      source: 'POS',
    });
    entries.push({
      id: seq++,
      date: s.sale_at,
      code,
      description: `Penjualan ${s.sale_code}`,
      accountCode: '4100',
      accountName: 'Pendapatan Penjualan',
      debit: 0,
      credit: s.grand_total,
      source: 'POS',
    });
  });

  // Cash transaction entries
  cashTransactions.forEach(ct => {
    const code = `JRN-${seq.toString().padStart(4, '0')}`;
    if (ct.transaction_type === 'expense') {
      entries.push({
        id: seq++,
        date: ct.transaction_date,
        code,
        description: ct.description || ct.transaction_code,
        accountCode: '6100',
        accountName: ct.cost_category?.name || 'Beban Umum',
        debit: ct.amount,
        credit: 0,
        source: 'Kas',
      });
      entries.push({
        id: seq++,
        date: ct.transaction_date,
        code,
        description: ct.description || ct.transaction_code,
        accountCode: '1101',
        accountName: ct.cash_account?.name || 'Kas',
        debit: 0,
        credit: ct.amount,
        source: 'Kas',
      });
    } else if (ct.transaction_type === 'income') {
      entries.push({
        id: seq++,
        date: ct.transaction_date,
        code,
        description: ct.description || ct.transaction_code,
        accountCode: '1101',
        accountName: ct.cash_account?.name || 'Kas',
        debit: ct.amount,
        credit: 0,
        source: 'Kas',
      });
      entries.push({
        id: seq++,
        date: ct.transaction_date,
        code,
        description: ct.description || ct.transaction_code,
        accountCode: '4101',
        accountName: 'Pendapatan Lain',
        debit: 0,
        credit: ct.amount,
        source: 'Kas',
      });
    }
  });

  // PO entries
  purchaseOrders.filter(p => p.status !== 'cancelled').forEach(po => {
    const code = `JRN-${seq.toString().padStart(4, '0')}`;
    entries.push({
      id: seq++,
      date: po.order_date,
      code,
      description: `Pembelian ${po.po_number}`,
      accountCode: '5100',
      accountName: 'Harga Pokok Penjualan',
      debit: po.total_amount,
      credit: 0,
      source: 'PO',
    });
    entries.push({
      id: seq++,
      date: po.order_date,
      code,
      description: `Pembelian ${po.po_number}`,
      accountCode: '2101',
      accountName: 'Utang Dagang',
      debit: 0,
      credit: po.total_amount,
      source: 'PO',
    });
  });

  // Service entries
  serviceTransactions.filter(s => s.status === 'taken').forEach(s => {
    const code = `JRN-${seq.toString().padStart(4, '0')}`;
    entries.push({
      id: seq++,
      date: s.updated_at,
      code,
      description: `Servis ${s.service_code}`,
      accountCode: '1101',
      accountName: 'Kas',
      debit: s.grand_total,
      credit: 0,
      source: 'Servis',
    });
    entries.push({
      id: seq++,
      date: s.updated_at,
      code,
      description: `Servis ${s.service_code}`,
      accountCode: '4102',
      accountName: 'Pendapatan Servis',
      debit: 0,
      credit: s.grand_total,
      source: 'Servis',
    });
  });

  return entries.sort((a, b) => a.date.localeCompare(b.date));
}

function buildAccountBalances(journal: JournalEntry[]): AccountBalance[] {
  const map = new Map<string, AccountBalance>();
  journal.forEach(j => {
    if (!map.has(j.accountCode)) {
      map.set(j.accountCode, {
        code: j.accountCode,
        name: j.accountName,
        type: j.accountCode.startsWith('1') ? 'asset' : j.accountCode.startsWith('2') ? 'liability' : j.accountCode.startsWith('3') ? 'equity' : j.accountCode.startsWith('4') ? 'revenue' : 'expense',
        debit: 0,
        credit: 0,
        balance: 0,
        balanceType: 'debit',
      });
    }
    const acc = map.get(j.accountCode)!;
    acc.debit += j.debit;
    acc.credit += j.credit;
  });

  map.forEach(acc => {
    acc.balance = Math.abs(acc.debit - acc.credit);
    acc.balanceType = acc.debit >= acc.credit ? 'debit' : 'credit';
  });

  return Array.from(map.values()).sort((a, b) => a.code.localeCompare(b.code));
}

export const AccountingView: React.FC = () => {
  const { sales, cashTransactions, purchaseOrders, serviceTransactions } = useApp();

  const [activeTab, setActiveTab] = useState<TabId>('jurnal');
  const [dateFrom, setDateFrom] = useState(startOfMonth);
  const [dateTo, setDateTo] = useState(endOfMonth);

  const allJournal = useMemo(
    () => buildJournalEntries(sales, cashTransactions, purchaseOrders, serviceTransactions),
    [sales, cashTransactions, purchaseOrders, serviceTransactions],
  );

  const filteredJournal = useMemo(
    () => allJournal.filter(j => inRange(j.date, dateFrom, dateTo)),
    [allJournal, dateFrom, dateTo],
  );

  const accountBalances = useMemo(() => buildAccountBalances(filteredJournal), [filteredJournal]);

  const totalDebit = useMemo(() => filteredJournal.reduce((s, j) => s + j.debit, 0), [filteredJournal]);
  const totalCredit = useMemo(() => filteredJournal.reduce((s, j) => s + j.credit, 0), [filteredJournal]);

  // P&L data
  const totalRevenue = accountBalances.filter(a => a.type === 'revenue').reduce((s, a) => s + a.credit, 0);
  const totalCOGS = accountBalances.filter(a => a.code === '5100').reduce((s, a) => s + a.debit, 0);
  const totalExpense = accountBalances.filter(a => a.type === 'expense' && a.code !== '5100').reduce((s, a) => s + a.debit, 0);
  const grossProfit = totalRevenue - totalCOGS;
  const netProfit = grossProfit - totalExpense;

  // Balance sheet data
  const totalAssets = accountBalances.filter(a => a.type === 'asset').reduce((s, a) => s + a.debit - a.credit, 0);
  const totalLiabilities = accountBalances.filter(a => a.type === 'liability').reduce((s, a) => s + a.credit - a.debit, 0);
  const totalEquity = netProfit;

  const handleExport = () => {
    const dateLabel = `${dateFrom}_sampai_${dateTo}`;
    const rows: string[][] = [
      ['Tanggal', 'Kode Jurnal', 'Deskripsi', 'Kode Akun', 'Nama Akun', 'Debet', 'Kredit'],
      ...filteredJournal.map(j => [
        fmtDate(j.date), j.code, j.description, j.accountCode, j.accountName,
        String(j.debit), String(j.credit),
      ]),
      ['', '', '', '', 'TOTAL', String(totalDebit), String(totalCredit)],
    ];
    const csv = rows.map(r => r.map(c => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `jurnal_umum_${dateLabel}.csv`;
    a.click();
    URL.revokeObjectURL(url);
  };

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div className="flex items-center gap-2">
            <BookOpen className="w-5 h-5 text-primary-600" />
            <h1 className="text-2xl font-black text-slate-900 tracking-tight">Akuntansi</h1>
          </div>
          <p className="text-xs text-slate-500 mt-1">
            Jurnal umum, buku besar, neraca saldo, laba rugi, dan neraca keuangan.
          </p>
        </div>
        <Button
          onClick={handleExport}
          variant="success"
          icon={<Download className="w-4 h-4" />}
        >
          Export CSV
        </Button>
      </div>

      {/* Date Range */}
      <Card className="p-4">
        <div className="flex flex-col sm:flex-row items-start sm:items-center gap-4">
          <div className="flex items-center gap-2 text-xs font-bold text-slate-600">
            <Calendar className="w-4 h-4 text-primary-600" />
            Periode:
          </div>
          <div className="flex items-center gap-2">
            <Input type="date" value={dateFrom} onChange={e => setDateFrom(e.target.value)} className="!w-auto" />
            <span className="text-xs font-bold text-slate-400">s/d</span>
            <Input type="date" value={dateTo} onChange={e => setDateTo(e.target.value)} className="!w-auto" />
          </div>
        </div>
      </Card>

      {/* Tabs */}
      <Tabs tabs={TABS} activeTab={activeTab} onChange={id => setActiveTab(id as TabId)} variant="pill" />

      {/* Summary Stats */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          label="Total Debet"
          value={rp(totalDebit)}
          description={`${filteredJournal.filter(j => j.debit > 0).length} transaksi`}
          icon={<ArrowDownRight className="w-4 h-4" />}
          color="primary"
        />
        <StatCard
          label="Total Kredit"
          value={rp(totalCredit)}
          description={`${filteredJournal.filter(j => j.credit > 0).length} transaksi`}
          icon={<ArrowUpRight className="w-4 h-4" />}
          color="info"
        />
        <StatCard
          label="Total Akun"
          value={String(accountBalances.length)}
          description="akun aktif"
          icon={<Landmark className="w-4 h-4" />}
          color="warning"
        />
        <StatCard
          label="Laba Bersih"
          value={rp(netProfit)}
          description={`Margin: ${totalRevenue > 0 ? Math.round((netProfit / totalRevenue) * 100) : 0}%`}
          icon={netProfit >= 0 ? <TrendingUp className="w-4 h-4" /> : <TrendingDown className="w-4 h-4" />}
          color={netProfit >= 0 ? 'success' : 'danger'}
        />
      </div>

      {/* === JURNAL UMUM === */}
      {activeTab === 'jurnal' && (
        <TableContainer>
          <TableHeader icon={<FileText className="w-4 h-4 text-primary-600" />} count={filteredJournal.length}>
            Jurnal Umum
          </TableHeader>
          <TableBase
            columns={[
              { header: '#', className: 'px-5' },
              { header: 'Tanggal' },
              { header: 'Kode' },
              { header: 'Deskripsi' },
              { header: 'Akun' },
              { header: 'Debet', align: 'right' },
              { header: 'Kredit', align: 'right' },
              { header: 'Sumber' },
            ]}
            emptyMessage="Belum ada data jurnal di periode ini."
            colSpan={8}
          >
            {filteredJournal.map((j, i) => (
              <TableRow key={j.id}>
                <td className="px-5 py-2.5 font-bold text-slate-400">{i + 1}</td>
                <td className="px-4 py-2.5 whitespace-nowrap">{fmtDate(j.date)}</td>
                <td className="px-4 py-2.5 font-mono font-bold text-primary-700">{j.code}</td>
                <td className="px-4 py-2.5 font-medium text-slate-800">{j.description}</td>
                <td className="px-4 py-2.5">
                  <span className="font-mono text-xs text-slate-600">{j.accountCode}</span>
                  <span className="text-slate-400 mx-1">-</span>
                  <span className="text-xs">{j.accountName}</span>
                </td>
                <td className="px-4 py-2.5 text-right font-bold">
                  {j.debit > 0 ? <span className="text-slate-800">{rp(j.debit)}</span> : '-'}
                </td>
                <td className="px-4 py-2.5 text-right font-bold">
                  {j.credit > 0 ? <span className="text-primary-700">{rp(j.credit)}</span> : '-'}
                </td>
                <td className="px-4 py-2.5">
                  <Badge variant={j.source === 'POS' ? 'success' : j.source === 'Kas' ? 'info' : j.source === 'PO' ? 'warning' : 'primary'}>
                    {j.source}
                  </Badge>
                </td>
              </TableRow>
            ))}
            {filteredJournal.length === 0 && <TableEmpty colSpan={8} message="Belum ada data jurnal di periode ini." />}
            {filteredJournal.length > 0 && (
              <tr className="bg-slate-50 border-t-2 border-slate-200 font-bold text-slate-800">
                <td colSpan={5} className="px-5 py-3 text-xs text-slate-500">TOTAL</td>
                <td className="px-4 py-3 text-right">{rp(totalDebit)}</td>
                <td className="px-4 py-3 text-right text-primary-700">{rp(totalCredit)}</td>
                <td></td>
              </tr>
            )}
          </TableBase>
        </TableContainer>
      )}

      {/* === BUKU BESAR === */}
      {activeTab === 'bukubesar' && (
        <TableContainer>
          <TableHeader icon={<BookOpen className="w-4 h-4 text-primary-600" />} count={accountBalances.length}>
            Buku Besar (Rekening Koran)
          </TableHeader>
          <TableBase
            columns={[
              { header: 'Kode', className: 'px-5' },
              { header: 'Nama Akun' },
              { header: 'Tipe' },
              { header: 'Total Debet', align: 'right' },
              { header: 'Total Kredit', align: 'right' },
              { header: 'Saldo', align: 'right' },
            ]}
            emptyMessage="Belum ada data buku besar."
            colSpan={6}
          >
            {accountBalances.map(acc => (
              <TableRow key={acc.code}>
                <td className="px-5 py-2.5 font-mono font-bold text-primary-700">{acc.code}</td>
                <td className="px-4 py-2.5 font-medium text-slate-800">{acc.name}</td>
                <td className="px-4 py-2.5">
                  <Badge variant={
                    acc.type === 'asset' ? 'success' :
                    acc.type === 'liability' ? 'danger' :
                    acc.type === 'revenue' ? 'info' :
                    acc.type === 'expense' ? 'warning' : 'primary'
                  }>
                    {acc.type === 'asset' ? 'Aset' : acc.type === 'liability' ? 'Kewajiban' : acc.type === 'revenue' ? 'Pendapatan' : acc.type === 'expense' ? 'Beban' : 'Modal'}
                  </Badge>
                </td>
                <td className="px-4 py-2.5 text-right font-bold">{acc.debit > 0 ? rp(acc.debit) : '-'}</td>
                <td className="px-4 py-2.5 text-right font-bold">{acc.credit > 0 ? rp(acc.credit) : '-'}</td>
                <td className={clsx('px-4 py-2.5 text-right font-black', acc.balanceType === 'debit' ? 'text-slate-900' : 'text-primary-700')}>
                  {acc.balance > 0 ? rp(acc.balance) : '-'}
                  <span className="text-[10px] text-slate-400 ml-1 uppercase">{acc.balanceType}</span>
                </td>
              </TableRow>
            ))}
            {accountBalances.length === 0 && <TableEmpty colSpan={6} message="Belum ada data buku besar." />}
          </TableBase>
        </TableContainer>
      )}

      {/* === NERACA SALDO === */}
      {activeTab === 'neracasaldo' && (
        <div className="max-w-3xl mx-auto">
          <TableContainer>
            <TableHeader icon={<Scale className="w-4 h-4 text-primary-600" />}>
              Neraca Saldo — {fmtDate(dateFrom)} s/d {fmtDate(dateTo)}
            </TableHeader>
            <TableBase
              columns={[
                { header: 'Kode', className: 'px-5' },
                { header: 'Nama Akun' },
                { header: 'Debet', align: 'right' },
                { header: 'Kredit', align: 'right' },
              ]}
              emptyMessage="Belum ada data neraca saldo."
              colSpan={4}
            >
              {accountBalances.map(acc => (
                <TableRow key={acc.code}>
                  <td className="px-5 py-2.5 font-mono font-bold text-slate-700">{acc.code}</td>
                  <td className="px-4 py-2.5 font-medium text-slate-800">{acc.name}</td>
                  <td className="px-4 py-2.5 text-right font-bold">{acc.debit > 0 ? rp(acc.debit) : '-'}</td>
                  <td className="px-4 py-2.5 text-right font-bold">{acc.credit > 0 ? rp(acc.credit) : '-'}</td>
                </TableRow>
              ))}
              {accountBalances.length === 0 && <TableEmpty colSpan={4} message="Belum ada data neraca saldo." />}
              {accountBalances.length > 0 && (
                <tr className="bg-slate-50 border-t-2 border-slate-200 font-bold text-slate-800">
                  <td colSpan={2} className="px-5 py-3 text-xs text-slate-500">TOTAL</td>
                  <td className="px-4 py-3 text-right">{rp(totalDebit)}</td>
                  <td className="px-4 py-3 text-right text-primary-700">{rp(totalCredit)}</td>
                </tr>
              )}
            </TableBase>
            {totalDebit !== totalCredit && (
              <div className="px-5 py-3 bg-amber-50 border-t border-amber-200 text-xs font-bold text-amber-700">
                Selisih: {rp(Math.abs(totalDebit - totalCredit))} — Neraca tidak seimbang!
              </div>
            )}
          </TableContainer>
        </div>
      )}

      {/* === LABA RUGI === */}
      {activeTab === 'labarugi' && (
        <div className="max-w-2xl mx-auto space-y-4">
          <TableContainer>
            <TableHeader icon={<TrendingUp className="w-4 h-4 text-primary-600" />}>
              Laporan Laba Rugi — {fmtDate(dateFrom)} s/d {fmtDate(dateTo)}
            </TableHeader>
            <div className="p-6 space-y-4">
              <div className="space-y-2">
                <h4 className="text-xs font-bold text-slate-500 uppercase tracking-wider">Pendapatan</h4>
                <div className="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                  <span className="text-xs font-semibold text-slate-700">Pendapatan Penjualan</span>
                  <span className="text-xs font-bold text-slate-900">{rp(accountBalances.filter(a => a.code === '4100').reduce((s, a) => s + a.credit, 0))}</span>
                </div>
                <div className="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                  <span className="text-xs font-semibold text-slate-700">Pendapatan Servis</span>
                  <span className="text-xs font-bold text-slate-900">{rp(accountBalances.filter(a => a.code === '4102').reduce((s, a) => s + a.credit, 0))}</span>
                </div>
                <div className="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                  <span className="text-xs font-semibold text-slate-700">Pendapatan Lain</span>
                  <span className="text-xs font-bold text-slate-900">{rp(accountBalances.filter(a => a.code === '4101').reduce((s, a) => s + a.credit, 0))}</span>
                </div>
                <div className="flex justify-between items-center p-3 rounded-xl bg-primary-50 border border-primary-200">
                  <span className="text-xs font-bold text-primary-800">Total Pendapatan</span>
                  <span className="text-sm font-black text-primary-700">{rp(totalRevenue)}</span>
                </div>
              </div>

              <div className="space-y-2">
                <h4 className="text-xs font-bold text-slate-500 uppercase tracking-wider">Harga Pokok Penjualan</h4>
                <div className="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                  <span className="text-xs font-semibold text-slate-700">HPP</span>
                  <span className="text-xs font-bold text-red-600">({rp(totalCOGS)})</span>
                </div>
              </div>

              <div className={clsx(
                'flex justify-between items-center p-4 rounded-xl border-2',
                grossProfit >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200',
              )}>
                <span className="text-sm font-bold text-slate-800">Laba Kotor</span>
                <span className={clsx('text-lg font-black', grossProfit >= 0 ? 'text-emerald-700' : 'text-red-700')}>{rp(grossProfit)}</span>
              </div>

              <div className="space-y-2">
                <h4 className="text-xs font-bold text-slate-500 uppercase tracking-wider">Beban Operasional</h4>
                <div className="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                  <span className="text-xs font-semibold text-slate-700">Beban Lainnya</span>
                  <span className="text-xs font-bold text-red-600">({rp(totalExpense)})</span>
                </div>
              </div>

              <div className={clsx(
                'flex justify-between items-center p-5 rounded-xl border-2',
                netProfit >= 0 ? 'bg-emerald-50 border-emerald-300' : 'bg-red-50 border-red-300',
              )}>
                <div className="flex items-center gap-2">
                  {netProfit >= 0 ? <TrendingUp className="w-5 h-5 text-emerald-600" /> : <TrendingDown className="w-5 h-5 text-red-600" />}
                  <span className="text-sm font-black text-slate-900">Laba Bersih</span>
                </div>
                <span className={clsx('text-xl font-black', netProfit >= 0 ? 'text-emerald-700' : 'text-red-700')}>{rp(netProfit)}</span>
              </div>
            </div>
          </TableContainer>
        </div>
      )}

      {/* === NERACA === */}
      {activeTab === 'neraca' && (
        <div className="max-w-3xl mx-auto">
          <TableContainer>
            <TableHeader icon={<Landmark className="w-4 h-4 text-primary-600" />}>
              Neraca (Balance Sheet) — {fmtDate(dateFrom)} s/d {fmtDate(dateTo)}
            </TableHeader>
            <div className="p-6 space-y-6">
              {/* Aset */}
              <div className="space-y-2">
                <h4 className="text-xs font-bold text-slate-500 uppercase tracking-wider">Aset</h4>
                {accountBalances.filter(a => a.type === 'asset').map(acc => (
                  <div key={acc.code} className="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div>
                      <span className="font-mono text-[11px] text-slate-500 mr-2">{acc.code}</span>
                      <span className="text-xs font-semibold text-slate-700">{acc.name}</span>
                    </div>
                    <span className="text-xs font-bold text-slate-900">{rp(acc.debit - acc.credit)}</span>
                  </div>
                ))}
                <div className="flex justify-between items-center p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                  <span className="text-xs font-bold text-emerald-800">Total Aset</span>
                  <span className="text-sm font-black text-emerald-700">{rp(totalAssets)}</span>
                </div>
              </div>

              {/* Kewajiban */}
              <div className="space-y-2">
                <h4 className="text-xs font-bold text-slate-500 uppercase tracking-wider">Kewajiban</h4>
                {accountBalances.filter(a => a.type === 'liability').map(acc => (
                  <div key={acc.code} className="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div>
                      <span className="font-mono text-[11px] text-slate-500 mr-2">{acc.code}</span>
                      <span className="text-xs font-semibold text-slate-700">{acc.name}</span>
                    </div>
                    <span className="text-xs font-bold text-red-600">{rp(acc.credit - acc.debit)}</span>
                  </div>
                ))}
                <div className="flex justify-between items-center p-3 rounded-xl bg-red-50 border border-red-200">
                  <span className="text-xs font-bold text-red-800">Total Kewajiban</span>
                  <span className="text-sm font-black text-red-700">{rp(totalLiabilities)}</span>
                </div>
              </div>

              {/* Modal */}
              <div className="space-y-2">
                <h4 className="text-xs font-bold text-slate-500 uppercase tracking-wider">Modal & Laba Bersih</h4>
                <div className="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                  <span className="text-xs font-semibold text-slate-700">Laba Bersih Periode Ini</span>
                  <span className={clsx('text-xs font-bold', netProfit >= 0 ? 'text-emerald-700' : 'text-red-600')}>{rp(netProfit)}</span>
                </div>
              </div>

              {/* Rekap */}
              <div className="grid grid-cols-2 gap-4 pt-4 border-t-2 border-slate-200">
                <div className="flex justify-between items-center p-4 rounded-xl bg-emerald-50 border-2 border-emerald-200">
                  <span className="text-sm font-bold text-emerald-800">Aset</span>
                  <span className="text-lg font-black text-emerald-700">{rp(totalAssets)}</span>
                </div>
                <div className="flex justify-between items-center p-4 rounded-xl bg-red-50 border-2 border-red-200">
                  <span className="text-sm font-bold text-red-800">Kewajiban + Modal</span>
                  <span className="text-lg font-black text-red-700">{rp(totalLiabilities + totalEquity)}</span>
                </div>
              </div>
              {Math.abs(totalAssets - (totalLiabilities + totalEquity)) > 1 && (
                <div className="px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl text-xs font-bold text-amber-700 text-center">
                  Selisih Neraca: {rp(Math.abs(totalAssets - (totalLiabilities + totalEquity)))}
                </div>
              )}
            </div>
          </TableContainer>
        </div>
      )}
    </div>
  );
};
