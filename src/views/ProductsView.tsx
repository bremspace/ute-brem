import React, { useState, useMemo } from 'react';
import { useApp } from '../context/AppContext';
import { Product } from '../types';
import clsx from 'clsx';
import {
  Plus,
  Package,
  AlertTriangle,
  TrendingUp,
  Layers,
  Edit3,
  Trash2,
  Upload,
  Barcode,
  MapPin,
  Save,
  Image,
  X,
} from 'lucide-react';
import {
  Button,
  Card,
  CardHeader,
  Input,
  Select,
  Badge,
  TableContainer,
  TableHeader,
  TableBase,
  TableRow,
  TableEmpty,
  Modal,
  Tabs,
  SearchInput,
  StatCard,
  Toggle,
} from '../components/ui';
import type { SelectOption } from '../components/ui';

/* ──────────────────────────── Types ──────────────────────────── */

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
  image_url: string;
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
  image_url: '',
};

type FormTab = 'standard' | 'advance' | 'member' | 'price-tiers' | 'variants';

const PAGE_SIZE_OPTIONS = [10, 25, 50, 100];

/* ──────────────────────────── Helpers ──────────────────────────── */

function formatRp(n: number) {
  return `Rp ${n.toLocaleString('id-ID')}`;
}

function stockBadgeVariant(qty: number, stockMin: number): 'danger' | 'amber' | 'success' {
  if (qty <= 0) return 'danger';
  if (qty <= stockMin) return 'amber';
  return 'success';
}

function stockBadgeLabel(qty: number, stockMin: number): string {
  if (qty <= 0) return 'Habis';
  if (qty <= stockMin) return 'Menipis';
  return 'Aman';
}

/* ──────────────────────────── Component ──────────────────────────── */

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

  /* ── filter / search state ── */
  const [search, setSearch] = useState('');
  const [filterCategory, setFilterCategory] = useState<number | 'all'>('all');
  const [filterBrand, setFilterBrand] = useState<number | 'all'>('all');
  const [filterStatus, setFilterStatus] = useState<'all' | 'active' | 'inactive'>('all');
  const [filterLowStock, setFilterLowStock] = useState(false);

  /* ── modal / form state ── */
  const [showForm, setShowForm] = useState(false);
  const [editId, setEditId] = useState<number | null>(null);
  const [form, setForm] = useState<FormState>(emptyForm);
  const [activeFormTab, setActiveFormTab] = useState<FormTab>('standard');

  /* ── pagination state ── */
  const [page, setPage] = useState(1);
  const [pageSize, setPageSize] = useState(25);

  /* ─────────── Derived data ─────────── */

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

  const totalPages = Math.max(1, Math.ceil(filteredProducts.length / pageSize));
  const safePage = Math.min(page, totalPages);
  const paginatedProducts = useMemo(() => {
    const start = (safePage - 1) * pageSize;
    return filteredProducts.slice(start, start + pageSize);
  }, [filteredProducts, safePage, pageSize]);

  const totalProduk = products.length;
  const totalStok = products.reduce((sum, p) => sum + p.stock_global, 0);
  const nilaiTotalStok = products.reduce((sum, p) => sum + p.stock_global * p.purchase_price, 0);

  const selectedLocName = locations.find(l => l.id === selectedLocationId)?.name || 'Global';

  const filteredSubCategories = useMemo(() => {
    if (!form.category_id) return subCategories;
    return subCategories.filter(sc => sc.category_id === form.category_id);
  }, [subCategories, form.category_id]);

  /* ─────────── Select option builders ─────────── */

  const categoryOptions: SelectOption[] = useMemo(
    () => [{ value: 'all', label: 'Semua Kategori' }, ...categories.map(c => ({ value: c.id, label: c.name }))],
    [categories],
  );

  const brandOptions: SelectOption[] = useMemo(
    () => [{ value: 'all', label: 'Semua Brand' }, ...brands.map(b => ({ value: b.id, label: b.name }))],
    [brands],
  );

  const statusFilterOptions: SelectOption[] = [
    { value: 'all', label: 'Semua Status' },
    { value: 'active', label: 'Aktif' },
    { value: 'inactive', label: 'Nonaktif' },
  ];

  const locationOptions: SelectOption[] = useMemo(
    () =>
      locations
        .filter(l => l.is_active)
        .map(l => ({ value: l.id, label: l.name })),
    [locations],
  );

  const formCategoryOptions: SelectOption[] = useMemo(
    () => [{ value: '', label: '— Pilih —' }, ...categories.map(c => ({ value: c.id, label: c.name }))],
    [categories],
  );

  const formSubCategoryOptions: SelectOption[] = useMemo(
    () => [{ value: '', label: '— Pilih —' }, ...filteredSubCategories.map(sc => ({ value: sc.id, label: sc.name }))],
    [filteredSubCategories],
  );

  const formBrandOptions: SelectOption[] = useMemo(
    () => [{ value: '', label: '— Pilih —' }, ...brands.map(b => ({ value: b.id, label: b.name }))],
    [brands],
  );

  const formMakerOptions: SelectOption[] = useMemo(
    () => [{ value: '', label: '— Pilih —' }, ...makers.map(m => ({ value: m.id, label: m.name }))],
    [makers],
  );

  const formTypeOptions: SelectOption[] = useMemo(
    () => [{ value: '', label: '— Pilih —' }, ...productTypes.map(pt => ({ value: pt.id, label: pt.name }))],
    [productTypes],
  );

  const formSupplierOptions: SelectOption[] = useMemo(
    () => [{ value: '', label: '— Pilih —' }, ...suppliers.map(s => ({ value: s.id, label: s.name }))],
    [suppliers],
  );

  const formUnitOptions: SelectOption[] = useMemo(
    () => [{ value: '', label: '— Pilih —' }, ...units.map(u => ({ value: u.id, label: u.name }))],
    [units],
  );

  /* ─────────── Actions ─────────── */

  function openAdd() {
    setEditId(null);
    setForm({ ...emptyForm, product_code: `PRD-${Date.now().toString().slice(-6)}` });
    setActiveFormTab('standard');
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
      image_url: '',
    });
    setActiveFormTab('standard');
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

  /* Reset page to 1 when filters change */
  React.useEffect(() => {
    setPage(1);
  }, [search, filterCategory, filterBrand, filterStatus, filterLowStock, pageSize]);

  /* ─────────── Table columns ─────────── */
  const columns = useMemo(
    () => [
      { header: 'Kode' },
      { header: 'Nama Produk' },
      { header: 'Kategori' },
      { header: 'Brand' },
      { header: 'Tipe' },
      { header: 'Satuan' },
      { header: 'Harga Jual', align: 'right' as const },
      { header: 'Stok', align: 'center' as const },
      { header: 'Status', align: 'center' as const },
      { header: 'Aksi', align: 'center' as const },
    ],
    [],
  );

  /* ─────────── Form tabs definition ─────────── */
  const formTabs = useMemo(
    () => [
      { id: 'standard', label: 'Standard' },
      { id: 'advance', label: 'Advance' },
      { id: 'member', label: 'Member / Group' },
      { id: 'price-tiers', label: 'Price Tiers' },
      { id: 'variants', label: 'Variants' },
    ],
    [],
  );

  /* ─────────── Render ─────────── */
  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      {/* ── Page Header ── */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-slate-900 tracking-tight">Master Produk &amp; Stok</h1>
          <p className="text-xs text-slate-500 mt-1">
            Kelola data produk, harga beli/jual, dan pantau stok per lokasi.
          </p>
        </div>
        <div className="flex items-center gap-2 flex-wrap">
          <Button variant="primary" icon={<Plus className="w-4 h-4" />} onClick={openAdd}>
            Tambah Produk
          </Button>
          <Button variant="secondary" icon={<Upload className="w-4 h-4" />}>
            Import Produk
          </Button>
          <Button variant="secondary" icon={<Barcode className="w-4 h-4" />}>
            Cetak Barcode
          </Button>
        </div>
      </div>

      {/* ── Stat Cards ── */}
      <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <StatCard
          value={totalProduk}
          label="Total Produk"
          icon={<Package className="w-4 h-4" />}
          color="primary"
        />
        <StatCard
          value={totalStok.toLocaleString('id-ID')}
          label="Total Stok"
          icon={<Layers className="w-4 h-4" />}
          color="success"
        />
        <StatCard
          value={formatRp(nilaiTotalStok)}
          label="Nilai Total Stok"
          icon={<TrendingUp className="w-4 h-4" />}
          color="violet"
        />
      </div>

      {/* ── Location + Filter Bar ── */}
      <Card noPadding>
        <div className="p-4 space-y-3">
          <div className="flex flex-wrap items-center gap-3">
            {/* Location selector */}
            <div className="flex items-center gap-2">
              <MapPin className="w-4 h-4 text-primary-600" />
              <span className="text-xs font-bold text-slate-700">Lokasi Stok:</span>
              <Select
                options={locationOptions}
                value={selectedLocationId}
                onChange={e => setSelectedLocationId(Number(e.target.value))}
                wrapperClassName="w-auto"
              />
            </div>

            <div className="h-5 w-px bg-slate-200 hidden sm:block" />

            {/* Search */}
            <div className="relative flex-1 min-w-[200px]">
              <SearchInput
                value={search}
                onChange={e => setSearch(e.target.value)}
                placeholder="Cari nama, kode, barcode..."
              />
            </div>

            {/* Category filter */}
            <Select
              options={categoryOptions}
              value={filterCategory}
              onChange={e => setFilterCategory(e.target.value === 'all' ? 'all' : Number(e.target.value))}
              wrapperClassName="w-auto"
            />

            {/* Brand filter */}
            <Select
              options={brandOptions}
              value={filterBrand}
              onChange={e => setFilterBrand(e.target.value === 'all' ? 'all' : Number(e.target.value))}
              wrapperClassName="w-auto"
            />

            {/* Status filter */}
            <Select
              options={statusFilterOptions}
              value={filterStatus}
              onChange={e => setFilterStatus(e.target.value as 'all' | 'active' | 'inactive')}
              wrapperClassName="w-auto"
            />

            {/* Low stock toggle */}
            <Button
              variant={filterLowStock ? 'outline' : 'ghost'}
              size="sm"
              icon={<AlertTriangle className="w-3.5 h-3.5" />}
              onClick={() => setFilterLowStock(!filterLowStock)}
              className={clsx(
                filterLowStock && 'border-amber-300 text-amber-800 bg-amber-50 hover:bg-amber-100',
              )}
            >
              Stok Menipis
            </Button>
          </div>
        </div>
      </Card>

      {/* ── Products Table ── */}
      <TableContainer>
        <TableHeader
          icon={<Layers className="w-4 h-4 text-primary-600" />}
          count={filteredProducts.length}
          extra={
            <span className="text-[10px] text-slate-400">
              Menampilkan stok:{' '}
              <span className="font-bold text-primary-600">{selectedLocName}</span>
            </span>
          }
        >
          Daftar Produk
        </TableHeader>

        <TableBase
          columns={columns}
          emptyMessage="Tidak ada produk ditemukan"
          emptyIcon={<Package className="w-10 h-10 mx-auto text-slate-300" />}
          colSpan={columns.length}
        >
          {paginatedProducts.length === 0 ? (
            <TableEmpty
              colSpan={columns.length}
              message="Tidak ada produk ditemukan"
              icon={<Package className="w-10 h-10 mx-auto mb-2 text-slate-300" />}
            />
          ) : (
            paginatedProducts.map(p => {
              const locStock =
                p.stocks?.find(s => s.location_id === selectedLocationId)?.quantity ?? 0;
              const isLow = p.stock_global <= p.stock_min && p.stock_global > 0;
              const isOut = p.stock_global <= 0;

              return (
                <TableRow key={p.id}>
                  {/* Kode */}
                  <td className="px-4 py-3 font-mono font-bold text-primary-700 text-[11px]">
                    {p.product_code}
                  </td>

                  {/* Nama */}
                  <td className="px-4 py-3">
                    <div className="font-bold text-slate-800 line-clamp-1">{p.name}</div>
                    <div className="text-[10px] text-slate-400 mt-0.5">
                      {[p.maker?.name, p.product_type?.name].filter(Boolean).join(' \u2022 ') || '\u2014'}
                    </div>
                  </td>

                  {/* Kategori */}
                  <td className="px-4 py-3 text-slate-600">{p.category?.name || '\u2014'}</td>

                  {/* Brand */}
                  <td className="px-4 py-3 text-slate-600">{p.brand?.name || '\u2014'}</td>

                  {/* Tipe */}
                  <td className="px-4 py-3 text-slate-600">{p.product_type?.name || '\u2014'}</td>

                  {/* Satuan */}
                  <td className="px-4 py-3 text-slate-600">{p.unit?.short_name || p.unit?.name || '\u2014'}</td>

                  {/* Harga Jual */}
                  <td className="px-4 py-3 text-right font-bold text-slate-900 font-mono">
                    {formatRp(p.selling_price)}
                  </td>

                  {/* Stok */}
                  <td className="px-4 py-3 text-center">
                    <div className="flex flex-col items-center gap-0.5">
                      <Badge variant={stockBadgeVariant(locStock, p.stock_min)}>
                        {locStock}
                      </Badge>
                      <span className="text-[9px] text-slate-400">
                        G: {p.stock_global}
                      </span>
                    </div>
                  </td>

                  {/* Status */}
                  <td className="px-4 py-3 text-center">
                    <div className="flex items-center justify-center gap-1.5">
                      <Badge
                        variant={p.is_active ? 'success' : 'neutral'}
                        className="cursor-pointer"
                        onClick={() => toggleActive(p.id)}
                        title={p.is_active ? 'Nonaktifkan' : 'Aktifkan'}
                      >
                        {p.is_active ? 'Aktif' : 'Nonaktif'}
                      </Badge>
                      <span
                        className="cursor-pointer"
                        onClick={() => togglePublished(p.id)}
                        title={p.is_published ? 'Sembunyikan dari POS' : 'Tampilkan di POS'}
                      >
                        {p.is_published ? (
                          <Badge variant="success" pill>Published</Badge>
                        ) : (
                          <Badge variant="neutral" pill>Draft</Badge>
                        )}
                      </span>
                    </div>
                  </td>

                  {/* Aksi */}
                  <td className="px-4 py-3">
                    <div className="flex items-center justify-center gap-1">
                      <Button
                        variant="ghost"
                        size="sm"
                        icon={<Edit3 className="w-3.5 h-3.5" />}
                        onClick={() => openEdit(p)}
                        title="Edit"
                      />
                      <Button
                        variant="danger"
                        size="sm"
                        icon={<Trash2 className="w-3.5 h-3.5" />}
                        onClick={() => handleDelete(p.id)}
                        title="Hapus"
                      />
                    </div>
                  </td>
                </TableRow>
              );
            })
          )}
        </TableBase>

        {/* ── Pagination ── */}
        <div className="px-5 py-3 border-t border-slate-100 flex items-center justify-between text-xs">
          <div className="flex items-center gap-2 text-slate-500">
            <span>Halaman</span>
            <span className="font-bold text-slate-700">
              {safePage} / {totalPages}
            </span>
            <span className="text-slate-400">({filteredProducts.length} data)</span>
          </div>
          <div className="flex items-center gap-2">
            {/* Page size selector */}
            <Select
              options={PAGE_SIZE_OPTIONS.map(s => ({ value: s, label: `${s} / hal` }))}
              value={pageSize}
              onChange={e => setPageSize(Number(e.target.value))}
              wrapperClassName="w-auto"
            />
            {/* Prev */}
            <Button
              variant="ghost"
              size="xs"
              disabled={safePage <= 1}
              onClick={() => setPage(p => Math.max(1, p - 1))}
            >
              Prev
            </Button>
            {/* Page numbers */}
            {Array.from({ length: totalPages }, (_, i) => i + 1)
              .filter(p => {
                if (totalPages <= 7) return true;
                if (p === 1 || p === totalPages) return true;
                if (Math.abs(p - safePage) <= 1) return true;
                return false;
              })
              .reduce<(number | '...')[]>((acc, p, i, arr) => {
                if (i > 0 && typeof arr[i - 1] === 'number' && p - (arr[i - 1] as number) > 1) {
                  acc.push('...');
                }
                acc.push(p);
                return acc;
              }, [])
              .map((p, i) =>
                p === '...' ? (
                  <span key={`ellipsis-${i}`} className="px-1 text-slate-400">
                    ...
                  </span>
                ) : (
                  <button
                    key={p}
                    onClick={() => setPage(p)}
                    className={clsx(
                      'w-7 h-7 rounded-lg text-[11px] font-bold transition-all',
                      p === safePage
                        ? 'bg-primary-600 text-white shadow-sm'
                        : 'text-slate-600 hover:bg-slate-100',
                    )}
                  >
                    {p}
                  </button>
                ),
              )}
            {/* Next */}
            <Button
              variant="ghost"
              size="xs"
              disabled={safePage >= totalPages}
              onClick={() => setPage(p => Math.min(totalPages, p + 1))}
            >
              Next
            </Button>
          </div>
        </div>
      </TableContainer>

      {/* ──────────────────── Modal Form Tambah / Edit Produk ──────────────────── */}
      <Modal
        open={showForm}
        onClose={() => setShowForm(false)}
        size="xl"
        title={editId !== null ? 'Edit Produk' : 'Tambah Produk Baru'}
        footer={
          <>
            <Button variant="ghost" onClick={() => setShowForm(false)}>
              Batal
            </Button>
            <Button
              variant="primary"
              icon={<Save className="w-3.5 h-3.5" />}
              onClick={handleSave}
            >
              {editId !== null ? 'Simpan Perubahan' : 'Tambah Produk'}
            </Button>
          </>
        }
      >
        {/* Tabs */}
        <Tabs
          tabs={formTabs}
          activeTab={activeFormTab}
          onChange={id => setActiveFormTab(id as FormTab)}
          variant="segmented"
          className="mb-5"
        />

        {/* ── Tab: Standard ── */}
        {activeFormTab === 'standard' && (
          <div className="space-y-4">
            {/* Kode & Nama */}
            <div className="grid grid-cols-3 gap-3">
              <Input
                label="Kode Produk"
                value={form.product_code}
                onChange={e => updateField('product_code', e.target.value)}
                placeholder="PRD-XXXXXX"
              />
              <div className="col-span-2">
                <Input
                  label="Nama Produk"
                  required
                  value={form.name}
                  onChange={e => updateField('name', e.target.value)}
                  placeholder="Nama produk"
                />
              </div>
            </div>

            {/* Barcode */}
            <Input
              label="Barcode"
              leftIcon={<Barcode className="w-3.5 h-3.5" />}
              value={form.barcode}
              onChange={e => updateField('barcode', e.target.value)}
              placeholder="Barcode produk"
            />

            {/* Kategori, Sub Kategori, Brand */}
            <div className="grid grid-cols-3 gap-3">
              <Select
                label="Kategori"
                options={formCategoryOptions}
                value={form.category_id}
                onChange={e => updateField('category_id', e.target.value ? Number(e.target.value) : '')}
              />
              <Select
                label="Sub Kategori"
                options={formSubCategoryOptions}
                value={form.sub_category_id}
                onChange={e => updateField('sub_category_id', e.target.value ? Number(e.target.value) : '')}
              />
              <Select
                label="Brand"
                options={formBrandOptions}
                value={form.brand_id}
                onChange={e => updateField('brand_id', e.target.value ? Number(e.target.value) : '')}
              />
            </div>

            {/* Maker, Tipe, Supplier */}
            <div className="grid grid-cols-3 gap-3">
              <Select
                label="Maker / Pabrikan"
                options={formMakerOptions}
                value={form.product_maker_id}
                onChange={e => updateField('product_maker_id', e.target.value ? Number(e.target.value) : '')}
              />
              <Select
                label="Tipe Produk"
                options={formTypeOptions}
                value={form.product_type_id}
                onChange={e => updateField('product_type_id', e.target.value ? Number(e.target.value) : '')}
              />
              <Select
                label="Supplier"
                options={formSupplierOptions}
                value={form.supplier_id}
                onChange={e => updateField('supplier_id', e.target.value ? Number(e.target.value) : '')}
              />
            </div>

            {/* Satuan, Harga Beli, Harga Jual */}
            <div className="grid grid-cols-3 gap-3">
              <Select
                label="Satuan"
                options={formUnitOptions}
                value={form.unit_id}
                onChange={e => updateField('unit_id', e.target.value ? Number(e.target.value) : '')}
              />
              <Input
                label="Harga Beli (Rp)"
                type="number"
                min={0}
                value={form.purchase_price}
                onChange={e => updateField('purchase_price', Number(e.target.value))}
              />
              <Input
                label="Harga Jual (Rp)"
                type="number"
                min={0}
                required
                value={form.selling_price}
                onChange={e => updateField('selling_price', Number(e.target.value))}
              />
            </div>

            {/* Stok Minimum */}
            <div className="w-1/3">
              <Input
                label="Stok Minimum"
                type="number"
                min={0}
                value={form.stock_min}
                onChange={e => updateField('stock_min', Number(e.target.value))}
              />
            </div>

            {/* Description */}
            <div>
              <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">
                Deskripsi
              </label>
              <textarea
                rows={3}
                value={form.description}
                onChange={e => updateField('description', e.target.value)}
                placeholder="Deskripsi produk (opsional)"
                className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
              />
            </div>

            {/* Image Upload Preview */}
            <div>
              <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">
                Gambar Produk
              </label>
              <div className="flex items-start gap-4">
                <div
                  className={clsx(
                    'w-24 h-24 rounded-xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center bg-slate-50 text-slate-400 overflow-hidden',
                    form.image_url && 'border-primary-300',
                  )}
                >
                  {form.image_url ? (
                    <img
                      src={form.image_url}
                      alt="Preview"
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <>
                      <Image className="w-5 h-5 mb-1" />
                      <span className="text-[9px] font-semibold">Upload</span>
                    </>
                  )}
                </div>
                <div className="flex flex-col gap-2 flex-1">
                  <Input
                    value={form.image_url}
                    onChange={e => updateField('image_url', e.target.value)}
                    placeholder="URL gambar produk"
                  />
                  {form.image_url && (
                    <Button
                      variant="ghost"
                      size="xs"
                      icon={<X className="w-3 h-3" />}
                      onClick={() => updateField('image_url', '')}
                    >
                      Hapus Gambar
                    </Button>
                  )}
                </div>
              </div>
            </div>
          </div>
        )}

        {/* ── Tab: Advance ── */}
        {activeFormTab === 'advance' && (
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <Toggle
                checked={form.allow_open_price}
                onChange={val => updateField('allow_open_price', val)}
                label="Allow Open Price"
              />
              <Toggle
                checked={form.has_serial_number}
                onChange={val => updateField('has_serial_number', val)}
                label="Serial Number"
              />
              <Toggle
                checked={form.is_active}
                onChange={val => updateField('is_active', val)}
                label="Aktif"
              />
              <Toggle
                checked={form.is_published}
                onChange={val => updateField('is_published', val)}
                label="Published (Tampil di POS)"
              />
            </div>
          </div>
        )}

        {/* ── Tab: Member / Group ── */}
        {activeFormTab === 'member' && (
          <div className="space-y-4">
            <Toggle
              checked={form.is_member_only}
              onChange={val => updateField('is_member_only', val)}
              label="Member Only"
            />
            <p className="text-xs text-slate-400">
              Aktifkan jika produk ini hanya tersedia untuk member / grup tertentu.
            </p>
          </div>
        )}

        {/* ── Tab: Price Tiers ── */}
        {activeFormTab === 'price-tiers' && (
          <div className="space-y-4">
            <p className="text-xs text-slate-500">
              Konfigurasi harga berdasarkan tier atau jumlah pembelian. (Coming soon)
            </p>
          </div>
        )}

        {/* ── Tab: Variants ── */}
        {activeFormTab === 'variants' && (
          <div className="space-y-4">
            <p className="text-xs text-slate-500">
              Kelola varian produk (warna, ukuran, kapasitas, dll). (Coming soon)
            </p>
          </div>
        )}
      </Modal>
    </div>
  );
};
