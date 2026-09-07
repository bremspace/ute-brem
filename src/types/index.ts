export interface Branch {
  id: number;
  code: string;
  name: string;
  address?: string;
  phone?: string;
  is_main: boolean;
  is_active: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface Location {
  id: number;
  branch_id?: number;
  branch?: Branch;
  code: string;
  name: string;
  description?: string;
  is_active: boolean;
  racks?: LocationRack[];
}

export interface LocationRack {
  id: number;
  location_id: number;
  code: string;
  name: string;
  description?: string;
  is_active: boolean;
}

export interface Category {
  id: number;
  name: string;
  slug?: string;
  code?: string;
  description?: string;
  is_active: boolean;
}

export interface SubCategory {
  id: number;
  category_id: number;
  category?: Category;
  name: string;
  slug?: string;
  code?: string;
  description?: string;
  is_active: boolean;
}

export interface Brand {
  id: number;
  name: string;
  slug?: string;
  description?: string;
  is_active: boolean;
}

export interface ProductMaker {
  id: number;
  name: string;
  description?: string;
  is_active: boolean;
}

export interface ProductType {
  id: number;
  brand_id?: number;
  brand?: Brand;
  name: string;
  code?: string;
  is_active: boolean;
}

export interface Unit {
  id: number;
  name: string;
  short_name?: string;
  is_active: boolean;
}

export interface Supplier {
  id: number;
  name: string;
  code?: string;
  contact_person?: string;
  phone?: string;
  email?: string;
  address?: string;
  is_active: boolean;
}

export interface CustomerGroup {
  id: number;
  name: string;
  discount_percent: number;
  description?: string;
  is_active: boolean;
}

export interface Customer {
  id: number;
  customer_group_id?: number;
  customer_group?: CustomerGroup;
  member_code?: string;
  name: string;
  phone?: string;
  email?: string;
  type: 'regular' | 'member';
  points_balance: number;
  is_active: boolean;
  created_at?: string;
}

export interface CustomerPointLedger {
  id: number;
  customer_id: number;
  sale_id?: number;
  points: number; // positive = earn, negative = redeem
  balance_after: number;
  source: 'sale' | 'redeem' | 'adjustment' | 'reset';
  reference_code?: string;
  notes?: string;
  created_by?: number;
  created_at: string;
}

export interface ProductStock {
  id: number;
  product_id: number;
  location_id: number;
  location?: Location;
  location_rack_id?: number;
  rack?: LocationRack;
  quantity: number;
  damaged_quantity: number;
  stock_min: number;
  stock_max?: number;
  notes?: string;
}

export interface Product {
  id: number;
  product_code: string;
  name: string;
  slug?: string;
  barcode?: string;
  category_id?: number;
  category?: Category;
  sub_category_id?: number;
  sub_category?: SubCategory;
  brand_id?: number;
  brand?: Brand;
  product_maker_id?: number;
  maker?: ProductMaker;
  product_type_id?: number;
  product_type?: ProductType;
  supplier_id?: number;
  supplier?: Supplier;
  unit_id?: number;
  unit?: Unit;
  purchase_price: number;
  selling_price: number;
  stock_min: number;
  stock_global: number;
  allow_open_price: boolean;
  has_serial_number: boolean;
  is_member_only: boolean;
  is_active: boolean;
  is_published: boolean;
  description?: string;
  stocks?: ProductStock[];
  created_at?: string;
  updated_at?: string;
}

export interface CartItem {
  product: Product;
  quantity: number;
  price: number;
  unit_id?: number;
  unit_name?: string;
  location_id: number;
  serial_numbers?: string[];
  discount_percent?: number;
  discount_amount?: number;
  subtotal: number;
}

export interface CashSession {
  id: number;
  user_id: number;
  user_name: string;
  location_id: number;
  location_name: string;
  opening_cash: number;
  closing_cash?: number;
  expected_cash?: number;
  difference?: number;
  status: 'open' | 'closed';
  opened_at: string;
  closed_at?: string;
  notes?: string;
}

export interface TransactionPayment {
  id: number;
  payment_method: 'cash' | 'transfer' | 'qris' | 'tempo';
  amount: number;
  reference?: string;
  notes?: string;
  created_at: string;
}

export interface SaleItem {
  id: number;
  sale_id: number;
  product_id: number;
  product_code: string;
  product_name: string;
  unit_name: string;
  quantity: number;
  purchase_price: number;
  unit_price: number;
  discount_amount: number;
  subtotal: number;
  serial_numbers?: string[];
}

export interface Sale {
  id: number;
  sale_code: string;
  sale_channel: 'toko' | 'cabang' | 'partai';
  customer_id?: number;
  customer?: Customer;
  location_id: number;
  location?: Location;
  cashier_id: number;
  cashier_name: string;
  cash_session_id?: number;
  items_count: number;
  subtotal: number;
  discount_total: number;
  grand_total: number;
  paid_amount: number;
  change_amount: number;
  payment_method: 'cash' | 'transfer' | 'qris' | 'split' | 'tempo';
  credit_status?: 'unpaid' | 'partial' | 'paid';
  credit_due_at?: string;
  points_earned: number;
  status: 'paid' | 'void';
  void_reason?: string;
  notes?: string;
  sale_at: string;
  items: SaleItem[];
  payments: TransactionPayment[];
}

export interface StockMovement {
  id: number;
  product_id: number;
  product?: Product;
  product_name: string;
  location_id: number;
  location?: Location;
  location_name: string;
  location_rack_id?: number;
  movement_type: 'in' | 'out' | 'transfer_in' | 'transfer_out' | 'adjustment_plus' | 'adjustment_minus';
  quantity: number;
  balance_before: number;
  balance_after: number;
  reference_type: string;
  reference_code: string;
  notes?: string;
  movement_at: string;
  created_by_name: string;
}

export interface BranchTransferItem {
  id: number;
  branch_transfer_id: number;
  product_id: number;
  product_code: string;
  product_name: string;
  quantity_sent: number;
  quantity_received?: number;
  quantity_lost?: number;
  notes?: string;
}

export interface BranchTransfer {
  id: number;
  transfer_code: string;
  source_branch_id: number;
  source_branch?: Branch;
  target_branch_id: number;
  target_branch?: Branch;
  source_location_id: number;
  source_location?: Location;
  target_location_id: number;
  target_location?: Location;
  status: 'draft' | 'in_transit' | 'completed' | 'cancelled';
  notes?: string;
  created_by_name: string;
  shipped_at?: string;
  received_at?: string;
  items: BranchTransferItem[];
  created_at: string;
}

export interface PurchaseOrderItem {
  id: number;
  purchase_order_id: number;
  product_id: number;
  product_code: string;
  product_name: string;
  quantity: number;
  received_quantity: number;
  unit_price: number;
  subtotal: number;
}

export interface PurchaseOrder {
  id: number;
  po_number: string;
  supplier_id: number;
  supplier?: Supplier;
  location_id: number;
  location?: Location;
  order_date: string;
  expected_date?: string;
  status: 'draft' | 'ordered' | 'received' | 'completed' | 'cancelled';
  payment_status: 'unpaid' | 'partial' | 'paid';
  total_amount: number;
  paid_amount: number;
  notes?: string;
  items: PurchaseOrderItem[];
  created_at: string;
}

export interface ServiceItem {
  id: number;
  code: string;
  name: string;
  category: string;
  estimated_price: number;
  description?: string;
  is_active: boolean;
}

export interface ServiceTransactionItem {
  id: number;
  service_transaction_id: number;
  service_id: number | null;
  product_id: number | null;
  name: string;
  type: 'service' | 'product';
  unit_price: number;
  quantity: number;
  discount_amount: number;
  subtotal: number;
  notes: string | null;
}

export interface ServiceTransaction {
  id: number;
  service_code: string;
  customer_id: number | null;
  cashier_id: number;
  technician_id: number | null;
  location_id: number;
  cash_session_id: number | null;
  device_brand: string;
  device_type: string;
  serial_number: string;
  device_lock_type: string | null;
  device_lock_value: string | null;
  check_notes: string;
  complaint: string;
  accessories: string | null;
  subtotal: number;
  discount_total: number;
  tax_total: number;
  grand_total: number;
  status: 'process' | 'done' | 'taken' | 'cancelled';
  created_at: string;
  updated_at: string;
  items: ServiceTransactionItem[];
}

export interface BackOfficeCashAccount {
  id: number;
  code: string;
  name: string;
  type: 'cash' | 'bank' | 'ewallet';
  opening_balance: number;
  current_balance: number;
  is_active: boolean;
}

export interface BackOfficeCostCategory {
  id: number;
  code: string;
  name: string;
  type: 'income' | 'expense';
  is_active: boolean;
}

export interface BackOfficeCashTransaction {
  id: number;
  transaction_code: string;
  transaction_date: string;
  transaction_type: 'income' | 'expense' | 'mutation' | 'employee_advance';
  cash_account_id: number;
  cash_account?: BackOfficeCashAccount;
  target_cash_account_id?: number;
  target_cash_account?: BackOfficeCashAccount;
  cost_category_id?: number;
  cost_category?: BackOfficeCostCategory;
  customer_id?: number;
  employee_id?: number;
  amount: number;
  description?: string;
  reference?: string;
  created_by_name: string;
}

export interface BackOfficeEmployeeAdvance {
  id: number;
  advance_code: string;
  advance_date: string;
  employee_id: number;
  employee_name: string;
  cash_account_id: number;
  cash_account_name: string;
  amount: number;
  status: 'open' | 'settled';
  description?: string;
  created_by_name: string;
}

export interface BackOfficeStockDocument {
  id: number;
  document_code: string;
  document_date: string;
  document_type: 'correction' | 'usage';
  product_id: number;
  product?: Product;
  location_id: number;
  location?: Location;
  location_rack_id?: number;
  rack?: LocationRack;
  movement_type: string;
  quantity: number;
  description?: string;
  created_by_name: string;
}

export interface User {
  id: number;
  name: string;
  username: string;
  email: string;
  branch_id?: number;
  branch?: Branch;
  role: 'super_admin' | 'admin' | 'cashier' | 'technician' | 'viewer';
  role_display_name: string;
  permissions: string[];
  is_active: boolean;
}

export interface PrinterSetting {
  mode: 'browser' | 'bridge';
  bridge_url: string;
  printer_name: string;
  paper_width_mm: 58 | 80;
  copies: number;
  header_text: string;
  footer_text: string;
  show_store_name: boolean;
  show_datetime: boolean;
  auto_print: boolean;
  company_name: string;
  company_address: string;
  company_city: string;
  company_phone: string;
  company_slogan: string;
}
