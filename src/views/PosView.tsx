import React, { useState, useRef, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { clsx } from 'clsx';
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
import { Button, Card, Input, Select, Badge } from '../components/ui';
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

  // F12 keyboard shortcut for payment
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'F12') {
        e.preventDefault();
        if (cart.length === 0) return;
        if (!activeCashSession) {
          setShowCashSessionModal(true);
          return;
        }
        setShowPaymentModal(true);
      }
    };
    document.addEventListener('keydown', handleKeyDown);
    return () => document.removeEventListener('keydown', handleKeyDown);
  }, [cart.length, activeCashSession]);

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

  const handlePayClick = () => {
    if (cart.length === 0) return;
    if (!activeCashSession) {
      setShowCashSessionModal(true);
      return;
    }
    setShowPaymentModal(true);
  };

  const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.price), 0);
  const discountTotal = cart.reduce((sum, item) => sum + (item.discount_amount || 0), 0);
  const grandTotal = Math.max(0, subtotal - discountTotal);
  const totalItems = cart.reduce((s, i) => s + i.quantity, 0);

  return (
    <div className="h-[calc(100vh-4rem)] flex flex-col lg:flex-row overflow-hidden bg-surface-100">
      {/* LEFT SECTION: Catalog & Search */}
      <div className="flex-1 flex flex-col min-w-0 border-r border-surface-200 bg-white">
        {/* Top Control Bar: Barcode scanner + Search input */}
        <div className="p-4 border-b border-surface-200 bg-surface-50 space-y-3">
          <div className="flex items-center gap-3">
            {/* Fast Barcode Scanner input */}
            <form onSubmit={handleBarcodeSubmit} className="flex-1">
              <Input
                ref={barcodeInputRef}
                type="text"
                value={barcodeInput}
                onChange={e => setBarcodeInput(e.target.value)}
                placeholder="Scan Barcode / Tekan Enter..."
                leftIcon={<Barcode className="w-4 h-4" />}
              />
            </form>

            {/* General Search */}
            <div className="flex-1">
              <Input
                type="text"
                value={searchTerm}
                onChange={e => setSearchTerm(e.target.value)}
                placeholder="Cari nama produk, tipe HP, kode..."
                leftIcon={<Search className="w-4 h-4" />}
              />
            </div>
          </div>

          {/* Category Pills */}
          <div className="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <Button
              variant={selectedCategory === 'all' ? 'primary' : 'ghost'}
              size="xs"
              onClick={() => setSelectedCategory('all')}
              className="whitespace-nowrap"
            >
              Semua Kategori
            </Button>
            {categories.map(c => (
              <Button
                key={c.id}
                variant={selectedCategory === c.id ? 'primary' : 'ghost'}
                size="xs"
                onClick={() => setSelectedCategory(c.id)}
                className="whitespace-nowrap"
              >
                {c.name}
              </Button>
            ))}
          </div>

          {/* Brand Pills */}
          <div className="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <Button
              variant={selectedBrand === 'all' ? 'primary' : 'ghost'}
              size="xs"
              onClick={() => setSelectedBrand('all')}
              className="whitespace-nowrap"
            >
              Semua Brand
            </Button>
            {brands.map(b => (
              <Button
                key={b.id}
                variant={selectedBrand === b.id ? 'primary' : 'ghost'}
                size="xs"
                onClick={() => setSelectedBrand(b.id)}
                className="whitespace-nowrap"
              >
                {b.name}
              </Button>
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
                <Card
                  key={product.id}
                  interactive
                  noPadding
                  onClick={() => addToCart(product)}
                  className="p-3.5 flex flex-col justify-between group active:scale-[0.98]"
                >
                  <div>
                    {/* Header tags */}
                    <div className="flex items-center justify-between gap-1 mb-1.5">
                      <Badge variant="neutral" className="font-mono truncate max-w-[120px]">
                        {product.product_code}
                      </Badge>
                      <Badge
                        variant={
                          currentStock > 0
                            ? isLowStock ? 'warning' : 'success'
                            : 'danger'
                        }
                      >
                        Stok: {currentStock}
                      </Badge>
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
                    <Button
                      variant="primary"
                      size="xs"
                      icon={<Plus className="w-4 h-4" />}
                      className="!w-7 !h-7 !p-0 !rounded-lg"
                    />
                  </div>
                </Card>
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

      {/* RIGHT SECTION: Cart & Checkout (Fixed 420px on desktop) */}
      <div className="w-full lg:w-[420px] bg-white flex flex-col h-full shadow-lg border-l border-surface-200">
        {/* Customer Header */}
        <div className="p-4 border-b border-surface-200 bg-surface-50 space-y-2.5">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <User className="w-4 h-4 text-primary-600" />
              <span className="text-xs font-bold text-slate-800">Pelanggan POS:</span>
            </div>
            {selectedCustomer?.type === 'member' && (
              <Badge variant="amber" pill>
                <Sparkles className="w-3 h-3 text-amber-600 mr-1" />
                {selectedCustomer.points_balance} Poin
              </Badge>
            )}
          </div>

          <Select
            value={selectedCustomerId}
            onChange={e => setSelectedCustomerId(Number(e.target.value))}
            options={customers.map(c => ({
              value: c.id,
              label: `${c.name} ${c.type === 'member' ? `(Member: ${c.member_code})` : '(Umum)'}`
            }))}
            wrapperClassName="flex-1"
          />

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
            <Card key={idx} noPadding className="p-3 space-y-2 hover:bg-slate-50 transition-colors !rounded-xl">
              <div className="flex justify-between items-start gap-2">
                <div>
                  <h5 className="text-xs font-bold text-slate-800 leading-tight">{item.product.name}</h5>
                  <div className="text-[10px] text-slate-500 mt-0.5">
                    {item.product.product_code} • {item.unit_name}
                  </div>
                </div>
                <Button
                  variant="ghost"
                  size="xs"
                  onClick={() => removeFromCart(idx)}
                  icon={<Trash2 className="w-3.5 h-3.5" />}
                  className="!p-1 text-slate-400 hover:text-red-600"
                />
              </div>

              {/* Price & Quantity Adjuster */}
              <div className="flex items-center justify-between pt-1">
                {/* Editable Price (Open Price support) */}
                {editingPriceIndex === idx ? (
                  <div className="flex items-center gap-1">
                    <Input
                      type="number"
                      min="0"
                      value={editingPriceValue}
                      onChange={e => setEditingPriceValue(Number(e.target.value))}
                      className="!w-24 !py-0.5 !text-xs !font-bold"
                    />
                    <Button
                      variant="primary"
                      size="xs"
                      icon={<Check className="w-3 h-3" />}
                      onClick={() => {
                        updateCartItemPrice(idx, editingPriceValue);
                        setEditingPriceIndex(null);
                      }}
                      className="!p-1 !text-[10px]"
                    />
                  </div>
                ) : (
                  <div
                    onClick={() => {
                      if (item.product.allow_open_price) {
                        setEditingPriceIndex(idx);
                        setEditingPriceValue(item.price);
                      }
                    }}
                    className={clsx(
                      'text-xs font-bold text-slate-800',
                      item.product.allow_open_price && 'cursor-pointer hover:text-primary-600 underline decoration-dashed'
                    )}
                    title={item.product.allow_open_price ? 'Klik untuk ubah harga (Open Price)' : undefined}
                  >
                    Rp {item.price.toLocaleString('id-ID')}
                  </div>
                )}

                {/* Qty Controls */}
                <div className="flex items-center gap-1.5 bg-white border border-slate-200 rounded-lg p-0.5">
                  <Button
                    variant="ghost"
                    size="xs"
                    onClick={() => updateCartItemQty(idx, item.quantity - 1)}
                    icon={<Minus className="w-3 h-3" />}
                    className="!w-6 !h-6 !p-0 !rounded"
                  />
                  <span className="w-8 text-center text-xs font-bold text-slate-900">{item.quantity}</span>
                  <Button
                    variant="ghost"
                    size="xs"
                    onClick={() => updateCartItemQty(idx, item.quantity + 1)}
                    icon={<Plus className="w-3 h-3" />}
                    className="!w-6 !h-6 !p-0 !rounded"
                  />
                </div>

                <div className="text-xs font-black text-primary-700 min-w-[70px] text-right">
                  Rp {item.subtotal.toLocaleString('id-ID')}
                </div>
              </div>
            </Card>
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
        <div className="p-4 border-t border-surface-200 bg-surface-50 space-y-3">
          <div className="space-y-1.5 text-xs text-slate-600">
            <div className="flex justify-between">
              <span>Subtotal ({totalItems} pcs):</span>
              <span className="font-semibold text-slate-800">Rp {subtotal.toLocaleString('id-ID')}</span>
            </div>
            {discountTotal > 0 && (
              <div className="flex justify-between text-emerald-700 font-semibold">
                <span>Total Diskon:</span>
                <span>-Rp {discountTotal.toLocaleString('id-ID')}</span>
              </div>
            )}
            <div className="pt-2 border-t border-surface-200 flex justify-between items-baseline font-bold text-base text-slate-900">
              <span>Total Tagihan:</span>
              <span className="text-xl text-primary-700 font-black">
                Rp {grandTotal.toLocaleString('id-ID')}
              </span>
            </div>
          </div>

          <div className="flex gap-2">
            <Button
              variant="ghost"
              size="md"
              disabled={cart.length === 0}
              onClick={clearCart}
              className="!px-3.5 !py-3 !bg-slate-200 hover:!bg-slate-300 !text-slate-700"
            >
              Reset
            </Button>
            <Button
              variant="primary"
              size="lg"
              disabled={cart.length === 0}
              onClick={handlePayClick}
              icon={<CreditCard className="w-4 h-4" />}
              className="flex-1"
            >
              Bayar Sekarang (F12)
            </Button>
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
