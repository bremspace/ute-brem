<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMaker;
use App\Models\ProductType;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class WebsiteProductController extends Controller
{
    public function index(Request $request)
    {
        $customer = session('website_customer');
        $isMember = ! empty($customer) && (($customer['type'] ?? null) === 'member');

        $query = Product::query()
            ->with(['category', 'subCategory', 'brand', 'maker', 'productTypes', 'images', 'stocks'])
            ->where('is_active', true)
            ->where('is_published', true)
            ->orderBy('name');

        $sharedIds = $this->decodeSharedProductIds($request->string('share')->toString());

        if ($sharedIds !== []) {
            $query->whereIn('id', $sharedIds);
        }

        if (Schema::hasColumn('products', 'is_member_only') && ! $isMember) {
            $query->where(function ($inner) {
                $inner->where('is_member_only', false)->orWhereNull('is_member_only');
            });
        }

        $this->applyFilters($query, $request);

        return view('website.products.index', [
            'products' => $query->paginate(18)->withQueryString(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'subCategories' => SubCategory::where('is_active', true)->orderBy('name')->get(['id', 'category_id', 'name']),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'makers' => ProductMaker::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'productTypes' => ProductType::where('is_active', true)->orderBy('name')->get(['id', 'brand_id', 'name']),
            'customer' => $customer,
            'isSharedCatalog' => $sharedIds !== [],
        ]);
    }

    public function show(string $slug)
    {
        $customer = session('website_customer');
        $isMember = ! empty($customer) && (($customer['type'] ?? null) === 'member');

        $product = Product::query()
            ->with(['category', 'subCategory', 'brand', 'maker', 'productTypes', 'images', 'stocks', 'defaultLocation', 'defaultRack', 'barcodes'])
            ->where('is_active', true)
            ->where('is_published', true)
            ->where('slug', $slug)
            ->first();

        if (! $product) {
            abort(404);
        }

        if (Schema::hasColumn('products', 'is_member_only') && $product->is_member_only && ! $isMember) {
            abort(404);
        }

        $relatedProducts = collect();
        if ($product->category_id) {
            $relatedQuery = Product::query()
                ->with(['images'])
                ->where('is_active', true)
                ->where('is_published', true)
                ->where('id', '!=', $product->id)
                ->where('category_id', $product->category_id);

            if (Schema::hasColumn('products', 'is_member_only') && ! $isMember) {
                $relatedQuery->where(function ($inner) {
                    $inner->where('is_member_only', false)->orWhereNull('is_member_only');
                });
            }

            $relatedProducts = $relatedQuery->limit(4)->get();
        }

        return view('website.products.show', [
            'product' => $product,
            'customer' => $customer,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    private function applyFilters($query, Request $request): void
    {
        $query->when($request->filled('q'), function ($filter) use ($request) {
            $search = $request->string('q')->toString();
            $filter->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        });

        $query->when($request->filled('category_id'), fn ($filter) => $filter->where('category_id', $request->integer('category_id')));
        $query->when($request->filled('sub_category_id'), fn ($filter) => $filter->where('sub_category_id', $request->integer('sub_category_id')));
        $brandIds = array_filter(array_map('intval', (array) $request->input('brand_id', [])));
        $makerIds = array_filter(array_map('intval', (array) $request->input('maker_id', [])));
        $productTypeIds = array_filter(array_map('intval', (array) $request->input('product_type_id', [])));

        if ($brandIds) {
            $query->whereIn('brand_id', $brandIds);
        }

        if ($makerIds) {
            $query->whereIn('product_maker_id', $makerIds);
        }

        if ($productTypeIds) {
            $query->whereHas('productTypes', fn ($filter) => $filter->whereIn('product_types.id', $productTypeIds));
        }
    }

    private function decodeSharedProductIds(?string $payload): array
    {
        if (! $payload) {
            return [];
        }

        $decoded = base64_decode(strtr($payload, '-_', '+/'), true);

        if (! $decoded) {
            return [];
        }

        return collect(explode(',', $decoded))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
