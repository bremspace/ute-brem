import React, { useState } from 'react';
import { useApp } from '../../context/AppContext';
import { Modal, Button, Input, Card, Badge } from '../ui';
import clsx from 'clsx';
import {
  CreditCard,
  Banknote,
  QrCode,
  Calendar,
  Layers,
  CheckCircle2,
  AlertTriangle,
  Award
} from 'lucide-react';

interface PaymentModalProps {
  onClose: () => void;
  onSuccess: () => void;
}

export const PaymentModal: React.FC<PaymentModalProps> = ({ onClose, onSuccess }) => {
  const { cart, selectedCustomerId, customers, customerGroups, checkoutSale } = useApp();

  const [paymentMethod, setPaymentMethod] = useState<'cash' | 'transfer' | 'qris' | 'tempo' | 'split'>('cash');
  const [paidAmount, setPaidAmount] = useState<number>(0);
  const [cashSplitAmount, setCashSplitAmount] = useState<number>(0);
  const [nonCashSplitAmount, setNonCashSplitAmount] = useState<number>(0);
  const [nonCashMethod, setNonCashMethod] = useState<'transfer' | 'qris'>('transfer');
  const [creditDueDate, setCreditDueDate] = useState<string>(() => {
    const d = new Date();
    d.setDate(d.getDate() + 14); // default 14 days credit
    return d.toISOString().split('T')[0];
  });
  const [notes, setNotes] = useState<string>('');
  const [saleChannel, setSaleChannel] = useState<'toko' | 'cabang' | 'partai'>('toko');

  const customer = customers.find(c => c.id === selectedCustomerId);
  const group = customer ? customerGroups.find(g => g.id === customer.customer_group_id) : null;

  const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.price), 0);
  const discountTotal = cart.reduce((sum, item) => sum + (item.discount_amount || 0), 0);
  const grandTotal = Math.max(0, subtotal - discountTotal);

  // Suggested fast payment buttons
  const cashSuggestions = [
    grandTotal,
    Math.ceil(grandTotal / 50000) * 50000,
    Math.ceil(grandTotal / 100000) * 100000,
    50000,
    100000,
    200000,
    500000
  ].filter((val, idx, self) => val >= grandTotal && self.indexOf(val) === idx).slice(0, 4);

  const pointsEarned = customer?.type === 'member' ? Math.floor(grandTotal / 50000) : 0;
  const changeAmount = paymentMethod === 'tempo' ? 0 : Math.max(0, paidAmount - grandTotal);

  const handleSubmit = (e?: React.FormEvent) => {
    e?.preventDefault();

    if (paymentMethod === 'split') {
      if (cashSplitAmount + nonCashSplitAmount < grandTotal) {
        alert('Total pembayaran split (Tunai + Non-Tunai) masih kurang dari total tagihan!');
        return;
      }
      checkoutSale({
        paymentMethod: 'split',
        paidAmount: cashSplitAmount + nonCashSplitAmount,
        payments: [
          { method: 'cash', amount: cashSplitAmount },
          { method: nonCashMethod, amount: nonCashSplitAmount, reference: 'Split Pay' }
        ],
        notes,
        saleChannel
      });
    } else {
      if (paymentMethod !== 'tempo' && paidAmount < grandTotal) {
        alert('Nominal pembayaran masih kurang dari total tagihan!');
        return;
      }
      checkoutSale({
        paymentMethod,
        paidAmount: paymentMethod === 'tempo' ? paidAmount : (paidAmount || grandTotal),
        creditDueDate: paymentMethod === 'tempo' ? creditDueDate : undefined,
        notes,
        saleChannel
      });
    }

    onSuccess();
  };

  return (
    <Modal
      open={true}
      onClose={onClose}
      size="xl"
      darkHeader
      title="Pembayaran Kasir POS"
      subtitle={`Pelanggan: ${customer?.name || 'Walk-in'} (${customer?.type === 'member' ? 'Member' : 'Umum'})`}
      headerIcon={<CreditCard className="w-5 h-5" />}
      footer={
        <>
          <Button variant="ghost" onClick={onClose}>Kembali</Button>
          <Button variant="success" icon={<CheckCircle2 className="w-4 h-4" />} onClick={() => handleSubmit()}>
            Selesaikan Transaksi (Cetak Struk)
          </Button>
        </>
      }
    >
      <form onSubmit={handleSubmit} className="space-y-5">
        {/* Total Summary Banner */}
        <div className="bg-gradient-to-r from-primary-900 to-indigo-900 text-white p-5 rounded-2xl shadow-md flex items-center justify-between">
          <div>
            <div className="text-xs uppercase tracking-wider text-primary-300 font-semibold">Total Tagihan Bersih</div>
            <div className="text-3xl font-black tracking-tight text-white mt-1">
              Rp {grandTotal.toLocaleString('id-ID')}
            </div>
            <div className="text-xs text-primary-200 mt-1">
              {cart.length} item barang ({cart.reduce((s, i) => s + i.quantity, 0)} pcs)
            </div>
          </div>

          {pointsEarned > 0 && (
            <div className="bg-white/10 border border-white/20 px-3.5 py-2 rounded-xl text-right">
              <Badge variant="amber" className="text-xs gap-1 justify-center">
                <Award className="w-4 h-4" />
                + {pointsEarned} Poin Member
              </Badge>
              <div className="text-[10px] text-slate-300 mt-1">Reward otomatis bertambah</div>
            </div>
          )}
        </div>

        {/* Sale Channel Selector */}
        <div className="flex items-center gap-2">
          <span className="text-xs font-bold text-slate-600">Jalur Penjualan:</span>
          <div className="flex gap-1.5 bg-slate-100 p-1 rounded-xl">
            {[
              { id: 'toko', label: 'Toko / Retail' },
              { id: 'cabang', label: 'Cabang' },
              { id: 'partai', label: 'Partai / Grosir' }
            ].map(c => (
              <Button
                key={c.id}
                type="button"
                variant="ghost"
                size="xs"
                onClick={() => setSaleChannel(c.id as any)}
                className={clsx(
                  'rounded-lg',
                  saleChannel === c.id && 'bg-white text-primary-700 shadow-sm'
                )}
              >
                {c.label}
              </Button>
            ))}
          </div>
        </div>

        {/* Payment Method Selector Tabs */}
        <div>
          <label className="block text-xs font-bold text-slate-700 mb-2">Pilih Metode Pembayaran:</label>
          <div className="grid grid-cols-5 gap-2">
            {[
              { id: 'cash', label: 'Tunai', icon: Banknote },
              { id: 'transfer', label: 'Transfer', icon: CreditCard },
              { id: 'qris', label: 'QRIS', icon: QrCode },
              { id: 'tempo', label: 'Tempo', icon: Calendar },
              { id: 'split', label: 'Split Pay', icon: Layers },
            ].map(m => {
              const Icon = m.icon;
              const isSelected = paymentMethod === m.id;
              return (
                <button
                  key={m.id}
                  type="button"
                  onClick={() => {
                    setPaymentMethod(m.id as any);
                    if (m.id === 'cash' || m.id === 'transfer' || m.id === 'qris') {
                      setPaidAmount(grandTotal);
                    } else if (m.id === 'split') {
                      setCashSplitAmount(Math.round(grandTotal / 2));
                      setNonCashSplitAmount(grandTotal - Math.round(grandTotal / 2));
                    }
                  }}
                  className={clsx(
                    'p-3 rounded-xl border flex flex-col items-center gap-1.5 text-xs font-bold transition-all',
                    isSelected
                      ? 'border-primary-600 bg-primary-50/80 text-primary-700 shadow-sm'
                      : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'
                  )}
                >
                  <Icon className={clsx('w-5 h-5', isSelected ? 'text-primary-600' : 'text-slate-500')} />
                  <span>{m.label}</span>
                </button>
              );
            })}
          </div>
        </div>

        {/* Dynamic Payment Input Section */}
        {paymentMethod === 'cash' && (
          <Card noPadding className="bg-slate-50 border-slate-200 p-4 space-y-3">
            <Input
              label="Uang Diterima dari Pelanggan (Rp)"
              required
              type="number"
              min={0}
              step="1000"
              value={paidAmount || ''}
              onChange={e => setPaidAmount(Number(e.target.value))}
              placeholder="Masukkan nominal uang tunai"
              className="!text-xl !font-bold !py-3"
            />

            {/* Fast Cash Preset Buttons */}
            <div className="flex flex-wrap gap-2">
              {cashSuggestions.map(amt => (
                <Button
                  key={amt}
                  type="button"
                  variant="secondary"
                  size="xs"
                  onClick={() => setPaidAmount(amt)}
                >
                  {amt === grandTotal ? 'Uang Pas' : `Rp ${amt.toLocaleString('id-ID')}`}
                </Button>
              ))}
            </div>

            {/* Change calculation */}
            <div className="pt-2 flex items-center justify-between border-t border-slate-200 text-sm">
              <span className="font-bold text-slate-700">Kembalian:</span>
              <span className={clsx(
                'text-lg font-black',
                changeAmount >= 0 ? 'text-emerald-600' : 'text-red-500'
              )}>
                Rp {changeAmount.toLocaleString('id-ID')}
              </span>
            </div>
          </Card>
        )}

        {(paymentMethod === 'transfer' || paymentMethod === 'qris') && (
          <Card noPadding className="bg-slate-50 border-slate-200 p-4 space-y-3">
            <div className="flex justify-between items-center text-xs">
              <span className="text-slate-600">Metode:</span>
              <span className="font-bold text-slate-900 uppercase">{paymentMethod} Payment</span>
            </div>
            <div className="flex justify-between items-center text-xs">
              <span className="text-slate-600">Total Terbayar:</span>
              <span className="font-bold text-emerald-600 text-sm">Rp {grandTotal.toLocaleString('id-ID')}</span>
            </div>
            <div className="text-[11px] text-slate-500 bg-white p-3 rounded-lg border border-slate-200">
              {paymentMethod === 'transfer'
                ? 'Pastikan bukti transfer bank sudah dicek & saldo masuk sebelum konfirmasi.'
                : 'Tampilkan QRIS statis / dinamis toko ke pelanggan dan pastikan notifikasi sukses.'}
            </div>
          </Card>
        )}

        {paymentMethod === 'tempo' && (
          <Card noPadding className="bg-warning-50 border-warning-200 p-4 space-y-3">
            <div className="flex items-center gap-2 text-warning-700 font-bold text-xs">
              <AlertTriangle className="w-4 h-4 text-warning-600" />
              Transaksi Piutang / Tempo Pelanggan
            </div>
            <div className="grid grid-cols-2 gap-3">
              <Input
                label="Uang Muka / DP (Rp)"
                type="number"
                min="0"
                max={grandTotal}
                value={paidAmount || ''}
                onChange={e => setPaidAmount(Number(e.target.value))}
                placeholder="0 (Jika tanpa DP)"
              />
              <Input
                label="Tanggal Jatuh Tempo"
                type="date"
                required
                value={creditDueDate}
                onChange={e => setCreditDueDate(e.target.value)}
              />
            </div>
            <div className="flex justify-between text-xs font-bold text-slate-800 pt-1">
              <span>Sisa Hutang:</span>
              <span className="text-danger-600">Rp {Math.max(0, grandTotal - paidAmount).toLocaleString('id-ID')}</span>
            </div>
          </Card>
        )}

        {paymentMethod === 'split' && (
          <Card noPadding className="bg-slate-50 border-slate-200 p-4 space-y-3">
            <div className="grid grid-cols-2 gap-3">
              <Input
                label="Porsi Tunai (Cash)"
                type="number"
                min="0"
                value={cashSplitAmount || ''}
                onChange={e => setCashSplitAmount(Number(e.target.value))}
              />
              <Input
                label={`Porsi Non-Tunai (${nonCashMethod.toUpperCase()})`}
                type="number"
                min="0"
                value={nonCashSplitAmount || ''}
                onChange={e => setNonCashSplitAmount(Number(e.target.value))}
              />
            </div>
            <div className="flex items-center gap-3 text-xs">
              <span className="font-semibold text-slate-600">Jenis Non-Tunai:</span>
              <label className="flex items-center gap-1.5 cursor-pointer font-medium">
                <input
                  type="radio"
                  name="nonCashMethod"
                  checked={nonCashMethod === 'transfer'}
                  onChange={() => setNonCashMethod('transfer')}
                />
                Transfer Bank
              </label>
              <label className="flex items-center gap-1.5 cursor-pointer font-medium">
                <input
                  type="radio"
                  name="nonCashMethod"
                  checked={nonCashMethod === 'qris'}
                  onChange={() => setNonCashMethod('qris')}
                />
                QRIS
              </label>
            </div>
          </Card>
        )}

        {/* Notes Input */}
        <Input
          label="Catatan Tambahan Transaksi"
          type="text"
          value={notes}
          onChange={e => setNotes(e.target.value)}
          placeholder="Opsional: Keterangan garansi / pengiriman"
        />
      </form>
    </Modal>
  );
};
