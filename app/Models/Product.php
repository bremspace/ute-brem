<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'brand_id',
        'product_maker_id',
        'quality',
        'default_location_id',
        'default_location_rack_id',
        'product_code',
        'name',
        'slug',
        'sku',
        'barcode',
        'legacy_image_path',
        'expired_date',
        'has_serial_number',
        'buy_unit',
        'sale_unit',
        'default_conversion_qty',
        'description',
        'purchase_price',
        'selling_price',
        'selling_price_margin_percent',
        'stock_global',
        'stock_min',
        'stock_max',
        'damaged_stock',
        'discount_value',
        'member_point',
        'staff_point',
        'sales_commission',
        'last_purchase_date',
        'rack_location',
        'additional_notes',
        'unit',
        'is_published',
        'is_member_only',
        'is_active',
        'is_open_price',
        'allow_discount_override',
        'sync_sell_price_to_branch',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'expired_date' => 'date',
        'last_purchase_date' => 'date',
        'has_serial_number' => 'boolean',
        'default_conversion_qty' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'selling_price_margin_percent' => 'decimal:2',
        'stock_global' => 'decimal:2',
        'stock_min' => 'decimal:2',
        'stock_max' => 'decimal:2',
        'damaged_stock' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'member_point' => 'decimal:2',
        'staff_point' => 'decimal:2',
        'sales_commission' => 'decimal:2',
        'is_published' => 'boolean',
        'is_member_only' => 'boolean',
        'is_active' => 'boolean',
        'is_open_price' => 'boolean',
        'allow_discount_override' => 'boolean',
        'sync_sell_price_to_branch' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function maker()
    {
        return $this->belongsTo(ProductMaker::class, 'product_maker_id');
    }

    public function defaultLocation()
    {
        return $this->belongsTo(Location::class, 'default_location_id');
    }

    public function defaultRack()
    {
        return $this->belongsTo(LocationRack::class, 'default_location_rack_id');
    }

    public function productTypes()
    {
        return $this->belongsToMany(ProductType::class, 'product_product_type');
    }

    public function barcodes()
    {
        return $this->hasMany(ProductBarcode::class)->orderBy('is_primary', 'desc')->orderBy('id');
    }

    public function units()
    {
        return $this->hasMany(ProductUnit::class)->orderBy('level');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function priceTiers()
    {
        return $this->hasMany(ProductPriceTier::class)->orderBy('unit_level')->orderBy('min_qty');
    }

    public function customerGroupPrices()
    {
        return $this->hasMany(ProductCustomerGroupPrice::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class)->with(['location', 'rack'])->orderBy('location_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class)->latest('movement_at');
    }

    public function stockTransfers()
    {
        return $this->hasMany(StockTransfer::class)->latest('transferred_at');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class)->latest('ordered_at');
    }

    public function productSuppliers()
    {
        return $this->hasMany(ProductSupplier::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_suppliers')
            ->withPivot(['supplier_product_code', 'last_purchase_price', 'is_primary', 'notes'])
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check() && ! $model->isForceDeleting()) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });
    }

    public function primaryImageUrl(): ?string
    {
        $path = $this->legacy_image_path ?: $this->images->first()?->image_path;

        return $path ? asset('storage/' . $path) : null;
    }

    public function productTypeNames(): string
    {
        return $this->productTypes->pluck('name')->join(', ');
    }

    public function primaryBarcode(): ?string
    {
        return $this->barcodes->firstWhere('is_primary', true)?->barcode
            ?? $this->barcodes->first()?->barcode
            ?? $this->barcode;
    }

    public function stockSummary(int $limit = 2): string
    {
        if ($this->relationLoaded('stocks') && $this->stocks->isNotEmpty()) {
            $items = $this->stocks
                ->take($limit)
                ->map(function ($stock) {
                    $quantity = rtrim(rtrim(number_format((float) $stock->quantity, 2, ',', '.'), '0'), ',');

                    return "{$stock->location?->name}: {$quantity}";
                })
                ->filter()
                ->implode('<br>');

            $remaining = $this->stocks->count() - min($this->stocks->count(), $limit);

            if ($remaining > 0) {
                $items .= '<br><span class="text-muted small">+' . $remaining . ' lokasi lain</span>';
            }

            return $items;
        }

        return rtrim(rtrim(number_format((float) $this->stock_global, 2, ',', '.'), '0'), ',') . ' ' . ($this->sale_unit ?: 'pcs');
    }
}
