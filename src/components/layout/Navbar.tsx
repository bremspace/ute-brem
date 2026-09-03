import React, { useState } from 'react';
import { useApp } from '../../context/AppContext';
import {
  Menu,
  Building,
  MapPin,
  CircleDollarSign,
  User,
  LogOut,
  Sparkles,
  ChevronDown
} from 'lucide-react';
import { CashSessionModal } from '../pos/CashSessionModal';

interface NavbarProps {
  onToggleSidebar: () => void;
}

export const Navbar: React.FC<NavbarProps> = ({ onToggleSidebar }) => {
  const {
    branches,
    selectedBranchId,
    setSelectedBranchId,
    locations,
    selectedLocationId,
    setSelectedLocationId,
    activeCashSession,
    currentUser,
    setCurrentUser
  } = useApp();

  const [showCashSessionModal, setShowCashSessionModal] = useState(false);
  const [showUserDropdown, setShowUserDropdown] = useState(false);

  const currentBranch = branches.find(b => b.id === selectedBranchId) || branches[0];
  const filteredLocations = locations.filter(l => l.branch_id === selectedBranchId || !l.branch_id);
  const currentLocation = locations.find(l => l.id === selectedLocationId) || filteredLocations[0];

  return (
    <>
      <header className="h-16 bg-white border-b border-slate-200 sticky top-0 z-30 flex items-center justify-between px-4 lg:px-8 shadow-sm">
        {/* Left Side: Mobile Hamburger & Branch Selector */}
        <div className="flex items-center gap-3 lg:gap-6">
          <button
            onClick={onToggleSidebar}
            className="p-2 rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden"
            aria-label="Toggle Navigation"
          >
            <Menu className="w-6 h-6" />
          </button>

          {/* Branch & Location Switchers */}
          <div className="flex items-center gap-2">
            <div className="flex items-center gap-1.5 bg-slate-100/90 text-slate-800 text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200">
              <Building className="w-3.5 h-3.5 text-primary-600" />
              <select
                value={selectedBranchId}
                onChange={e => {
                  const branchId = Number(e.target.value);
                  setSelectedBranchId(branchId);
                  const firstLoc = locations.find(l => l.branch_id === branchId);
                  if (firstLoc) setSelectedLocationId(firstLoc.id);
                }}
                className="bg-transparent font-semibold text-slate-800 focus:outline-none cursor-pointer text-xs"
              >
                {branches.map(b => (
                  <option key={b.id} value={b.id}>
                    {b.name}
                  </option>
                ))}
              </select>
            </div>

            <div className="hidden sm:flex items-center gap-1.5 bg-slate-100/90 text-slate-800 text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200">
              <MapPin className="w-3.5 h-3.5 text-emerald-600" />
              <select
                value={selectedLocationId}
                onChange={e => setSelectedLocationId(Number(e.target.value))}
                className="bg-transparent font-semibold text-slate-800 focus:outline-none cursor-pointer text-xs"
              >
                {filteredLocations.map(l => (
                  <option key={l.id} value={l.id}>
                    {l.name}
                  </option>
                ))}
              </select>
            </div>
          </div>
        </div>

        {/* Right Side: Cash Session & User Profile */}
        <div className="flex items-center gap-3">
          {/* Cash Session Indicator Button */}
          <button
            onClick={() => setShowCashSessionModal(true)}
            className={`flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border ${
              activeCashSession
                ? 'bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100'
                : 'bg-amber-50 text-amber-700 border-amber-300 hover:bg-amber-100'
            }`}
          >
            <CircleDollarSign className={`w-4 h-4 ${activeCashSession ? 'text-emerald-600' : 'text-amber-600'}`} />
            <span className="hidden md:inline">
              {activeCashSession
                ? `Kas Aktif: Rp ${(activeCashSession.opening_cash || 0).toLocaleString('id-ID')}`
                : 'Buka Kasir'}
            </span>
            <span className="md:hidden">{activeCashSession ? 'Kas Buka' : 'Buka Kas'}</span>
          </button>

          {/* User Profile dropdown */}
          <div className="relative">
            <button
              onClick={() => setShowUserDropdown(!showUserDropdown)}
              className="flex items-center gap-2.5 p-1.5 pr-2.5 rounded-xl hover:bg-slate-100 transition-colors"
            >
              <div className="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
                {currentUser.name.charAt(0)}
              </div>
              <div className="text-left hidden sm:block">
                <div className="text-xs font-bold text-slate-800 leading-tight">{currentUser.name}</div>
                <div className="text-[10px] text-slate-500 font-medium capitalize">{currentUser.role_display_name}</div>
              </div>
              <ChevronDown className="w-3.5 h-3.5 text-slate-400" />
            </button>

            {showUserDropdown && (
              <div className="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 py-2 z-50 animate-in fade-in zoom-in-95">
                <div className="px-4 py-2 border-b border-slate-100">
                  <div className="text-xs font-bold text-slate-800">{currentUser.name}</div>
                  <div className="text-[11px] text-slate-500">{currentUser.email}</div>
                  <span className="inline-block mt-1 text-[10px] uppercase font-bold bg-primary-50 text-primary-600 px-2 py-0.5 rounded-full">
                    {currentUser.role_display_name}
                  </span>
                </div>
                <div className="py-1">
                  <div className="px-4 py-1.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                    Ganti Role Akses:
                  </div>
                  <button
                    onClick={() => {
                      setCurrentUser({ ...currentUser, role: 'super_admin', role_display_name: 'Super Admin' });
                      setShowUserDropdown(false);
                    }}
                    className="w-full text-left px-4 py-1.5 text-xs text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                  >
                    <span>Super Admin / Owner</span>
                    {currentUser.role === 'super_admin' && <Sparkles className="w-3.5 h-3.5 text-primary-600" />}
                  </button>
                  <button
                    onClick={() => {
                      setCurrentUser({ ...currentUser, role: 'cashier', role_display_name: 'Kasir Toko' });
                      setShowUserDropdown(false);
                    }}
                    className="w-full text-left px-4 py-1.5 text-xs text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                  >
                    <span>Kasir POS</span>
                    {currentUser.role === 'cashier' && <Sparkles className="w-3.5 h-3.5 text-primary-600" />}
                  </button>
                  <button
                    onClick={() => {
                      setCurrentUser({ ...currentUser, role: 'technician', role_display_name: 'Teknisi Servis' });
                      setShowUserDropdown(false);
                    }}
                    className="w-full text-left px-4 py-1.5 text-xs text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                  >
                    <span>Teknisi Servis</span>
                    {currentUser.role === 'technician' && <Sparkles className="w-3.5 h-3.5 text-primary-600" />}
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
      </header>

      {/* Cash Session Modal */}
      {showCashSessionModal && (
        <CashSessionModal onClose={() => setShowCashSessionModal(false)} />
      )}
    </>
  );
};
