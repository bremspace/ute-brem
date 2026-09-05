import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import clsx from 'clsx';
import {
  Globe,
  Package,
  Store,
  Eye,
  ShoppingBag,
  MapPin,
} from 'lucide-react';
import {
  Card,
  CardHeader,
  Badge,
  Button,
  Select,
  SearchInput,
  StatCard,
  TableContainer,
  TableHeader,
  TableBase,
  TableRow,
  TableEmpty,
  Toggle,
} from '../components/ui';
import type { SelectOption } from '../components/ui';

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

  const categoryOptions: SelectOption[] = [
    { value: 'all', label: 'Semua Kategori' },
    ...categories.map(c => ({ value: c.id, label: c.name })),
  ];

  const brandOptions: SelectOption[] = [
    { value: 'all', label: 'Semua Merek' },
    ...brands.map(b => ({ value: b.id, label: b.name })),
  ];

  const statusOptions: SelectOption[] = [
    { value: 'all', label: 'Semua Status' },
    { value: 'published', label: 'Terpublikasi' },
    { value: 'unpublished', label: 'Tidak Terpublikasi' },
  ];

  const locationOptions: SelectOption[] = locations.map(l => ({
    value: l.id,
    label: l.name,
  }));

  const tableColumns = useMemo(
    () => [
      { header: 'Kode' },
      { header: 'Nama' },
      { header: 'Kategori' },
      { header: 'Harga', align: 'right' as const },
      { header: 'Stok', align: 'right' as const },
      { header: 'Status' },
      { header: 'Aktif', align: 'center' as const },
      { header: 'Publish', align: 'center' as const },
    ],
    [],
  );

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
          <Card noPadding>
            <CardHeader>
              <div className="flex items-center gap-2">
                <Package className="w-4 h-4 text-primary-600" />
                <span className="text-sm font-bold text-slate-800">Manajemen Katalog Publik</span>
              </div>
            </CardHeader>

            {/* Summary Cards */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4">
              <StatCard
                value={products.length.toLocaleString('id-ID')}
                label="Total Produk"
                icon={<Package className="w-4 h-4" />}
                color="primary"
              />
              <StatCard
                value={totalPublished.toLocaleString('id-ID')}
                label="Terpublikasi"
                icon={<Globe className="w-4 h-4" />}
                color="success"
              />
              <StatCard
                value={totalUnpublished.toLocaleString('id-ID')}
                label="Tidak Terpublikasi"
                icon={<Package className="w-4 h-4" />}
                color="info"
              />
              <StatCard
                value={lowStockPublished.toLocaleString('id-ID')}
                label="Stok Menipis"
                description="Produk terpublikasi"
                icon={<Package className="w-4 h-4" />}
                color="warning"
              />
            </div>

            {/* Filter Bar */}
            <div className="px-4 pb-4 space-y-3">
              <div className="flex flex-col sm:flex-row gap-3">
                <div className="relative flex-1">
                  <SearchInput
                    value={searchTerm}
                    onChange={e => setSearchTerm(e.target.value)}
                    placeholder="Cari nama / kode produk..."
                  />
                </div>
                <Select
                  options={locationOptions}
                  value={selectedLocationId}
                  onChange={e => setSelectedLocationId(Number(e.target.value))}
                  wrapperClassName="w-auto"
                />
                <Select
                  options={categoryOptions}
                  value={filterCategory}
                  onChange={e => setFilterCategory(e.target.value === 'all' ? 'all' : Number(e.target.value))}
                  wrapperClassName="w-auto"
                />
                <Select
                  options={brandOptions}
                  value={filterBrand}
                  onChange={e => setFilterBrand(e.target.value === 'all' ? 'all' : Number(e.target.value))}
                  wrapperClassName="w-auto"
                />
                <Select
                  options={statusOptions}
                  value={filterStatus}
                  onChange={e => setFilterStatus(e.target.value as typeof filterStatus)}
                  wrapperClassName="w-auto"
                />
              </div>
            </div>
          </Card>

          {/* Product Table */}
          <TableContainer>
            <TableHeader
              icon={<Package className="w-4 h-4 text-primary-600" />}
              count={filteredProducts.length}
            >
              Produk Katalog
            </TableHeader>

            <TableBase columns={tableColumns} colSpan={tableColumns.length}>
              {filteredProducts.length === 0 ? (
                <TableEmpty
                  colSpan={tableColumns.length}
                  message="Tidak ada produk ditemukan"
                  icon={<Package className="w-10 h-10 mx-auto mb-2 text-slate-300" />}
                />
              ) : (
                filteredProducts.map(p => {
                  const stock = getStockQty(p);
                  const isLowStock = p.is_published && p.is_active && stock <= p.stock_min;
                  return (
                    <TableRow key={p.id}>
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
                        <span className={clsx(
                          'font-bold',
                          isLowStock ? 'text-red-600' : stock > 0 ? 'text-emerald-600' : 'text-slate-400',
                        )}>
                          {stock}
                        </span>
                      </td>
                      <td className="px-4 py-3">
                        {!p.is_active ? (
                          <Badge variant="danger">Nonaktif</Badge>
                        ) : p.is_published ? (
                          <Badge variant="success">Published</Badge>
                        ) : (
                          <Badge variant="neutral">Draft</Badge>
                        )}
                      </td>
                      <td className="px-4 py-3 text-center">
                        <Toggle
                          checked={p.is_active}
                          onChange={() => toggleActive(p.id)}
                          label={p.is_active ? 'Nonaktifkan' : 'Aktifkan'}
                        />
                      </td>
                      <td className="px-4 py-3 text-center">
                        <Toggle
                          checked={p.is_published}
                          onChange={() => togglePublished(p.id)}
                          label={p.is_published ? 'Unpublish' : 'Publish'}
                        />
                      </td>
                    </TableRow>
                  );
                })
              )}
            </TableBase>
          </TableContainer>
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
                  <SearchInput
                    value={previewSearch}
                    onChange={e => setPreviewSearch(e.target.value)}
                    placeholder="Cari produk..."
                    className="!bg-white/95"
                  />
                </div>
              </div>

              {/* Category Pills */}
              <div className="px-3 pt-2 pb-1 flex gap-1.5 overflow-x-auto no-scrollbar">
                <button
                  onClick={() => setPreviewCategory('all')}
                  className={clsx(
                    'px-2 py-1 rounded text-[9px] font-bold whitespace-nowrap transition-all',
                    previewCategory === 'all'
                      ? 'bg-primary-600 text-white'
                      : 'bg-slate-100 text-slate-600',
                  )}
                >
                  Semua
                </button>
                {categories.map(c => (
                  <button
                    key={c.id}
                    onClick={() => setPreviewCategory(c.id)}
                    className={clsx(
                      'px-2 py-1 rounded text-[9px] font-bold whitespace-nowrap transition-all',
                      previewCategory === c.id
                        ? 'bg-primary-600 text-white'
                        : 'bg-slate-100 text-slate-600',
                    )}
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
                        <span className={clsx(
                          'mt-0.5 flex-shrink-0 w-2 h-2 rounded-full',
                          avail ? 'bg-emerald-500' : 'bg-red-400',
                        )} title={avail ? 'Tersedia' : 'Habis'} />
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
