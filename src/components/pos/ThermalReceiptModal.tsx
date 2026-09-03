import React from 'react';
import { Sale } from '../../types';
import { useApp } from '../../context/AppContext';
import { Printer, X, Check, Share2 } from 'lucide-react';

interface ThermalReceiptModalProps {
  sale: Sale;
  onClose: () => void;
}

export const ThermalReceiptModal: React.FC<ThermalReceiptModalProps> = ({ sale, onClose }) => {
  const { printerSetting } = useApp();

  const handlePrint = () => {
    window.print();
  };

  const handleShareWhatsApp = () => {
    let text = `*${printerSetting.company_name}*\n`;
    text += `${printerSetting.company_address}\n`;
    text += `Telp: ${printerSetting.company_phone}\n`;
    text += `--------------------------------\n`;
    text += `No. Nota : ${sale.sale_code}\n`;
    text += `Tanggal  : ${new Date(sale.sale_at).toLocaleString('id-ID')}\n`;
    text += `Kasir    : ${sale.cashier_name}\n`;
    text += `Pelanggan: ${sale.customer?.name || 'Umum'}\n`;
    text += `--------------------------------\n`;
    sale.items.forEach(it => {
      text += `${it.product_name}\n`;
      text += `  ${it.quantity} x Rp ${it.unit_price.toLocaleString('id-ID')} = Rp ${it.subtotal.toLocaleString('id-ID')}\n`;
    });
    text += `--------------------------------\n`;
    text += `Total     : Rp ${sale.grand_total.toLocaleString('id-ID')}\n`;
    text += `Bayar     : Rp ${sale.paid_amount.toLocaleString('id-ID')}\n`;
    if (sale.change_amount > 0) {
      text += `Kembali   : Rp ${sale.change_amount.toLocaleString('id-ID')}\n`;
    }
    if (sale.points_earned > 0) {
      text += `Poin Didapat: +${sale.points_earned} Poin\n`;
    }
    text += `--------------------------------\n`;
    text += `${printerSetting.footer_text}\n`;

    const encoded = encodeURIComponent(text);
    const phone = sale.customer?.phone ? sale.customer.phone.replace(/^0/, '62') : '';
    window.open(`https://wa.me/${phone}?text=${encoded}`, '_blank');
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in">
      <div className="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
        {/* Header */}
        <div className="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between flex-shrink-0">
          <div className="flex items-center gap-2">
            <Printer className="w-5 h-5 text-primary-400" />
            <h3 className="font-bold text-sm">Cetak Struk Thermal ({printerSetting.paper_width_mm}mm)</h3>
          </div>
          <button
            onClick={onClose}
            className="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Receipt Preview Area */}
        <div className="flex-1 overflow-y-auto p-4 bg-slate-100 flex justify-center">
          <div
            id="thermal-receipt-printable"
            className="bg-white p-4 shadow-sm border border-slate-200 text-slate-900 font-mono text-[11px] leading-tight"
            style={{ width: printerSetting.paper_width_mm === 80 ? '300px' : '230px' }}
          >
            {/* Header */}
            <div className="text-center space-y-0.5 pb-2 border-b border-dashed border-slate-400">
              <div className="font-bold text-xs uppercase">{printerSetting.company_name}</div>
              <div className="text-[10px] text-slate-600">{printerSetting.company_address}</div>
              <div className="text-[10px] text-slate-600">Telp: {printerSetting.company_phone}</div>
              {printerSetting.company_slogan && (
                <div className="text-[9px] text-slate-500 italic mt-0.5">{printerSetting.company_slogan}</div>
              )}
            </div>

            {/* Metadata */}
            <div className="py-2 border-b border-dashed border-slate-400 text-[10px] space-y-0.5">
              <div className="flex justify-between">
                <span>No. Nota:</span>
                <span className="font-bold">{sale.sale_code}</span>
              </div>
              <div className="flex justify-between">
                <span>Tanggal:</span>
                <span>{new Date(sale.sale_at).toLocaleDateString('id-ID')} {new Date(sale.sale_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</span>
              </div>
              <div className="flex justify-between">
                <span>Kasir:</span>
                <span>{sale.cashier_name}</span>
              </div>
              <div className="flex justify-between">
                <span>Pelanggan:</span>
                <span>{sale.customer?.name || 'Umum / Walk-in'}</span>
              </div>
              {sale.sale_channel !== 'toko' && (
                <div className="flex justify-between uppercase">
                  <span>Jalur:</span>
                  <span className="font-bold">{sale.sale_channel}</span>
                </div>
              )}
            </div>

            {/* Items */}
            <div className="py-2 border-b border-dashed border-slate-400 space-y-1.5">
              {sale.items.map((it, idx) => (
                <div key={idx}>
                  <div className="font-bold text-[10px]">{it.product_name}</div>
                  <div className="flex justify-between text-[10px] text-slate-700">
                    <span>
                      {it.quantity} x Rp {it.unit_price.toLocaleString('id-ID')}
                      {it.discount_amount > 0 && ` (-${it.discount_amount})`}
                    </span>
                    <span className="font-semibold">Rp {it.subtotal.toLocaleString('id-ID')}</span>
                  </div>
                </div>
              ))}
            </div>

            {/* Totals */}
            <div className="py-2 border-b border-dashed border-slate-400 text-[10px] space-y-0.5">
              <div className="flex justify-between">
                <span>Subtotal:</span>
                <span>Rp {sale.subtotal.toLocaleString('id-ID')}</span>
              </div>
              {sale.discount_total > 0 && (
                <div className="flex justify-between text-emerald-700">
                  <span>Diskon:</span>
                  <span>-Rp {sale.discount_total.toLocaleString('id-ID')}</span>
                </div>
              )}
              <div className="flex justify-between font-bold text-xs pt-1 border-t border-slate-200">
                <span>GRAND TOTAL:</span>
                <span>Rp {sale.grand_total.toLocaleString('id-ID')}</span>
              </div>
              <div className="flex justify-between pt-1">
                <span>Bayar ({sale.payment_method.toUpperCase()}):</span>
                <span>Rp {sale.paid_amount.toLocaleString('id-ID')}</span>
              </div>
              {sale.change_amount > 0 && (
                <div className="flex justify-between font-semibold">
                  <span>Kembali:</span>
                  <span>Rp {sale.change_amount.toLocaleString('id-ID')}</span>
                </div>
              )}
              {sale.payment_method === 'tempo' && (
                <div className="flex justify-between font-bold text-red-600">
                  <span>Sisa Hutang:</span>
                  <span>Rp {Math.max(0, sale.grand_total - sale.paid_amount).toLocaleString('id-ID')}</span>
                </div>
              )}
            </div>

            {/* Points earned */}
            {sale.points_earned > 0 && (
              <div className="py-1 text-center font-bold text-[10px] text-amber-800 bg-amber-50 rounded mt-1">
                ★ Anda Mendapatkan +{sale.points_earned} Poin Member ★
              </div>
            )}

            {/* Footer */}
            <div className="text-center pt-3 text-[9px] text-slate-500 leading-tight">
              <div>{printerSetting.footer_text}</div>
              <div className="mt-1 font-semibold text-[8px] text-slate-400">--- Powered by UTE Parts POS ---</div>
            </div>
          </div>
        </div>

        {/* Footer Actions */}
        <div className="p-4 bg-white border-t border-slate-200 flex items-center justify-between gap-3 flex-shrink-0">
          <button
            type="button"
            onClick={handleShareWhatsApp}
            className="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-300 flex items-center gap-1.5"
          >
            <Share2 className="w-4 h-4" />
            Kirim WhatsApp
          </button>

          <div className="flex items-center gap-2">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl"
            >
              Tutup
            </button>
            <button
              type="button"
              onClick={handlePrint}
              className="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/30 flex items-center gap-2"
            >
              <Printer className="w-4 h-4" />
              Cetak Struk Sekarang
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
