<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\SidRetailConfig;
use App\Models\SidRetailImportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SidRetailImportService
{
    private array $tableMappings = [
        'barang' => ['model' => Product::class, 'strategy' => 'replace'],
        'kategori' => ['model' => Category::class, 'strategy' => 'replace'],
        'supplier' => ['model' => Supplier::class, 'strategy' => 'replace'],
        'pelanggan' => ['model' => Customer::class, 'strategy' => 'merge'],
        'penjualan' => ['model' => Sale::class, 'strategy' => 'append'],
        'pembelian' => ['model' => PurchaseOrder::class, 'strategy' => 'append'],
        'arus_stok' => ['model' => StockMovement::class, 'strategy' => 'append'],
        'arus_stok_2_2026' => ['model' => StockMovement::class, 'strategy' => 'append'],
        'user' => ['model' => User::class, 'strategy' => 'merge'],
    ];

    private array $fieldMappings = [
        'barang' => [
            'kode' => 'code', 'nama' => 'name', 'kategori' => 'category_id',
            'golongan' => 'category_id', 'satuanbeli' => 'unit_beli', 'satuan' => 'unit_id',
            'isi' => 'price_level', 'toko' => 'price_toko', 'hpp' => 'price_buy',
            'harga_toko' => 'price', 'diskon' => 'discount', 'point' => 'point_value',
            'jenis' => 'type', 'stokmin' => 'stock_min', 'stokmax' => 'stock_max',
            'warningstok' => 'stock_alert', 'gambar' => 'image', 'ukuran' => 'size',
            'supplier' => 'supplier_code', 'harga_karyawan' => 'employee_price',
            'harga_member' => 'member_price', 'margin_toko' => 'margin',
            'harga_terakhir' => 'last_price', 'jenis_point' => 'point_type',
            'toko_rusak' => 'damaged_price', 'toko2' => 'price2', 'toko3' => 'price3',
            'toko4' => 'price4', 'ket' => 'description',
        ],
        'kategori' => ['kode' => 'code', 'title' => 'name', 'description' => 'description'],
        'supplier' => ['kode' => 'code', 'nama' => 'name', 'alamat' => 'address',
            'telp' => 'phone', 'email' => 'email', 'contact' => 'contact_person'],
        'pelanggan' => ['kode' => 'code', 'nama' => 'name', 'alamat' => 'address',
            'email' => 'email', 'telp' => 'phone', 'max_piutang' => 'credit_limit',
            'point' => 'point_balance', 'sales' => 'sales_person'],
    ];

    private array $columnDefinitions = [
        'barang' => ['kode', 'nama', 'kategori', 'golongan', 'subgolongan1', 'subgolongan2',
            'satuanbeli', 'satuan', 'satuan2', 'satuan3', 'satuan4', 'isi', 'isi2', 'isi3', 'isi4',
            'elektrik', 'sn', 'master', 'tr_saldo', 'nol_price', 'nol_price_diskon', 'toko', 'gudang',
            'hpp', 'harga_toko', 'harga_toko2', 'harga_toko3', 'harga_toko4', 'harga_partai',
            'harga_partai2', 'harga_partai3', 'harga_partai4', 'harga_cabang', 'harga_cabang2',
            'harga_cabang3', 'harga_cabang4', 'diskon', 'point', 'point_m', 'jenis', 'sinkron',
            'stokmin', 'stokmax', 'warningstok', 'jenis_point', 'point_k1', 'point_k2', 'gambar'],
        'kategori' => ['kode', 'point_member', 'diskon_global_persentase', 'description', 'title'],
        'supplier' => ['kode', 'nama', 'alamat', 'saldo_piutang', 'tgl_saldo', 'nomor', 'telp',
            'fax', 'email', 'no_npwp', 'tampil', 'kdgrouphrg', 'kota', 'alamat2', 'contact', 'saldo_deposit'],
        'pelanggan' => ['kode', 'nama', 'alamat', 'max_piutang', 'saldo_piutang', 'password', 'area',
            'instansi', 'pekerjaan', 'email', 'tgllahir', 'telp', 'tampil', 'tgl_lahir', 'foto',
            'kota', 'rayon', 'diskn_penjualan', 'persen_shu', 'sales', 'nonpwp', 'nofax',
            'kdgrouphrg', 'nama_toko', 'saldo_tabungan', 'pelanggan_kena_pajak',
            'blokir_piutang_hari', 'point', 'sisa', 'join_date', 'wa_oto_piutang_last_date'],
        'penjualan' => ['kode', 'tanggal', 'pelanggan', 'nama_pelanggan', 'alamat_pelanggan',
            'member', 'kode_kas', 'keterangan', 'angsuran', 'subtotal', 'diskon', 'diskon_rupiah',
            'tax', 'tax_rupiah', 'jumlah', 'bayar', 'kembali', 'operator', 'point_penjualan',
            'jt', 'lunas', 'visa', 'nomor_visa', 'nama_visa', 'jenis', 'piutang', 'po', 'receive',
            'jasakirim', 'biayakirim', 'pelanggan_visa', 'kasir', 'tahan', 'status', 'spg', 'jam'],
        'pembelian' => ['kode', 'tanggal', 'supplier', 'kode_kas', 'keterangan', 'diskon', 'tax',
            'jumlah', 'operator', 'jt', 'lunas', 'visa', 'nomor_visa', 'nama_visa', 'hutang',
            'po', 'receive', 'jasakirim', 'biayakirim', 'lokasistok', 'pr', 'kode_deposit_giro'],
        'arus_stok' => ['kode', 'tanggal', 'jam', 'kode_barang', 'awal', 'nilai_awal', 'masuk',
            'nilai_masuk', 'keluar', 'nilai_keluar', 'sisa', 'nilai_sisa', 'lokasi_cabang',
            'lokasi_stok', 'transaksi', 'no_transaksi', 'operator'],
    ];

    public function importSidRetailDatabase(): void
    {
        try {
            DB::beginTransaction();

            // Create or update import config
            $this->createOrUpdateImportConfig();

            // Parse SQL file
            $sqlContent = file_get_contents(base_path('latest.sql'));

            // Parse tables and their data
            $tables = $this->parseAllTableData($sqlContent);

            // Import each table
            $this->importTables($tables);

            DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('SID Retail Import Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function createOrUpdateImportConfig(): void
    {
        SidRetailConfig::updateOrCreate(
            ['name' => 'SID Retail'],
            [
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => 'toko_1_3',
                'username' => 'root',
                'password' => '',
                'prefix' => '',
                'is_active' => true,
            ]
        );
    }

    private function parseAllTableData(string $sql): array
    {
        $result = [];

        foreach ($this->tableMappings as $tableName => $mapping) {
            if ($mapping['strategy'] === 'skip') continue;

            $columns = $this->columnDefinitions[$tableName] ?? [];
            $rows = $this->parseTableData($sql, $tableName, $columns);

            if (!empty($rows)) {
                $result[$tableName] = [
                    'columns' => $columns,
                    'rows' => $rows,
                    'mapping' => $mapping,
                ];
            }
        }

        return $result;
    }

    private function parseTableData(string $sql, string $tableName, array $columns): array
    {
        $data = [];

        // Pattern to match INSERT INTO with column names
        $pattern = '/INSERT INTO `' . preg_quote($tableName, '/') . '`\s*\(([^)]+)\)\s*VALUES\s*(.*?);/si';

        if (preg_match($pattern, $sql, $matches)) {
            // Parse column names
            $colNames = array_map('trim', explode(',', $matches[1]));
            $colNames = array_map(function($c) { return trim($c, '`'); }, $colNames);

            // Get all VALUES blocks (might be multiple rows in one INSERT)
            $valuesBlock = $matches[2];

            // Split by VALUES keyword
            $valueBlocks = preg_split('/\)\s*,\s*\(/s', $valuesBlock);

            foreach ($valueBlocks as $valueBlock) {
                $valueBlock = trim($valueBlock);
                $valueBlock = trim($valueBlock, ',');
                $valueBlock = trim($valueBlock, '(');

                $values = $this->parseValuesFromRow($valueBlock);

                if (count($values) === count($colNames)) {
                    // Create associative array with column names as keys
                    $row = array_combine($colNames, $values);
                    $data[] = $row;
                }
            }
        }

        return $data;
    }

    private function parseValuesFromRow(string $valuesString): array
    {
        $values = [];
        $current = '';
        $inQuote = false;
        $quoteChar = "'";
        $escaped = false;
        $len = strlen($valuesString);

        for ($i = 0; $i < $len; $i++) {
            $char = $valuesString[$i];

            if ($escaped) {
                $current .= $char;
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $escaped = true;
                continue;
            }

            if (($char === "'" || $char === '"') && !$inQuote) {
                $inQuote = true;
                $quoteChar = $char;
                continue;
            }

            if ($char === $quoteChar && $inQuote) {
                // Check if next char is also a quote (escaped quote)
                if ($i + 1 < $len && $valuesString[$i + 1] === $quoteChar) {
                    $current .= $quoteChar;
                    $i++;
                    continue;
                }
                $inQuote = false;
                continue;
            }

            if ($char === ',' && !$inQuote) {
                $values[] = $this->normalizeValue(trim($current));
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $values[] = $this->normalizeValue(trim($current));
        }

        return $values;
    }

    private function normalizeValue(string $value): mixed
    {
        $value = trim($value);

        if ($value === '' || $value === 'NULL' || $value === 'null') {
            return null;
        }

        // Remove surrounding quotes
        if (preg_match('/^\'(.*)\'$/s', $value, $m)) {
            return $m[1];
        }
        if (preg_match('/^"(.*)"$/s', $value, $m)) {
            return $m[1];
        }

        // Numeric values
        if (preg_match('/^-?\d+$/', $value)) {
            return (int) $value;
        }
        if (preg_match('/^-?\d+\.\d+$/', $value)) {
            return (float) $value;
        }

        return $value;
    }

    private function importTables(array $tables): void
    {
        $config = SidRetailConfig::where('name', 'SID Retail')->first();

        foreach ($tables as $tableName => $tableData) {
            $this->importTable($config->id, $tableName, $tableData);
        }
    }

    private function importTable(int $configId, string $tableName, array $tableData): void
    {
        $mapping = $tableData['mapping'];
        $rows = $tableData['rows'];
        $columns = $tableData['columns'];
        $fieldMapping = $this->fieldMappings[$tableName] ?? [];

        $logId = $this->logImport($configId, $tableName, 'running', count($rows), 0);

        try {
            if (empty($rows)) {
                $this->updateLog($logId, 'skipped', 'No data found', 0, 0);
                return;
            }

            $imported = 0;
            $failed = 0;

            DB::transaction(function () use ($tableName, $rows, $mapping, $fieldMapping, &$imported, &$failed) {
                foreach ($rows as $row) {
                    try {
                        $modelData = $this->transformRow($tableName, $row, $fieldMapping);

                        if (empty($modelData)) {
                            $failed++;
                            continue;
                        }

                        switch ($mapping['strategy']) {
                            case 'replace':
                                $this->replaceModel($mapping['model'], $modelData);
                                break;
                            case 'merge':
                                $this->mergeModel($mapping['model'], $modelData);
                                break;
                            case 'append':
                                $mapping['model']::create($modelData);
                                break;
                        }
                        $imported++;
                    } catch (Throwable $e) {
                        $failed++;
                        Log::warning("Failed to import row for table $tableName", [
                            'error' => $e->getMessage(),
                            'row' => $row,
                        ]);
                    }
                }
            });

            $status = $failed > 0 && $imported > 0 ? 'partial' : ($failed > 0 ? 'failed' : 'completed');
            $this->updateLog($logId, $status, $failed > 0 ? "$failed rows failed" : null, count($rows), $imported);

        } catch (Throwable $e) {
            $this->updateLog($logId, 'failed', $e->getMessage(), count($rows), 0);
            throw $e;
        }
    }

    private function transformRow(string $tableName, array $row, array $fieldMapping): array
    {
        $transformed = [];

        foreach ($fieldMapping as $sidField => $uteField) {
            if (isset($row[$sidField]) && $row[$sidField] !== null) {
                $transformed[$uteField] = $row[$sidField];
            }
        }

        $this->applyBusinessLogic($tableName, $transformed, $row);

        return $transformed;
    }

    private function applyBusinessLogic(string $tableName, array &$data, array $originalRow): void
    {
        switch ($tableName) {
            case 'barang':
                // Map category code to category id
                if (!empty($data['category_id'])) {
                    $category = Category::where('code', $data['category_id'])->first();
                    if ($category) {
                        $data['category_id'] = $category->id;
                    } else {
                        unset($data['category_id']);
                    }
                }
                // Set slug from name
                if (!empty($data['name'])) {
                    $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
                }
                break;

            case 'pelanggan':
                // Reset customer-specific fields
                $data['is_member'] = ($originalRow['member'] ?? '') === 'True';
                $data['point_balance'] = $data['point_balance'] ?? 0;
                $data['credit_limit'] = $data['credit_limit'] ?? 0;
                // Generate member code if not exists
                if (empty($data['member_code'])) {
                    $data['member_code'] = 'MEM-' . strtoupper(substr(md5(uniqid()), 0, 6));
                }
                break;

            case 'penjualan':
                // Set sale status
                $data['status'] = 'completed';
                $data['sale_channel'] = 'toko';
                $data['sale_code'] = $data['code'];
                $data['sale_date'] = $data['tanggal'] ?? now();
                break;

            case 'pembelian':
                // Set PO status
                $data['status'] = ($originalRow['receive'] ?? '') === 'True' ? 'received' : 'pending';
                $data['ordered_at'] = $data['tanggal'] ?? now();
                break;

            case 'arus_stok':
            case 'arus_stok_2_2026':
                // Map stock movement
                $data['product_code'] = $originalRow['kode_barang'] ?? null;
                $data['movement_type'] = $this->determineMovementType($originalRow['transaksi'] ?? '');
                $data['quantity_in'] = $originalRow['masuk'] ?? 0;
                $data['quantity_out'] = $originalRow['keluar'] ?? 0;
                $data['reference_no'] = $originalRow['no_transaksi'] ?? null;
                $data['movement_at'] = $originalRow['tanggal'] ??
                    ($originalRow['jam'] ? $originalRow['jam'] : now());
                break;

            case 'kategori':
                if (!empty($data['name'])) {
                    $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
                }
                $data['is_active'] = true;
                break;

            case 'supplier':
                $data['is_active'] = true;
                break;

            case 'user':
                $data['password'] = bcrypt('password123');
                $data['email_verified_at'] = now();
                break;
        }
    }

    private function determineMovementType(string $transaksi): string
    {
        return match (strtoupper($transaksi)) {
            'PENJUALAN' => 'sale',
            'PEMBELIAN' => 'purchase',
            'INPUT BARU' => 'stock_in',
            'DEL BARANG' => 'stock_adjustment',
            'DEL PENJUALAN' => 'sale_return',
            'RETUR' => 'return',
            default => 'adjustment',
        };
    }

    private function replaceModel(string $modelClass, array $data): void
    {
        $codeField = $this->getCodeField($modelClass);

        if (isset($data[$codeField]) && !empty($data[$codeField])) {
            $modelClass::updateOrCreate([$codeField => $data[$codeField]], $data);
        } else {
            // If no code field, just create
            $modelClass::create($data);
        }
    }

    private function mergeModel(string $modelClass, array $data): void
    {
        $codeField = $this->getCodeField($modelClass);

        if (isset($data[$codeField]) && !empty($data[$codeField])) {
            $existing = $modelClass::where($codeField, $data[$codeField])->first();
            if ($existing) {
                $existing->update($data);
            } else {
                $modelClass::create($data);
            }
        } else {
            $modelClass::create($data);
        }
    }

    private function getCodeField(string $modelClass): string
    {
        $modelName = class_basename($modelClass);

        return match ($modelName) {
            'Product' => 'code',
            'Category' => 'code',
            'Supplier' => 'code',
            'Customer' => 'code',
            'User' => 'username',
            default => 'id',
        };
    }

    private function logImport(int $configId, string $tableName, string $status, int $total, int $imported): int
    {
        $log = SidRetailImportLog::create([
            'sid_retail_config_id' => $configId,
            'table_name' => $tableName,
            'status' => $status,
            'records_total' => $total,
            'records_imported' => $imported,
            'records_failed' => max(0, $total - $imported),
            'started_at' => now(),
            'completed_at' => in_array($status, ['completed', 'failed', 'skipped', 'partial']) ? now() : null,
        ]);

        return $log->id;
    }

    private function updateLog(int $logId, string $status, ?string $errorMessage, int $total, int $imported): void
    {
        $log = SidRetailImportLog::find($logId);

        if ($log) {
            $log->update([
                'status' => $status,
                'records_total' => $total,
                'records_imported' => $imported,
                'records_failed' => max(0, $total - $imported),
                'error_message' => $errorMessage,
                'completed_at' => now(),
            ]);
        }
    }

    public function getImportLogs()
    {
        return SidRetailImportLog::with('config')->orderBy('id', 'desc')->get();
    }

    public function getImportLogDetails(int $logId): array
    {
        $log = SidRetailImportLog::findOrFail($logId);
        return [
            'log' => $log,
            'config' => $log->config,
        ];
    }

    public function getTableList(): array
    {
        return array_keys($this->tableMappings);
    }

    public function getFieldMapping(string $tableName): array
    {
        return $this->fieldMappings[$tableName] ?? [];
    }
}
