<?php

namespace App\Http\Controllers;

use App\Models\ItemSerial;
use App\Models\Location;
use App\Models\LocationRack;
use App\Models\Product;
use Illuminate\Http\Request;

class ItemSerialController extends Controller
{
    public function index(Request $request)
    {
        $query = ItemSerial::with(['product', 'productStock', 'rack']);

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                  ->orWhere('reference_code', 'like', "%{$search}%")
                  ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($productId = $request->integer('product')) {
            $query->where('product_id', $productId);
        }

        $serials = $query->latest()->paginate(30);
        $products = Product::where('has_serial_number', true)->orWhere('is_active', true)->orderBy('name')->get();

        return view('item-serials.index', compact('serials', 'products'));
    }

    public function create()
    {
        $products = Product::where('has_serial_number', true)->orderBy('name')->get();
        $racks = LocationRack::with('location')->orderBy('code')->get();

        return view('item-serials.create', compact('products', 'racks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'location_rack_id' => 'nullable|exists:location_racks,id',
            'serials' => 'required|string', // dipisah baris / koma
        ]);

        $product = Product::find($validated['product_id']);
        if (! $product->has_serial_number) {
            return back()->with('error', "Produk '{$product->name}' tidak dilacak per serial. Aktifkan {has_serial_number} dulu.");
        }

        $rackId = $validated['location_rack_id'] ?? null;
        $rawSerials = preg_split('/[\r\n,;]+/', $validated['serials']);
        $created = 0;
        $skipped = 0;

        foreach ($rawSerials as $serial) {
            $serial = trim($serial);
            if ($serial === '') {
                continue;
            }
            try {
                ItemSerial::create([
                    'product_id' => $product->id,
                    'location_rack_id' => $rackId,
                    'serial_number' => $serial,
                    'status' => 'available',
                    'created_by' => auth()->id(),
                ]);
                $created++;
            } catch (\Throwable $e) {
                $skipped++; // duplikat / error
            }
        }

        return back()->with('success', "Ditambahkan {$created} serial (available).")->with('info', $skipped > 0 ? "{$skipped} serial dilewati (duplikat)." : null);
    }
}
