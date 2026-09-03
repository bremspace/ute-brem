import React, { useState, useRef, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { Product } from '../types';
import {
  Search,
  Barcode,
  Plus,
  Minus,
  Trash2,
  User,
  ShoppingBag,
  CreditCard,
  Tag,
  AlertCircle,
  CircleDollarSign,
  Sparkles,
  Percent,
  Check
} from 'lucide-react';
import { PaymentModal } from '../components/pos/PaymentModal';
import { ThermalReceiptModal } from '../components/pos/ThermalReceiptModal';
import { CashSessionModal } from '../components/pos/CashSessionModal';

export const PosView: React.FC = () => {
  const {
    products,
    categories,
    brands,
    cart,
    addToCart,
    updateCartItemQty,
    updateCartItemPrice,
    updateCartItemDiscount,
    removeFromCart,
    clearCart,
    selectedCustomerId,
    setSelectedCustomerId,
    customers,
    customerGroups,
    activeCashSession,
    selectedLocationId,
    lastCompletedSale,
    setLastCompletedSale
  } = useApp();

  const [searchTerm, setSearchTerm] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<number | 'all'>('all');
  const [selectedBrand, setSelectedBrand] = useState<number | 'all'>('all');
  const [barcodeInput, setBarcodeInput] = useState('');
  const [showPaymentModal, setShowPaymentModal] = useState(false);
  const [showReceiptModal, setShowReceiptModal] = useState(false);
  const [showCashSessionModal, setShowCashSessionModal] = useState(false);
  const [editingPriceIndex, setEditingPriceIndex] = useState<number | null>(null);
  const [editingPriceValue, setEditingPriceValue] = useState<number>(0);

  const barcodeInputRef = useRef<HTMLInputElement>(null);

  // Focus barcode input on mount
  useEffect(() => {
    barcodeInputRef.current?.focus();
  }, []);

  const selectedCustomer = customers.find(c => c.id === selectedCustomerId);
  const customerGroup = selectedCustomer ? customerGroups.find(g => g.id === selectedCustomer.customer_group_id) : null;

  // Filter products by search, category, brand, and active status
  const filteredProducts = products.filter(p => {
    if (!p.is_active) return false;
    const matchCat = selectedCategory === 'all' || p.category_id === selectedCategory;
    const matchBrand = selectedBrand === 'all' || p.brand_id === selectedBrand;
    const matchSearch =
      p.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      p.product_code.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (p.barcode && p.barcode.includes(searchTerm));
    return matchCat && matchBrand && matchSearch;
  });

  const handleBarcodeSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!barcodeInput.trim()) return;

    const found = products.find(p => p.barcode === barcodeInput.trim() || p.product_code.toLowerCase() === barcodeInput.trim().toLowerCase());
    if (found) {
      addToCart(found);
      setBarcodeInput('');
    } else {
      alert(`Produk dengan barcode/kode "${barcodeInput}" tidak ditemukan!`);
    }
  };

  const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.price), 0);
  const discountTotal = cart.reduce((sum, item) => sum + (item.discount_amount || 0), 0);
  const grandTotal = Math.max(0, subtotal - discountTotal);

  return (
    <div className="h-[calc(100vh-4rem)] flex flex-col lg:flex-row overflow-hidden bg-slate-100">
      {/* LEFT SECTION: Catalog & Search */}
      <div className="flex-1 flex flex-col min-w-0 border-r border-slate-200 bg-white">
        {/* Top Control Bar: Barcode scanner + Search input */}
        <div className="p-4 border-b border-slate-200 bg-slate-50/70 space-y-3">
          <div className="flex items-center gap-3">
            {/* Fast Barcode Scanner input */}
            <form onSubmit={handleBarcodeSubmit} className="relative flex-1">
              <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-primary-600">
                <Barcode className="w-5 h-5" />
              </div>
              <input
                ref={barcodeInputRef}
                type="text"
                value={barcodeInput}
                onChange={e => setBarcodeInput(e.target.value)}
                placeholder="Scan Barcode / Tekan Enter..."
                className="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm"
              />
            </form>

            {/* General Search */}
            <div className="relative flex-1">
              <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <Search className="w-4 h-4" />
              </div>
              <input
                type="text"
                value={searchTerm}
                onChange={e => setSearchTerm(e.target.value)}
                placeholder="Cari nama produk, tipe HP, kode..."
                className="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm"
              />
            </div>
          </div>

          {/* Category & Brand Pills */}
          <div className="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <button
              onClick={() => setSelectedCategory('all')}
              className={`px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap transition-all ${
                selectedCategory === 'all'
                  ? 'bg-primary-600 text-white shadow-sm'
                  : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'
              }`}
            >
              Semua Kategori
            </button>
            {categories.map(c => (
              <button
                key={c.id}
                onClick={() => setSelectedCategory(c.id)}
                className={`px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap transition-all ${
                  selectedCategory === c.id
                    ? 'bg-primary-600 text-white shadow-sm'
                    : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'
                }`}
              >
                {c.name}
              </button>
            ))}
          </div>
        </div>

        {/* Product Grid */}
        <div className="flex-1 overflow-y-auto p-4">
          <div className="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
            {filteredProducts.map(product => {
              const currentStock = product.stocks?.find(s => s.location_id === selectedLocationId)?.quantity || 0;
              const isLowStock = currentStock <= (product.stock_min || 0);

              return (
                <div
                  key={product.id}
                  onClick={() => addToCart(product)}
                  className="bg-white border border-slate-200 hover:border-primary-400 hover:shadow-md rounded-2xl p-3.5 flex flex-col justify-between cursor-pointer transition-all duration-150 group active:scale-[0.98]"
                >
                  <div>
                    {/* Header tags */}
                    <div className="flex items-center justify-between gap-1 mb-1.5">
                      <span className="text-[10px] font-mono font-semibold bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded truncate max-w-[120px]">
                        {product.product_code}
                      </span>
                      <span className={`text-[10px] font-bold px-1.5 py-0.5 rounded ${
                        currentStock > 0
                          ? isLowStock ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'
                          : 'bg-red-100 text-red-800'
                      }`}>
                        Stok: {currentStock}
                      </span>
                    </div>

                    {/* Product Name */}
                    <h4 className="text-xs font-bold text-slate-800 line-clamp-2 group-hover:text-primary-600 leading-snug">
                      {product.name}
                    </h4>

                    <div className="text-[11px] text-slate-400 mt-1 truncate">
                      {product.brand?.name || 'All'} • {product.maker?.name || 'Standard'}
                    </div>
                  </div>

                  {/* Price & Add Button */}
                  <div className="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <div>
                      <div className="text-xs font-black text-primary-700">
                        Rp {product.selling_price.toLocaleString('id-ID')}
                      </div>
                      {product.allow_open_price && (
                        <div className="text-[9px] text-amber-600 font-semibold">Open Price</div>
                      )}
                    </div>
                    <button
                      type="button"
                      className="w-7 h-7 rounded-lg bg-primary-50 group-hover:bg-primary-600 text-primary-600 group-hover:text-white flex items-center justify-center transition-colors shadow-sm"
                    >
                      <Plus className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              );
            })}
          </div>

          {filteredProducts.length === 0 && (
            <div className="h-64 flex flex-col items-center justify-center text-slate-400 text-center p-6">
              <ShoppingBag className="w-12 h-12 stroke-[1.2] mb-2 text-slate-300" />
              <p className="text-sm font-semibold">Tidak ada produk ditemukan</p>
              <p className="text-xs text-slate-400 mt-0.5">Coba gunakan kata kunci pencarian atau kategori lain</p>
            </div>
          )}
        </div>
      </div>

      {/* RIGHT SECTION: Cart & Checkout (Fixed 380px on desktop) */}
      <div className="w-full lg:w-[420px] bg-white flex flex-col h-full shadow-lg border-l border-slate-200">
        {/* Customer Header */}
        <div className="p-4 border-b border-slate-200 bg-slate-50 space-y-2.5">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <User className="w-4 h-4 text-primary-600" />
              <span className="text-xs font-bold text-slate-800">Pelanggan POS:</span>
            </div>
            {selectedCustomer?.type === 'member' && (
              <span className="text-[10px] font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full flex items-center gap-1">
                <Sparkles className="w-3 h-3 text-amber-600" />
                {selectedCustomer.points_balance} Poin
              </span>
            )}
          </div>

          <div className="flex gap-2">
            <select
              value={selectedCustomerId}
              onChange={e => setSelectedCustomerId(Number(e.target.value))}
              className="flex-1 px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              {customers.map(c => (
                <option key={c.id} value={c.id}>
                  {c.name} {c.type === 'member' ? `(Member: ${c.member_code})` : '(Umum)'}
                </option>
              ))}
            </select>
          </div>

          {customerGroup && customerGroup.discount_percent > 0 && (
            <div className="text-[11px] text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 flex items-center justify-between font-semibold">
              <span>Grup {customerGroup.name}</span>
              <span>Diskon Otomatis {customerGroup.discount_percent}%</span>
            </div>
          )}
        </div>

        {/* Cart Item List */}
        <div className="flex-1 overflow-y-auto p-4 space-y-2.5">
          {cart.map((item, idx) => (
            <div
              key={idx}
              className="p-3 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/90 space-y-2 transition-colors"
            >
              <div className="flex justify-between items-start gap-2">
                <div>
                  <h5 className="text-xs font-bold text-slate-800 leading-tight">{item.product.name}</h5>
                  <div className="text-[10px] text-slate-500 mt-0.5">
                    {item.product.product_code} • {item.unit_name}
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => removeFromCart(idx)}
                  className="p-1 text-slate-400 hover:text-red-600 rounded-lg"
                >
                  <Trash2 className="w-3.5 h-3.5" />
                </button>
              </div>

              {/* Price & Quantity Adjuster */}
              <div className="flex items-center justify-between pt-1">
                {/* Editable Price (Open Price support) */}
                {editingPriceIndex === idx ? (
                  <div className="flex items-center gap-1">
                    <input
                      type="number"
                      min="0"
                      value={editingPriceValue}
                      onChange={e => setEditingPriceValue(Number(e.target.value))}
                      className="w-24 px-1.5 py-0.5 text-xs font-bold border border-primary-500 rounded bg-white text-slate-900"
                    />
                    <button
                      type="button"
                      onClick={() => {
                        updateCartItemPrice(idx, editingPriceValue);
                        setEditingPriceIndex(null);
                      }}
                      className="p-1 bg-primary-600 text-white rounded text-[10px]"
                    >
                      <Check className="w-3 h-3" />
                    </button>
                  </div>
                ) : (
                  <div
                    onClick={() => {
                      if (item.product.allow_open_price) {
                        setEditingPriceIndex(idx);
                        setEditingPriceValue(item.price);
                      }
                    }}
                    className={`text-xs font-bold text-slate-800 ${
                      item.product.allow_open_price ? 'cursor-pointer hover:text-primary-600 underline decoration-dashed' : ''
                    }`}
                    title={item.product.allow_open_price ? 'Klik untuk ubah harga (Open Price)' : undefined}
                  >
                    Rp {item.price.toLocaleString('id-ID')}
                  </div>
                )}

                {/* Qty Controls */}
                <div className="flex items-center gap-1.5 bg-white border border-slate-300 rounded-lg p-0.5">
                  <button
                    type="button"
                    onClick={() => updateCartItemQty(idx, item.quantity - 1)}
                    className="w-6 h-6 rounded flex items-center justify-center text-slate-600 hover:bg-slate-100"
                  >
                    <Minus className="w-3 h-3" />
                  </button>
                  <span className="w-8 text-center text-xs font-bold text-slate-900">{item.quantity}</span>
                  <button
                    type="button"
                    onClick={() => updateCartItemQty(idx, item.quantity + 1)}
                    className="w-6 h-6 rounded flex items-center justify-center text-slate-600 hover:bg-slate-100"
                  >
                    <Plus className="w-3 h-3" />
                  </button>
                </div>

                <div className="text-xs font-black text-primary-700 min-w-[70px] text-right">
                  Rp {item.subtotal.toLocaleString('id-ID')}
                </div>
              </div>
            </div>
          ))}

          {cart.length === 0 && (
            <div className="h-48 flex flex-col items-center justify-center text-slate-400 text-center">
              <ShoppingBag className="w-10 h-10 stroke-[1.2] mb-1.5 text-slate-300" />
              <p className="text-xs font-semibold">Keranjang Masih Kosong</p>
              <p className="text-[11px] text-slate-400">Pilih produk di sebelah kiri atau scan barcode</p>
            </div>
          )}
        </div>

        {/* Footer Checkout Summary */}
        <div className="p-4 border-t border-slate-200 bg-slate-50 space-y-3">
          <div className="space-y-1.5 text-xs text-slate-600">
            <div className="flex justify-between">
              <span>Subtotal ({cart.reduce((s, i) => s + i.quantity, 0)} pcs):</span>
              <span className="font-semibold text-slate-800">Rp {subtotal.toLocaleString('id-ID')}</span>
            </div>
            {discountTotal > 0 && (
              <div className="flex justify-between text-emerald-700 font-semibold">
                <span>Total Diskon:</span>
                <span>-Rp {discountTotal.toLocaleString('id-ID')}</span>
              </div>
            )}
            <div className="pt-2 border-t border-slate-200 flex justify-between items-baseline font-bold text-base text-slate-900">
              <span>Total Tagihan:</span>
              <span className="text-xl text-primary-700 font-black">
                Rp {grandTotal.toLocaleString('id-ID')}
              </span>
            </div>
          </div>

          <div className="flex gap-2">
            <button
              type="button"
              disabled={cart.length === 0}
              onClick={clearCart}
              className="px-3.5 py-3 bg-slate-200 hover:bg-slate-300 disabled:opacity-50 text-slate-700 font-bold text-xs rounded-xl"
            >
              Reset
            </button>
            <button
              type="button"
              disabled={cart.length === 0}
              onClick={() => {
                if (!activeCashSession) {
                  setShowCashSessionModal(true);
                  return;
                }
                setShowPaymentModal(true);
              }}
              className="flex-1 py-3 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white font-bold text-sm rounded-xl shadow-lg shadow-primary-600/30 transition-all flex items-center justify-center gap-2"
            >
              <CreditCard className="w-4 h-4" />
              Bayar Sekarang (F12)
            </button>
          </div>
        </div>
      </div>

      {/* Payment Checkout Modal */}
      {showPaymentModal && (
        <PaymentModal
          onClose={() => setShowPaymentModal(false)}
          onSuccess={() => {
            setShowPaymentModal(false);
            setShowReceiptModal(true);
          }}
        />
      )}

      {/* Thermal Receipt Print Modal */}
      {showReceiptModal && lastCompletedSale && (
        <ThermalReceiptModal
          sale={lastCompletedSale}
          onClose={() => setShowReceiptModal(false)}
        />
      )}

      {/* Cash Session Open Drawer Modal */}
      {showCashSessionModal && (
        <CashSessionModal onClose={() => setShowCashSessionModal(false)} />
      )}
    </div>
  );
};
