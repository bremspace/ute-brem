<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomerGroup;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductCustomerGroupPrice;
use App\Models\ProductMaker;
use App\Models\ProductPriceTier;
use App\Models\ProductSupplier;
use App\Models\ProductType;
use App\Models\ProductUnit;
use App\Models\ProductVariant;
use App\Models\SubCategory;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating sample products with full form data...');

        if (Category::count() === 0) {
            $this->command->warn('No categories found. Please run master barang seeders first.');
            return;
        }

        $products = [
            [
                'category' => 'lcd-touchscreen',
                'sub_category' => 'lcd-touchscreen-oled',
                'brand' => 'iPhone',
                'maker' => 'OEM Premium',
                'types' => ['iPhone 11'],
                'default_location_code' => 'TOKO',
                'product_code' => 'SP-LCD-IP11-OLED',
                'name' => 'LCD iPhone 11 OLED Premium',
                'sku' => 'LCD-IP11-OLED-001',
                'barcode' => '8998802001001',
                'description' => 'LCD set OLED premium untuk iPhone 11, sudah include touchscreen dan frame.',
                'purchase_price' => 420000,
                'selling_price' => 535000,
                'expired_date' => null,
                'has_serial_number' => true,
                'buy_unit' => 'PCS',
                'sale_unit' => 'PCS',
                'default_conversion_qty' => 1,
                'stock_min' => 5,
                'stock_max' => 40,
                'discount_value' => 0,
                'member_point' => 10,
                'staff_point' => 5,
                'sales_commission' => 7500,
                'last_purchase_date' => now()->subDays(7)->toDateString(),
                'rack_location' => 'Rak LCD A1',
                'additional_notes' => 'Barang fragile, wajib cek fleksibel dan dead pixel sebelum dijual.',
                'is_open_price' => false,
                'allow_discount_override' => true,
                'sync_sell_price_to_branch' => true,
                'is_published' => false,
                'is_active' => true,
                'suppliers' => [
                    ['code' => 'SUP-LCD', 'supplier_product_code' => 'IP11-OLED-PREM', 'last_purchase_price' => 420000, 'is_primary' => true, 'notes' => 'Supplier utama LCD premium'],
                    ['code' => 'SUP-GPC', 'supplier_product_code' => 'IPH11-LCD-OLED', 'last_purchase_price' => 430000, 'is_primary' => false, 'notes' => 'Supplier cadangan'],
                ],
                'units' => [
                    ['level' => 1, 'unit_name' => 'PCS', 'conversion_qty' => 1, 'barcode' => '8998802001001', 'barcode_label' => 'LCD IP11 PCS', 'price_toko' => 535000, 'margin_toko' => 115000, 'price_partai' => 510000, 'margin_partai' => 90000, 'price_cabang' => 520000, 'margin_cabang' => 100000, 'price_lain' => 500000, 'margin_lain' => 80000],
                    ['level' => 2, 'unit_name' => 'SET', 'conversion_qty' => 5, 'barcode' => '8998802001002', 'barcode_label' => 'LCD IP11 SET 5', 'price_toko' => 2600000, 'margin_toko' => 500000, 'price_partai' => 2500000, 'margin_partai' => 400000, 'price_cabang' => 2550000, 'margin_cabang' => 450000, 'price_lain' => 2450000, 'margin_lain' => 350000],
                ],
                'price_tiers' => [
                    ['unit_level' => 1, 'min_qty' => 3, 'price' => 520000],
                    ['unit_level' => 1, 'min_qty' => 10, 'price' => 500000],
                ],
                'variants' => [
                    ['size' => 'Original Size', 'color' => 'Black', 'stock' => 0, 'price' => 535000, 'notes' => 'Grade premium'],
                ],
                'group_prices' => [
                    'Group 1' => ['toko' => 535000, 'partai' => 510000],
                    'Group 2' => ['toko' => 520000, 'partai' => 500000],
                    'Group 3' => ['toko' => 510000, 'partai' => 495000],
                    'Group 4' => ['toko' => 500000, 'partai' => 485000],
                ],
            ],
            [
                'category' => 'baterai',
                'sub_category' => 'baterai-baterai-double-power',
                'brand' => 'Samsung',
                'maker' => 'BPE',
                'types' => ['A12', 'A13'],
                'default_location_code' => 'GDG',
                'product_code' => 'SP-BAT-SAM-A12-DP',
                'name' => 'Baterai Samsung A12/A13 Double Power',
                'sku' => 'BAT-SAM-A12-DP-001',
                'barcode' => '8998802002001',
                'description' => 'Baterai double power kompatibel Samsung A12 dan A13.',
                'purchase_price' => 78000,
                'selling_price' => 125000,
                'expired_date' => now()->addYear()->toDateString(),
                'has_serial_number' => false,
                'buy_unit' => 'PCS',
                'sale_unit' => 'PCS',
                'default_conversion_qty' => 1,
                'stock_min' => 15,
                'stock_max' => 120,
                'discount_value' => 5000,
                'member_point' => 3,
                'staff_point' => 2,
                'sales_commission' => 2500,
                'last_purchase_date' => now()->subDays(3)->toDateString(),
                'rack_location' => 'Rak Baterai B2',
                'additional_notes' => 'Simpan di suhu ruang, hindari panas langsung.',
                'is_open_price' => false,
                'allow_discount_override' => true,
                'sync_sell_price_to_branch' => true,
                'is_published' => false,
                'is_active' => true,
                'suppliers' => [
                    ['code' => 'SUP-BAT', 'supplier_product_code' => 'BAT-A12-DP', 'last_purchase_price' => 78000, 'is_primary' => true, 'notes' => 'Supplier baterai utama'],
                    ['code' => 'SUP-SPG', 'supplier_product_code' => 'SAMA12-BAT-DP', 'last_purchase_price' => 80000, 'is_primary' => false, 'notes' => 'Backup fast moving'],
                ],
                'units' => [
                    ['level' => 1, 'unit_name' => 'PCS', 'conversion_qty' => 1, 'barcode' => '8998802002001', 'barcode_label' => 'BAT SAM A12 PCS', 'price_toko' => 125000, 'margin_toko' => 47000, 'price_partai' => 115000, 'margin_partai' => 37000, 'price_cabang' => 118000, 'margin_cabang' => 40000, 'price_lain' => 110000, 'margin_lain' => 32000],
                    ['level' => 2, 'unit_name' => 'PACK', 'conversion_qty' => 10, 'barcode' => '8998802002002', 'barcode_label' => 'BAT SAM A12 PACK 10', 'price_toko' => 1180000, 'margin_toko' => 400000, 'price_partai' => 1100000, 'margin_partai' => 320000, 'price_cabang' => 1130000, 'margin_cabang' => 350000, 'price_lain' => 1080000, 'margin_lain' => 300000],
                ],
                'price_tiers' => [
                    ['unit_level' => 1, 'min_qty' => 5, 'price' => 118000],
                    ['unit_level' => 1, 'min_qty' => 20, 'price' => 110000],
                ],
                'variants' => [
                    ['size' => 'A12', 'color' => 'Black', 'stock' => 0, 'price' => 125000, 'notes' => 'Kompatibel A12'],
                    ['size' => 'A13', 'color' => 'Black', 'stock' => 0, 'price' => 125000, 'notes' => 'Kompatibel A13'],
                ],
                'group_prices' => [
                    'Group 1' => ['toko' => 125000, 'partai' => 115000],
                    'Group 2' => ['toko' => 120000, 'partai' => 112000],
                    'Group 3' => ['toko' => 118000, 'partai' => 110000],
                    'Group 4' => ['toko' => 115000, 'partai' => 108000],
                ],
            ],
            [
                'category' => 'backdoor-casing',
                'sub_category' => 'backdoor-casing-backdoor',
                'brand' => 'Xiaomi',
                'maker' => 'Vizz',
                'types' => ['Redmi Note 10'],
                'default_location_code' => 'TOKO',
                'product_code' => 'SP-BD-RN10-BLK',
                'name' => 'Backdoor Xiaomi Redmi Note 10 Hitam',
                'sku' => 'BD-RN10-BLK-001',
                'barcode' => '8998802003001',
                'description' => 'Backdoor pengganti Xiaomi Redmi Note 10 warna hitam (tanpa logo tambahan).',
                'purchase_price' => 45000,
                'selling_price' => 85000,
                'expired_date' => null,
                'has_serial_number' => false,
                'buy_unit' => 'PCS',
                'sale_unit' => 'PCS',
                'default_conversion_qty' => 1,
                'stock_min' => 3,
                'stock_max' => 50,
                'discount_value' => 0,
                'member_point' => 1,
                'staff_point' => 1,
                'sales_commission' => 1500,
                'last_purchase_date' => now()->subDays(10)->toDateString(),
                'rack_location' => 'Rak Backdoor C1',
                'additional_notes' => 'Cek warna dan presisi lubang kamera sebelum pemasangan.',
                'is_open_price' => false,
                'allow_discount_override' => true,
                'sync_sell_price_to_branch' => true,
                'is_published' => false,
                'is_active' => true,
                'suppliers' => [
                    ['code' => 'SUP-SPG', 'supplier_product_code' => 'BD-RN10-BLK', 'last_purchase_price' => 45000, 'is_primary' => true, 'notes' => 'Supplier utama casing/backdoor'],
                    ['code' => 'SUP-GPC', 'supplier_product_code' => 'XIA-RN10-BD-BLK', 'last_purchase_price' => 47000, 'is_primary' => false, 'notes' => 'Cadangan jika stok kosong'],
                ],
                'units' => [
                    ['level' => 1, 'unit_name' => 'PCS', 'conversion_qty' => 1, 'barcode' => '8998802003001', 'barcode_label' => 'BD RN10 PCS', 'price_toko' => 85000, 'margin_toko' => 40000, 'price_partai' => 80000, 'margin_partai' => 35000, 'price_cabang' => 82000, 'margin_cabang' => 37000, 'price_lain' => 78000, 'margin_lain' => 33000],
                    ['level' => 2, 'unit_name' => 'PACK', 'conversion_qty' => 10, 'barcode' => '8998802003002', 'barcode_label' => 'BD RN10 PACK 10', 'price_toko' => 820000, 'margin_toko' => 370000, 'price_partai' => 780000, 'margin_partai' => 330000, 'price_cabang' => 800000, 'margin_cabang' => 350000, 'price_lain' => 760000, 'margin_lain' => 310000],
                ],
                'price_tiers' => [
                    ['unit_level' => 1, 'min_qty' => 5, 'price' => 82000],
                    ['unit_level' => 1, 'min_qty' => 15, 'price' => 78000],
                ],
                'variants' => [
                    ['size' => 'Standard', 'color' => 'Black', 'stock' => 0, 'price' => 85000, 'notes' => 'Finishing matte'],
                ],
                'group_prices' => [
                    'Group 1' => ['toko' => 85000, 'partai' => 80000],
                    'Group 2' => ['toko' => 83000, 'partai' => 79000],
                    'Group 3' => ['toko' => 82000, 'partai' => 78000],
                    'Group 4' => ['toko' => 80000, 'partai' => 76000],
                ],
            ],
            [
                'category' => 'konektor-charging',
                'sub_category' => 'konektor-charging-board-charger',
                'brand' => 'Oppo',
                'maker' => 'Rakkipanda',
                'types' => ['A3S'],
                'default_location_code' => 'GDG',
                'product_code' => 'SP-BC-OPPO-A3S',
                'name' => 'Board Charger Oppo A3S',
                'sku' => 'BC-OPPO-A3S-001',
                'barcode' => '8998802004001',
                'description' => 'Board charger untuk Oppo A3S, ready mic dan konektor charging.',
                'purchase_price' => 30000,
                'selling_price' => 55000,
                'expired_date' => null,
                'has_serial_number' => false,
                'buy_unit' => 'PCS',
                'sale_unit' => 'PCS',
                'default_conversion_qty' => 1,
                'stock_min' => 10,
                'stock_max' => 200,
                'discount_value' => 0,
                'member_point' => 1,
                'staff_point' => 1,
                'sales_commission' => 1000,
                'last_purchase_date' => now()->subDays(5)->toDateString(),
                'rack_location' => 'Rak Charger D2',
                'additional_notes' => 'Sebelum jual, cek test charging dan mic.',
                'is_open_price' => false,
                'allow_discount_override' => true,
                'sync_sell_price_to_branch' => true,
                'is_published' => false,
                'is_active' => true,
                'suppliers' => [
                    ['code' => 'SUP-GPC', 'supplier_product_code' => 'OP-A3S-BC', 'last_purchase_price' => 30000, 'is_primary' => true, 'notes' => 'Board charger supply'],
                    ['code' => 'SUP-SPG', 'supplier_product_code' => 'BC-A3S', 'last_purchase_price' => 32000, 'is_primary' => false, 'notes' => 'Backup supplier'],
                ],
                'units' => [
                    ['level' => 1, 'unit_name' => 'PCS', 'conversion_qty' => 1, 'barcode' => '8998802004001', 'barcode_label' => 'BC OPPO A3S PCS', 'price_toko' => 55000, 'margin_toko' => 25000, 'price_partai' => 52000, 'margin_partai' => 22000, 'price_cabang' => 53000, 'margin_cabang' => 23000, 'price_lain' => 50000, 'margin_lain' => 20000],
                    ['level' => 2, 'unit_name' => 'PACK', 'conversion_qty' => 20, 'barcode' => '8998802004002', 'barcode_label' => 'BC OPPO A3S PACK 20', 'price_toko' => 1040000, 'margin_toko' => 440000, 'price_partai' => 980000, 'margin_partai' => 380000, 'price_cabang' => 1000000, 'margin_cabang' => 400000, 'price_lain' => 960000, 'margin_lain' => 360000],
                ],
                'price_tiers' => [
                    ['unit_level' => 1, 'min_qty' => 10, 'price' => 53000],
                    ['unit_level' => 1, 'min_qty' => 50, 'price' => 50000],
                ],
                'variants' => [
                    ['size' => 'Standard', 'color' => 'N/A', 'stock' => 0, 'price' => 55000, 'notes' => 'Include mic'],
                ],
                'group_prices' => [
                    'Group 1' => ['toko' => 55000, 'partai' => 52000],
                    'Group 2' => ['toko' => 54000, 'partai' => 51000],
                    'Group 3' => ['toko' => 53000, 'partai' => 50000],
                    'Group 4' => ['toko' => 52000, 'partai' => 49000],
                ],
            ],
            [
                'category' => 'flexibel-tombol',
                'sub_category' => 'flexibel-tombol-flexibel-power',
                'brand' => 'Vivo',
                'maker' => 'Hippo',
                'types' => ['Y12'],
                'default_location_code' => 'ETL',
                'product_code' => 'SP-FLX-PWR-Y12',
                'name' => 'Flexibel Power Vivo Y12',
                'sku' => 'FLX-PWR-Y12-001',
                'barcode' => '8998802005001',
                'description' => 'Flexibel tombol power untuk Vivo Y12, cocok untuk penggantian switch power.',
                'purchase_price' => 15000,
                'selling_price' => 30000,
                'expired_date' => null,
                'has_serial_number' => false,
                'buy_unit' => 'PCS',
                'sale_unit' => 'PCS',
                'default_conversion_qty' => 1,
                'stock_min' => 10,
                'stock_max' => 150,
                'discount_value' => 0,
                'member_point' => 1,
                'staff_point' => 1,
                'sales_commission' => 750,
                'last_purchase_date' => now()->subDays(14)->toDateString(),
                'rack_location' => 'Rak Flex E1',
                'additional_notes' => 'Pastikan fleksibel tidak terlipat/robek saat pemasangan.',
                'is_open_price' => false,
                'allow_discount_override' => true,
                'sync_sell_price_to_branch' => true,
                'is_published' => false,
                'is_active' => true,
                'suppliers' => [
                    ['code' => 'SUP-SPG', 'supplier_product_code' => 'FLX-PWR-Y12', 'last_purchase_price' => 15000, 'is_primary' => true, 'notes' => 'Flexibel/tombol'],
                    ['code' => 'SUP-GPC', 'supplier_product_code' => 'VIVOY12-PWR-FLX', 'last_purchase_price' => 16000, 'is_primary' => false, 'notes' => 'Cadangan'],
                ],
                'units' => [
                    ['level' => 1, 'unit_name' => 'PCS', 'conversion_qty' => 1, 'barcode' => '8998802005001', 'barcode_label' => 'FLX PWR Y12 PCS', 'price_toko' => 30000, 'margin_toko' => 15000, 'price_partai' => 28000, 'margin_partai' => 13000, 'price_cabang' => 29000, 'margin_cabang' => 14000, 'price_lain' => 27000, 'margin_lain' => 12000],
                    ['level' => 2, 'unit_name' => 'PACK', 'conversion_qty' => 25, 'barcode' => '8998802005002', 'barcode_label' => 'FLX PWR Y12 PACK 25', 'price_toko' => 700000, 'margin_toko' => 325000, 'price_partai' => 650000, 'margin_partai' => 300000, 'price_cabang' => 675000, 'margin_cabang' => 312500, 'price_lain' => 625000, 'margin_lain' => 287500],
                ],
                'price_tiers' => [
                    ['unit_level' => 1, 'min_qty' => 10, 'price' => 29000],
                    ['unit_level' => 1, 'min_qty' => 50, 'price' => 27000],
                ],
                'variants' => [
                    ['size' => 'Standard', 'color' => 'N/A', 'stock' => 0, 'price' => 30000, 'notes' => 'Vivo Y12'],
                ],
                'group_prices' => [
                    'Group 1' => ['toko' => 30000, 'partai' => 28000],
                    'Group 2' => ['toko' => 29500, 'partai' => 27500],
                    'Group 3' => ['toko' => 29000, 'partai' => 27000],
                    'Group 4' => ['toko' => 28500, 'partai' => 26500],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::where('slug', $productData['category'])->first();
            $subCategory = SubCategory::where('slug', $productData['sub_category'])->first();
            $brand = Brand::where('name', $productData['brand'])->first();
            $maker = ProductMaker::where('name', $productData['maker'])->first();
            $defaultLocation = Location::with('racks')->where('code', $productData['default_location_code'])->first();

            if (! $category) {
                continue;
            }

            $product = Product::updateOrCreate(
                ['sku' => $productData['sku']],
                [
                    'product_code' => $productData['product_code'],
                    'category_id' => $category->id,
                    'sub_category_id' => $subCategory?->id,
                    'brand_id' => $brand?->id,
                    'product_maker_id' => $maker?->id,
                    'default_location_id' => $defaultLocation?->id,
                    'default_location_rack_id' => $defaultLocation?->racks->first()?->id,
                    'name' => $productData['name'],
                    'slug' => Str::slug($productData['name']),
                    'barcode' => $productData['barcode'],
                    'description' => $productData['description'],
                    'purchase_price' => $productData['purchase_price'],
                    'selling_price' => $productData['selling_price'],
                    'expired_date' => $productData['expired_date'],
                    'has_serial_number' => $productData['has_serial_number'],
                    'buy_unit' => $productData['buy_unit'],
                    'sale_unit' => $productData['sale_unit'],
                    'default_conversion_qty' => $productData['default_conversion_qty'],
                    'stock_min' => $productData['stock_min'],
                    'stock_max' => $productData['stock_max'],
                    'discount_value' => $productData['discount_value'],
                    'member_point' => $productData['member_point'],
                    'staff_point' => $productData['staff_point'],
                    'sales_commission' => $productData['sales_commission'],
                    'last_purchase_date' => $productData['last_purchase_date'],
                    'rack_location' => $productData['rack_location'],
                    'additional_notes' => $productData['additional_notes'],
                    'unit' => $productData['sale_unit'],
                    'is_open_price' => $productData['is_open_price'],
                    'allow_discount_override' => $productData['allow_discount_override'],
                    'sync_sell_price_to_branch' => $productData['sync_sell_price_to_branch'],
                    'is_published' => $productData['is_published'],
                    'is_active' => $productData['is_active'],
                ]
            );

            $typeIds = ProductType::whereIn('name', $productData['types'])
                ->when($brand, fn ($query) => $query->where('brand_id', $brand->id))
                ->pluck('id')
                ->all();
            $product->productTypes()->sync($typeIds);

            $this->syncBarcodes($product, $productData);
            $this->syncSuppliers($product, $productData['suppliers']);
            $this->syncUnits($product, $productData['units']);
            $this->syncPriceTiers($product, $productData['price_tiers']);
            $this->syncVariants($product, $productData['variants']);
            $this->syncCustomerGroupPrices($product, $productData['group_prices']);
        }

        $this->command->info(count($products) . ' sample products created successfully!');
    }

    private function syncBarcodes(Product $product, array $productData): void
    {
        ProductBarcode::where('product_id', $product->id)->delete();

        ProductBarcode::create([
            'product_id' => $product->id,
            'barcode' => $productData['barcode'],
            'label' => 'Barcode Utama',
            'unit_level' => 1,
            'is_primary' => true,
        ]);
    }

    private function syncSuppliers(Product $product, array $suppliers): void
    {
        ProductSupplier::where('product_id', $product->id)->delete();

        foreach ($suppliers as $supplierRow) {
            $supplier = Supplier::where('code', $supplierRow['code'])->first();

            if (! $supplier) {
                continue;
            }

            ProductSupplier::create([
                'product_id' => $product->id,
                'supplier_id' => $supplier->id,
                'supplier_product_code' => $supplierRow['supplier_product_code'] ?? null,
                'last_purchase_price' => $supplierRow['last_purchase_price'] ?? null,
                'is_primary' => (bool) ($supplierRow['is_primary'] ?? false),
                'notes' => $supplierRow['notes'] ?? null,
            ]);
        }
    }

    private function syncUnits(Product $product, array $units): void
    {
        ProductUnit::where('product_id', $product->id)->delete();

        foreach ($units as $unit) {
            ProductUnit::create(array_merge($unit, ['product_id' => $product->id]));
        }
    }

    private function syncPriceTiers(Product $product, array $priceTiers): void
    {
        ProductPriceTier::where('product_id', $product->id)->delete();

        foreach ($priceTiers as $priceTier) {
            ProductPriceTier::create(array_merge($priceTier, ['product_id' => $product->id]));
        }
    }

    private function syncVariants(Product $product, array $variants): void
    {
        ProductVariant::where('product_id', $product->id)->delete();

        foreach ($variants as $variant) {
            ProductVariant::create(array_merge($variant, ['product_id' => $product->id]));
        }
    }

    private function syncCustomerGroupPrices(Product $product, array $groupPrices): void
    {
        ProductCustomerGroupPrice::where('product_id', $product->id)->delete();

        foreach ($groupPrices as $groupName => $prices) {
            $group = CustomerGroup::where('name', $groupName)->first();

            if (! $group) {
                continue;
            }

            ProductCustomerGroupPrice::create([
                'product_id' => $product->id,
                'customer_group_id' => $group->id,
                'channel' => 'toko',
                'price' => $prices['toko'],
            ]);

            ProductCustomerGroupPrice::create([
                'product_id' => $product->id,
                'customer_group_id' => $group->id,
                'channel' => 'partai',
                'price' => $prices['partai'],
            ]);
        }
    }
}
