import React, { ReactNode, useState } from 'react';
import {
  Building,
  MapPin,
  Boxes,
  Tags,
  Tag,
  Factory,
  Component,
  Ruler,
  Truck,
  Database,
  Plus,
  Pencil,
  Trash2,
  ChevronDown,
  Search,
  Package,
  Layers,
} from 'lucide-react';
import { useApp } from '../context/AppContext';
import {
  Branch,
  Location as StoreLocation,
  LocationRack,
  Category,
  SubCategory,
  Brand,
  ProductMaker,
  ProductType,
  Unit,
  Supplier,
} from '../types';
import {
  Button,
  Card,
  CardHeader as UICardHeader,
  Input,
  Select,
  SearchInput,
  Toggle,
  Badge,
  Modal,
  Tabs,
} from '../components/ui';
import type { SelectOption } from '../components/ui';

// ---------- Local UI helpers (not in shared library) ----------

const EmptyState: React.FC<{ icon: ReactNode; text: string }> = ({ icon, text }) => (
  <div className="py-12 flex flex-col items-center justify-center text-center text-slate-400">
    {icon}
    <p className="text-xs font-semibold mt-2">{text}</p>
  </div>
);

const ActionButtons: React.FC<{ onEdit: () => void; onDelete: () => void }> = ({ onEdit, onDelete }) => (
  <div className="flex items-center gap-1.5">
    <button
      onClick={e => { e.stopPropagation(); onEdit(); }}
      className="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors"
      title="Edit"
    >
      <Pencil className="w-3.5 h-3.5" />
    </button>
    <button
      onClick={e => { e.stopPropagation(); onDelete(); }}
      className="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors"
      title="Hapus"
    >
      <Trash2 className="w-3.5 h-3.5" />
    </button>
  </div>
);

const Field: React.FC<{ label: string; children: ReactNode; className?: string }> = ({ label, children, className }) => (
  <label className={`block ${className || ''}`}>
    <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wide">{label}</span>
    <div className="mt-1">{children}</div>
  </label>
);

const ListCardWrapper: React.FC<{ children: ReactNode }> = ({ children }) => (
  <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">{children}</div>
);

const ListItem: React.FC<{ inactive?: boolean; children: ReactNode }> = ({ inactive, children }) => (
  <div className={`px-6 py-3 flex items-center justify-between hover:bg-slate-50/70 transition-colors ${inactive ? 'bg-slate-50 opacity-60' : ''}`}>
    {children}
  </div>
);

const ActiveBadge: React.FC<{ active: boolean }> = ({ active }) => (
  <Badge variant={active ? 'success' : 'neutral'}>
    {active ? 'Aktif' : 'Nonaktif'}
  </Badge>
);

// ---------- Entity CRUD components ----------

// --- Branch ---
const BranchesTab: React.FC = () => {
  const { branches, setBranches } = useApp();
  const [search, setSearch] = useState('');
  const [modal, setModal] = useState<{ open: boolean; editing?: Branch }>({ open: false });
  const [form, setForm] = useState({ code: '', name: '', address: '', phone: '', is_main: false, is_active: true });

  const openAdd = () => {
    setForm({ code: '', name: '', address: '', phone: '', is_main: false, is_active: true });
    setModal({ open: true });
  };
  const openEdit = (b: Branch) => {
    setForm({ code: b.code, name: b.name, address: b.address || '', phone: b.phone || '', is_main: b.is_main, is_active: b.is_active });
    setModal({ open: true, editing: b });
  };
  const save = () => {
    if (!form.code.trim() || !form.name.trim()) return;
    if (modal.editing) {
      setBranches(prev => prev.map(b => b.id === modal.editing!.id ? { ...b, ...form } : b));
    } else {
      setBranches(prev => [...prev, { id: Date.now(), ...form }]);
    }
    setModal({ open: false });
  };
  const del = (b: Branch) => {
    if (confirm(`Hapus cabang "${b.name}"?`)) setBranches(prev => prev.filter(x => x.id !== b.id));
  };
  const toggle = (b: Branch) => setBranches(prev => prev.map(x => x.id === b.id ? { ...x, is_active: !x.is_active } : x));

  const list = branches.filter(b =>
    (b.code + ' ' + b.name).toLowerCase().includes(search.toLowerCase())
  );

  return (
    <ListCardWrapper>
      <UICardHeader>
        <div className="flex items-center justify-between w-full">
          <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
            <span className="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
              <Building className="w-4 h-4" />
            </span>
            Cabang
            <span className="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{branches.length}</span>
          </div>
          <Button size="sm" icon={<Plus className="w-3.5 h-3.5" />} onClick={openAdd}>
            Tambah
          </Button>
        </div>
      </UICardHeader>
      <div className="px-6 py-3 border-b border-slate-100">
        <div className="relative">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            value={search}
            onChange={e => setSearch(e.target.value)}
            placeholder="Cari kode atau nama cabang..."
            className="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all"
          />
        </div>
      </div>
      <div className="divide-y divide-slate-100">
        {list.map(b => (
          <ListItem key={b.id} inactive={!b.is_active}>
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-[10px]">
                {b.code}
              </div>
              <div>
                <div className="font-bold text-xs text-slate-800 flex items-center gap-2">
                  {b.name}
                  {b.is_main && <span className="px-1.5 py-0.5 rounded text-[9px] font-bold bg-primary-100 text-primary-700">UTAMA</span>}
                </div>
                <div className="text-[10px] text-slate-400 mt-0.5">
                  {[b.address, b.phone].filter(Boolean).join(' • ') || '—'}
                </div>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <ActiveBadge active={b.is_active} />
              <Toggle checked={b.is_active} onChange={() => toggle(b)} />
              <ActionButtons onEdit={() => openEdit(b)} onDelete={() => del(b)} />
            </div>
          </ListItem>
        ))}
        {list.length === 0 && <EmptyState icon={<Building className="w-8 h-8" />} text="Tidak ada cabang ditemukan" />}
      </div>

      <Modal
        open={modal.open}
        onClose={() => setModal({ open: false })}
        title={modal.editing ? 'Edit Cabang' : 'Tambah Cabang'}
        size="md"
        footer={
          <>
            <Button variant="secondary" onClick={() => setModal({ open: false })}>Batal</Button>
            <Button onClick={save}>Simpan</Button>
          </>
        }
      >
        <div className="space-y-3">
          <div className="grid grid-cols-2 gap-3">
            <Input label="Kode" value={form.code} onChange={e => setForm({ ...form, code: e.target.value })} placeholder="CBG" />
            <Input label="Nama" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} placeholder="Cabang 1" />
          </div>
          <Input label="Alamat" value={form.address} onChange={e => setForm({ ...form, address: e.target.value })} placeholder="Alamat" />
          <Input label="Telepon" value={form.phone} onChange={e => setForm({ ...form, phone: e.target.value })} placeholder="08xx" />
          <label className="flex items-center gap-2 pt-1">
            <input type="checkbox" checked={form.is_main} onChange={e => setForm({ ...form, is_main: e.target.checked })} className="w-4 h-4 accent-primary-600" />
            <span className="text-xs font-bold text-slate-700">Cabang Utama</span>
          </label>
        </div>
      </Modal>
    </ListCardWrapper>
  );
};

// --- Location + Rack ---
const LocationsTab: React.FC = () => {
  const { locations, setLocations, racks, setRacks, branches } = useApp();
  const [search, setSearch] = useState('');
  const [modal, setModal] = useState<{ open: boolean; editing?: StoreLocation }>({ open: false });
  const [form, setForm] = useState({ branch_id: branches[0]?.id || 0, code: '', name: '', description: '', is_active: true });
  const [openRackId, setOpenRackId] = useState<number | null>(null);

  const openAdd = () => {
    setForm({ branch_id: branches[0]?.id || 0, code: '', name: '', description: '', is_active: true });
    setModal({ open: true });
  };
  const openEdit = (l: StoreLocation) => {
    setForm({ branch_id: l.branch_id || branches[0]?.id || 0, code: l.code, name: l.name, description: l.description || '', is_active: l.is_active });
    setModal({ open: true, editing: l });
  };
  const save = () => {
    if (!form.code.trim() || !form.name.trim()) return;
    if (modal.editing) {
      setLocations(prev => prev.map(l => l.id === modal.editing!.id ? { ...l, ...form } : l));
    } else {
      setLocations(prev => [...prev, { id: Date.now(), ...form }]);
    }
    setModal({ open: false });
  };
  const del = (l: StoreLocation) => {
    if (!confirm(`Hapus lokasi "${l.name}"? Semua rak terkait juga dihapus.`)) return;
    setLocations(prev => prev.filter(x => x.id !== l.id));
    setRacks(prev => prev.filter(r => r.location_id !== l.id));
  };
  const toggle = (l: StoreLocation) => setLocations(prev => prev.map(x => x.id === l.id ? { ...x, is_active: !x.is_active } : x));

  const branchName = (id?: number) => branches.find(b => b.id === id)?.name || '—';
  const locRacks = (locId: number) => racks.filter(r => r.location_id === locId);

  const list = locations.filter(l =>
    (l.code + ' ' + l.name + ' ' + branchName(l.branch_id)).toLowerCase().includes(search.toLowerCase())
  );

  const branchOptions: SelectOption[] = branches.map(b => ({ value: b.id, label: b.name }));

  return (
    <ListCardWrapper>
      <UICardHeader>
        <div className="flex items-center justify-between w-full">
          <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
            <span className="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
              <MapPin className="w-4 h-4" />
            </span>
            Lokasi & Rak
            <span className="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{locations.length}</span>
          </div>
          <Button size="sm" icon={<Plus className="w-3.5 h-3.5" />} onClick={openAdd}>
            Tambah
          </Button>
        </div>
      </UICardHeader>
      <div className="px-6 py-3 border-b border-slate-100">
        <div className="relative">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            value={search}
            onChange={e => setSearch(e.target.value)}
            placeholder="Cari lokasi atau cabang..."
            className="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all"
          />
        </div>
      </div>
      <div className="divide-y divide-slate-100">
        {list.map(l => {
          const rks = locRacks(l.id);
          const isOpen = openRackId === l.id;
          return (
            <div key={l.id}>
              <ListItem inactive={!l.is_active}>
                <div className="flex items-center gap-3">
                  <div className="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-[10px]">{l.code}</div>
                  <div>
                    <div className="font-bold text-xs text-slate-800">{l.name}</div>
                    <div className="text-[10px] text-slate-400 mt-0.5">{branchName(l.branch_id)}{l.description ? ` • ${l.description}` : ''}</div>
                  </div>
                </div>
                <div className="flex items-center gap-3">
                  <button
                    onClick={e => { e.stopPropagation(); setOpenRackId(isOpen ? null : l.id); }}
                    className="flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold text-slate-500 hover:text-primary-600 hover:bg-primary-50 transition-colors"
                  >
                    <Layers className="w-3.5 h-3.5" />
                    Rak ({rks.filter(r => r.is_active).length})
                    <ChevronDown className={`w-3 h-3 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
                  </button>
                  <ActiveBadge active={l.is_active} />
                  <Toggle checked={l.is_active} onChange={() => toggle(l)} />
                  <ActionButtons onEdit={() => openEdit(l)} onDelete={() => del(l)} />
                </div>
              </ListItem>
              {isOpen && <RackManager location={l} racks={rks} />}
            </div>
          );
        })}
        {list.length === 0 && <EmptyState icon={<MapPin className="w-8 h-8" />} text="Tidak ada lokasi ditemukan" />}
      </div>

      <Modal
        open={modal.open}
        onClose={() => setModal({ open: false })}
        title={modal.editing ? 'Edit Lokasi' : 'Tambah Lokasi'}
        size="md"
        footer={
          <>
            <Button variant="secondary" onClick={() => setModal({ open: false })}>Batal</Button>
            <Button onClick={save}>Simpan</Button>
          </>
        }
      >
        <div className="space-y-3">
          <Select label="Cabang" options={branchOptions} value={form.branch_id} onChange={e => setForm({ ...form, branch_id: Number(e.target.value) })} />
          <div className="grid grid-cols-2 gap-3">
            <Input label="Kode" value={form.code} onChange={e => setForm({ ...form, code: e.target.value })} placeholder="L01" />
            <Input label="Nama" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} placeholder="Gudang Utama" />
          </div>
          <Input label="Deskripsi" value={form.description} onChange={e => setForm({ ...form, description: e.target.value })} placeholder="Deskripsi" />
        </div>
      </Modal>
    </ListCardWrapper>
  );
};

const RackManager: React.FC<{ location: StoreLocation; racks: LocationRack[] }> = ({ location, racks }) => {
  const { setRacks } = useApp();
  const [rackForm, setRackForm] = useState({ code: '', name: '' });

  const addRack = () => {
    if (!rackForm.code.trim() || !rackForm.name.trim()) return;
    setRacks(prev => [...prev, { id: Date.now(), location_id: location.id, code: rackForm.code, name: rackForm.name, is_active: true }]);
    setRackForm({ code: '', name: '' });
  };
  const delRack = (r: LocationRack) => {
    if (confirm(`Hapus rak "${r.name}"?`)) setRacks(prev => prev.filter(x => x.id !== r.id));
  };
  const toggleRack = (r: LocationRack) => setRacks(prev => prev.map(x => x.id === r.id ? { ...x, is_active: !x.is_active } : x));

  return (
    <div className="bg-slate-50/60 px-6 py-4 border-t border-slate-100">
      <div className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Daftar Rak</div>
      <div className="space-y-1.5 mb-3">
        {racks.map(r => (
          <div key={r.id} className={`px-3 py-2 rounded-xl bg-white border border-slate-200 flex items-center justify-between ${!r.is_active ? 'opacity-60' : ''}`}>
            <div className="flex items-center gap-2.5">
              <div className="w-6 h-6 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-[9px]">{r.code}</div>
              <span className="font-bold text-xs text-slate-700">{r.name}</span>
            </div>
            <div className="flex items-center gap-2.5">
              <ActiveBadge active={r.is_active} />
              <Toggle checked={r.is_active} onChange={() => toggleRack(r)} />
              <ActionButtons onEdit={() => {}} onDelete={() => delRack(r)} />
            </div>
          </div>
        ))}
        {racks.length === 0 && <p className="text-[10px] text-slate-400">Belum ada rak di lokasi ini.</p>}
      </div>
      <div className="flex items-end gap-2">
        <Input label="Kode" value={rackForm.code} onChange={e => setRackForm({ ...rackForm, code: e.target.value })} placeholder="R01" wrapperClassName="w-24" />
        <Input label="Nama Rak" value={rackForm.name} onChange={e => setRackForm({ ...rackForm, name: e.target.value })} placeholder="Rak A" wrapperClassName="flex-1" />
        <Button size="sm" icon={<Plus className="w-3.5 h-3.5" />} onClick={addRack}>
          Tambah Rak
        </Button>
      </div>
    </div>
  );
};

// --- Category ---
const CategoriesTab: React.FC = () => {
  const { categories, setCategories } = useApp();
  const [search, setSearch] = useState('');
  const [modal, setModal] = useState<{ open: boolean; editing?: Category }>({ open: false });
  const [form, setForm] = useState({ name: '', code: '', description: '', is_active: true });

  const openAdd = () => { setForm({ name: '', code: '', description: '', is_active: true }); setModal({ open: true }); };
  const openEdit = (c: Category) => { setForm({ name: c.name, code: c.code || '', description: c.description || '', is_active: c.is_active }); setModal({ open: true, editing: c }); };
  const save = () => {
    if (!form.name.trim()) return;
    if (modal.editing) setCategories(prev => prev.map(c => c.id === modal.editing!.id ? { ...c, ...form } : c));
    else setCategories(prev => [...prev, { id: Date.now(), ...form }]);
    setModal({ open: false });
  };
  const del = (c: Category) => { if (confirm(`Hapus kategori "${c.name}"?`)) setCategories(prev => prev.filter(x => x.id !== c.id)); };
  const toggle = (c: Category) => setCategories(prev => prev.map(x => x.id === c.id ? { ...x, is_active: !x.is_active } : x));

  const list = categories.filter(c => (c.name + ' ' + (c.code || '')).toLowerCase().includes(search.toLowerCase()));

  return (
    <ListCardWrapper>
      <UICardHeader>
        <div className="flex items-center justify-between w-full">
          <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
            <span className="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
              <Boxes className="w-4 h-4" />
            </span>
            Kategori
            <span className="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{categories.length}</span>
          </div>
          <Button size="sm" icon={<Plus className="w-3.5 h-3.5" />} onClick={openAdd}>
            Tambah
          </Button>
        </div>
      </UICardHeader>
      <div className="px-6 py-3 border-b border-slate-100">
        <div className="relative">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            value={search}
            onChange={e => setSearch(e.target.value)}
            placeholder="Cari kategori..."
            className="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all"
          />
        </div>
      </div>
      <div className="divide-y divide-slate-100">
        {list.map(c => (
          <ListItem key={c.id} inactive={!c.is_active}>
            <div>
              <div className="font-bold text-xs text-slate-800">{c.name}</div>
              <div className="text-[10px] text-slate-400 mt-0.5">{c.code ? `#${c.code} • ` : ''}{c.description || '—'}</div>
            </div>
            <div className="flex items-center gap-3">
              <ActiveBadge active={c.is_active} />
              <Toggle checked={c.is_active} onChange={() => toggle(c)} />
              <ActionButtons onEdit={() => openEdit(c)} onDelete={() => del(c)} />
            </div>
          </ListItem>
        ))}
        {list.length === 0 && <EmptyState icon={<Boxes className="w-8 h-8" />} text="Tidak ada kategori ditemukan" />}
      </div>

      <Modal
        open={modal.open}
        onClose={() => setModal({ open: false })}
        title={modal.editing ? 'Edit Kategori' : 'Tambah Kategori'}
        size="md"
        footer={
          <>
            <Button variant="secondary" onClick={() => setModal({ open: false })}>Batal</Button>
            <Button onClick={save}>Simpan</Button>
          </>
        }
      >
        <div className="space-y-3">
          <div className="grid grid-cols-2 gap-3">
            <Input label="Nama" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} placeholder="Sparepart Mesin" />
            <Input label="Kode" value={form.code} onChange={e => setForm({ ...form, code: e.target.value })} placeholder="KTG" />
          </div>
          <Input label="Deskripsi" value={form.description} onChange={e => setForm({ ...form, description: e.target.value })} placeholder="Deskripsi" />
        </div>
      </Modal>
    </ListCardWrapper>
  );
};

// --- SubCategory ---
const SubCategoriesTab: React.FC = () => {
  const { subCategories, setSubCategories, categories } = useApp();
  const [search, setSearch] = useState('');
  const [modal, setModal] = useState<{ open: boolean; editing?: SubCategory }>({ open: false });
  const [form, setForm] = useState({ category_id: categories[0]?.id || 0, name: '', code: '', description: '', is_active: true });

  const openAdd = () => { setForm({ category_id: categories[0]?.id || 0, name: '', code: '', description: '', is_active: true }); setModal({ open: true }); };
  const openEdit = (s: SubCategory) => { setForm({ category_id: s.category_id, name: s.name, code: s.code || '', description: s.description || '', is_active: s.is_active }); setModal({ open: true, editing: s }); };
  const save = () => {
    if (!form.name.trim()) return;
    if (modal.editing) setSubCategories(prev => prev.map(s => s.id === modal.editing!.id ? { ...s, ...form } : s));
    else setSubCategories(prev => [...prev, { id: Date.now(), ...form }]);
    setModal({ open: false });
  };
  const del = (s: SubCategory) => { if (confirm(`Hapus sub kategori "${s.name}"?`)) setSubCategories(prev => prev.filter(x => x.id !== s.id)); };
  const toggle = (s: SubCategory) => setSubCategories(prev => prev.map(x => x.id === s.id ? { ...x, is_active: !x.is_active } : x));

  const catName = (id: number) => categories.find(c => c.id === id)?.name || '—';
  const list = subCategories.filter(s =>
    (s.name + ' ' + (s.code || '') + ' ' + catName(s.category_id)).toLowerCase().includes(search.toLowerCase())
  );

  const categoryOptions: SelectOption[] = categories.map(c => ({ value: c.id, label: c.name }));

  return (
    <ListCardWrapper>
      <UICardHeader>
        <div className="flex items-center justify-between w-full">
          <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
            <span className="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
              <Layers className="w-4 h-4" />
            </span>
            Sub Kategori
            <span className="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{subCategories.length}</span>
          </div>
          <Button size="sm" icon={<Plus className="w-3.5 h-3.5" />} onClick={openAdd}>
            Tambah
          </Button>
        </div>
      </UICardHeader>
      <div className="px-6 py-3 border-b border-slate-100">
        <div className="relative">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            value={search}
            onChange={e => setSearch(e.target.value)}
            placeholder="Cari sub kategori..."
            className="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all"
          />
        </div>
      </div>
      <div className="divide-y divide-slate-100">
        {list.map(s => (
          <ListItem key={s.id} inactive={!s.is_active}>
            <div>
              <div className="font-bold text-xs text-slate-800">{s.name}</div>
              <div className="text-[10px] text-slate-400 mt-0.5">
                <span className="text-primary-600 font-semibold">{catName(s.category_id)}</span>
                {s.code ? ` • #${s.code}` : ''}{s.description ? ` • ${s.description}` : ''}
              </div>
            </div>
            <div className="flex items-center gap-3">
              <ActiveBadge active={s.is_active} />
              <Toggle checked={s.is_active} onChange={() => toggle(s)} />
              <ActionButtons onEdit={() => openEdit(s)} onDelete={() => del(s)} />
            </div>
          </ListItem>
        ))}
        {list.length === 0 && <EmptyState icon={<Layers className="w-8 h-8" />} text="Tidak ada sub kategori ditemukan" />}
      </div>

      <Modal
        open={modal.open}
        onClose={() => setModal({ open: false })}
        title={modal.editing ? 'Edit Sub Kategori' : 'Tambah Sub Kategori'}
        size="md"
        footer={
          <>
            <Button variant="secondary" onClick={() => setModal({ open: false })}>Batal</Button>
            <Button onClick={save}>Simpan</Button>
          </>
        }
      >
        <div className="space-y-3">
          <Select label="Kategori" options={categoryOptions} value={form.category_id} onChange={e => setForm({ ...form, category_id: Number(e.target.value) })} />
          <div className="grid grid-cols-2 gap-3">
            <Input label="Nama" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} placeholder="Oli Mesin" />
            <Input label="Kode" value={form.code} onChange={e => setForm({ ...form, code: e.target.value })} placeholder="SBC" />
          </div>
          <Input label="Deskripsi" value={form.description} onChange={e => setForm({ ...form, description: e.target.value })} placeholder="Deskripsi" />
        </div>
      </Modal>
    </ListCardWrapper>
  );
};

// --- Brand ---
const BrandsTab: React.FC = () => {
  const { brands, setBrands } = useApp();
  const [search, setSearch] = useState('');
  const [modal, setModal] = useState<{ open: boolean; editing?: Brand }>({ open: false });
  const [form, setForm] = useState({ name: '', description: '', is_active: true });

  const openAdd = () => { setForm({ name: '', description: '', is_active: true }); setModal({ open: true }); };
  const openEdit = (b: Brand) => { setForm({ name: b.name, description: b.description || '', is_active: b.is_active }); setModal({ open: true, editing: b }); };
  const save = () => {
    if (!form.name.trim()) return;
    if (modal.editing) setBrands(prev => prev.map(b => b.id === modal.editing!.id ? { ...b, ...form } : b));
    else setBrands(prev => [...prev, { id: Date.now(), ...form }]);
    setModal({ open: false });
  };
  const del = (b: Brand) => { if (confirm(`Hapus brand "${b.name}"?`)) setBrands(prev => prev.filter(x => x.id !== b.id)); };
  const toggle = (b: Brand) => setBrands(prev => prev.map(x => x.id === b.id ? { ...x, is_active: !x.is_active } : x));

  const list = brands.filter(b => b.name.toLowerCase().includes(search.toLowerCase()));

  return (
    <ListCardWrapper>
      <UICardHeader>
        <div className="flex items-center justify-between w-full">
          <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
            <span className="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
              <Tag className="w-4 h-4" />
            </span>
            Brand
            <span className="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{brands.length}</span>
          </div>
          <Button size="sm" icon={<Plus className="w-3.5 h-3.5" />} onClick={openAdd}>
            Tambah
          </Button>
        </div>
      </UICardHeader>
      <div className="px-6 py-3 border-b border-slate-100">
        <div className="relative">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            value={search}
            onChange={e => setSearch(e.target.value)}
            placeholder="Cari brand..."
            className="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all"
          />
        </div>
      </div>
      <div className="divide-y divide-slate-100">
        {list.map(b => (
          <ListItem key={b.id} inactive={!b.is_active}>
            <div>
              <div className="font-bold text-xs text-slate-800">{b.name}</div>
              <div className="text-[10px] text-slate-400 mt-0.5">{b.description || '—'}</div>
            </div>
            <div className="flex items-center gap-3">
              <ActiveBadge active={b.is_active} />
              <Toggle checked={b.is_active} onChange={() => toggle(b)} />
              <ActionButtons onEdit={() => openEdit(b)} onDelete={() => del(b)} />
            </div>
          </ListItem>
        ))}
        {list.length === 0 && <EmptyState icon={<Tag className="w-8 h-8" />} text="Tidak ada brand ditemukan" />}
      </div>

      <Modal
        open={modal.open}
        onClose={() => setModal({ open: false })}
        title={modal.editing ? 'Edit Brand' : 'Tambah Brand'}
        size="md"
        footer={
          <>
            <Button variant="secondary" onClick={() => setModal({ open: false })}>Batal</Button>
            <Button onClick={save}>Simpan</Button>
          </>
        }
      >
        <div className="space-y-3">
          <Input label="Nama" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} placeholder="Yamaha" />
          <Input label="Deskripsi" value={form.description} onChange={e => setForm({ ...form, description: e.target.value })} placeholder="Deskripsi" />
        </div>
      </Modal>
    </ListCardWrapper>
  );
};

// --- Maker ---
const MakersTab: React.FC = () => {
  const { makers, setMakers } = useApp();
  const [search, setSearch] = useState('');
  const [modal, setModal] = useState<{ open: boolean; editing?: ProductMaker }>({ open: false });
  const [form, setForm] = useState({ name: '', description: '', is_active: true });

  const openAdd = () => { setForm({ name: '', description: '', is_active: true }); setModal({ open: true }); };
  const openEdit = (m: ProductMaker) => { setForm({ name: m.name, description: m.description || '', is_active: m.is_active }); setModal({ open: true, editing: m }); };
  const save = () => {
    if (!form.name.trim()) return;
    if (modal.editing) setMakers(prev => prev.map(m => m.id === modal.editing!.id ? { ...m, ...form } : m));
    else setMakers(prev => [...prev, { id: Date.now(), ...form }]);
    setModal({ open: false });
  };
  const del = (m: ProductMaker) => { if (confirm(`Hapus maker "${m.name}"?`)) setMakers(prev => prev.filter(x => x.id !== m.id)); };
  const toggle = (m: ProductMaker) => setMakers(prev => prev.map(x => x.id === m.id ? { ...x, is_active: !x.is_active } : x));

  const list = makers.filter(m => m.name.toLowerCase().includes(search.toLowerCase()));

  return (
    <ListCardWrapper>
      <UICardHeader>
        <div className="flex items-center justify-between w-full">
          <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
            <span className="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
              <Factory className="w-4 h-4" />
            </span>
            Maker
            <span className="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{makers.length}</span>
          </div>
          <Button size="sm" icon={<Plus className="w-3.5 h-3.5" />} onClick={openAdd}>
            Tambah
          </Button>
        </div>
      </UICardHeader>
      <div className="px-6 py-3 border-b border-slate-100">
        <div className="relative">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            value={search}
            onChange={e => setSearch(e.target.value)}
            placeholder="Cari maker..."
            className="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all"
          />
        </div>
      </div>
      <div className="divide-y divide-slate-100">
        {list.map(m => (
          <ListItem key={m.id} inactive={!m.is_active}>
            <div>
              <div className="font-bold text-xs text-slate-800">{m.name}</div>
              <div className="text-[10px] text-slate-400 mt-0.5">{m.description || '—'}</div>
            </div>
            <div className="flex items-center gap-3">
              <ActiveBadge active={m.is_active} />
              <Toggle checked={m.is_active} onChange={() => toggle(m)} />
              <ActionButtons onEdit={() => openEdit(m)} onDelete={() => del(m)} />
            </div>
          </ListItem>
        ))}
        {list.length === 0 && <EmptyState icon={<Factory className="w-8 h-8" />} text="Tidak ada maker ditemukan" />}
      </div>

      <Modal
        open={modal.open}
        onClose={() => setModal({ open: false })}
        title={modal.editing ? 'Edit Maker' : 'Tambah Maker'}
        size="md"
        footer={
          <>
            <Button variant="secondary" onClick={() => setModal({ open: false })}>Batal</Button>
            <Button onClick={save}>Simpan</Button>
          </>
        }
      >
        <div className="space-y-3">
          <Input label="Nama" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} placeholder="PT. Sparepart Indonesia" />
          <Input label="Deskripsi" value={form.description} onChange={e => setForm({ ...form, description: e.target.value })} placeholder="Deskripsi" />
        </div>
      </Modal>
    </ListCardWrapper>
  );
};

// --- ProductType ---
const ProductTypesTab: React.FC = () => {
  const { productTypes, setProductTypes, brands } = useApp();
  const [search, setSearch] = useState('');
  const [modal, setModal] = useState<{ open: boolean; editing?: ProductType }>({ open: false });
  const [form, setForm] = useState({ brand_id: brands[0]?.id || 0, name: '', code: '', is_active: true });

  const openAdd = () => { setForm({ brand_id: brands[0]?.id || 0, name: '', code: '', is_active: true }); setModal({ open: true }); };
  const openEdit = (t: ProductType) => { setForm({ brand_id: t.brand_id || brands[0]?.id || 0, name: t.name, code: t.code || '', is_active: t.is_active }); setModal({ open: true, editing: t }); };
  const save = () => {
    if (!form.name.trim()) return;
    if (modal.editing) setProductTypes(prev => prev.map(t => t.id === modal.editing!.id ? { ...t, ...form } : t));
    else setProductTypes(prev => [...prev, { id: Date.now(), ...form }]);
    setModal({ open: false });
  };
  const del = (t: ProductType) => { if (confirm(`Hapus tipe produk "${t.name}"?`)) setProductTypes(prev => prev.filter(x => x.id !== t.id)); };
  const toggle = (t: ProductType) => setProductTypes(prev => prev.map(x => x.id === t.id ? { ...x, is_active: !x.is_active } : x));

  const brandName = (id?: number) => brands.find(b => b.id === id)?.name || '—';
  const list = productTypes.filter(t =>
    (t.name + ' ' + (t.code || '') + ' ' + brandName(t.brand_id)).toLowerCase().includes(search.toLowerCase())
  );

  const brandOptions: SelectOption[] = brands.map(b => ({ value: b.id, label: b.name }));

  return (
    <ListCardWrapper>
      <UICardHeader>
        <div className="flex items-center justify-between w-full">
          <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
            <span className="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
              <Component className="w-4 h-4" />
            </span>
            Tipe Produk
            <span className="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{productTypes.length}</span>
          </div>
          <Button size="sm" icon={<Plus className="w-3.5 h-3.5" />} onClick={openAdd}>
            Tambah
          </Button>
        </div>
      </UICardHeader>
      <div className="px-6 py-3 border-b border-slate-100">
        <div className="relative">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            value={search}
            onChange={e => setSearch(e.target.value)}
            placeholder="Cari tipe produk..."
            className="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all"
          />
        </div>
      </div>
      <div className="divide-y divide-slate-100">
        {list.map(t => (
          <ListItem key={t.id} inactive={!t.is_active}>
            <div>
              <div className="font-bold text-xs text-slate-800">{t.name}</div>
              <div className="text-[10px] text-slate-400 mt-0.5">
                <span className="text-primary-600 font-semibold">{brandName(t.brand_id)}</span>
                {t.code ? ` • ${t.code}` : ''}
              </div>
            </div>
            <div className="flex items-center gap-3">
              <ActiveBadge active={t.is_active} />
              <Toggle checked={t.is_active} onChange={() => toggle(t)} />
              <ActionButtons onEdit={() => openEdit(t)} onDelete={() => del(t)} />
            </div>
          </ListItem>
        ))}
        {list.length === 0 && <EmptyState icon={<Component className="w-8 h-8" />} text="Tidak ada tipe produk ditemukan" />}
      </div>

      <Modal
        open={modal.open}
        onClose={() => setModal({ open: false })}
        title={modal.editing ? 'Edit Tipe Produk' : 'Tambah Tipe Produk'}
        size="md"
        footer={
          <>
            <Button variant="secondary" onClick={() => setModal({ open: false })}>Batal</Button>
            <Button onClick={save}>Simpan</Button>
          </>
        }
      >
        <div className="space-y-3">
          <Select label="Brand" options={brandOptions} value={form.brand_id} onChange={e => setForm({ ...form, brand_id: Number(e.target.value) })} />
          <div className="grid grid-cols-2 gap-3">
            <Input label="Nama" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} placeholder="Filter Oli" />
            <Input label="Kode" value={form.code} onChange={e => setForm({ ...form, code: e.target.value })} placeholder="TPD" />
          </div>
        </div>
      </Modal>
    </ListCardWrapper>
  );
};

// --- Unit ---
const UnitsTab: React.FC = () => {
  const { units, setUnits } = useApp();
  const [search, setSearch] = useState('');
  const [modal, setModal] = useState<{ open: boolean; editing?: Unit }>({ open: false });
  const [form, setForm] = useState({ name: '', short_name: '', is_active: true });

  const openAdd = () => { setForm({ name: '', short_name: '', is_active: true }); setModal({ open: true }); };
  const openEdit = (u: Unit) => { setForm({ name: u.name, short_name: u.short_name || '', is_active: u.is_active }); setModal({ open: true, editing: u }); };
  const save = () => {
    if (!form.name.trim()) return;
    if (modal.editing) setUnits(prev => prev.map(u => u.id === modal.editing!.id ? { ...u, ...form } : u));
    else setUnits(prev => [...prev, { id: Date.now(), ...form }]);
    setModal({ open: false });
  };
  const del = (u: Unit) => { if (confirm(`Hapus satuan "${u.name}"?`)) setUnits(prev => prev.filter(x => x.id !== u.id)); };
  const toggle = (u: Unit) => setUnits(prev => prev.map(x => x.id === u.id ? { ...x, is_active: !x.is_active } : x));

  const list = units.filter(u => (u.name + ' ' + (u.short_name || '')).toLowerCase().includes(search.toLowerCase()));

  return (
    <ListCardWrapper>
      <UICardHeader>
        <div className="flex items-center justify-between w-full">
          <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
            <span className="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
              <Ruler className="w-4 h-4" />
            </span>
            Satuan
            <span className="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{units.length}</span>
          </div>
          <Button size="sm" icon={<Plus className="w-3.5 h-3.5" />} onClick={openAdd}>
            Tambah
          </Button>
        </div>
      </UICardHeader>
      <div className="px-6 py-3 border-b border-slate-100">
        <div className="relative">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            value={search}
            onChange={e => setSearch(e.target.value)}
            placeholder="Cari satuan..."
            className="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all"
          />
        </div>
      </div>
      <div className="divide-y divide-slate-100">
        {list.map(u => (
          <ListItem key={u.id} inactive={!u.is_active}>
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-[10px] uppercase">{u.short_name || u.name.slice(0, 3)}</div>
              <div>
                <div className="font-bold text-xs text-slate-800">{u.name}</div>
                <div className="text-[10px] text-slate-400 mt-0.5">{u.short_name ? `Singkatan: ${u.short_name}` : '—'}</div>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <ActiveBadge active={u.is_active} />
              <Toggle checked={u.is_active} onChange={() => toggle(u)} />
              <ActionButtons onEdit={() => openEdit(u)} onDelete={() => del(u)} />
            </div>
          </ListItem>
        ))}
        {list.length === 0 && <EmptyState icon={<Ruler className="w-8 h-8" />} text="Tidak ada satuan ditemukan" />}
      </div>

      <Modal
        open={modal.open}
        onClose={() => setModal({ open: false })}
        title={modal.editing ? 'Edit Satuan' : 'Tambah Satuan'}
        size="md"
        footer={
          <>
            <Button variant="secondary" onClick={() => setModal({ open: false })}>Batal</Button>
            <Button onClick={save}>Simpan</Button>
          </>
        }
      >
        <div className="grid grid-cols-2 gap-3">
          <Input label="Nama" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} placeholder="Pieces" />
          <Input label="Singkatan" value={form.short_name} onChange={e => setForm({ ...form, short_name: e.target.value })} placeholder="pcs" />
        </div>
      </Modal>
    </ListCardWrapper>
  );
};

// --- Supplier ---
const SuppliersTab: React.FC = () => {
  const { suppliers, setSuppliers } = useApp();
  const [search, setSearch] = useState('');
  const [modal, setModal] = useState<{ open: boolean; editing?: Supplier }>({ open: false });
  const [form, setForm] = useState({ code: '', name: '', contact_person: '', phone: '', email: '', address: '', is_active: true });

  const openAdd = () => { setForm({ code: '', name: '', contact_person: '', phone: '', email: '', address: '', is_active: true }); setModal({ open: true }); };
  const openEdit = (s: Supplier) => {
    setForm({ code: s.code || '', name: s.name, contact_person: s.contact_person || '', phone: s.phone || '', email: s.email || '', address: s.address || '', is_active: s.is_active });
    setModal({ open: true, editing: s });
  };
  const save = () => {
    if (!form.code.trim() || !form.name.trim()) return;
    if (modal.editing) setSuppliers(prev => prev.map(s => s.id === modal.editing!.id ? { ...s, ...form } : s));
    else setSuppliers(prev => [...prev, { id: Date.now(), ...form }]);
    setModal({ open: false });
  };
  const del = (s: Supplier) => { if (confirm(`Hapus supplier "${s.name}"?`)) setSuppliers(prev => prev.filter(x => x.id !== s.id)); };
  const toggle = (s: Supplier) => setSuppliers(prev => prev.map(x => x.id === s.id ? { ...x, is_active: !x.is_active } : x));

  const list = suppliers.filter(s =>
    (s.code + ' ' + s.name + ' ' + (s.contact_person || '')).toLowerCase().includes(search.toLowerCase())
  );

  return (
    <ListCardWrapper>
      <UICardHeader>
        <div className="flex items-center justify-between w-full">
          <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
            <span className="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
              <Truck className="w-4 h-4" />
            </span>
            Supplier
            <span className="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{suppliers.length}</span>
          </div>
          <Button size="sm" icon={<Plus className="w-3.5 h-3.5" />} onClick={openAdd}>
            Tambah
          </Button>
        </div>
      </UICardHeader>
      <div className="px-6 py-3 border-b border-slate-100">
        <div className="relative">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            value={search}
            onChange={e => setSearch(e.target.value)}
            placeholder="Cari kode, nama, atau kontak..."
            className="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 transition-all"
          />
        </div>
      </div>
      <div className="divide-y divide-slate-100">
        {list.map(s => (
          <ListItem key={s.id} inactive={!s.is_active}>
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-[10px]">{s.code}</div>
              <div>
                <div className="font-bold text-xs text-slate-800">{s.name}</div>
                <div className="text-[10px] text-slate-400 mt-0.5">
                  {[s.contact_person, s.phone, s.email].filter(Boolean).join(' • ') || '—'}
                  {s.address ? ` • ${s.address}` : ''}
                </div>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <ActiveBadge active={s.is_active} />
              <Toggle checked={s.is_active} onChange={() => toggle(s)} />
              <ActionButtons onEdit={() => openEdit(s)} onDelete={() => del(s)} />
            </div>
          </ListItem>
        ))}
        {list.length === 0 && <EmptyState icon={<Truck className="w-8 h-8" />} text="Tidak ada supplier ditemukan" />}
      </div>

      <Modal
        open={modal.open}
        onClose={() => setModal({ open: false })}
        title={modal.editing ? 'Edit Supplier' : 'Tambah Supplier'}
        size="md"
        footer={
          <>
            <Button variant="secondary" onClick={() => setModal({ open: false })}>Batal</Button>
            <Button onClick={save}>Simpan</Button>
          </>
        }
      >
        <div className="space-y-3">
          <div className="grid grid-cols-2 gap-3">
            <Input label="Kode" value={form.code} onChange={e => setForm({ ...form, code: e.target.value })} placeholder="SUP" />
            <Input label="Nama" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} placeholder="PT. Supplier" />
          </div>
          <div className="grid grid-cols-2 gap-3">
            <Input label="Kontak" value={form.contact_person} onChange={e => setForm({ ...form, contact_person: e.target.value })} placeholder="Andi" />
            <Input label="Telepon" value={form.phone} onChange={e => setForm({ ...form, phone: e.target.value })} placeholder="08xx" />
          </div>
          <Input label="Email" value={form.email} onChange={e => setForm({ ...form, email: e.target.value })} placeholder="email@example.com" />
          <Input label="Alamat" value={form.address} onChange={e => setForm({ ...form, address: e.target.value })} placeholder="Alamat" />
        </div>
      </Modal>
    </ListCardWrapper>
  );
};

// ---------- Main View ----------

const TABS = [
  { id: 'cabang', label: 'Cabang', icon: <Building className="w-3.5 h-3.5" /> },
  { id: 'lokasi', label: 'Lokasi & Rak', icon: <MapPin className="w-3.5 h-3.5" /> },
  { id: 'kategori', label: 'Kategori', icon: <Boxes className="w-3.5 h-3.5" /> },
  { id: 'subkategori', label: 'Sub Kategori', icon: <Layers className="w-3.5 h-3.5" /> },
  { id: 'brand', label: 'Brand', icon: <Tag className="w-3.5 h-3.5" /> },
  { id: 'maker', label: 'Maker', icon: <Factory className="w-3.5 h-3.5" /> },
  { id: 'tipe', label: 'Tipe Produk', icon: <Component className="w-3.5 h-3.5" /> },
  { id: 'satuan', label: 'Satuan', icon: <Ruler className="w-3.5 h-3.5" /> },
  { id: 'supplier', label: 'Supplier', icon: <Truck className="w-3.5 h-3.5" /> },
];

export const MasterDataView: React.FC = () => {
  const [activeTab, setActiveTab] = useState('cabang');

  const render = () => {
    switch (activeTab) {
      case 'cabang': return <BranchesTab />;
      case 'lokasi': return <LocationsTab />;
      case 'kategori': return <CategoriesTab />;
      case 'subkategori': return <SubCategoriesTab />;
      case 'brand': return <BrandsTab />;
      case 'maker': return <MakersTab />;
      case 'tipe': return <ProductTypesTab />;
      case 'satuan': return <UnitsTab />;
      case 'supplier': return <SuppliersTab />;
    }
  };

  return (
    <div className="p-6 space-y-6 max-w-7xl mx-auto">
      <div className="flex items-center gap-2">
        <h1 className="text-2xl font-black text-slate-900 tracking-tight">Master Data</h1>
        <span className="w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
          <Database className="w-4 h-4" />
        </span>
      </div>
      <p className="text-xs text-slate-500 -mt-4">
        Kelola referensi data master: cabang, lokasi & rak, kategori, brand, satuan, dan supplier.
      </p>

      <Tabs
        tabs={TABS}
        activeTab={activeTab}
        onChange={setActiveTab}
      />

      {render()}
      <p className="text-center text-[10px] text-slate-400">
        <Package className="w-3 h-3 inline mr-1" />
        Data tersimpan otomatis di perangkat ini.
      </p>
    </div>
  );
};
