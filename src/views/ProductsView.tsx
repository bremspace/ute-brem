import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import { Product } from '../types';
import {
  Plus,
  Search,
  Package,
  AlertTriangle,
  TrendingUp,
  Layers,
  Filter,
  Edit3,
  Trash2,
  Upload,
  Barcode,
  ChevronDown,
  X,
  Save,
  Eye,
  EyeOff,
  MapPin
} from 'lucide-react';

type FormState = {
  product_code: string;
  name: string;
  barcode: string;
  category_id: number | '';
  sub_category_id: number | '';
  brand_id: number | '';
  product_maker_id: number | '';
  product_type_id: number | '';
  supplier_id: number | '';
  unit_id: number | '';
  purchase_price: number;
  selling_price: number;
  stock_min: number;
  allow_open_price: boolean;
  has_serial_number: boolean;
  is_member_only: boolean;
  is_active: boolean;
  is_published: boolean;
  description: string;
};

const emptyForm: FormState = {
  product_code: '',
  name: '',
  barcode: '',
  category_id: '',
  sub_category_id: '',
  brand_id: '',
  product_maker_id: '',
  product_type_id: '',
  supplier_id: '',
  unit_id: '',
  purchase_price: 0,
  selling_price: 0,
  stock_min: 0,
  allow_open_price: false,
  has_serial_number: false,
  is_member_only: false,
  is_active: true,
  is_published: true,
  description: '',
};

export const ProductsView: React.FC = () => {
  const {
    products,
    setProducts,
    categories,
    subCategories,
    brands,
    makers,
    productTypes,
    units,
    suppliers,
    locations,
    selectedLocationId,
    setSelectedLocationId,
  } = useApp();

  const [search, setSearch] = useState('');
  const [filterCategory, setFilterCategory] = useState<number | 'all'>('all');
  const [filterBrand, setFilterBrand] = useState<number | 'all'>('all');
  const [filterStatus, setFilterStatus] = useState<'all' | 'active' | 'inactive'>('all');
  const [filterLowStock, setFilterLowStock] = useState(false);
  const [showForm, setShowForm] = useState(false);
  const [editId, setEditId] = useState<number | null>(null);
  const [form, setForm] = useState<FormState>(emptyForm);

  const filteredProducts = useMemo(() => {
    return products.filter(p => {
      const matchSearch =
        p.name.toLowerCase().includes(search.toLowerCase()) ||
        p.product_code.toLowerCase().includes(search.toLowerCase()) ||
        (p.barcode && p.barcode.includes(search));
      const matchCat = filterCategory === 'all' || p.category_id === filterCategory;
      const matchBrand = filterBrand === 'all' || p.brand_id === filterBrand;
      const matchStatus =
        filterStatus === 'all' ||
        (filterStatus === 'active' && p.is_active) ||
        (filterStatus === 'inactive' && !p.is_active);
      const matchLowStock = !filterLowStock || p.stock_global <= p.stock_min;
      return matchSearch && matchCat && matchBrand && matchStatus && matchLowStock;
    });
  }, [products, search, filterCategory, filterBrand, filterStatus, filterLowStock]);

  const totalProduk = products.length;
  const totalSkuAktif = products.filter(p => p.is_active).length;
  const stokMenipis = products.filter(p => p.stock_global <= p.stock_min).length;
  const nilaiTotalStok = products.reduce((sum, p) => sum + p.stock_global * p.purchase_price, 0);

  const selectedLocName = locations.find(l => l.id === selectedLocationId)?.name || 'Global';

  const filteredSubCategories = useMemo(() => {
    if (!form.category_id) return subCategories;
    return subCategories.filter(sc => sc.category_id === form.category_id);
  }, [subCategories, form.category_id]);

  function openAdd() {
    setEditId(null);
    setForm({ ...emptyForm, product_code: `PRD-${Date.now().toString().slice(-6)}` });
    setShowForm(true);
  }

  function openEdit(p: Product) {
    setEditId(p.id);
    setForm({
      product_code: p.product_code,
      name: p.name,
      barcode: p.barcode || '',
      category_id: p.category_id || '',
      sub_category_id: p.sub_category_id || '',
      brand_id: p.brand_id || '',
      product_maker_id: p.product_maker_id || '',
      product_type_id: p.product_type_id || '',
      supplier_id: p.supplier_id || '',
      unit_id: p.unit_id || '',
      purchase_price: p.purchase_price,
      selling_price: p.selling_price,
      stock_min: p.stock_min,
      allow_open_price: p.allow_open_price,
      has_serial_number: p.has_serial_number,
      is_member_only: p.is_member_only,
      is_active: p.is_active,
      is_published: p.is_published,
      description: p.description || '',
    });
    setShowForm(true);
  }

  function handleSave() {
    if (!form.name.trim()) return;
    const code = form.product_code.trim() || `PRD-${Date.now().toString().slice(-6)}`;
    const cat = categories.find(c => c.id === form.category_id);
    const subCat = subCategories.find(sc => sc.id === form.sub_category_id);
    const brand = brands.find(b => b.id === form.brand_id);
    const maker = makers.find(m => m.id === form.product_maker_id);
    const pType = productTypes.find(pt => pt.id === form.product_type_id);
    const supplier = suppliers.find(s => s.id === form.supplier_id);
    const unit = units.find(u => u.id === form.unit_id);

    if (editId !== null) {
      setProducts(prev =>
        prev.map(p => {
          if (p.id !== editId) return p;
          return {
            ...p,
            product_code: code,
            name: form.name.trim(),
            barcode: form.barcode || undefined,
            category_id: (form.category_id as number) || undefined,
            category: cat,
            sub_category_id: (form.sub_category_id as number) || undefined,
            sub_category: subCat,
            brand_id: (form.brand_id as number) || undefined,
            brand,
            product_maker_id: (form.product_maker_id as number) || undefined,
            maker,
            product_type_id: (form.product_type_id as number) || undefined,
            product_type: pType,
            supplier_id: (form.supplier_id as number) || undefined,
            supplier,
            unit_id: (form.unit_id as number) || undefined,
            unit,
            purchase_price: form.purchase_price,
            selling_price: form.selling_price,
            stock_min: form.stock_min,
            allow_open_price: form.allow_open_price,
            has_serial_number: form.has_serial_number,
            is_member_only: form.is_member_only,
            is_active: form.is_active,
            is_published: form.is_published,
            description: form.description || undefined,
            updated_at: new Date().toISOString(),
          };
        })
      );
    } else {
      const newProduct: Product = {
        id: Date.now(),
        product_code: code,
        name: form.name.trim(),
        barcode: form.barcode || undefined,
        category_id: (form.category_id as number) || undefined,
        category: cat,
        sub_category_id: (form.sub_category_id as number) || undefined,
        sub_category: subCat,
        brand_id: (form.brand_id as number) || undefined,
        brand,
        product_maker_id: (form.product_maker_id as number) || undefined,
        maker,
        product_type_id: (form.product_type_id as number) || undefined,
        product_type: pType,
        supplier_id: (form.supplier_id as number) || undefined,
        supplier,
        unit_id: (form.unit_id as number) || undefined,
        unit,
        purchase_price: form.purchase_price,
        selling_price: form.selling_price,
        stock_min: form.stock_min,
        stock_global: 0,
        allow_open_price: form.allow_open_price,
        has_serial_number: form.has_serial_number,
        is_member_only: form.is_member_only,
        is_active: form.is_active,
        is_published: form.is_published,
        description: form.description || undefined,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
      };
      setProducts(prev => [newProduct, ...prev]);
    }
    setShowForm(false);
    setEditId(null);
  }

  function handleDelete(id: number) {
    if (!confirm('Yakin ingin menghapus produk ini?')) return;
    setProducts(prev => prev.filter(p => p.id !== id));
  }

  function toggleActive(id: number) {
    setProducts(prev =>
      prev.map(p => (p.id === id ? { ...p, is_active: !p.is_active } : p))
    );
  }

  function togglePublished(id: number) {
    setProducts(prev =>
      prev.map(p => (p.id === id ? { ...p, is_published: !p.is_published } : p))
    );
  }

  function updateField<K extends keyof FormState>(key: K, value: FormState[K]) {
    setForm(prev => {
      const next = { ...prev, [key]: value };
      if (key === 'category_id') next.sub_category_id = '';
      return next;
    });
  }

  function formatRp(n: number) {
    return `Rp ${n.toLocaleString('id-ID')}`;
  }

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* Page Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-slate-900 tracking-tight">Master Produk & Stok</h1>
          <p className="text-xs text-slate-500 mt-1">
            Kelola data produk, harga beli/jual, dan pantau stok per lokasi.
          </p>
        </div>
        <div className="flex items-center gap-2 flex-wrap">
          <button
            onClick={openAdd}
            className="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/30 transition-all flex items-center gap-1.5"
          >
            <Plus className="w-4 h-4" />
            Tambah Produk
          </button>
          <button className="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5">
            <Upload className="w-4 h-4" />
            Import Produk
          </button>
          <button className="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5">
            <Barcode className="w-4 h-4" />
            Cetak Barcode
          </button>
        </div>
      </div>

      {/* Stat Cards */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div className="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm">
          <div className="flex items-center justify-between mb-2">
            <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Produk</span>
            <div className="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
              <Package className="w-3.5 h-3.5" />
            </div>
          </div>
          <div className="text-xl font-black text-slate-900">{totalProduk}</div>
        </div>
        <div className="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm">
          <div className="flex items-center justify-between mb-2">
            <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">SKU Aktif</span>
            <div className="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <Eye className="w-3.5 h-3.5" />
            </div>
          </div>
          <div className="text-xl font-black text-emerald-700">{totalSkuAktif}</div>
        </div>
        <div className="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm">
          <div className="flex items-center justify-between mb-2">
            <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Stok Menipis</span>
            <div className="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
              <AlertTriangle className="w-3.5 h-3.5" />
            </div>
          </div>
          <div className="text-xl font-black text-amber-600">{stokMenipis}</div>
        </div>
        <div className="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm">
          <div className="flex items-center justify-between mb-2">
            <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Nilai Total Stok</span>
            <div className="w-7 h-7 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center">
              <TrendingUp className="w-3.5 h-3.5" />
            </div>
          </div>
          <div className="text-lg font-black text-slate-900">{formatRp(nilaiTotalStok)}</div>
        </div>
      </div>

      {/* Location Selector & Filter Bar */}
      <div className="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm space-y-3">
        <div className="flex flex-wrap items-center gap-3">
          <div className="flex items-center gap-2">
            <MapPin className="w-4 h-4 text-primary-600" />
            <span className="text-xs font-bold text-slate-700">Lokasi Stok:</span>
            <select
              value={selectedLocationId}
              onChange={e => setSelectedLocationId(Number(e.target.value))}
              className="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              {locations.filter(l => l.is_active).map(l => (
                <option key={l.id} value={l.id}>
                  {l.name}
                </option>
              ))}
            </select>
          </div>

          <div className="h-5 w-px bg-slate-200 hidden sm:block" />

          <div className="relative flex-1 min-w-[200px]">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
            <input
              type="text"
              value={search}
              onChange={e => setSearch(e.target.value)}
              placeholder="Cari nama, kode, barcode..."
              className="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <select
            value={filterCategory}
            onChange={e => setFilterCategory(e.target.value === 'all' ? 'all' : Number(e.target.value))}
            className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="all">Semua Kategori</option>
            {categories.map(c => (
              <option key={c.id} value={c.id}>
                {c.name}
              </option>
            ))}
          </select>

          <select
            value={filterBrand}
            onChange={e => setFilterBrand(e.target.value === 'all' ? 'all' : Number(e.target.value))}
            className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="all">Semua Brand</option>
            {brands.map(b => (
              <option key={b.id} value={b.id}>
                {b.name}
              </option>
            ))}
          </select>

          <select
            value={filterStatus}
            onChange={e => setFilterStatus(e.target.value as 'all' | 'active' | 'inactive')}
            className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="all">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
          </select>

          <button
            onClick={() => setFilterLowStock(!filterLowStock)}
            className={`px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 ${
              filterLowStock
                ? 'bg-amber-100 border border-amber-300 text-amber-800'
                : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100'
            }`}
          >
            <AlertTriangle className="w-3.5 h-3.5" />
            Stok Menipis
          </button>
        </div>
      </div>

      {/* Products Table */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div className="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
          <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
            <Layers className="w-4 h-4 text-primary-600" />
            Daftar Produk
            <span className="text-[10px] font-semibold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">
              {filteredProducts.length}
            </span>
          </div>
          <span className="text-[10px] text-slate-400">
            Menampilkan stok: <span className="font-bold text-primary-600">{selectedLocName}</span>
          </span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
              <tr>
                <th className="px-4 py-3">Kode</th>
                <th className="px-4 py-3">Nama Produk</th>
                <th className="px-4 py-3">Kategori</th>
                <th className="px-4 py-3">Brand</th>
                <th className="px-4 py-3">Supplier</th>
                <th className="px-4 py-3 text-right">Harga Beli</th>
                <th className="px-4 py-3 text-right">Harga Jual</th>
                <th className="px-4 py-3 text-center">Stok</th>
                <th className="px-4 py-3 text-center">Status</th>
                <th className="px-4 py-3 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 text-slate-700">
              {filteredProducts.map(p => {
                const locStock =
                  p.stocks?.find(s => s.location_id === selectedLocationId)?.quantity ?? 0;
                const isLow = p.stock_global <= p.stock_min;

                return (
                  <tr key={p.id} className="hover:bg-slate-50/70 transition-colors">
                    <td className="px-4 py-3 font-mono font-bold text-primary-700 text-[11px]">
                      {p.product_code}
                    </td>
                    <td className="px-4 py-3">
                      <div className="font-bold text-slate-800 line-clamp-1">{p.name}</div>
                      <div className="text-[10px] text-slate-400 mt-0.5">
                        {[p.maker?.name, p.product_type?.name].filter(Boolean).join(' • ') || '—'}
                      </div>
                    </td>
                    <td className="px-4 py-3 text-slate-600">{p.category?.name || '—'}</td>
                    <td className="px-4 py-3 text-slate-600">{p.brand?.name || '—'}</td>
                    <td className="px-4 py-3 text-slate-600">{p.supplier?.name || '—'}</td>
                    <td className="px-4 py-3 text-right font-semibold text-slate-700">
                      {formatRp(p.purchase_price)}
                    </td>
                    <td className="px-4 py-3 text-right font-bold text-slate-900">
                      {formatRp(p.selling_price)}
                    </td>
                    <td className="px-4 py-3 text-center">
                      <div className="flex flex-col items-center gap-0.5">
                        <span
                          className={`text-xs font-black ${
                            locStock <= 0
                              ? 'text-red-600'
                              : isLow
                              ? 'text-amber-600'
                              : 'text-emerald-700'
                          }`}
                        >
                          {locStock}
                        </span>
                        <span className="text-[9px] text-slate-400">
                          G: {p.stock_global}
                        </span>
                      </div>
                    </td>
                    <td className="px-4 py-3 text-center">
                      <div className="flex items-center justify-center gap-1.5">
                        <button
                          onClick={() => toggleActive(p.id)}
                          className={`px-1.5 py-0.5 rounded text-[9px] font-bold transition-colors ${
                            p.is_active
                              ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                              : 'bg-slate-100 text-slate-500 hover:bg-slate-200'
                          }`}
                          title={p.is_active ? 'Nonaktifkan' : 'Aktifkan'}
                        >
                          {p.is_active ? 'AKTIF' : 'NONAKTIF'}
                        </button>
                        {p.is_published ? (
                          <Eye className="w-3 h-3 text-emerald-500" />
                        ) : (
                          <EyeOff className="w-3 h-3 text-slate-400" />
                        )}
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex items-center justify-center gap-1">
                        <button
                          onClick={() => openEdit(p)}
                          className="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors"
                          title="Edit"
                        >
                          <Edit3 className="w-3.5 h-3.5" />
                        </button>
                        <button
                          onClick={() => handleDelete(p.id)}
                          className="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                          title="Hapus"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                );
              })}
              {filteredProducts.length === 0 && (
                <tr>
                  <td colSpan={10} className="px-5 py-12 text-center text-slate-400">
                    <Package className="w-10 h-10 mx-auto mb-2 text-slate-300" />
                    <p className="text-xs font-semibold">Tidak ada produk ditemukan</p>
                    <p className="text-[10px] text-slate-400 mt-0.5">Ubah filter atau tambah produk baru</p>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Form Tambah / Edit Produk */}
      {showForm && (
        <div className="fixed inset-0 z-50 flex items-start justify-center pt-10 px-4">
          <div className="fixed inset-0 bg-black/40 backdrop-blur-sm" onClick={() => setShowForm(false)} />
          <div className="relative bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-y-auto">
            {/* Form Header */}
            <div className="sticky top-0 bg-white z-10 px-6 py-4 border-b border-slate-100 flex items-center justify-between rounded-t-2xl">
              <h3 className="text-sm font-black text-slate-900">
                {editId !== null ? 'Edit Produk' : 'Tambah Produk Baru'}
              </h3>
              <button
                onClick={() => setShowForm(false)}
                className="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <div className="p-6 space-y-4">
              {/* Kode & Nama */}
              <div className="grid grid-cols-3 gap-3">
                <div>
                  <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Kode Produk</label>
                  <input
                    type="text"
                    value={form.product_code}
                    onChange={e => updateField('product_code', e.target.value)}
                    placeholder="PRD-XXXXXX"
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  />
                </div>
                <div className="col-span-2">
                  <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nama Produk *</label>
                  <input
                    type="text"
                    value={form.name}
                    onChange={e => updateField('name', e.target.value)}
                    placeholder="Nama produk"
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  />
                </div>
              </div>

              {/* Barcode */}
              <div>
                <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Barcode</label>
                <input
                  type="text"
                  value={form.barcode}
                  onChange={e => updateField('barcode', e.target.value)}
                  placeholder="Barcode produk"
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>

              {/* Relations */}
              <div className="grid grid-cols-3 gap-3">
                <div>
                  <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Kategori</label>
                  <select
                    value={form.category_id}
                    onChange={e => updateField('category_id', e.target.value ? Number(e.target.value) : '')}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">— Pilih —</option>
                    {categories.map(c => (
                      <option key={c.id} value={c.id}>{c.name}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Sub Kategori</label>
                  <select
                    value={form.sub_category_id}
                    onChange={e => updateField('sub_category_id', e.target.value ? Number(e.target.value) : '')}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">— Pilih —</option>
                    {filteredSubCategories.map(sc => (
                      <option key={sc.id} value={sc.id}>{sc.name}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Brand</label>
                  <select
                    value={form.brand_id}
                    onChange={e => updateField('brand_id', e.target.value ? Number(e.target.value) : '')}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">— Pilih —</option>
                    {brands.map(b => (
                      <option key={b.id} value={b.id}>{b.name}</option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-3 gap-3">
                <div>
                  <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Maker / Pabrikan</label>
                  <select
                    value={form.product_maker_id}
                    onChange={e => updateField('product_maker_id', e.target.value ? Number(e.target.value) : '')}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">— Pilih —</option>
                    {makers.map(m => (
                      <option key={m.id} value={m.id}>{m.name}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tipe Produk</label>
                  <select
                    value={form.product_type_id}
                    onChange={e => updateField('product_type_id', e.target.value ? Number(e.target.value) : '')}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">— Pilih —</option>
                    {productTypes.map(pt => (
                      <option key={pt.id} value={pt.id}>{pt.name}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Supplier</label>
                  <select
                    value={form.supplier_id}
                    onChange={e => updateField('supplier_id', e.target.value ? Number(e.target.value) : '')}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">— Pilih —</option>
                    {suppliers.map(s => (
                      <option key={s.id} value={s.id}>{s.name}</option>
                    ))}
                  </select>
                </div>
              </div>

              {/* Unit & Prices */}
              <div className="grid grid-cols-3 gap-3">
                <div>
                  <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Satuan</label>
                  <select
                    value={form.unit_id}
                    onChange={e => updateField('unit_id', e.target.value ? Number(e.target.value) : '')}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">— Pilih —</option>
                    {units.map(u => (
                      <option key={u.id} value={u.id}>{u.name}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga Beli (Rp)</label>
                  <input
                    type="number"
                    min={0}
                    value={form.purchase_price}
                    onChange={e => updateField('purchase_price', Number(e.target.value))}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  />
                </div>
                <div>
                  <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga Jual (Rp)</label>
                  <input
                    type="number"
                    min={0}
                    value={form.selling_price}
                    onChange={e => updateField('selling_price', Number(e.target.value))}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  />
                </div>
              </div>

              {/* Stock Min */}
              <div className="w-1/3">
                <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Stok Minimum</label>
                <input
                  type="number"
                  min={0}
                  value={form.stock_min}
                  onChange={e => updateField('stock_min', Number(e.target.value))}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>

              {/* Checkboxes */}
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                {[
                  { key: 'allow_open_price' as const, label: 'Open Price' },
                  { key: 'has_serial_number' as const, label: 'Serial Number' },
                  { key: 'is_member_only' as const, label: 'Member Only' },
                  { key: 'is_active' as const, label: 'Aktif' },
                ].map(cb => (
                  <label key={cb.key} className="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                    <input
                      type="checkbox"
                      checked={form[cb.key]}
                      onChange={e => updateField(cb.key, e.target.checked)}
                      className="w-3.5 h-3.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                    />
                    <span className="text-xs font-bold text-slate-700">{cb.label}</span>
                  </label>
                ))}
              </div>
              <label className="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors w-fit">
                <input
                  type="checkbox"
                  checked={form.is_published}
                  onChange={e => updateField('is_published', e.target.checked)}
                  className="w-3.5 h-3.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                />
                <span className="text-xs font-bold text-slate-700">Published (Tampil di POS)</span>
              </label>

              {/* Description */}
              <div>
                <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">Deskripsi</label>
                <textarea
                  rows={3}
                  value={form.description}
                  onChange={e => updateField('description', e.target.value)}
                  placeholder="Deskripsi produk (opsional)"
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
                />
              </div>
            </div>

            {/* Form Footer */}
            <div className="sticky bottom-0 bg-white z-10 px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-2 rounded-b-2xl">
              <button
                onClick={() => setShowForm(false)}
                className="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all"
              >
                Batal
              </button>
              <button
                onClick={handleSave}
                className="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/30 transition-all flex items-center gap-1.5"
              >
                <Save className="w-3.5 h-3.5" />
                {editId !== null ? 'Simpan Perubahan' : 'Tambah Produk'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
