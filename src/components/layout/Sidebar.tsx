import React from 'react';
import { useApp } from '../../context/AppContext';
import {
  LayoutDashboard,
  ShoppingCart,
  Package,
  ArrowLeftRight,
  Truck,
  Wrench,
  Building2,
  BarChart3,
  Users,
  Database,
  Printer,
  Globe,
  Store
} from 'lucide-react';

interface SidebarProps {
  isOpen: boolean;
  setIsOpen: (open: boolean) => void;
}

export const Sidebar: React.FC<SidebarProps> = ({ isOpen, setIsOpen }) => {
  const { currentView, setCurrentView, cart, activeCashSession, serviceTransactions, products } = useApp();

  const lowStockCount = products.filter(p => (p.stock_global || 0) <= (p.stock_min || 0)).length;
  const pendingServiceCount = serviceTransactions.filter(s => s.status === 'pending' || s.status === 'in_progress').length;

  const menuItems = [
    { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { id: 'pos', label: 'POS Kasir', icon: ShoppingCart, badge: cart.length > 0 ? `${cart.length}` : (activeCashSession ? 'Open' : undefined), badgeColor: activeCashSession ? 'bg-emerald-500' : 'bg-primary-500' },
    { id: 'products', label: 'Master Produk & Stok', icon: Package, badge: lowStockCount > 0 ? `${lowStockCount}` : undefined, badgeColor: 'bg-amber-500' },
    { id: 'stock-movements', label: 'Mutasi Stok Ledger', icon: ArrowLeftRight },
    { id: 'branch-transfers', label: 'Transfer Cabang', icon: Store },
    { id: 'purchase-orders', label: 'Purchase Order (PO)', icon: Truck },
    { id: 'services', label: 'Service Center HP', icon: Wrench, badge: pendingServiceCount > 0 ? `${pendingServiceCount}` : undefined, badgeColor: 'bg-blue-500' },
    { id: 'back-office', label: 'Back Office & Kas', icon: Building2 },
    { id: 'reports', label: 'Laporan & Laba Rugi', icon: BarChart3 },
    { id: 'customers', label: 'Pelanggan & Poin', icon: Users },
    { id: 'master-data', label: 'Master Data', icon: Database },
    { id: 'settings', label: 'Pengaturan & Backup', icon: Printer },
    { id: 'website', label: 'Katalog Website', icon: Globe },
  ];

  return (
    <>
      {/* Mobile Backdrop */}
      {isOpen && (
        <div
          className="fixed inset-0 bg-slate-900/50 z-40 lg:hidden"
          onClick={() => setIsOpen(false)}
        />
      )}

      <aside
        className={`fixed top-0 left-0 bottom-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 ${
          isOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        {/* Brand Header */}
        <div className="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800/80 gap-3">
          <div className="w-9 h-9 rounded-lg bg-gradient-to-tr from-primary-600 to-indigo-500 flex items-center justify-center font-black text-white text-lg shadow-lg shadow-primary-500/30">
            UT
          </div>
          <div>
            <div className="font-bold text-white text-base tracking-wide flex items-center gap-1.5">
              UTE PARTS
              <span className="text-[10px] uppercase tracking-wider bg-primary-500/20 text-primary-400 font-semibold px-1.5 py-0.5 rounded border border-primary-500/30">
                POS
              </span>
            </div>
            <div className="text-[11px] text-slate-400 truncate">Sparepart & Servis HP</div>
          </div>
        </div>

        {/* Menu Navigation */}
        <div className="flex-1 overflow-y-auto py-4 px-3 space-y-1">
          {menuItems.map(item => {
            const Icon = item.icon;
            const isActive = currentView === item.id;
            return (
              <button
                key={item.id}
                onClick={() => {
                  setCurrentView(item.id);
                  if (window.innerWidth < 1024) setIsOpen(false);
                }}
                className={`w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all text-left ${
                  isActive
                    ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30 font-semibold'
                    : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60'
                }`}
              >
                <Icon className={`w-5 h-5 flex-shrink-0 ${isActive ? 'text-white' : 'text-slate-400'}`} />
                <span className="flex-1 truncate">{item.label}</span>
                {item.badge && (
                  <span className={`text-[10px] font-bold text-white px-2 py-0.5 rounded-full ${item.badgeColor || 'bg-primary-500'}`}>
                    {item.badge}
                  </span>
                )}
              </button>
            );
          })}
        </div>

        {/* Footer info */}
        <div className="p-4 border-t border-slate-800 bg-slate-950/50">
          <div className="text-xs text-slate-400">
            <span className="font-semibold text-slate-200">Google AI Studio Ready</span>
            <div className="text-[11px] text-slate-500 mt-0.5">Version 1.0.0 (Vite + React)</div>
          </div>
        </div>
      </aside>
    </>
  );
};
