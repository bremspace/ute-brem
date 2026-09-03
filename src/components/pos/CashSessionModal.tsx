import React, { useState } from 'react';
import { useApp } from '../../context/AppContext';
import { CircleDollarSign, X, Check, ArrowRight, AlertCircle } from 'lucide-react';

interface CashSessionModalProps {
  onClose: () => void;
}

export const CashSessionModal: React.FC<CashSessionModalProps> = ({ onClose }) => {
  const { activeCashSession, openCashSession, closeCashSession, sales, currentUser, locations, selectedLocationId } = useApp();
  
  const [openingCash, setOpeningCash] = useState<number>(100000);
  const [closingCash, setClosingCash] = useState<number>(0);
  const [notes, setNotes] = useState<string>('');

  const currentLocation = locations.find(l => l.id === selectedLocationId) || locations[0];

  // Calculate live session statistics if open
  const sessionSales = activeCashSession
    ? sales.filter(s => s.cash_session_id === activeCashSession.id && s.status === 'paid')
    : [];

  const totalCashSales = sessionSales
    .filter(s => s.payment_method === 'cash')
    .reduce((sum, s) => sum + s.grand_total, 0);

  const totalNonCashSales = sessionSales
    .filter(s => s.payment_method !== 'cash')
    .reduce((sum, s) => sum + s.grand_total, 0);

  const expectedCashInDrawer = activeCashSession
    ? activeCashSession.opening_cash + totalCashSales
    : 0;

  const handleOpen = (e: React.FormEvent) => {
    e.preventDefault();
    openCashSession(openingCash, notes);
    onClose();
  };

  const handleClose = (e: React.FormEvent) => {
    e.preventDefault();
    closeCashSession(closingCash, notes);
    onClose();
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in">
      <div className="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-slate-100">
        {/* Header */}
        <div className="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 rounded-xl bg-primary-600/30 border border-primary-500/40 flex items-center justify-center text-primary-400">
              <CircleDollarSign className="w-5 h-5" />
            </div>
            <div>
              <h3 className="font-bold text-base">Sesi Kasir (Cash Session)</h3>
              <p className="text-xs text-slate-400">{currentLocation.name}</p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Content */}
        <div className="p-6">
          {activeCashSession ? (
            /* CLOSE SESSION FORM */
            <form onSubmit={handleClose} className="space-y-4">
              <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2 text-xs">
                <div className="flex justify-between text-slate-600">
                  <span>Kasir Aktif:</span>
                  <span className="font-bold text-slate-800">{activeCashSession.user_name}</span>
                </div>
                <div className="flex justify-between text-slate-600">
                  <span>Waktu Dibuka:</span>
                  <span className="font-medium text-slate-800">
                    {new Date(activeCashSession.opened_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
                  </span>
                </div>
                <div className="flex justify-between text-slate-600">
                  <span>Kas Awal (Modal):</span>
                  <span className="font-bold text-slate-800">
                    Rp {activeCashSession.opening_cash.toLocaleString('id-ID')}
                  </span>
                </div>
                <div className="flex justify-between text-slate-600">
                  <span>Penjualan Tunai ({sessionSales.filter(s => s.payment_method === 'cash').length} trx):</span>
                  <span className="font-bold text-emerald-600">
                    +Rp {totalCashSales.toLocaleString('id-ID')}
                  </span>
                </div>
                <div className="flex justify-between text-slate-600">
                  <span>Penjualan Non-Tunai:</span>
                  <span className="font-medium text-blue-600">
                    Rp {totalNonCashSales.toLocaleString('id-ID')}
                  </span>
                </div>
                <div className="pt-2 border-t border-slate-200 flex justify-between font-bold text-sm text-slate-900">
                  <span>Ekspektasi Uang Fisik di Laci:</span>
                  <span className="text-primary-700">Rp {expectedCashInDrawer.toLocaleString('id-ID')}</span>
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">
                  Hitung Fisik Uang di Laci Kasir (Rp) <span className="text-red-500">*</span>
                </label>
                <input
                  type="number"
                  min="0"
                  required
                  value={closingCash || ''}
                  onChange={e => setClosingCash(Number(e.target.value))}
                  placeholder="Masukkan total uang cash di laci saat ini"
                  className="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 font-bold text-base focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              {closingCash > 0 && (
                <div className={`p-3 rounded-xl text-xs font-semibold flex items-center justify-between ${
                  closingCash === expectedCashInDrawer
                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                    : closingCash > expectedCashInDrawer
                    ? 'bg-blue-50 text-blue-700 border border-blue-200'
                    : 'bg-red-50 text-red-700 border border-red-200'
                }`}>
                  <span>Selisih:</span>
                  <span>
                    {closingCash === expectedCashInDrawer
                      ? 'Pas (Tepat 0)'
                      : closingCash > expectedCashInDrawer
                      ? `Lebih +Rp ${(closingCash - expectedCashInDrawer).toLocaleString('id-ID')}`
                      : `Kurang -Rp ${(expectedCashInDrawer - closingCash).toLocaleString('id-ID')}`}
                  </span>
                </div>
              )}

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Catatan Tutup Kas</label>
                <input
                  type="text"
                  value={notes}
                  onChange={e => setNotes(e.target.value)}
                  placeholder="Contoh: Selisih uang koin kembalian"
                  className="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>

              <div className="pt-2 flex items-center justify-end gap-3">
                <button
                  type="button"
                  onClick={onClose}
                  className="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-md shadow-red-500/30 transition-all flex items-center gap-2"
                >
                  <Check className="w-4 h-4" />
                  Tutup Kas Sekarang
                </button>
              </div>
            </form>
          ) : (
            /* OPEN SESSION FORM */
            <form onSubmit={handleOpen} className="space-y-4">
              <div className="p-4 bg-primary-50 border border-primary-200 rounded-xl flex items-start gap-3">
                <AlertCircle className="w-5 h-5 text-primary-600 flex-shrink-0 mt-0.5" />
                <div className="text-xs text-primary-900 leading-relaxed">
                  Buka kas awal untuk memulai transaksi penjualan di kasir <span className="font-bold">{currentLocation.name}</span>.
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">
                  Uang Modal Kas Awal / Kembalian (Rp) <span className="text-red-500">*</span>
                </label>
                <input
                  type="number"
                  min="0"
                  step="1000"
                  required
                  value={openingCash || ''}
                  onChange={e => setOpeningCash(Number(e.target.value))}
                  placeholder="Contoh: 100000"
                  className="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 font-bold text-base focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              {/* Quick Preset Buttons */}
              <div className="flex gap-2">
                {[50000, 100000, 200000, 500000].map(amt => (
                  <button
                    key={amt}
                    type="button"
                    onClick={() => setOpeningCash(amt)}
                    className="flex-1 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg border border-slate-200"
                  >
                    Rp {(amt / 1000)}k
                  </button>
                ))}
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                <input
                  type="text"
                  value={notes}
                  onChange={e => setNotes(e.target.value)}
                  placeholder="Opsional: Keterangan uang receh"
                  className="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>

              <div className="pt-2 flex items-center justify-end gap-3">
                <button
                  type="button"
                  onClick={onClose}
                  className="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/30 transition-all flex items-center gap-2"
                >
                  <ArrowRight className="w-4 h-4" />
                  Buka Kasir Sekarang
                </button>
              </div>
            </form>
          )}
        </div>
      </div>
    </div>
  );
};
