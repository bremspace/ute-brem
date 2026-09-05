import React, { useState } from 'react';
import { useApp } from '../../context/AppContext';
import { Modal, Button, Input, Card, Alert } from '../ui';
import clsx from 'clsx';
import { CircleDollarSign, Check, ArrowRight } from 'lucide-react';

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

  const handleOpen = (e?: React.FormEvent) => {
    e?.preventDefault();
    openCashSession(openingCash, notes);
    onClose();
  };

  const handleClose = (e?: React.FormEvent) => {
    e?.preventDefault();
    closeCashSession(closingCash, notes);
    onClose();
  };

  if (activeCashSession) {
    /* CLOSE SESSION FORM */
    return (
      <Modal
        open={true}
        onClose={onClose}
        size="md"
        darkHeader
        title="Sesi Kasir (Cash Session)"
        subtitle={currentLocation.name}
        headerIcon={<CircleDollarSign className="w-5 h-5" />}
        footer={
          <>
            <Button variant="ghost" onClick={onClose}>Batal</Button>
            <Button variant="danger" icon={<Check className="w-4 h-4" />} onClick={() => handleClose()}>
              Tutup Kas Sekarang
            </Button>
          </>
        }
      >
        <form onSubmit={handleClose} className="space-y-4">
          <Card noPadding className="bg-slate-50 border-slate-200 p-4 space-y-2 text-xs">
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
          </Card>

          <Input
            label="Hitung Fisik Uang di Laci Kasir (Rp)"
            required
            type="number"
            min="0"
            value={closingCash || ''}
            onChange={e => setClosingCash(Number(e.target.value))}
            placeholder="Masukkan total uang cash di laci saat ini"
            className="!font-bold !text-base"
          />

          {closingCash > 0 && (
            <Alert
              variant={closingCash === expectedCashInDrawer ? 'success' : closingCash > expectedCashInDrawer ? 'info' : 'error'}
              className="!text-xs font-semibold"
            >
              <div className="flex items-center justify-between">
                <span>Selisih:</span>
                <span>
                  {closingCash === expectedCashInDrawer
                    ? 'Pas (Tepat 0)'
                    : closingCash > expectedCashInDrawer
                    ? `Lebih +Rp ${(closingCash - expectedCashInDrawer).toLocaleString('id-ID')}`
                    : `Kurang -Rp ${(expectedCashInDrawer - closingCash).toLocaleString('id-ID')}`}
                </span>
              </div>
            </Alert>
          )}

          <Input
            label="Catatan Tutup Kas"
            type="text"
            value={notes}
            onChange={e => setNotes(e.target.value)}
            placeholder="Contoh: Selisih uang koin kembalian"
          />
        </form>
      </Modal>
    );
  }

  /* OPEN SESSION FORM */
  return (
    <Modal
      open={true}
      onClose={onClose}
      size="md"
      darkHeader
      title="Sesi Kasir (Cash Session)"
      subtitle={currentLocation.name}
      headerIcon={<CircleDollarSign className="w-5 h-5" />}
      footer={
        <>
          <Button variant="ghost" onClick={onClose}>Batal</Button>
          <Button variant="primary" icon={<ArrowRight className="w-4 h-4" />} onClick={() => handleOpen()}>
            Buka Kasir Sekarang
          </Button>
        </>
      }
    >
      <form onSubmit={handleOpen} className="space-y-4">
        <Alert variant="info">
          Buka kas awal untuk memulai transaksi penjualan di kasir <span className="font-bold">{currentLocation.name}</span>.
        </Alert>

        <Input
          label="Uang Modal Kas Awal / Kembalian (Rp)"
          required
          type="number"
          min="0"
          step="1000"
          value={openingCash || ''}
          onChange={e => setOpeningCash(Number(e.target.value))}
          placeholder="Contoh: 100000"
          className="!font-bold !text-base"
        />

        {/* Quick Preset Buttons */}
        <div className="flex gap-2">
          {[50000, 100000, 200000, 500000].map(amt => (
            <Button
              key={amt}
              type="button"
              variant="secondary"
              size="xs"
              onClick={() => setOpeningCash(amt)}
              className="flex-1"
            >
              Rp {(amt / 1000)}k
            </Button>
          ))}
        </div>

        <Input
          label="Catatan Tambahan"
          type="text"
          value={notes}
          onChange={e => setNotes(e.target.value)}
          placeholder="Opsional: Keterangan uang receh"
        />
      </form>
    </Modal>
  );
};
