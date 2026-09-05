import React, { useState, useEffect, useRef } from 'react';
import { useApp } from '../context/AppContext';
import { PrinterSetting } from '../types';
import clsx from 'clsx';
import {
  Building2,
  Printer,
  User,
  Database,
  Info,
  Check,
  AlertTriangle,
  Download,
  Upload,
  RotateCcw,
  Save,
} from 'lucide-react';
import {
  Button,
  Card,
  CardHeader,
  Input,
  Select,
  Alert,
} from '../components/ui';
import type { SelectOption } from '../components/ui';

export const SettingsView: React.FC = () => {
  const {
    printerSetting,
    updatePrinterSetting,
    currentUser,
    setCurrentUser,
    exportDatabaseJson,
    importDatabaseJson,
    resetToDefaultData
  } = useApp();

  const [form, setForm] = useState<PrinterSetting>({ ...printerSetting });
  const [showSuccess, setShowSuccess] = useState(false);
  const [importResult, setImportResult] = useState<{ show: boolean; success: boolean }>({ show: false, success: false });
  const fileInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    setForm({ ...printerSetting });
  }, [printerSetting]);

  const handleChange = <K extends keyof PrinterSetting>(key: K, value: PrinterSetting[K]) => {
    setForm(prev => ({ ...prev, [key]: value }));
  };

  const handleSave = () => {
    updatePrinterSetting(form);
    setShowSuccess(true);
    setTimeout(() => setShowSuccess(false), 3000);
  };

  const handleExport = () => {
    exportDatabaseJson();
  };

  const handleImport = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (ev) => {
      const text = ev.target?.result as string;
      const ok = importDatabaseJson(text);
      setImportResult({ show: true, success: ok });
      setTimeout(() => setImportResult({ show: false, success: false }), 3000);
    };
    reader.readAsText(file);

    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  const handleReset = () => {
    if (confirm('Semua data akan direset ke data awal. Lanjutkan?')) {
      resetToDefaultData();
    }
  };

  const handleRoleSwitch = (role: 'super_admin' | 'cashier' | 'technician', displayName: string) => {
    setCurrentUser({ ...currentUser, role, role_display_name: displayName });
  };

  const modeOptions: SelectOption[] = [
    { value: 'browser', label: 'Browser Print (Web API)' },
    { value: 'bridge', label: 'Bridge Server (Local)' },
  ];

  const paperWidthOptions: SelectOption[] = [
    { value: 58, label: '58mm (Standard)' },
    { value: 80, label: '80mm (Lebar)' },
  ];

  return (
    <div className="p-6 space-y-6 max-w-4xl mx-auto">
      {/* Success Banner */}
      {showSuccess && (
        <div className="fixed top-4 right-4 z-50 flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 animate-bounce">
          <Check className="w-4 h-4" />
          Pengaturan berhasil disimpan!
        </div>
      )}

      {importResult.show && (
        <div className={clsx(
          'fixed top-4 right-4 z-50 flex items-center gap-2 px-4 py-2.5 text-white text-xs font-bold rounded-xl shadow-lg',
          importResult.success ? 'bg-emerald-600 shadow-emerald-600/30' : 'bg-red-600 shadow-red-600/30',
        )}>
          {importResult.success ? <Check className="w-4 h-4" /> : <AlertTriangle className="w-4 h-4" />}
          {importResult.success ? 'Import berhasil!' : 'Import gagal. Format file tidak valid.'}
        </div>
      )}

      {/* Header */}
      <div>
        <h1 className="text-2xl font-black text-slate-900 tracking-tight">Pengaturan & Backup</h1>
        <p className="text-xs text-slate-500 mt-1">
          Kelola identitas toko, pengaturan printer, dan backup data aplikasi.
        </p>
      </div>

      {/* Section 1: Identitas Toko */}
      <Card noPadding>
        <CardHeader>
          <div className="flex items-center gap-2">
            <Building2 className="w-4 h-4 text-primary-600" />
            <span className="font-bold text-slate-800 text-sm">Identitas Toko / Perusahaan</span>
          </div>
        </CardHeader>
        <div className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div className="sm:col-span-2">
            <Input
              label="Nama Perusahaan / Toko"
              value={form.company_name}
              onChange={e => handleChange('company_name', e.target.value)}
              placeholder="Nama toko Anda"
            />
          </div>
          <div className="sm:col-span-2">
            <Input
              label="Alamat"
              value={form.company_address}
              onChange={e => handleChange('company_address', e.target.value)}
              placeholder="Jl. Contoh No. 123"
            />
          </div>
          <div>
            <Input
              label="Kota"
              value={form.company_city}
              onChange={e => handleChange('company_city', e.target.value)}
              placeholder="Jakarta"
            />
          </div>
          <div>
            <Input
              label="Telepon"
              value={form.company_phone}
              onChange={e => handleChange('company_phone', e.target.value)}
              placeholder="0812xxxxxxx"
            />
          </div>
          <div className="sm:col-span-2">
            <Input
              label="Slogan / Tagline"
              value={form.company_slogan}
              onChange={e => handleChange('company_slogan', e.target.value)}
              placeholder="Slogan toko Anda"
            />
          </div>
        </div>
      </Card>

      {/* Section 2: Pengaturan Printer */}
      <Card noPadding>
        <CardHeader>
          <div className="flex items-center gap-2">
            <Printer className="w-4 h-4 text-primary-600" />
            <span className="font-bold text-slate-800 text-sm">Pengaturan Printer Thermal</span>
          </div>
        </CardHeader>
        <div className="p-6 space-y-5">
          {/* Mode & Basic Settings */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Select
              label="Mode Cetak"
              options={modeOptions}
              value={form.mode}
              onChange={e => handleChange('mode', e.target.value as 'browser' | 'bridge')}
            />

            {form.mode === 'bridge' && (
              <Input
                label="Bridge URL"
                value={form.bridge_url}
                onChange={e => handleChange('bridge_url', e.target.value)}
                placeholder="http://localhost:8080"
              />
            )}

            <Input
              label="Nama Printer"
              value={form.printer_name}
              onChange={e => handleChange('printer_name', e.target.value)}
              placeholder="Printer name"
            />

            <Select
              label="Lebar Kertas"
              options={paperWidthOptions}
              value={form.paper_width_mm}
              onChange={e => handleChange('paper_width_mm', Number(e.target.value) as 58 | 80)}
            />

            <Input
              label="Jumlah Salinan"
              type="number"
              min={1}
              max={5}
              value={form.copies}
              onChange={e => handleChange('copies', Math.min(5, Math.max(1, Number(e.target.value))))}
            />
          </div>

          {/* Checkboxes */}
          <div className="flex flex-wrap gap-4">
            <label className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                checked={form.show_store_name}
                onChange={e => handleChange('show_store_name', e.target.checked)}
                className="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
              />
              <span className="text-xs font-semibold text-slate-700">Tampilkan Nama Toko</span>
            </label>
            <label className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                checked={form.show_datetime}
                onChange={e => handleChange('show_datetime', e.target.checked)}
                className="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
              />
              <span className="text-xs font-semibold text-slate-700">Tampilkan Tanggal & Jam</span>
            </label>
            <label className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                checked={form.auto_print}
                onChange={e => handleChange('auto_print', e.target.checked)}
                className="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
              />
              <span className="text-xs font-semibold text-slate-700">Auto Print</span>
            </label>
          </div>

          {/* Header & Footer */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">
                Teks Header (di atas struk)
              </label>
              <textarea
                value={form.header_text}
                onChange={e => handleChange('header_text', e.target.value)}
                rows={3}
                className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
                placeholder="Contoh: Terima kasih telah berbelanja!"
              />
            </div>
            <div>
              <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">
                Teks Footer (di bawah struk)
              </label>
              <textarea
                value={form.footer_text}
                onChange={e => handleChange('footer_text', e.target.value)}
                rows={3}
                className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
                placeholder="Contoh: Barang yang sudah dibeli tidak dapat dikembalikan."
              />
            </div>
          </div>

          {/* Receipt Preview */}
          <div>
            <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">
              Preview Struk
            </label>
            <div className="bg-white border-2 border-dashed border-slate-300 rounded-xl p-4 max-w-[260px] mx-auto shadow-inner">
              <div className="font-mono text-[10px] leading-relaxed text-slate-800 space-y-0.5">
                {form.header_text && (
                  <div className="text-center mb-2 text-slate-500 whitespace-pre-wrap">{form.header_text}</div>
                )}
                {form.show_store_name && (
                  <>
                    <div className="text-center font-black text-sm">{form.company_name || 'Nama Toko'}</div>
                    {form.company_address && <div className="text-center text-slate-600">{form.company_address}</div>}
                    {form.company_city && <div className="text-center text-slate-600">{form.company_city}</div>}
                    {form.company_phone && <div className="text-center text-slate-600">Telp: {form.company_phone}</div>}
                    {form.company_slogan && <div className="text-center italic text-slate-500 mt-0.5">{form.company_slogan}</div>}
                  </>
                )}
                <div className="border-t border-dashed border-slate-300 my-2" />
                {form.show_datetime && <div className="text-center text-slate-500">03/09/2026 14:30</div>}
                <div className="text-[10px] text-slate-500">Kasir: Admin</div>
                <div className="border-t border-dashed border-slate-300 my-2" />
                <div className="flex justify-between"><span>2x Kabel USB-C</span><span>Rp 50.000</span></div>
                <div className="flex justify-between"><span>1x Screen Guard</span><span>Rp 25.000</span></div>
                <div className="flex justify-between"><span>1x Case HP</span><span>Rp 35.000</span></div>
                <div className="border-t border-dashed border-slate-300 my-2" />
                <div className="flex justify-between font-black text-xs">
                  <span>TOTAL</span>
                  <span>Rp 110.000</span>
                </div>
                <div className="text-center text-slate-500 mt-0.5">Tunai: Rp 120.000</div>
                <div className="text-center text-slate-500">Kembali: Rp 10.000</div>
                <div className="border-t border-dashed border-slate-300 my-2" />
                {form.footer_text && (
                  <div className="text-center text-slate-500 whitespace-pre-wrap">{form.footer_text}</div>
                )}
                <div className="text-center text-slate-400 mt-1">***</div>
              </div>
            </div>
          </div>
        </div>
      </Card>

      {/* Section 3: Pengguna & Role */}
      <Card noPadding>
        <CardHeader>
          <div className="flex items-center gap-2">
            <User className="w-4 h-4 text-primary-600" />
            <span className="font-bold text-slate-800 text-sm">Pengguna & Role</span>
          </div>
        </CardHeader>
        <div className="p-6 space-y-4">
          <div className="p-4 bg-slate-50 rounded-xl border border-slate-200 flex items-center gap-3">
            <div className="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-black text-sm">
              {currentUser.name.charAt(0).toUpperCase()}
            </div>
            <div>
              <div className="text-xs font-bold text-slate-800">{currentUser.name}</div>
              <div className="text-[11px] text-slate-500">{currentUser.email}</div>
              <div className="text-[10px] font-bold text-primary-600 mt-0.5">{currentUser.role_display_name}</div>
            </div>
          </div>
          <p className="text-[11px] text-slate-500">
            Beralih role hanya untuk keperluan demonstrasi. Role tidak mengubah hak akses aktual di localStorage.
          </p>
          <div>
            <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">
              Pilih Role (Demo)
            </label>
            <div className="flex flex-wrap gap-2 mt-1">
              {([
                { role: 'super_admin' as const, label: 'Super Admin' },
                { role: 'cashier' as const, label: 'Kasir POS' },
                { role: 'technician' as const, label: 'Teknisi Servis' }
              ]).map(opt => (
                <Button
                  key={opt.role}
                  variant={currentUser.role === opt.role ? 'primary' : 'outline'}
                  onClick={() => handleRoleSwitch(opt.role, opt.label)}
                >
                  {opt.label}
                </Button>
              ))}
            </div>
          </div>
        </div>
      </Card>

      {/* Section 4: Backup & Restore */}
      <Card noPadding>
        <CardHeader>
          <div className="flex items-center gap-2">
            <Database className="w-4 h-4 text-primary-600" />
            <span className="font-bold text-slate-800 text-sm">Backup & Restore Data</span>
          </div>
        </CardHeader>
        <div className="p-6 space-y-4">
          <div className="flex flex-wrap gap-3">
            <Button
              variant="primary"
              icon={<Download className="w-4 h-4" />}
              onClick={handleExport}
            >
              Export Backup (JSON)
            </Button>

            <Button
              variant="secondary"
              icon={<Upload className="w-4 h-4" />}
              onClick={() => fileInputRef.current?.click()}
            >
              Import Backup
            </Button>
            <input
              ref={fileInputRef}
              type="file"
              accept=".json"
              onChange={handleImport}
              className="hidden"
            />

            <Button
              variant="danger"
              icon={<RotateCcw className="w-4 h-4" />}
              onClick={handleReset}
            >
              Reset ke Data Awal
            </Button>
          </div>

          <Alert variant="warning" icon={<AlertTriangle className="w-4 h-4" />}>
            Semua data disimpan di <strong>localStorage</strong> browser Anda.
            Clearing cache / mengganti browser akan menghapus semua data.
            Gunakan Export secara berkala untuk backup aman.
          </Alert>
        </div>
      </Card>

      {/* Section 5: Tentang Aplikasi */}
      <Card noPadding>
        <CardHeader>
          <div className="flex items-center gap-2">
            <Info className="w-4 h-4 text-primary-600" />
            <span className="font-bold text-slate-800 text-sm">Tentang Aplikasi</span>
          </div>
        </CardHeader>
        <div className="p-6 space-y-3">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 rounded-xl bg-primary-600 text-white flex items-center justify-center font-black text-lg">
              U
            </div>
            <div>
              <div className="text-sm font-black text-slate-900">UTE POS</div>
              <div className="text-[11px] text-slate-500">Versi 1.0.0</div>
            </div>
          </div>
          <p className="text-xs text-slate-600 leading-relaxed">
            Aplikasi Point of Sale & Service Center untuk toko sparepart HP dan servis.
            Mendukung penjualan kasir, manajemen stok, transfer antar cabang,
            purchase order, hingga modul servis HP.
          </p>
          <div className="flex flex-wrap gap-2 mt-1">
            {['Vite', 'React 18', 'TypeScript', 'Tailwind CSS', 'localStorage'].map(t => (
              <span key={t} className="px-2.5 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-lg border border-slate-200">
                {t}
              </span>
            ))}
          </div>
        </div>
      </Card>

      {/* Save Button (Fixed Bottom) */}
      <div className="flex justify-end pb-6">
        <Button
          variant="primary"
          size="lg"
          icon={<Save className="w-4 h-4" />}
          onClick={handleSave}
        >
          Simpan Pengaturan
        </Button>
      </div>
    </div>
  );
};
