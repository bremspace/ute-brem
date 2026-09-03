import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import {
  Globe,
  Package,
  Search,
  Store,
  Eye,
  ToggleLeft,
  ToggleRight,
  ShoppingBag,
  Filter,
  MapPin,
  ExternalLink,
  AlertTriangle,
} from 'lucide-react';

export const WebsiteCatalogView: React.FC = () => {
  const {
    products,
    setProducts,
    categories,
    brands,
    locations,
    selectedLocationId,
    setSelectedLocationId,
    currentUser,
  } = useApp();

  const [searchTerm, setSearchTerm] = useState('');
  const [filterCategory, setFilterCategory] = useState<number | 'all'>('all');
  const [filterBrand, setFilterBrand] = useState<number | 'all'>('all');
  const [filterStatus, setFilterStatus] = useState<'all' | 'published' | 'unpublished'>('all');
  const [previewSearch, setPreviewSearch] = useState('');
  const [previewCategory, setPreviewCategory] = useState<number | 'all'>('all');

  const storeName = currentUser?.branch?.name || 'UTE PARTS';

  const togglePublished = (id: number) => {
    setProducts(prev =>
      prev.map(p => (p.id === id ? { ...p, is_published: !p.is_published } : p))
    );
  };

  const toggleActive = (id: number) => {
    setProducts(prev =>
      prev.map(p => (p.id === id ? { ...p, is_active: !p.is_active } : p))
    );
  };

  const getStockQty = (p: typeof products[0]) => {
    const locStock = p.stocks?.find(s => s.location_id === selectedLocationId)?.quantity;
    return locStock !== undefined ? locStock : p.stock_global;
  };

  const filteredProducts = useMemo(() => {
    return products.filter(p => {
      const matchSearch =
        p.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        p.product_code.toLowerCase().includes(searchTerm.toLowerCase());
      const matchCat = filterCategory === 'all' || p.category_id === filterCategory;
      const matchBrand = filterBrand === 'all' || p.brand_id === filterBrand;
      let matchStatus = true;
      if (filterStatus === 'published') matchStatus = p.is_published && p.is_active;
      if (filterStatus === 'unpublished') matchStatus = !p.is_published;
      return matchSearch && matchCat && matchBrand && matchStatus;
    });
  }, [products, searchTerm, filterCategory, filterBrand, filterStatus]);

  const totalPublished = products.filter(p => p.is_published && p.is_active).length;
  const totalUnpublished = products.filter(p => !p.is_published).length;
  const lowStockPublished = products.filter(
    p => p.is_published && p.is_active && p.stock_global <= p.stock_min
  ).length;

  const previewProducts = useMemo(() => {
    return products.filter(p => {
      if (!p.is_published || !p.is_active) return false;
      const matchSearch =
        !previewSearch ||
        p.name.toLowerCase().includes(previewSearch.toLowerCase()) ||
        p.product_code.toLowerCase().includes(previewSearch.toLowerCase());
      const matchCat = previewCategory === 'all' || p.category_id === previewCategory;
      return matchSearch && matchCat;
    });
  }, [products, previewSearch, previewCategory]);

  return (
    <div className="p-6 space-y-6 max-w-[1600px] mx-auto">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <Globe className="w-6 h-6 text-primary-600" />
            Katalog Website
          </h1>
          <p className="text-xs text-slate-500 mt-1">
            Hanya produk dengan status <span className="font-bold text-emerald-600">Terpublikasi</span> &{' '}
            <span className="font-bold text-emerald-600">Aktif</span> yang tampil di situs web.
          </p>
        </div>
      </div>

      <div className="flex flex-col xl:flex-row gap-6">
        {/* ─── Panel 1: Manajemen Katalog Publik ─── */}
        <div className="flex-1 min-w-0 space-y-4">
          <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
              <Package className="w-4 h-4 text-primary-600" />
              <span className="text-sm font-bold text-slate-800">Manajemen Katalog Publik</span>
            </div>

            {/* Summary Cards */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4">
              <div className="bg-slate-50 rounded-xl border border-slate-200/80 p-3">
                <div className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Produk</div>
                <div className="text-lg font-black text-slate-900 mt-1">{products.length}</div>
              </div>
              <div className="bg-emerald-50 rounded-xl border border-emerald-200/80 p-3">
                <div className="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Terpublikasi</div>
                <div className="text-lg font-black text-emerald-700 mt-1">{totalPublished}</div>
              </div>
              <div className="bg-slate-50 rounded-xl border border-slate-200/80 p-3">
                <div className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tidak Terpublikasi</div>
                <div className="text-lg font-black text-slate-600 mt-1">{totalUnpublished}</div>
              </div>
              <div className="bg-amber-50 rounded-xl border border-amber-200/80 p-3">
                <div className="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Stok Menipis (Published)</div>
                <div className="text-lg font-black text-amber-700 mt-1">{lowStockPublished}</div>
              </div>
            </div>

            {/* Filter Bar */}
            <div className="px-4 pb-4 space-y-3">
              <div className="flex flex-col sm:flex-row gap-3">
                {/* Search */}
                <div className="relative flex-1">
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <Search className="w-4 h-4" />
                  </div>
                  <input
                    type="text"
                    value={searchTerm}
                    onChange={e => setSearchTerm(e.target.value)}
                    placeholder="Cari nama / kode produk..."
                    className="w-full pl-9 pr-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm"
                  />
                </div>

                {/* Location selector */}
                <div className="relative">
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <MapPin className="w-4 h-4" />
                  </div>
                  <select
                    value={selectedLocationId}
                    onChange={e => setSelectedLocationId(Number(e.target.value))}
                    className="pl-9 pr-8 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm appearance-none"
                  >
                    {locations.map(l => (
                      <option key={l.id} value={l.id}>{l.name}</option>
                    ))}
                  </select>
                </div>

                {/* Category */}
                <select
                  value={filterCategory}
                  onChange={e => setFilterCategory(e.target.value === 'all' ? 'all' : Number(e.target.value))}
                  className="px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm"
                >
                  <option value="all">Semua Kategori</option>
                  {categories.map(c => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                  ))}
                </select>

                {/* Brand */}
                <select
                  value={filterBrand}
                  onChange={e => setFilterBrand(e.target.value === 'all' ? 'all' : Number(e.target.value))}
                  className="px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm"
                >
                  <option value="all">Semua Merek</option>
                  {brands.map(b => (
                    <option key={b.id} value={b.id}>{b.name}</option>
                  ))}
                </select>

                {/* Status */}
                <select
                  value={filterStatus}
                  onChange={e => setFilterStatus(e.target.value as typeof filterStatus)}
                  className="px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm"
                >
                  <option value="all">Semua Status</option>
                  <option value="published">Terpublikasi</option>
                  <option value="unpublished">Tidak Terpublikasi</option>
                </select>
              </div>
            </div>
          </div>

          {/* Product Table */}
          <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
                  <tr>
                    <th className="px-4 py-3">Kode</th>
                    <th className="px-4 py-3">Nama</th>
                    <th className="px-4 py-3">Kategori</th>
                    <th className="px-4 py-3 text-right">Harga</th>
                    <th className="px-4 py-3 text-right">Stok</th>
                    <th className="px-4 py-3">Status</th>
                    <th className="px-4 py-3 text-center">Aktif</th>
                    <th className="px-4 py-3 text-center">Publish</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 text-slate-700">
                  {filteredProducts.map(p => {
                    const stock = getStockQty(p);
                    const isLowStock = p.is_published && p.is_active && stock <= p.stock_min;
                    return (
                      <tr key={p.id} className="hover:bg-slate-50/70 transition-colors">
                        <td className="px-4 py-3 font-mono font-bold text-slate-600">{p.product_code}</td>
                        <td className="px-4 py-3">
                          <div className="font-bold text-slate-800 line-clamp-1">{p.name}</div>
                          <div className="text-[10px] text-slate-400">
                            {p.brand?.name || ''}{p.brand && p.maker ? ' • ' : ''}{p.maker?.name || ''}
                          </div>
                        </td>
                        <td className="px-4 py-3 text-slate-600">{p.category?.name || '-'}</td>
                        <td className="px-4 py-3 text-right font-bold text-slate-800">
                          Rp {p.selling_price.toLocaleString('id-ID')}
                        </td>
                        <td className="px-4 py-3 text-right">
                          <span className={`font-bold ${isLowStock ? 'text-red-600' : stock > 0 ? 'text-emerald-600' : 'text-slate-400'}`}>
                            {stock}
                          </span>
                        </td>
                        <td className="px-4 py-3">
                          {!p.is_active ? (
                            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">Nonaktif</span>
                          ) : p.is_published ? (
                            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Published</span>
                          ) : (
                            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Draft</span>
                          )}
                        </td>
                        <td className="px-4 py-3 text-center">
                          <button
                            onClick={() => toggleActive(p.id)}
                            className="inline-flex"
                            title={p.is_active ? 'Nonaktifkan' : 'Aktifkan'}
                          >
                            {p.is_active ? (
                              <ToggleRight className="w-6 h-6 text-emerald-600" />
                            ) : (
                              <ToggleLeft className="w-6 h-6 text-slate-300" />
                            )}
                          </button>
                        </td>
                        <td className="px-4 py-3 text-center">
                          <button
                            onClick={() => togglePublished(p.id)}
                            className="inline-flex"
                            title={p.is_published ? 'Unpublish' : 'Publish'}
                          >
                            {p.is_published ? (
                              <ToggleRight className="w-6 h-6 text-primary-600" />
                            ) : (
                              <ToggleLeft className="w-6 h-6 text-slate-300" />
                            )}
                          </button>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>

              {filteredProducts.length === 0 && (
                <div className="h-48 flex flex-col items-center justify-center text-slate-400 text-center p-6">
                  <Package className="w-10 h-10 stroke-[1.2] mb-2 text-slate-300" />
                  <p className="text-sm font-semibold">Tidak ada produk ditemukan</p>
                  <p className="text-xs text-slate-400 mt-0.5">Ubah filter atau kata kunci pencarian</p>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* ─── Panel 2: Pratinjau Situs Web ─── */}
        <div className="w-full xl:w-[380px] flex-shrink-0 space-y-3">
          <p className="text-[11px] text-slate-500 text-center font-semibold">
            Pratinjau: hanya produk <span className="text-emerald-600">Terpublikasi</span> &{' '}
            <span className="text-emerald-600">Aktif</span> yang ditampilkan
          </p>

          {/* Phone Frame */}
          <div className="mx-auto max-w-[340px] bg-slate-900 rounded-[2.5rem] p-3 shadow-xl border border-slate-700">
            <div className="bg-white rounded-[2rem] overflow-hidden flex flex-col" style={{ aspectRatio: '9/16' }}>
              {/* Fake Navbar */}
              <div className="bg-primary-600 px-4 pt-4 pb-3 space-y-2.5">
                <div className="flex items-center gap-2">
                  <Store className="w-4 h-4 text-white/80" />
                  <span className="text-xs font-black text-white tracking-wide">{storeName}</span>
                </div>
                <div className="relative">
                  <Search className="absolute left-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-400" />
                  <input
                    type="text"
                    value={previewSearch}
                    onChange={e => setPreviewSearch(e.target.value)}
                    placeholder="Cari produk..."
                    className="w-full pl-7 pr-2.5 py-1.5 rounded-lg bg-white/95 text-[10px] text-slate-800 placeholder:text-slate-400 focus:outline-none"
                  />
                </div>
              </div>

              {/* Category Pills */}
              <div className="px-3 pt-2 pb-1 flex gap-1.5 overflow-x-auto no-scrollbar">
                <button
                  onClick={() => setPreviewCategory('all')}
                  className={`px-2 py-1 rounded text-[9px] font-bold whitespace-nowrap transition-all ${
                    previewCategory === 'all'
                      ? 'bg-primary-600 text-white'
                      : 'bg-slate-100 text-slate-600'
                  }`}
                >
                  Semua
                </button>
                {categories.map(c => (
                  <button
                    key={c.id}
                    onClick={() => setPreviewCategory(c.id)}
                    className={`px-2 py-1 rounded text-[9px] font-bold whitespace-nowrap transition-all ${
                      previewCategory === c.id
                        ? 'bg-primary-600 text-white'
                        : 'bg-slate-100 text-slate-600'
                    }`}
                  >
                    {c.name}
                  </button>
                ))}
              </div>

              {/* Product Grid */}
              <div className="flex-1 overflow-y-auto px-3 py-2 space-y-2">
                {previewProducts.map(p => {
                  const avail = p.stock_global > 0;
                  return (
                    <div
                      key={p.id}
                      className="bg-white border border-slate-200 rounded-xl p-2.5 space-y-1.5"
                    >
                      <div className="flex items-start justify-between gap-2">
                        <div className="min-w-0">
                          <h5 className="text-[11px] font-bold text-slate-800 line-clamp-2 leading-tight">
                            {p.name}
                          </h5>
                          <div className="text-[9px] text-slate-400 mt-0.5">
                            {p.category?.name || ''}{p.brand ? ` • ${p.brand.name}` : ''}
                          </div>
                        </div>
                        <span className={`mt-0.5 flex-shrink-0 w-2 h-2 rounded-full ${avail ? 'bg-emerald-500' : 'bg-red-400'}`} title={avail ? 'Tersedia' : 'Habis'} />
                      </div>
                      <div className="flex items-center justify-between">
                        <span className="text-[11px] font-black text-primary-700">
                          Rp {p.selling_price.toLocaleString('id-ID')}
                        </span>
                        <button className="px-2 py-0.5 bg-primary-50 text-primary-700 rounded text-[9px] font-bold flex items-center gap-0.5 hover:bg-primary-100 transition-colors">
                          <Eye className="w-2.5 h-2.5" />
                          Lihat
                        </button>
                      </div>
                    </div>
                  );
                })}

                {previewProducts.length === 0 && (
                  <div className="h-full flex flex-col items-center justify-center text-slate-400 text-center py-8">
                    <ShoppingBag className="w-8 h-8 stroke-[1.2] text-slate-300 mb-1.5" />
                    <p className="text-[10px] font-semibold">Belum ada produk ditampilkan</p>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
