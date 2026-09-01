<?php

namespace App\Http\Controllers;

use App\Models\TablePreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class TablePreferenceController extends Controller
{
    public function show(string $tableKey)
    {
        $this->validateTableKey($tableKey);

        if (! Schema::hasTable('table_preferences')) {
            return response()->json(['preferences' => null]);
        }

        $preference = TablePreference::where('user_id', auth()->id())
            ->where('table_key', $tableKey)
            ->first();

        return response()->json([
            'preferences' => $preference?->preferences,
        ]);
    }

    public function store(Request $request, string $tableKey)
    {
        $this->validateTableKey($tableKey);

        if (! Schema::hasTable('table_preferences')) {
            return response()->json([
                'message' => 'Tabel table_preferences belum tersedia. Jalankan migration terlebih dahulu.',
            ], 409);
        }

        $validated = $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.column_order' => ['nullable', 'array'],
            'preferences.column_order.*' => ['string', 'max:100'],
            'preferences.column_visibility' => ['nullable', 'array'],
        ]);

        $preference = TablePreference::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'table_key' => $tableKey,
            ],
            [
                'preferences' => $validated['preferences'],
            ]
        );

        return response()->json([
            'message' => 'Pengaturan tabel berhasil disimpan.',
            'preferences' => $preference->preferences,
        ]);
    }

    private function validateTableKey(string $tableKey): void
    {
        validator(
            ['table_key' => $tableKey],
            ['table_key' => ['required', Rule::in(['products'])]]
        )->validate();
    }
}
