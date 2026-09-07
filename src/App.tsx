import React, { useState } from 'react';
import { useApp } from './context/AppContext';
import { Sidebar } from './components/layout/Sidebar';
import { Navbar } from './components/layout/Navbar';
import { DashboardView } from './views/DashboardView';
import { PosView } from './views/PosView';
import { ProductsView } from './views/ProductsView';
import { StockMovementsView } from './views/StockMovementsView';
import { BranchTransfersView } from './views/BranchTransfersView';
import { PurchaseOrdersView } from './views/PurchaseOrdersView';
import { ServicesView } from './views/ServicesView';
import { BackOfficeView } from './views/BackOfficeView';
import { ReportsView } from './views/ReportsView';
import { CustomersView } from './views/CustomersView';
import { MasterDataView } from './views/MasterDataView';
import { SettingsView } from './views/SettingsView';
import { WebsiteCatalogView } from './views/WebsiteCatalogView';
import { AccountingView } from './views/AccountingView';
import { StockOpnameView } from './views/StockOpnameView';
import { PickingRequestsView } from './views/PickingRequestsView';
import { ItemSerialsView } from './views/ItemSerialsView';

export const App: React.FC = () => {
  const { currentView } = useApp();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const renderView = () => {
    switch (currentView) {
      case 'dashboard':
        return <DashboardView />;
      case 'pos':
        return <PosView />;
      case 'products':
        return <ProductsView />;
      case 'stock-movements':
        return <StockMovementsView />;
      case 'stock-opname':
        return <StockOpnameView />;
      case 'picking-requests':
        return <PickingRequestsView />;
      case 'item-serials':
        return <ItemSerialsView />;
      case 'branch-transfers':
        return <BranchTransfersView />;
      case 'purchase-orders':
        return <PurchaseOrdersView />;
      case 'services':
        return <ServicesView />;
      case 'back-office':
        return <BackOfficeView />;
      case 'reports':
        return <ReportsView />;
      case 'accounting':
        return <AccountingView />;
      case 'customers':
        return <CustomersView />;
      case 'master-data':
        return <MasterDataView />;
      case 'settings':
        return <SettingsView />;
      case 'website':
        return <WebsiteCatalogView />;
      default:
        return <DashboardView />;
    }
  };

  const isPos = currentView === 'pos';

  return (
    <div className="min-h-screen bg-[#f5f5f9]">
      <Sidebar isOpen={sidebarOpen} setIsOpen={setSidebarOpen} />
      <div className="lg:pl-64 flex flex-col min-h-screen">
        <Navbar onToggleSidebar={() => setSidebarOpen(open => !open)} />
        <main className={`flex-1 ${isPos ? '' : 'p-0'}`}>
          {renderView()}
        </main>
      </div>
    </div>
  );
};
