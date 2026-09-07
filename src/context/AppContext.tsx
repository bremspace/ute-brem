import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import {
  Branch,
  Location,
  LocationRack,
  Category,
  SubCategory,
  Brand,
  ProductMaker,
  ProductType,
  Unit,
  Supplier,
  CustomerGroup,
  Customer,
  CustomerPointLedger,
  Product,
  ProductStock,
  CartItem,
  CashSession,
  Sale,
  SaleItem,
  TransactionPayment,
  StockMovement,
  BranchTransfer,
  BranchTransferItem,
  PurchaseOrder,
  PurchaseOrderItem,
  ServiceItem,
  ServiceTransaction,
  ServiceTransactionItem,
  BackOfficeCashAccount,
  BackOfficeCostCategory,
  BackOfficeCashTransaction,
  BackOfficeEmployeeAdvance,
  BackOfficeStockDocument,
  User,
  PrinterSetting
} from '../types';
import {
  initialBranches,
  initialLocations,
  initialRacks,
  initialCategories,
  initialSubCategories,
  initialBrands,
  initialMakers,
  initialProductTypes,
  initialUnits,
  initialSuppliers,
  initialCustomerGroups,
  initialCustomers,
  initialProducts,
  initialUsers,
  initialServiceItems,
  initialCashAccounts,
  initialCostCategories,
  initialPrinterSetting
} from '../data/seedData';

interface AppContextType {
  // Navigation & Auth
  currentView: string;
  setCurrentView: (view: string) => void;
  currentUser: User;
  setCurrentUser: (user: User) => void;
  selectedBranchId: number;
  setSelectedBranchId: (id: number) => void;
  selectedLocationId: number;
  setSelectedLocationId: (id: number) => void;

  // Master Data
  branches: Branch[];
  setBranches: React.Dispatch<React.SetStateAction<Branch[]>>;
  locations: Location[];
  setLocations: React.Dispatch<React.SetStateAction<Location[]>>;
  racks: LocationRack[];
  setRacks: React.Dispatch<React.SetStateAction<LocationRack[]>>;
  categories: Category[];
  setCategories: React.Dispatch<React.SetStateAction<Category[]>>;
  subCategories: SubCategory[];
  setSubCategories: React.Dispatch<React.SetStateAction<SubCategory[]>>;
  brands: Brand[];
  setBrands: React.Dispatch<React.SetStateAction<Brand[]>>;
  makers: ProductMaker[];
  setMakers: React.Dispatch<React.SetStateAction<ProductMaker[]>>;
  productTypes: ProductType[];
  setProductTypes: React.Dispatch<React.SetStateAction<ProductType[]>>;
  units: Unit[];
  setUnits: React.Dispatch<React.SetStateAction<Unit[]>>;
  suppliers: Supplier[];
  setSuppliers: React.Dispatch<React.SetStateAction<Supplier[]>>;
  customerGroups: CustomerGroup[];
  setCustomerGroups: React.Dispatch<React.SetStateAction<CustomerGroup[]>>;
  customers: Customer[];
  setCustomers: React.Dispatch<React.SetStateAction<Customer[]>>;
  customerPointLedgers: CustomerPointLedger[];
  products: Product[];
  setProducts: React.Dispatch<React.SetStateAction<Product[]>>;
  serviceItems: ServiceItem[];
  setServiceItems: React.Dispatch<React.SetStateAction<ServiceItem[]>>;

  // POS & Sales
  activeCashSession: CashSession | null;
  openCashSession: (openingCash: number, notes?: string) => void;
  closeCashSession: (closingCash: number, notes?: string) => void;
  cart: CartItem[];
  addToCart: (product: Product, quantity?: number, customPrice?: number) => void;
  updateCartItemQty: (index: number, quantity: number) => void;
  updateCartItemPrice: (index: number, price: number) => void;
  updateCartItemDiscount: (index: number, discountAmount: number) => void;
  removeFromCart: (index: number) => void;
  clearCart: () => void;
  selectedCustomerId: number;
  setSelectedCustomerId: (id: number) => void;
  sales: Sale[];
  checkoutSale: (payload: {
    paymentMethod: 'cash' | 'transfer' | 'qris' | 'split' | 'tempo';
    paidAmount: number;
    payments?: { method: 'cash' | 'transfer' | 'qris'; amount: number; reference?: string }[];
    notes?: string;
    creditDueDate?: string;
    saleChannel?: 'toko' | 'cabang' | 'partai';
  }) => Sale;
  voidSale: (saleId: number, reason: string) => void;

  // Stock Movement & Transfers
  stockMovements: StockMovement[];
  branchTransfers: BranchTransfer[];
  createBranchTransfer: (payload: {
    sourceBranchId: number;
    targetBranchId: number;
    sourceLocationId: number;
    targetLocationId: number;
    items: { productId: number; quantity: number; notes?: string }[];
    notes?: string;
  }) => BranchTransfer;
  shipBranchTransfer: (transferId: number) => void;
  receiveBranchTransfer: (transferId: number, receivedItems: { productId: number; receivedQuantity: number }[]) => void;
  cancelBranchTransfer: (transferId: number) => void;

  // Purchase Orders
  purchaseOrders: PurchaseOrder[];
  createPurchaseOrder: (payload: {
    supplierId: number;
    locationId: number;
    orderDate: string;
    expectedDate?: string;
    items: { productId: number; quantity: number; unitPrice: number }[];
    notes?: string;
  }) => PurchaseOrder;
  receivePurchaseOrder: (poId: number, receivedItems: { productId: number; receivedQuantity: number }[]) => void;
  payPurchaseOrder: (poId: number, amount: number, cashAccountId: number) => void;

  // Service Center
  serviceTransactions: ServiceTransaction[];
  createServiceTransaction: (payload: Omit<Partial<ServiceTransaction>, 'items'> & {
    customerName: string;
    customerPhone: string;
    deviceBrand: string;
    deviceType: string;
    serialNumber?: string;
    complaint: string;
    checkNotes?: string;
    accessories?: string;
    deviceLockType?: string;
    deviceLockValue?: string;
    technicianId?: number;
    items?: { type: 'service' | 'product'; serviceId?: number; productId?: number; name: string; quantity: number; unitPrice: number; discountAmount?: number }[];
  }) => ServiceTransaction;
  updateServiceStatus: (serviceId: number, status: ServiceTransaction['status']) => void;
  completeServicePayment: (serviceId: number, paymentMethod: 'cash' | 'transfer' | 'qris' | 'tempo', paidAmount: number) => void;

  // Back Office & Cash
  cashAccounts: BackOfficeCashAccount[];
  setCashAccounts: React.Dispatch<React.SetStateAction<BackOfficeCashAccount[]>>;
  costCategories: BackOfficeCostCategory[];
  setCostCategories: React.Dispatch<React.SetStateAction<BackOfficeCostCategory[]>>;
  cashTransactions: BackOfficeCashTransaction[];
  addCashTransaction: (payload: {
    type: 'income' | 'expense';
    cashAccountId: number;
    amount: number;
    costCategoryId?: number;
    description?: string;
    reference?: string;
  }) => void;
  addCashMutation: (payload: {
    sourceAccountId: number;
    targetAccountId: number;
    amount: number;
    description?: string;
    reference?: string;
  }) => void;
  employeeAdvances: BackOfficeEmployeeAdvance[];
  addEmployeeAdvance: (payload: {
    employeeId: number;
    cashAccountId: number;
    amount: number;
    description?: string;
  }) => void;
  stockDocuments: BackOfficeStockDocument[];
  addStockDocument: (payload: {
    documentType: 'correction' | 'usage';
    movementType: string;
    productId: number;
    locationId: number;
    locationRackId?: number;
    quantity: number;
    description?: string;
  }) => void;

  // Customer Points
  redeemCustomerPoints: (customerId: number, points: number, notes?: string) => boolean;
  payCustomerCredit: (customerId: number, saleId: number, amount: number, paymentMethod: 'cash' | 'transfer' | 'qris') => void;

  // Settings & Printer
  printerSetting: PrinterSetting;
  updatePrinterSetting: (settings: Partial<PrinterSetting>) => void;
  lastCompletedSale: Sale | null;
  setLastCompletedSale: (sale: Sale | null) => void;

  // Backup & Reset Data
  exportDatabaseJson: () => void;
  importDatabaseJson: (jsonData: string) => boolean;
  resetToDefaultData: () => void;
}

const AppContext = createContext<AppContextType | undefined>(undefined);

const STORAGE_KEY = 'uteparts_pos_state_v1';

export const AppProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  // Navigation & Current User
  const [currentView, setCurrentView] = useState<string>('pos');
  const [currentUser, setCurrentUser] = useState<User>(initialUsers[0]);
  const [selectedBranchId, setSelectedBranchId] = useState<number>(1);
  const [selectedLocationId, setSelectedLocationId] = useState<number>(2); // Toko Kasir Pusat

  // Master Data
  const [branches, setBranches] = useState<Branch[]>(() => loadInitial('branches', initialBranches));
  const [locations, setLocations] = useState<Location[]>(() => loadInitial('locations', initialLocations));
  const [racks, setRacks] = useState<LocationRack[]>(() => loadInitial('racks', initialRacks));
  const [categories, setCategories] = useState<Category[]>(() => loadInitial('categories', initialCategories));
  const [subCategories, setSubCategories] = useState<SubCategory[]>(() => loadInitial('subCategories', initialSubCategories));
  const [brands, setBrands] = useState<Brand[]>(() => loadInitial('brands', initialBrands));
  const [makers, setMakers] = useState<ProductMaker[]>(() => loadInitial('makers', initialMakers));
  const [productTypes, setProductTypes] = useState<ProductType[]>(() => loadInitial('productTypes', initialProductTypes));
  const [units, setUnits] = useState<Unit[]>(() => loadInitial('units', initialUnits));
  const [suppliers, setSuppliers] = useState<Supplier[]>(() => loadInitial('suppliers', initialSuppliers));
  const [customerGroups, setCustomerGroups] = useState<CustomerGroup[]>(() => loadInitial('customerGroups', initialCustomerGroups));
  const [customers, setCustomers] = useState<Customer[]>(() => loadInitial('customers', initialCustomers));
  const [customerPointLedgers, setCustomerPointLedgers] = useState<CustomerPointLedger[]>(() => loadInitial('customerPointLedgers', []));
  const [products, setProducts] = useState<Product[]>(() => loadInitial('products', initialProducts));
  const [serviceItems, setServiceItems] = useState<ServiceItem[]>(() => loadInitial('serviceItems', initialServiceItems));

  // POS & Transactions
  const [activeCashSession, setActiveCashSession] = useState<CashSession | null>(() => loadInitial('activeCashSession', null));
  const [cart, setCart] = useState<CartItem[]>([]);
  const [selectedCustomerId, setSelectedCustomerId] = useState<number>(1); // Walk-in
  const [sales, setSales] = useState<Sale[]>(() => loadInitial('sales', []));
  const [lastCompletedSale, setLastCompletedSale] = useState<Sale | null>(null);

  // Stock Movements & Transfers
  const [stockMovements, setStockMovements] = useState<StockMovement[]>(() => loadInitial('stockMovements', []));
  const [branchTransfers, setBranchTransfers] = useState<BranchTransfer[]>(() => loadInitial('branchTransfers', []));

  // Purchase Orders
  const [purchaseOrders, setPurchaseOrders] = useState<PurchaseOrder[]>(() => loadInitial('purchaseOrders', []));

  // Services
  const [serviceTransactions, setServiceTransactions] = useState<ServiceTransaction[]>(() => loadInitial('serviceTransactions', []));

  // Back Office
  const [cashAccounts, setCashAccounts] = useState<BackOfficeCashAccount[]>(() => loadInitial('cashAccounts', initialCashAccounts));
  const [costCategories, setCostCategories] = useState<BackOfficeCostCategory[]>(() => loadInitial('costCategories', initialCostCategories));
  const [cashTransactions, setCashTransactions] = useState<BackOfficeCashTransaction[]>(() => loadInitial('cashTransactions', []));
  const [employeeAdvances, setEmployeeAdvances] = useState<BackOfficeEmployeeAdvance[]>(() => loadInitial('employeeAdvances', []));
  const [stockDocuments, setStockDocuments] = useState<BackOfficeStockDocument[]>(() => loadInitial('stockDocuments', []));

  // Printer Settings
  const [printerSetting, setPrinterSetting] = useState<PrinterSetting>(() => loadInitial('printerSetting', initialPrinterSetting));

  function loadInitial<T>(key: string, fallback: T): T {
    try {
      const stored = localStorage.getItem(`${STORAGE_KEY}_${key}`);
      if (stored) {
        return JSON.parse(stored);
      }
    } catch (e) {
      console.error(`Failed to load ${key} from storage:`, e);
    }
    return fallback;
  }

  // Auto save to localStorage
  useEffect(() => {
    try {
      localStorage.setItem(`${STORAGE_KEY}_branches`, JSON.stringify(branches));
      localStorage.setItem(`${STORAGE_KEY}_locations`, JSON.stringify(locations));
      localStorage.setItem(`${STORAGE_KEY}_racks`, JSON.stringify(racks));
      localStorage.setItem(`${STORAGE_KEY}_categories`, JSON.stringify(categories));
      localStorage.setItem(`${STORAGE_KEY}_subCategories`, JSON.stringify(subCategories));
      localStorage.setItem(`${STORAGE_KEY}_brands`, JSON.stringify(brands));
      localStorage.setItem(`${STORAGE_KEY}_makers`, JSON.stringify(makers));
      localStorage.setItem(`${STORAGE_KEY}_productTypes`, JSON.stringify(productTypes));
      localStorage.setItem(`${STORAGE_KEY}_units`, JSON.stringify(units));
      localStorage.setItem(`${STORAGE_KEY}_suppliers`, JSON.stringify(suppliers));
      localStorage.setItem(`${STORAGE_KEY}_customerGroups`, JSON.stringify(customerGroups));
      localStorage.setItem(`${STORAGE_KEY}_customers`, JSON.stringify(customers));
      localStorage.setItem(`${STORAGE_KEY}_customerPointLedgers`, JSON.stringify(customerPointLedgers));
      localStorage.setItem(`${STORAGE_KEY}_products`, JSON.stringify(products));
      localStorage.setItem(`${STORAGE_KEY}_serviceItems`, JSON.stringify(serviceItems));
      localStorage.setItem(`${STORAGE_KEY}_activeCashSession`, JSON.stringify(activeCashSession));
      localStorage.setItem(`${STORAGE_KEY}_sales`, JSON.stringify(sales));
      localStorage.setItem(`${STORAGE_KEY}_stockMovements`, JSON.stringify(stockMovements));
      localStorage.setItem(`${STORAGE_KEY}_branchTransfers`, JSON.stringify(branchTransfers));
      localStorage.setItem(`${STORAGE_KEY}_purchaseOrders`, JSON.stringify(purchaseOrders));
      localStorage.setItem(`${STORAGE_KEY}_serviceTransactions`, JSON.stringify(serviceTransactions));
      localStorage.setItem(`${STORAGE_KEY}_cashAccounts`, JSON.stringify(cashAccounts));
      localStorage.setItem(`${STORAGE_KEY}_costCategories`, JSON.stringify(costCategories));
      localStorage.setItem(`${STORAGE_KEY}_cashTransactions`, JSON.stringify(cashTransactions));
      localStorage.setItem(`${STORAGE_KEY}_employeeAdvances`, JSON.stringify(employeeAdvances));
      localStorage.setItem(`${STORAGE_KEY}_stockDocuments`, JSON.stringify(stockDocuments));
      localStorage.setItem(`${STORAGE_KEY}_printerSetting`, JSON.stringify(printerSetting));
    } catch (e) {
      console.error('Failed to save state to localStorage:', e);
    }
  }, [
    branches, locations, racks, categories, subCategories, brands, makers, productTypes,
    units, suppliers, customerGroups, customers, customerPointLedgers, products, serviceItems,
    activeCashSession, sales, stockMovements, branchTransfers, purchaseOrders, serviceTransactions,
    cashAccounts, costCategories, cashTransactions, employeeAdvances, stockDocuments, printerSetting
  ]);

  // Cash Session Methods
  const openCashSession = (openingCash: number, notes?: string) => {
    const loc = locations.find(l => l.id === selectedLocationId) || locations[0];
    const session: CashSession = {
      id: Date.now(),
      user_id: currentUser.id,
      user_name: currentUser.name,
      location_id: loc.id,
      location_name: loc.name,
      opening_cash: openingCash,
      status: 'open',
      opened_at: new Date().toISOString(),
      notes,
    };
    setActiveCashSession(session);
  };

  const closeCashSession = (closingCash: number, notes?: string) => {
    if (!activeCashSession) return;
    
    // Calculate expected cash from sales in this session
    const sessionSales = sales.filter(s => s.cash_session_id === activeCashSession.id && s.status === 'paid');
    const cashTotal = sessionSales
      .filter(s => s.payment_method === 'cash')
      .reduce((sum, s) => sum + s.grand_total, 0);
    
    const expected = activeCashSession.opening_cash + cashTotal;
    const diff = closingCash - expected;

    const closed: CashSession = {
      ...activeCashSession,
      closing_cash: closingCash,
      expected_cash: expected,
      difference: diff,
      status: 'closed',
      closed_at: new Date().toISOString(),
      notes,
    };
    setActiveCashSession(null);
  };

  // Cart Management
  const addToCart = (product: Product, quantity: number = 1, customPrice?: number) => {
    const cust = customers.find(c => c.id === selectedCustomerId);
    let effectivePrice = customPrice !== undefined ? customPrice : product.selling_price;
    
    // Apply customer group discount if applicable and no custom price
    if (customPrice === undefined && cust && cust.customer_group_id) {
      const group = customerGroups.find(g => g.id === cust.customer_group_id);
      if (group && group.discount_percent > 0) {
        effectivePrice = Math.round(product.selling_price * (1 - group.discount_percent / 100));
      }
    }

    setCart(prev => {
      const existingIdx = prev.findIndex(item => item.product.id === product.id && item.price === effectivePrice);
      if (existingIdx >= 0) {
        const updated = [...prev];
        const newQty = updated[existingIdx].quantity + quantity;
        updated[existingIdx] = {
          ...updated[existingIdx],
          quantity: newQty,
          subtotal: Math.max(0, newQty * updated[existingIdx].price - (updated[existingIdx].discount_amount || 0))
        };
        return updated;
      }

      const newItem: CartItem = {
        product,
        quantity,
        price: effectivePrice,
        location_id: selectedLocationId,
        unit_name: product.unit?.name || 'Pcs',
        discount_amount: 0,
        subtotal: quantity * effectivePrice
      };
      return [...prev, newItem];
    });
  };

  const updateCartItemQty = (index: number, quantity: number) => {
    if (quantity <= 0) {
      removeFromCart(index);
      return;
    }
    setCart(prev => {
      const updated = [...prev];
      if (updated[index]) {
        updated[index] = {
          ...updated[index],
          quantity,
          subtotal: Math.max(0, quantity * updated[index].price - (updated[index].discount_amount || 0))
        };
      }
      return updated;
    });
  };

  const updateCartItemPrice = (index: number, price: number) => {
    setCart(prev => {
      const updated = [...prev];
      if (updated[index]) {
        updated[index] = {
          ...updated[index],
          price,
          subtotal: Math.max(0, updated[index].quantity * price - (updated[index].discount_amount || 0))
        };
      }
      return updated;
    });
  };

  const updateCartItemDiscount = (index: number, discountAmount: number) => {
    setCart(prev => {
      const updated = [...prev];
      if (updated[index]) {
        updated[index] = {
          ...updated[index],
          discount_amount: discountAmount,
          subtotal: Math.max(0, updated[index].quantity * updated[index].price - discountAmount)
        };
      }
      return updated;
    });
  };

  const removeFromCart = (index: number) => {
    setCart(prev => prev.filter((_, idx) => idx !== index));
  };

  const clearCart = () => {
    setCart([]);
  };

  // Checkout Sale & Stock Deductions
  const checkoutSale = (payload: {
    paymentMethod: 'cash' | 'transfer' | 'qris' | 'split' | 'tempo';
    paidAmount: number;
    payments?: { method: 'cash' | 'transfer' | 'qris'; amount: number; reference?: string }[];
    notes?: string;
    creditDueDate?: string;
    saleChannel?: 'toko' | 'cabang' | 'partai';
  }): Sale => {
    const saleCode = `TRX-${new Date().getFullYear().toString().slice(-2)}${(new Date().getMonth() + 1).toString().padStart(2, '0')}${new Date().getDate().toString().padStart(2, '0')}-${Math.floor(1000 + Math.random() * 9000)}`;
    const cust = customers.find(c => c.id === selectedCustomerId);
    const loc = locations.find(l => l.id === selectedLocationId) || locations[0];

    const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.price), 0);
    const discountTotal = cart.reduce((sum, item) => sum + (item.discount_amount || 0), 0);
    const grandTotal = Math.max(0, subtotal - discountTotal);
    const changeAmount = payload.paymentMethod === 'tempo' ? 0 : Math.max(0, payload.paidAmount - grandTotal);

    // Points calculation (1 point per Rp 50.000)
    const pointsEarned = cust?.type === 'member' ? Math.floor(grandTotal / 50000) : 0;

    const saleItems: SaleItem[] = cart.map((item, idx) => ({
      id: Date.now() + idx,
      sale_id: Date.now(),
      product_id: item.product.id,
      product_code: item.product.product_code,
      product_name: item.product.name,
      unit_name: item.unit_name || 'Pcs',
      quantity: item.quantity,
      purchase_price: item.product.purchase_price,
      unit_price: item.price,
      discount_amount: item.discount_amount || 0,
      subtotal: item.subtotal,
      serial_numbers: item.serial_numbers
    }));

    const payments: TransactionPayment[] = payload.payments && payload.payments.length > 0
      ? payload.payments.map((p, idx) => ({
          id: Date.now() + idx,
          payment_method: p.method,
          amount: p.amount,
          reference: p.reference,
          created_at: new Date().toISOString()
        }))
      : [{
          id: Date.now(),
          payment_method: payload.paymentMethod === 'split' ? 'cash' : payload.paymentMethod,
          amount: payload.paidAmount,
          notes: payload.notes,
          created_at: new Date().toISOString()
        }];

    const newSale: Sale = {
      id: Date.now(),
      sale_code: saleCode,
      sale_channel: payload.saleChannel || 'toko',
      customer_id: cust?.id,
      customer: cust,
      location_id: loc.id,
      location: loc,
      cashier_id: currentUser.id,
      cashier_name: currentUser.name,
      cash_session_id: activeCashSession?.id,
      items_count: cart.reduce((sum, item) => sum + item.quantity, 0),
      subtotal,
      discount_total: discountTotal,
      grand_total: grandTotal,
      paid_amount: payload.paymentMethod === 'tempo' ? payload.paidAmount : Math.min(grandTotal, payload.paidAmount),
      change_amount: changeAmount,
      payment_method: payload.paymentMethod,
      credit_status: payload.paymentMethod === 'tempo' ? (payload.paidAmount >= grandTotal ? 'paid' : payload.paidAmount > 0 ? 'partial' : 'unpaid') : undefined,
      credit_due_at: payload.creditDueDate,
      points_earned: pointsEarned,
      status: 'paid',
      notes: payload.notes,
      sale_at: new Date().toISOString(),
      items: saleItems,
      payments
    };

    // 1. Save Sale
    setSales(prev => [newSale, ...prev]);

    // 2. Deduct stock and record StockMovement
    setProducts(prevProducts => {
      return prevProducts.map(p => {
        const cartItem = cart.find(ci => ci.product.id === p.id);
        if (!cartItem) return p;

        const currentGlobal = p.stock_global || 0;
        const newGlobal = Math.max(0, currentGlobal - cartItem.quantity);

        const updatedStocks = (p.stocks || []).map(st => {
          if (st.location_id === loc.id) {
            return {
              ...st,
              quantity: Math.max(0, st.quantity - cartItem.quantity)
            };
          }
          return st;
        });

        // Record stock movement
        const movement: StockMovement = {
          id: Date.now() + p.id,
          product_id: p.id,
          product_name: p.name,
          location_id: loc.id,
          location_name: loc.name,
          movement_type: 'out',
          quantity: cartItem.quantity,
          balance_before: currentGlobal,
          balance_after: newGlobal,
          reference_type: 'pos_sale',
          reference_code: saleCode,
          notes: `Penjualan POS #${saleCode}`,
          movement_at: new Date().toISOString(),
          created_by_name: currentUser.name
        };
        setStockMovements(prevM => [movement, ...prevM]);

        return {
          ...p,
          stock_global: newGlobal,
          stocks: updatedStocks
        };
      });
    });

    // 3. Update customer points balance if member
    if (cust && cust.type === 'member' && pointsEarned > 0) {
      const newPoints = cust.points_balance + pointsEarned;
      setCustomers(prev => prev.map(c => c.id === cust.id ? { ...c, points_balance: newPoints } : c));
      
      const pointLedger: CustomerPointLedger = {
        id: Date.now(),
        customer_id: cust.id,
        sale_id: newSale.id,
        points: pointsEarned,
        balance_after: newPoints,
        source: 'sale',
        reference_code: saleCode,
        notes: `Poin dari transaksi POS #${saleCode}`,
        created_by: currentUser.id,
        created_at: new Date().toISOString()
      };
      setCustomerPointLedgers(prev => [pointLedger, ...prev]);
    }

    setLastCompletedSale(newSale);
    clearCart();
    return newSale;
  };

  const voidSale = (saleId: number, reason: string) => {
    const sale = sales.find(s => s.id === saleId);
    if (!sale || sale.status === 'void') return;

    // 1. Revert product stocks
    setProducts(prevProducts => {
      return prevProducts.map(p => {
        const item = sale.items.find(si => si.product_id === p.id);
        if (!item) return p;

        const currentGlobal = p.stock_global || 0;
        const newGlobal = currentGlobal + item.quantity;

        const updatedStocks = (p.stocks || []).map(st => {
          if (st.location_id === sale.location_id) {
            return {
              ...st,
              quantity: st.quantity + item.quantity
            };
          }
          return st;
        });

        // Record stock revert movement
        const movement: StockMovement = {
          id: Date.now() + p.id,
          product_id: p.id,
          product_name: p.name,
          location_id: sale.location_id,
          location_name: sale.location?.name || '',
          movement_type: 'in',
          quantity: item.quantity,
          balance_before: currentGlobal,
          balance_after: newGlobal,
          reference_type: 'void_sale',
          reference_code: sale.sale_code,
          notes: `Void Penjualan #${sale.sale_code}: ${reason}`,
          movement_at: new Date().toISOString(),
          created_by_name: currentUser.name
        };
        setStockMovements(prevM => [movement, ...prevM]);

        return {
          ...p,
          stock_global: newGlobal,
          stocks: updatedStocks
        };
      });
    });

    // 2. Revert points if member
    if (sale.customer_id && sale.points_earned > 0) {
      setCustomers(prev => prev.map(c => {
        if (c.id === sale.customer_id) {
          return {
            ...c,
            points_balance: Math.max(0, c.points_balance - sale.points_earned)
          };
        }
        return c;
      }));
    }

    // 3. Mark sale as void
    setSales(prev => prev.map(s => s.id === saleId ? { ...s, status: 'void', void_reason: reason } : s));
  };

  // Branch Transfer Logic
  const createBranchTransfer = (payload: {
    sourceBranchId: number;
    targetBranchId: number;
    sourceLocationId: number;
    targetLocationId: number;
    items: { productId: number; quantity: number; notes?: string }[];
    notes?: string;
  }): BranchTransfer => {
    const code = `TRF-${new Date().getFullYear().toString().slice(-2)}${(new Date().getMonth() + 1).toString().padStart(2, '0')}-${Math.floor(1000 + Math.random() * 9000)}`;
    const srcBranch = branches.find(b => b.id === payload.sourceBranchId);
    const tgtBranch = branches.find(b => b.id === payload.targetBranchId);
    const srcLoc = locations.find(l => l.id === payload.sourceLocationId);
    const tgtLoc = locations.find(l => l.id === payload.targetLocationId);

    const items: BranchTransferItem[] = payload.items.map((it, idx) => {
      const prod = products.find(p => p.id === it.productId);
      return {
        id: Date.now() + idx,
        branch_transfer_id: Date.now(),
        product_id: it.productId,
        product_code: prod?.product_code || '',
        product_name: prod?.name || '',
        quantity_sent: it.quantity,
        notes: it.notes
      };
    });

    const newTransfer: BranchTransfer = {
      id: Date.now(),
      transfer_code: code,
      source_branch_id: payload.sourceBranchId,
      source_branch: srcBranch,
      target_branch_id: payload.targetBranchId,
      target_branch: tgtBranch,
      source_location_id: payload.sourceLocationId,
      source_location: srcLoc,
      target_location_id: payload.targetLocationId,
      target_location: tgtLoc,
      status: 'draft',
      notes: payload.notes,
      created_by_name: currentUser.name,
      items,
      created_at: new Date().toISOString()
    };

    setBranchTransfers(prev => [newTransfer, ...prev]);
    return newTransfer;
  };

  const shipBranchTransfer = (transferId: number) => {
    const transfer = branchTransfers.find(t => t.id === transferId);
    if (!transfer || transfer.status !== 'draft') return;

    // Deduct stock from source location
    setProducts(prevProducts => {
      return prevProducts.map(p => {
        const item = transfer.items.find(ti => ti.product_id === p.id);
        if (!item) return p;

        const updatedStocks = (p.stocks || []).map(st => {
          if (st.location_id === transfer.source_location_id) {
            return {
              ...st,
              quantity: Math.max(0, st.quantity - item.quantity_sent)
            };
          }
          return st;
        });

        // Global stock is also reduced while in-transit
        const newGlobal = Math.max(0, (p.stock_global || 0) - item.quantity_sent);

        const movement: StockMovement = {
          id: Date.now() + p.id,
          product_id: p.id,
          product_name: p.name,
          location_id: transfer.source_location_id,
          location_name: transfer.source_location?.name || '',
          movement_type: 'transfer_out',
          quantity: item.quantity_sent,
          balance_before: p.stock_global || 0,
          balance_after: newGlobal,
          reference_type: 'branch_transfer_ship',
          reference_code: transfer.transfer_code,
          notes: `Pengiriman transfer cabang #${transfer.transfer_code} ke ${transfer.target_branch?.name}`,
          movement_at: new Date().toISOString(),
          created_by_name: currentUser.name
        };
        setStockMovements(prevM => [movement, ...prevM]);

        return {
          ...p,
          stock_global: newGlobal,
          stocks: updatedStocks
        };
      });
    });

    setBranchTransfers(prev => prev.map(t => t.id === transferId ? { ...t, status: 'in_transit', shipped_at: new Date().toISOString() } : t));
  };

  const receiveBranchTransfer = (transferId: number, receivedItems: { productId: number; receivedQuantity: number }[]) => {
    const transfer = branchTransfers.find(t => t.id === transferId);
    if (!transfer || transfer.status !== 'in_transit') return;

    // Add stock to target location based on received quantity
    setProducts(prevProducts => {
      return prevProducts.map(p => {
        const rec = receivedItems.find(ri => ri.productId === p.id);
        const item = transfer.items.find(ti => ti.product_id === p.id);
        if (!rec || !item) return p;

        const qtyReceived = rec.receivedQuantity;
        const currentGlobal = p.stock_global || 0;
        const newGlobal = currentGlobal + qtyReceived;

        let foundLoc = false;
        let updatedStocks = (p.stocks || []).map(st => {
          if (st.location_id === transfer.target_location_id) {
            foundLoc = true;
            return {
              ...st,
              quantity: st.quantity + qtyReceived
            };
          }
          return st;
        });

        if (!foundLoc) {
          updatedStocks.push({
            id: Date.now() + p.id,
            product_id: p.id,
            location_id: transfer.target_location_id,
            quantity: qtyReceived,
            damaged_quantity: 0,
            stock_min: 5
          });
        }

        const movement: StockMovement = {
          id: Date.now() + p.id,
          product_id: p.id,
          product_name: p.name,
          location_id: transfer.target_location_id,
          location_name: transfer.target_location?.name || '',
          movement_type: 'transfer_in',
          quantity: qtyReceived,
          balance_before: currentGlobal,
          balance_after: newGlobal,
          reference_type: 'branch_transfer_receive',
          reference_code: transfer.transfer_code,
          notes: `Penerimaan transfer #${transfer.transfer_code} dari ${transfer.source_branch?.name}`,
          movement_at: new Date().toISOString(),
          created_by_name: currentUser.name
        };
        setStockMovements(prevM => [movement, ...prevM]);

        return {
          ...p,
          stock_global: newGlobal,
          stocks: updatedStocks
        };
      });
    });

    const updatedItems = transfer.items.map(ti => {
      const rec = receivedItems.find(ri => ri.productId === ti.product_id);
      const qtyRec = rec ? rec.receivedQuantity : ti.quantity_sent;
      const lost = Math.max(0, ti.quantity_sent - qtyRec);
      return {
        ...ti,
        quantity_received: qtyRec,
        quantity_lost: lost
      };
    });

    setBranchTransfers(prev => prev.map(t => t.id === transferId ? {
      ...t,
      status: 'completed',
      received_at: new Date().toISOString(),
      items: updatedItems
    } : t));
  };

  const cancelBranchTransfer = (transferId: number) => {
    const transfer = branchTransfers.find(t => t.id === transferId);
    if (!transfer) return;

    if (transfer.status === 'in_transit') {
      // Revert stock back to source location
      setProducts(prevProducts => {
        return prevProducts.map(p => {
          const item = transfer.items.find(ti => ti.product_id === p.id);
          if (!item) return p;

          const currentGlobal = p.stock_global || 0;
          const newGlobal = currentGlobal + item.quantity_sent;

          const updatedStocks = (p.stocks || []).map(st => {
            if (st.location_id === transfer.source_location_id) {
              return {
                ...st,
                quantity: st.quantity + item.quantity_sent
              };
            }
            return st;
          });

          return {
            ...p,
            stock_global: newGlobal,
            stocks: updatedStocks
          };
        });
      });
    }

    setBranchTransfers(prev => prev.map(t => t.id === transferId ? { ...t, status: 'cancelled' } : t));
  };

  // Purchase Orders
  const createPurchaseOrder = (payload: {
    supplierId: number;
    locationId: number;
    orderDate: string;
    expectedDate?: string;
    items: { productId: number; quantity: number; unitPrice: number }[];
    notes?: string;
  }): PurchaseOrder => {
    const code = `PO-${new Date().getFullYear().toString().slice(-2)}${(new Date().getMonth() + 1).toString().padStart(2, '0')}-${Math.floor(1000 + Math.random() * 9000)}`;
    const supp = suppliers.find(s => s.id === payload.supplierId);
    const loc = locations.find(l => l.id === payload.locationId);

    const items: PurchaseOrderItem[] = payload.items.map((it, idx) => {
      const prod = products.find(p => p.id === it.productId);
      return {
        id: Date.now() + idx,
        purchase_order_id: Date.now(),
        product_id: it.productId,
        product_code: prod?.product_code || '',
        product_name: prod?.name || '',
        quantity: it.quantity,
        received_quantity: 0,
        unit_price: it.unitPrice,
        subtotal: it.quantity * it.unitPrice
      };
    });

    const total = items.reduce((sum, i) => sum + i.subtotal, 0);

    const newPO: PurchaseOrder = {
      id: Date.now(),
      po_number: code,
      supplier_id: payload.supplierId,
      supplier: supp,
      location_id: payload.locationId,
      location: loc,
      order_date: payload.orderDate,
      expected_date: payload.expectedDate,
      status: 'ordered',
      payment_status: 'unpaid',
      total_amount: total,
      paid_amount: 0,
      notes: payload.notes,
      items,
      created_at: new Date().toISOString()
    };

    setPurchaseOrders(prev => [newPO, ...prev]);
    return newPO;
  };

  const receivePurchaseOrder = (poId: number, receivedItems: { productId: number; receivedQuantity: number }[]) => {
    const po = purchaseOrders.find(p => p.id === poId);
    if (!po) return;

    // Add incoming stock to location
    setProducts(prevProducts => {
      return prevProducts.map(p => {
        const rec = receivedItems.find(ri => ri.productId === p.id);
        if (!rec || rec.receivedQuantity <= 0) return p;

        const qty = rec.receivedQuantity;
        const currentGlobal = p.stock_global || 0;
        const newGlobal = currentGlobal + qty;

        let foundLoc = false;
        const updatedStocks = (p.stocks || []).map(st => {
          if (st.location_id === po.location_id) {
            foundLoc = true;
            return {
              ...st,
              quantity: st.quantity + qty
            };
          }
          return st;
        });

        if (!foundLoc) {
          updatedStocks.push({
            id: Date.now() + p.id,
            product_id: p.id,
            location_id: po.location_id,
            quantity: qty,
            damaged_quantity: 0,
            stock_min: 5
          });
        }

        const movement: StockMovement = {
          id: Date.now() + p.id,
          product_id: p.id,
          product_name: p.name,
          location_id: po.location_id,
          location_name: po.location?.name || '',
          movement_type: 'in',
          quantity: qty,
          balance_before: currentGlobal,
          balance_after: newGlobal,
          reference_type: 'po_receive',
          reference_code: po.po_number,
          notes: `Penerimaan PO #${po.po_number} dari ${po.supplier?.name}`,
          movement_at: new Date().toISOString(),
          created_by_name: currentUser.name
        };
        setStockMovements(prevM => [movement, ...prevM]);

        return {
          ...p,
          stock_global: newGlobal,
          stocks: updatedStocks
        };
      });
    });

    const updatedItems = po.items.map(it => {
      const rec = receivedItems.find(ri => ri.productId === it.product_id);
      return {
        ...it,
        received_quantity: (it.received_quantity || 0) + (rec?.receivedQuantity || 0)
      };
    });

    const allReceived = updatedItems.every(it => it.received_quantity >= it.quantity);

    setPurchaseOrders(prev => prev.map(p => p.id === poId ? {
      ...p,
      status: allReceived ? 'completed' : 'received',
      items: updatedItems
    } : p));
  };

  const payPurchaseOrder = (poId: number, amount: number, cashAccountId: number) => {
    const po = purchaseOrders.find(p => p.id === poId);
    if (!po) return;

    const newPaid = (po.paid_amount || 0) + amount;
    const paymentStatus = newPaid >= po.total_amount ? 'paid' : newPaid > 0 ? 'partial' : 'unpaid';

    // Deduct cash from selected account
    setCashAccounts(prev => prev.map(ca => {
      if (ca.id === cashAccountId) {
        return {
          ...ca,
          current_balance: ca.current_balance - amount
        };
      }
      return ca;
    }));

    // Record cash transaction
    const cashTrx: BackOfficeCashTransaction = {
      id: Date.now(),
      transaction_code: `TKK-${Date.now()}`,
      transaction_date: new Date().toISOString().split('T')[0],
      transaction_type: 'expense',
      cash_account_id: cashAccountId,
      amount,
      description: `Pembayaran PO #${po.po_number} ke ${po.supplier?.name}`,
      reference: po.po_number,
      created_by_name: currentUser.name
    };
    setCashTransactions(prev => [cashTrx, ...prev]);

    setPurchaseOrders(prev => prev.map(p => p.id === poId ? {
      ...p,
      paid_amount: newPaid,
      payment_status: paymentStatus
    } : p));
  };

  // Service Center Logic
  const createServiceTransaction = (payload: Omit<Partial<ServiceTransaction>, 'items'> & {
    customerName: string;
    customerPhone: string;
    deviceBrand: string;
    deviceType: string;
    serialNumber?: string;
    complaint: string;
    checkNotes?: string;
    accessories?: string;
    deviceLockType?: string;
    deviceLockValue?: string;
    technicianId?: number;
    items?: { type: 'service' | 'product'; serviceId?: number; productId?: number; name: string; quantity: number; unitPrice: number; discountAmount?: number }[];
  }): ServiceTransaction => {
    const code = `SRV-${new Date().getFullYear().toString().slice(-2)}${(new Date().getMonth() + 1).toString().padStart(2, '0')}-${Math.floor(1000 + Math.random() * 9000)}`;

    const items: ServiceTransactionItem[] = (payload.items || []).map((it, idx) => ({
      id: Date.now() + idx,
      service_transaction_id: Date.now(),
      service_id: it.serviceId ?? null,
      product_id: it.productId ?? null,
      name: it.name,
      type: it.type,
      unit_price: it.unitPrice,
      quantity: it.quantity,
      discount_amount: it.discountAmount ?? 0,
      subtotal: it.quantity * it.unitPrice - (it.discountAmount ?? 0),
      notes: null,
    }));

    const subtotal = items.reduce((sum, i) => sum + i.subtotal, 0);

    const newService: ServiceTransaction = {
      id: Date.now(),
      service_code: code,
      customer_id: null,
      cashier_id: currentUser.id,
      technician_id: payload.technicianId ?? null,
      location_id: selectedLocationId,
      cash_session_id: null,
      device_brand: payload.deviceBrand,
      device_type: payload.deviceType,
      serial_number: payload.serialNumber ?? '',
      device_lock_type: payload.deviceLockType ?? null,
      device_lock_value: payload.deviceLockValue ?? null,
      check_notes: payload.checkNotes ?? '',
      complaint: payload.complaint,
      accessories: payload.accessories ?? null,
      subtotal,
      discount_total: 0,
      tax_total: 0,
      grand_total: subtotal,
      status: 'process',
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
      items,
    };

    setServiceTransactions(prev => [newService, ...prev]);
    return newService;
  };

  const updateServiceStatus = (serviceId: number, status: ServiceTransaction['status']) => {
    setServiceTransactions(prev => prev.map(s => {
      if (s.id === serviceId) {
        return {
          ...s,
          status,
          updated_at: new Date().toISOString(),
        };
      }
      return s;
    }));
  };

  const completeServicePayment = (serviceId: number, paymentMethod: 'cash' | 'transfer' | 'qris' | 'tempo', paidAmount: number) => {
    const service = serviceTransactions.find(s => s.id === serviceId);
    if (!service) return;

    // Deduct used spareparts from stock if not deducted yet
    service.items.filter(i => i.type === 'product' && i.product_id).forEach(sp => {
      setProducts(prevProducts => prevProducts.map(p => {
        if (p.id === sp.product_id) {
          const newGlobal = Math.max(0, (p.stock_global || 0) - sp.quantity);
          const updatedStocks = (p.stocks || []).map(st => {
            if (st.location_id === selectedLocationId) {
              return { ...st, quantity: Math.max(0, st.quantity - sp.quantity) };
            }
            return st;
          });
          return { ...p, stock_global: newGlobal, stocks: updatedStocks };
        }
        return p;
      }));
    });

    setServiceTransactions(prev => prev.map(s => {
      if (s.id === serviceId) {
        return {
          ...s,
          status: 'taken',
          updated_at: new Date().toISOString(),
        };
      }
      return s;
    }));
  };

  // Back Office Cash & Stock Documents
  const addCashTransaction = (payload: {
    type: 'income' | 'expense';
    cashAccountId: number;
    amount: number;
    costCategoryId?: number;
    description?: string;
    reference?: string;
  }) => {
    const prefix = payload.type === 'income' ? 'TPK' : 'TKK';
    const code = `${prefix}-${Date.now()}`;
    const delta = payload.type === 'income' ? payload.amount : -payload.amount;

    setCashAccounts(prev => prev.map(ca => {
      if (ca.id === payload.cashAccountId) {
        return { ...ca, current_balance: ca.current_balance + delta };
      }
      return ca;
    }));

    const trx: BackOfficeCashTransaction = {
      id: Date.now(),
      transaction_code: code,
      transaction_date: new Date().toISOString().split('T')[0],
      transaction_type: payload.type,
      cash_account_id: payload.cashAccountId,
      cost_category_id: payload.costCategoryId,
      amount: payload.amount,
      description: payload.description,
      reference: payload.reference,
      created_by_name: currentUser.name
    };

    setCashTransactions(prev => [trx, ...prev]);
  };

  const addCashMutation = (payload: {
    sourceAccountId: number;
    targetAccountId: number;
    amount: number;
    description?: string;
    reference?: string;
  }) => {
    const code = `MTK-${Date.now()}`;

    setCashAccounts(prev => prev.map(ca => {
      if (ca.id === payload.sourceAccountId) {
        return { ...ca, current_balance: ca.current_balance - payload.amount };
      }
      if (ca.id === payload.targetAccountId) {
        return { ...ca, current_balance: ca.current_balance + payload.amount };
      }
      return ca;
    }));

    const trx: BackOfficeCashTransaction = {
      id: Date.now(),
      transaction_code: code,
      transaction_date: new Date().toISOString().split('T')[0],
      transaction_type: 'mutation',
      cash_account_id: payload.sourceAccountId,
      target_cash_account_id: payload.targetAccountId,
      amount: payload.amount,
      description: payload.description || 'Mutasi Kas Antar Akun',
      reference: payload.reference,
      created_by_name: currentUser.name
    };

    setCashTransactions(prev => [trx, ...prev]);
  };

  const addEmployeeAdvance = (payload: {
    employeeId: number;
    cashAccountId: number;
    amount: number;
    description?: string;
  }) => {
    const code = `KSB-${Date.now()}`;
    const emp = initialUsers.find(u => u.id === payload.employeeId);
    const ca = cashAccounts.find(a => a.id === payload.cashAccountId);

    setCashAccounts(prev => prev.map(a => a.id === payload.cashAccountId ? { ...a, current_balance: a.current_balance - payload.amount } : a));

    const advance: BackOfficeEmployeeAdvance = {
      id: Date.now(),
      advance_code: code,
      advance_date: new Date().toISOString().split('T')[0],
      employee_id: payload.employeeId,
      employee_name: emp?.name || 'Karyawan',
      cash_account_id: payload.cashAccountId,
      cash_account_name: ca?.name || 'Kas',
      amount: payload.amount,
      status: 'open',
      description: payload.description,
      created_by_name: currentUser.name
    };

    setEmployeeAdvances(prev => [advance, ...prev]);

    const cashTrx: BackOfficeCashTransaction = {
      id: Date.now(),
      transaction_code: code,
      transaction_date: new Date().toISOString().split('T')[0],
      transaction_type: 'employee_advance',
      cash_account_id: payload.cashAccountId,
      employee_id: payload.employeeId,
      amount: payload.amount,
      description: `Kasbon: ${emp?.name} - ${payload.description || ''}`,
      reference: code,
      created_by_name: currentUser.name
    };

    setCashTransactions(prev => [cashTrx, ...prev]);
  };

  const addStockDocument = (payload: {
    documentType: 'correction' | 'usage';
    movementType: string;
    productId: number;
    locationId: number;
    locationRackId?: number;
    quantity: number;
    description?: string;
  }) => {
    const prefix = payload.documentType === 'correction' ? 'KRS' : 'PMB';
    const code = `${prefix}-${Date.now()}`;
    const prod = products.find(p => p.id === payload.productId);
    const loc = locations.find(l => l.id === payload.locationId);

    const isAdd = payload.movementType === 'adjustment_plus';
    const delta = isAdd ? payload.quantity : -payload.quantity;

    setProducts(prevProducts => prevProducts.map(p => {
      if (p.id === payload.productId) {
        const currentGlobal = p.stock_global || 0;
        const newGlobal = Math.max(0, currentGlobal + delta);

        const updatedStocks = (p.stocks || []).map(st => {
          if (st.location_id === payload.locationId) {
            return {
              ...st,
              quantity: Math.max(0, st.quantity + delta)
            };
          }
          return st;
        });

        const movement: StockMovement = {
          id: Date.now(),
          product_id: p.id,
          product_name: p.name,
          location_id: payload.locationId,
          location_name: loc?.name || '',
          movement_type: payload.movementType as any,
          quantity: payload.quantity,
          balance_before: currentGlobal,
          balance_after: newGlobal,
          reference_type: payload.documentType,
          reference_code: code,
          notes: payload.description || `${payload.documentType === 'correction' ? 'Koreksi Stok' : 'Pemakaian Barang'} #${code}`,
          movement_at: new Date().toISOString(),
          created_by_name: currentUser.name
        };
        setStockMovements(prevM => [movement, ...prevM]);

        return {
          ...p,
          stock_global: newGlobal,
          stocks: updatedStocks
        };
      }
      return p;
    }));

    const doc: BackOfficeStockDocument = {
      id: Date.now(),
      document_code: code,
      document_date: new Date().toISOString().split('T')[0],
      document_type: payload.documentType,
      product_id: payload.productId,
      product: prod,
      location_id: payload.locationId,
      location: loc,
      location_rack_id: payload.locationRackId,
      movement_type: payload.movementType,
      quantity: payload.quantity,
      description: payload.description,
      created_by_name: currentUser.name
    };

    setStockDocuments(prev => [doc, ...prev]);
  };

  // Customer Point Redemption
  const redeemCustomerPoints = (customerId: number, points: number, notes?: string): boolean => {
    const cust = customers.find(c => c.id === customerId);
    if (!cust || cust.points_balance < points) return false;

    const after = cust.points_balance - points;
    setCustomers(prev => prev.map(c => c.id === customerId ? { ...c, points_balance: after } : c));

    const pointLedger: CustomerPointLedger = {
      id: Date.now(),
      customer_id: customerId,
      points: -points,
      balance_after: after,
      source: 'redeem',
      reference_code: `RDM-${Date.now()}`,
      notes: notes || `Penukaran ${points} poin hadiah`,
      created_by: currentUser.id,
      created_at: new Date().toISOString()
    };

    setCustomerPointLedgers(prev => [pointLedger, ...prev]);
    return true;
  };

  // Pay Customer Credit (Tempo)
  const payCustomerCredit = (customerId: number, saleId: number, amount: number, paymentMethod: 'cash' | 'transfer' | 'qris') => {
    const sale = sales.find(s => s.id === saleId);
    if (!sale) return;

    const newPaid = (sale.paid_amount || 0) + amount;
    const isFullyPaid = newPaid >= sale.grand_total;

    const newPayment: TransactionPayment = {
      id: Date.now(),
      payment_method: paymentMethod,
      amount,
      notes: 'Pelunasan piutang tempo',
      created_at: new Date().toISOString()
    };

    setSales(prev => prev.map(s => {
      if (s.id === saleId) {
        return {
          ...s,
          paid_amount: newPaid,
          credit_status: isFullyPaid ? 'paid' : 'partial',
          payments: [...s.payments, newPayment]
        };
      }
      return s;
    }));
  };

  // Printer Settings
  const updatePrinterSetting = (settings: Partial<PrinterSetting>) => {
    setPrinterSetting(prev => ({ ...prev, ...settings }));
  };

  // Database JSON Export & Import
  const exportDatabaseJson = () => {
    const data = {
      version: '1.0',
      exported_at: new Date().toISOString(),
      branches,
      locations,
      racks,
      categories,
      subCategories,
      brands,
      makers,
      productTypes,
      units,
      suppliers,
      customerGroups,
      customers,
      customerPointLedgers,
      products,
      serviceItems,
      sales,
      stockMovements,
      branchTransfers,
      purchaseOrders,
      serviceTransactions,
      cashAccounts,
      costCategories,
      cashTransactions,
      employeeAdvances,
      stockDocuments,
      printerSetting
    };

    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `uteparts_pos_backup_${new Date().toISOString().split('T')[0]}.json`;
    link.click();
    URL.revokeObjectURL(url);
  };

  const importDatabaseJson = (jsonData: string): boolean => {
    try {
      const data = JSON.parse(jsonData);
      if (data.products) setProducts(data.products);
      if (data.categories) setCategories(data.categories);
      if (data.subCategories) setSubCategories(data.subCategories);
      if (data.brands) setBrands(data.brands);
      if (data.makers) setMakers(data.makers);
      if (data.productTypes) setProductTypes(data.productTypes);
      if (data.locations) setLocations(data.locations);
      if (data.branches) setBranches(data.branches);
      if (data.customers) setCustomers(data.customers);
      if (data.suppliers) setSuppliers(data.suppliers);
      if (data.sales) setSales(data.sales);
      if (data.stockMovements) setStockMovements(data.stockMovements);
      if (data.branchTransfers) setBranchTransfers(data.branchTransfers);
      if (data.purchaseOrders) setPurchaseOrders(data.purchaseOrders);
      if (data.serviceTransactions) setServiceTransactions(data.serviceTransactions);
      if (data.cashAccounts) setCashAccounts(data.cashAccounts);
      if (data.cashTransactions) setCashTransactions(data.cashTransactions);
      if (data.printerSetting) setPrinterSetting(data.printerSetting);
      return true;
    } catch (e) {
      console.error('Failed to parse import JSON:', e);
      return false;
    }
  };

  const resetToDefaultData = () => {
    setBranches(initialBranches);
    setLocations(initialLocations);
    setRacks(initialRacks);
    setCategories(initialCategories);
    setSubCategories(initialSubCategories);
    setBrands(initialBrands);
    setMakers(initialMakers);
    setProductTypes(initialProductTypes);
    setUnits(initialUnits);
    setSuppliers(initialSuppliers);
    setCustomerGroups(initialCustomerGroups);
    setCustomers(initialCustomers);
    setCustomerPointLedgers([]);
    setProducts(initialProducts);
    setServiceItems(initialServiceItems);
    setSales([]);
    setStockMovements([]);
    setBranchTransfers([]);
    setPurchaseOrders([]);
    setServiceTransactions([]);
    setCashAccounts(initialCashAccounts);
    setCostCategories(initialCostCategories);
    setCashTransactions([]);
    setEmployeeAdvances([]);
    setStockDocuments([]);
    setPrinterSetting(initialPrinterSetting);
    setActiveCashSession(null);
    setCart([]);
  };

  return (
    <AppContext.Provider
      value={{
        currentView,
        setCurrentView,
        currentUser,
        setCurrentUser,
        selectedBranchId,
        setSelectedBranchId,
        selectedLocationId,
        setSelectedLocationId,
        branches,
        setBranches,
        locations,
        setLocations,
        racks,
        setRacks,
        categories,
        setCategories,
        subCategories,
        setSubCategories,
        brands,
        setBrands,
        makers,
        setMakers,
        productTypes,
        setProductTypes,
        units,
        setUnits,
        suppliers,
        setSuppliers,
        customerGroups,
        setCustomerGroups,
        customers,
        setCustomers,
        customerPointLedgers,
        products,
        setProducts,
        serviceItems,
        setServiceItems,
        activeCashSession,
        openCashSession,
        closeCashSession,
        cart,
        addToCart,
        updateCartItemQty,
        updateCartItemPrice,
        updateCartItemDiscount,
        removeFromCart,
        clearCart,
        selectedCustomerId,
        setSelectedCustomerId,
        sales,
        checkoutSale,
        voidSale,
        stockMovements,
        branchTransfers,
        createBranchTransfer,
        shipBranchTransfer,
        receiveBranchTransfer,
        cancelBranchTransfer,
        purchaseOrders,
        createPurchaseOrder,
        receivePurchaseOrder,
        payPurchaseOrder,
        serviceTransactions,
        createServiceTransaction,
        updateServiceStatus,
        completeServicePayment,
        cashAccounts,
        setCashAccounts,
        costCategories,
        setCostCategories,
        cashTransactions,
        addCashTransaction,
        addCashMutation,
        employeeAdvances,
        addEmployeeAdvance,
        stockDocuments,
        addStockDocument,
        redeemCustomerPoints,
        payCustomerCredit,
        printerSetting,
        updatePrinterSetting,
        lastCompletedSale,
        setLastCompletedSale,
        exportDatabaseJson,
        importDatabaseJson,
        resetToDefaultData
      }}
    >
      {children}
    </AppContext.Provider>
  );
};

export const useApp = () => {
  const context = useContext(AppContext);
  if (!context) {
    throw new Error('useApp must be used within an AppProvider');
  }
  return context;
};
