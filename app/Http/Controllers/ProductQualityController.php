<?php

namespace App\Http\Controllers;

use App\Models\ProductQuality;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductQualityController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_qualities,name',
            'code' => 'nullable|string|max:30|unique:product_qualities,code',
            'is_active' => 'nullable|boolean',
        ]);

        $quality = ProductQuality::create([
            'name' => trim((string) $validated['name']),
            'code' => filled($validated['code'] ?? null) ? strtoupper(trim((string) $validated['code'])) : null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        UserLog::log('CREATE_PRODUCT_QUALITY', "Created product quality: {$quality->name}", null, null, $quality->only(['name', 'code', 'is_active']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Quality berhasil ditambahkan.',
                'quality' => $quality->only(['id', 'name', 'code', 'is_active']),
            ], 201);
        }

        return back()->with('success', 'Quality berhasil ditambahkan.');
    }
}
