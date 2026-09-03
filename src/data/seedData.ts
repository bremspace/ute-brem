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
  Product,
  User,
  ServiceItem,
  BackOfficeCashAccount,
  BackOfficeCostCategory,
  PrinterSetting
} from '../types';

export const initialBranches: Branch[] = [
  { id: 1, code: 'PST', name: 'Cabang Pusat (Jakarta)', address: 'Jl. Hayam Wuruk No. 108, Jakarta Barat', phone: '021-6288899', is_main: true, is_active: true },
  { id: 2, code: 'SBY', name: 'Cabang Surabaya', address: 'Jl. Kusuma Bangsa No. 45, Surabaya', phone: '031-5344556', is_main: false, is_active: true },
];

export const initialLocations: Location[] = [
  { id: 1, branch_id: 1, code: 'GDG-PST', name: 'Gudang Utama Pusat', description: 'Gudang penyimpanan utama Jakarta', is_active: true },
  { id: 2, branch_id: 1, code: 'TKO-PST', name: 'Toko Kasir Depan Pusat', description: 'Area etalase dan kasir toko Jakarta', is_active: true },
  { id: 3, branch_id: 2, code: 'GDG-SBY', name: 'Gudang Cabang Surabaya', description: 'Gudang stok cabang Surabaya', is_active: true },
  { id: 4, branch_id: 2, code: 'TKO-SBY', name: 'Toko Kasir Surabaya', description: 'Area kasir toko Surabaya', is_active: true },
];

export const initialRacks: LocationRack[] = [
  { id: 1, location_id: 1, code: 'RAK-LCD-01', name: 'Rak LCD Apple', description: 'Rak khusus LCD iPhone', is_active: true },
  { id: 2, location_id: 1, code: 'RAK-BAT-01', name: 'Rak Baterai', description: 'Rak khusus baterai all series', is_active: true },
  { id: 3, location_id: 1, code: 'RAK-IC-01', name: 'Rak IC & Komponen', description: 'Rak part kecil micro component', is_active: true },
  { id: 4, location_id: 2, code: 'ETL-DEP-01', name: 'Etalase Kasir 1', description: 'Fast moving item', is_active: true },
  { id: 5, location_id: 3, code: 'RAK-SBY-01', name: 'Rak Utama Surabaya', description: 'Rak simpan Surabaya', is_active: true },
];

export const initialCategories: Category[] = [
  { id: 1, name: 'LCD / Layar Sentuh', slug: 'lcd-layar-sentuh', code: 'CAT-LCD', is_active: true },
  { id: 2, name: 'Baterai & Power', slug: 'baterai-power', code: 'CAT-BAT', is_active: true },
  { id: 3, name: 'Flex & Konektor Charger', slug: 'flex-konektor', code: 'CAT-FLX', is_active: true },
  { id: 4, name: 'Kaca / Backdoor / Frame', slug: 'kaca-backdoor-frame', code: 'CAT-KAC', is_active: true },
  { id: 5, name: 'IC & Komponen PCB', slug: 'ic-komponen-pcb', code: 'CAT-IC', is_active: true },
  { id: 6, name: 'Alat Servis & Perlengkapan', slug: 'alat-servis', code: 'CAT-ALT', is_active: true },
];

export const initialSubCategories: SubCategory[] = [
  { id: 1, category_id: 1, name: 'LCD iPhone', code: 'SUB-LCD-IPH', is_active: true },
  { id: 2, category_id: 1, name: 'LCD Samsung AMOLED', code: 'SUB-LCD-SAM', is_active: true },
  { id: 3, category_id: 1, name: 'LCD Oppo / Realme', code: 'SUB-LCD-OPP', is_active: true },
  { id: 4, category_id: 2, name: 'Baterai iPhone High Capacity', code: 'SUB-BAT-IPH', is_active: true },
  { id: 5, category_id: 2, name: 'Baterai Samsung Original', code: 'SUB-BAT-SAM', is_active: true },
  { id: 6, category_id: 5, name: 'IC Power & Audio', code: 'SUB-IC-PWR', is_active: true },
];

export const initialBrands: Brand[] = [
  { id: 1, name: 'Apple', slug: 'apple', is_active: true },
  { id: 2, name: 'Samsung', slug: 'samsung', is_active: true },
  { id: 3, name: 'Xiaomi', slug: 'xiaomi', is_active: true },
  { id: 4, name: 'Oppo', slug: 'oppo', is_active: true },
  { id: 5, name: 'Vivo', slug: 'vivo', is_active: true },
  { id: 6, name: 'Infinix', slug: 'infinix', is_active: true },
];

export const initialMakers: ProductMaker[] = [
  { id: 1, name: 'Original Service Pack', is_active: true },
  { id: 2, name: 'OEM Crown Super', is_active: true },
  { id: 3, name: 'Incell GX', is_active: true },
  { id: 4, name: 'OLED JK / ZY', is_active: true },
  { id: 5, name: 'Nohon Battery', is_active: true },
];

export const initialProductTypes: ProductType[] = [
  { id: 1, brand_id: 1, name: 'iPhone 11', code: 'IPH-11', is_active: true },
  { id: 2, brand_id: 1, name: 'iPhone 12 / 12 Pro', code: 'IPH-12', is_active: true },
  { id: 3, brand_id: 1, name: 'iPhone 13 Pro Max', code: 'IPH-13PM', is_active: true },
  { id: 4, brand_id: 2, name: 'Galaxy S21 Ultra', code: 'SAM-S21U', is_active: true },
  { id: 5, brand_id: 2, name: 'Galaxy A54 5G', code: 'SAM-A54', is_active: true },
  { id: 6, brand_id: 4, name: 'Oppo Reno 8 5G', code: 'OPP-RN8', is_active: true },
];

export const initialUnits: Unit[] = [
  { id: 1, name: 'Pcs', short_name: 'pcs', is_active: true },
  { id: 2, name: 'Set', short_name: 'set', is_active: true },
  { id: 3, name: 'Box', short_name: 'box', is_active: true },
];

export const initialSuppliers: Supplier[] = [
  { id: 1, code: 'SUP-01', name: 'PT Global Indo Sparepart', contact_person: 'Budi Santoso', phone: '081299887766', email: 'sales@globalindo.test', address: 'Mangga Dua Mall Lt. 4 No. 12, Jakarta', is_active: true },
  { id: 2, code: 'SUP-02', name: 'CV Berkah Komponen Jaya', contact_person: 'Hendrawan', phone: '081388776655', email: 'hendra@berkahjaya.test', address: 'Roxy Square Lt. LG, Jakarta', is_active: true },
  { id: 3, code: 'SUP-03', name: 'Shenzhen Apex Tech Ltd', contact_person: 'Alex Chen', phone: '+8613800138000', email: 'alex@apextech.test', address: 'Huaqiangbei, Shenzhen, China', is_active: true },
];

export const initialCustomerGroups: CustomerGroup[] = [
  { id: 1, name: 'Pelanggan Umum (Retail)', discount_percent: 0, description: 'Harga standar tanpa potongan', is_active: true },
  { id: 2, name: 'Member Silver', discount_percent: 5, description: 'Diskon 5% untuk pembelian rutin', is_active: true },
  { id: 3, name: 'Member Gold (Toko/Teknisi)', discount_percent: 10, description: 'Diskon 10% untuk mitra toko teknisi servis', is_active: true },
  { id: 4, name: 'Mitra VIP Distributor', discount_percent: 15, description: 'Diskon 15% untuk partai besar', is_active: true },
];

export const initialCustomers: Customer[] = [
  { id: 1, customer_group_id: 1, member_code: 'CUST-0001', name: 'Pelanggan Umum / Walk-in', phone: '08000000000', email: 'walkin@uteparts.test', type: 'regular', points_balance: 0, is_active: true },
  { id: 2, customer_group_id: 3, member_code: 'MBR-1002', name: 'Fajar Cell Service (Fajar Pratama)', phone: '081234567890', email: 'fajar@fajarcell.test', type: 'member', points_balance: 350, is_active: true },
  { id: 3, customer_group_id: 2, member_code: 'MBR-1003', name: 'Rina Wijaya', phone: '081288991122', email: 'rina.wijaya@gmail.test', type: 'member', points_balance: 120, is_active: true },
  { id: 4, customer_group_id: 4, member_code: 'MBR-1004', name: 'Surabaya Fix Point (Kevin Santoso)', phone: '081377889900', email: 'kevin@fixpoint.test', type: 'member', points_balance: 850, is_active: true },
];

export const initialProducts: Product[] = [
  {
    id: 1,
    product_code: 'LCD-IPH11-OLED',
    name: 'LCD Screen iPhone 11 (OLED JK Quality)',
    slug: 'lcd-screen-iphone-11-oled-jk',
    barcode: '8991001001011',
    category_id: 1,
    sub_category_id: 1,
    brand_id: 1,
    product_maker_id: 4,
    product_type_id: 1,
    supplier_id: 1,
    unit_id: 1,
    purchase_price: 380000,
    selling_price: 550000,
    stock_min: 5,
    stock_global: 48,
    allow_open_price: true,
    has_serial_number: false,
    is_member_only: false,
    is_active: true,
    is_published: true,
    description: 'Layar OLED JK tajam, TrueTone support, warna pekat dan sensitivitas sentuhan 1:1 original.',
    stocks: [
      { id: 1, product_id: 1, location_id: 1, location_rack_id: 1, quantity: 30, damaged_quantity: 0, stock_min: 5 },
      { id: 2, product_id: 1, location_id: 2, location_rack_id: 4, quantity: 8, damaged_quantity: 0, stock_min: 2 },
      { id: 3, product_id: 1, location_id: 3, location_rack_id: 5, quantity: 10, damaged_quantity: 0, stock_min: 3 },
    ]
  },
  {
    id: 2,
    product_code: 'LCD-IPH12-OEM',
    name: 'LCD Screen iPhone 12 / 12 Pro (Crown Super OEM)',
    slug: 'lcd-screen-iphone-12-crown-super',
    barcode: '8991001001012',
    category_id: 1,
    sub_category_id: 1,
    brand_id: 1,
    product_maker_id: 2,
    product_type_id: 2,
    supplier_id: 1,
    unit_id: 1,
    purchase_price: 620000,
    selling_price: 880000,
    stock_min: 5,
    stock_global: 32,
    allow_open_price: true,
    has_serial_number: false,
    is_member_only: false,
    is_active: true,
    is_published: true,
    description: 'Kualitas Crown Super OEM, bezel super tipis dan refresh rate stabil.',
    stocks: [
      { id: 4, product_id: 2, location_id: 1, location_rack_id: 1, quantity: 20, damaged_quantity: 0, stock_min: 5 },
      { id: 5, product_id: 2, location_id: 2, location_rack_id: 4, quantity: 5, damaged_quantity: 0, stock_min: 2 },
      { id: 6, product_id: 2, location_id: 3, location_rack_id: 5, quantity: 7, damaged_quantity: 0, stock_min: 3 },
    ]
  },
  {
    id: 3,
    product_code: 'BAT-IPH11-NOHON',
    name: 'Baterai iPhone 11 Nohon 3550mAh High Capacity',
    slug: 'baterai-iphone-11-nohon-high-capacity',
    barcode: '8992002002011',
    category_id: 2,
    sub_category_id: 4,
    brand_id: 1,
    product_maker_id: 5,
    product_type_id: 1,
    supplier_id: 2,
    unit_id: 1,
    purchase_price: 165000,
    selling_price: 260000,
    stock_min: 10,
    stock_global: 55,
    allow_open_price: false,
    has_serial_number: true,
    is_member_only: false,
    is_active: true,
    is_published: true,
    description: 'Baterai Nohon daya tahan 20% lebih lama, include perekat baterai dan segel resmi.',
    stocks: [
      { id: 7, product_id: 3, location_id: 1, location_rack_id: 2, quantity: 35, damaged_quantity: 0, stock_min: 10 },
      { id: 8, product_id: 3, location_id: 2, location_rack_id: 4, quantity: 12, damaged_quantity: 0, stock_min: 5 },
      { id: 9, product_id: 3, location_id: 3, location_rack_id: 5, quantity: 8, damaged_quantity: 0, stock_min: 5 },
    ]
  },
  {
    id: 4,
    product_code: 'BAT-IPH12-ORIG',
    name: 'Baterai iPhone 12 Original Service Pack 2815mAh',
    slug: 'baterai-iphone-12-original-service-pack',
    barcode: '8992002002012',
    category_id: 2,
    sub_category_id: 4,
    brand_id: 1,
    product_maker_id: 1,
    product_type_id: 2,
    supplier_id: 2,
    unit_id: 1,
    purchase_price: 240000,
    selling_price: 360000,
    stock_min: 6,
    stock_global: 28,
    allow_open_price: false,
    has_serial_number: true,
    is_member_only: false,
    is_active: true,
    is_published: true,
    description: 'Baterai 100% Original Apple Service Pack dengan chip baterai asli.',
    stocks: [
      { id: 10, product_id: 4, location_id: 1, location_rack_id: 2, quantity: 18, damaged_quantity: 0, stock_min: 6 },
      { id: 11, product_id: 4, location_id: 2, location_rack_id: 4, quantity: 5, damaged_quantity: 0, stock_min: 3 },
      { id: 12, product_id: 4, location_id: 3, location_rack_id: 5, quantity: 5, damaged_quantity: 0, stock_min: 3 },
    ]
  },
  {
    id: 5,
    product_code: 'LCD-SAM-S21U-ORIG',
    name: 'LCD Screen Samsung S21 Ultra 5G Original Service Pack with Frame',
    slug: 'lcd-samsung-s21-ultra-original-frame',
    barcode: '8993003003021',
    category_id: 1,
    sub_category_id: 2,
    brand_id: 2,
    product_maker_id: 1,
    product_type_id: 4,
    supplier_id: 1,
    unit_id: 1,
    purchase_price: 2450000,
    selling_price: 3200000,
    stock_min: 2,
    stock_global: 11,
    allow_open_price: true,
    has_serial_number: false,
    is_member_only: true,
    is_active: true,
    is_published: true,
    description: 'Dynamic AMOLED 2X 120Hz Original Samsung Service Pack lengkap dengan frame bezel.',
    stocks: [
      { id: 13, product_id: 5, location_id: 1, location_rack_id: 1, quantity: 7, damaged_quantity: 0, stock_min: 2 },
      { id: 14, product_id: 5, location_id: 2, location_rack_id: 4, quantity: 2, damaged_quantity: 0, stock_min: 1 },
      { id: 15, product_id: 5, location_id: 3, location_rack_id: 5, quantity: 2, damaged_quantity: 0, stock_min: 1 },
    ]
  },
  {
    id: 6,
    product_code: 'FLX-CHG-IPH11',
    name: 'Flexible Charging Port Board iPhone 11 Original',
    slug: 'flexible-charging-port-board-iphone-11',
    barcode: '8994004004011',
    category_id: 3,
    sub_category_id: 1,
    brand_id: 1,
    product_maker_id: 1,
    product_type_id: 1,
    supplier_id: 2,
    unit_id: 1,
    purchase_price: 75000,
    selling_price: 135000,
    stock_min: 8,
    stock_global: 42,
    allow_open_price: false,
    has_serial_number: false,
    is_member_only: false,
    is_active: true,
    is_published: true,
    description: 'Flex port cas + mic bawah original, fast charging & transfer data lancar.',
    stocks: [
      { id: 16, product_id: 6, location_id: 1, location_rack_id: 3, quantity: 25, damaged_quantity: 0, stock_min: 8 },
      { id: 17, product_id: 6, location_id: 2, location_rack_id: 4, quantity: 10, damaged_quantity: 0, stock_min: 4 },
      { id: 18, product_id: 6, location_id: 3, location_rack_id: 5, quantity: 7, damaged_quantity: 0, stock_min: 4 },
    ]
  },
  {
    id: 7,
    product_code: 'IC-PWR-PMIC-IPH',
    name: 'IC Power PMIC Main Power Management iPhone 11/12',
    slug: 'ic-power-pmic-iphone-11-12',
    barcode: '8995005005001',
    category_id: 5,
    sub_category_id: 6,
    brand_id: 1,
    product_maker_id: 1,
    product_type_id: 1,
    supplier_id: 3,
    unit_id: 1,
    purchase_price: 90000,
    selling_price: 175000,
    stock_min: 15,
    stock_global: 60,
    allow_open_price: false,
    has_serial_number: false,
    is_member_only: true,
    is_active: true,
    is_published: true,
    description: 'IC BGA Original untuk perbaikan mati total atau short circuit.',
    stocks: [
      { id: 19, product_id: 7, location_id: 1, location_rack_id: 3, quantity: 45, damaged_quantity: 0, stock_min: 15 },
      { id: 20, product_id: 7, location_id: 2, location_rack_id: 4, quantity: 5, damaged_quantity: 0, stock_min: 5 },
      { id: 21, product_id: 7, location_id: 3, location_rack_id: 5, quantity: 10, damaged_quantity: 0, stock_min: 5 },
    ]
  },
  {
    id: 8,
    product_code: 'TOOL-SUNSHINE-FLUX',
    name: 'Flux Solder Pasta Sunshine NC-559-ASM 100g',
    slug: 'flux-solder-pasta-sunshine-nc559',
    barcode: '8996006006001',
    category_id: 6,
    sub_category_id: 6,
    brand_id: 1,
    product_maker_id: 2,
    product_type_id: 1,
    supplier_id: 2,
    unit_id: 1,
    purchase_price: 45000,
    selling_price: 75000,
    stock_min: 10,
    stock_global: 35,
    allow_open_price: false,
    has_serial_number: false,
    is_member_only: false,
    is_active: true,
    is_published: true,
    description: 'Pasta timah flux bebas timbal tidak berasap, cocok untuk pasang IC BGA.',
    stocks: [
      { id: 22, product_id: 8, location_id: 1, location_rack_id: 3, quantity: 20, damaged_quantity: 0, stock_min: 10 },
      { id: 23, product_id: 8, location_id: 2, location_rack_id: 4, quantity: 10, damaged_quantity: 0, stock_min: 5 },
      { id: 24, product_id: 8, location_id: 3, location_rack_id: 5, quantity: 5, damaged_quantity: 0, stock_min: 5 },
    ]
  }
];

export const initialUsers: User[] = [
  {
    id: 1,
    name: 'Super Administrator / Owner',
    username: 'owner',
    email: 'owner@uteparts.test',
    branch_id: 1,
    role: 'super_admin',
    role_display_name: 'Owner / Super Admin',
    permissions: ['all'],
    is_active: true
  },
  {
    id: 2,
    name: 'Ahmad Fauzi (Manager Toko)',
    username: 'manager',
    email: 'manager@uteparts.test',
    branch_id: 1,
    role: 'admin',
    role_display_name: 'Store Manager',
    permissions: ['pos', 'inventory', 'reports', 'customers', 'services'],
    is_active: true
  },
  {
    id: 3,
    name: 'Siti Rahmawati (Kasir)',
    username: 'kasir',
    email: 'kasir@uteparts.test',
    branch_id: 1,
    role: 'cashier',
    role_display_name: 'Kasir POS',
    permissions: ['pos', 'customers.view'],
    is_active: true
  },
  {
    id: 4,
    name: 'Rudi Hartono (Teknisi Senior)',
    username: 'teknisi',
    email: 'teknisi@uteparts.test',
    branch_id: 1,
    role: 'technician',
    role_display_name: 'Teknisi Servis',
    permissions: ['services', 'inventory.view'],
    is_active: true
  }
];

export const initialServiceItems: ServiceItem[] = [
  { id: 1, code: 'SRV-LCD', name: 'Jasa Pasang & Ganti LCD Touchscreen', category: 'Layar', estimated_price: 100000, description: 'Bongkar pasang LCD + garansi tes 7 hari', is_active: true },
  { id: 2, code: 'SRV-BAT', name: 'Jasa Ganti Baterai Tanam / iPhone', category: 'Baterai', estimated_price: 75000, description: 'Ganti baterai + kalibrasi baterai', is_active: true },
  { id: 3, code: 'SRV-IC', name: 'Jasa Servis Mesin / Reball IC Power / Audio', category: 'Mesin', estimated_price: 350000, description: 'Pengerjaan mikroskopik BGA & pembersihan jalur', is_active: true },
  { id: 4, code: 'SRV-SW', name: 'Flash / Software / Bypass Akun Lupa Sandi', category: 'Software', estimated_price: 150000, description: 'Instal ulang firmware resmi', is_active: true },
  { id: 5, code: 'SRV-BKD', name: 'Jasa Ganti Kaca Belakang / Laser Backdoor', category: 'Body', estimated_price: 120000, description: 'Pembersihan laser dan pres kaca baru', is_active: true },
];

export const initialCashAccounts: BackOfficeCashAccount[] = [
  { id: 1, code: 'KAS-KASIR', name: 'Kas Laci Kasir Toko', type: 'cash', opening_balance: 1000000, current_balance: 3450000, is_active: true },
  { id: 2, code: 'KAS-OPERASIONAL', name: 'Kas Kecil Operasional', type: 'cash', opening_balance: 2000000, current_balance: 1650000, is_active: true },
  { id: 3, code: 'BANK-BCA', name: 'Bank BCA Utama 8820-9988-11', type: 'bank', opening_balance: 25000000, current_balance: 38450000, is_active: true },
  { id: 4, code: 'BANK-MANDIRI', name: 'Bank Mandiri 1420-0099-22', type: 'bank', opening_balance: 15000000, current_balance: 18200000, is_active: true },
];

export const initialCostCategories: BackOfficeCostCategory[] = [
  { id: 1, code: 'BIAYA-LISTRIK', name: 'Listrik, Air & Internet', type: 'expense', is_active: true },
  { id: 2, code: 'BIAYA-GAJI', name: 'Gaji, Lembur & Bonus Karyawan', type: 'expense', is_active: true },
  { id: 3, code: 'BIAYA-KONSUMSI', name: 'Konsumsi & Logistik Toko', type: 'expense', is_active: true },
  { id: 4, code: 'BIAYA-SEWA', name: 'Sewa Tempat & Kebersihan', type: 'expense', is_active: true },
  { id: 5, code: 'INC-JASA-LAIN', name: 'Pendapatan Non-Operasional', type: 'income', is_active: true },
];

export const initialPrinterSetting: PrinterSetting = {
  mode: 'browser',
  bridge_url: '',
  printer_name: 'POS-58 Thermal Printer',
  paper_width_mm: 58,
  copies: 1,
  header_text: 'UTE PARTS POS - SPAREPART & SERVICE HP',
  footer_text: 'Barang yang sudah dibeli tidak dapat ditukar kecuali ada garansi tes. Terima kasih atas kunjungan Anda!',
  show_store_name: true,
  show_datetime: true,
  auto_print: true,
  company_name: 'UTE Parts (Uteparts)',
  company_address: 'Jl. Hayam Wuruk No. 108, Jakarta Barat',
  company_city: 'Jakarta Barat',
  company_phone: '021-6288899 / 0812-9988-7766',
  company_slogan: 'Pusat Sparepart & Servis HP Terlengkap & Bergaransi',
};
