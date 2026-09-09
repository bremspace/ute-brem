<?php

namespace App\Http\Controllers\SidRetail;

use App\Http\Controllers\Controller;
use App\Services\SidRetailImportService;
use App\Models\SidRetailImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SidRetailImportController extends Controller
{
    private SidRetailImportService $importService;

    public function __construct()
    {
        $this->importService = app(SidRetailImportService::class);
    }

    public function index()
    {
        $logs = SidRetailImportLog::with('config')->latest()->paginate(10);
        return view('sid_retail.import', compact('logs'));
    }

    public function migrateNow(Request $request)
    {
        $request->validate([
            'confirm' => 'required|accepted',
        ]);

        try {
            $this->importService->importSidRetailDatabase();

            return back()->with('success', 'Migrasi database SID Retail selesai berhasil! Lihat log detail di bawah.');
        } catch (Throwable $e) {
            Log::error('SID Retail Import Controller Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Migrasi gagal: ' . $e->getMessage());
        }
    }

    public function status(Request $request)
    {
        $logs = $this->importService->getImportLogs();
        return response()->json([
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'table_name' => $log->table_name,
                    'status' => $log->status,
                    'records_total' => $log->records_total,
                    'records_imported' => $log->records_imported,
                    'records_failed' => $log->records_failed,
                    'error_message' => $log->error_message,
                    'started_at' => $log->started_at?->toDateTimeString(),
                    'completed_at' => $log->completed_at?->toDateTimeString(),
                    'config_name' => $log->config?->name,
                ];
            })->values()->all(),
        ]);
    }

    public function config()
    {
        $config = config('database.connections.sid_retail') ?? null;
        return view('sid_retail.config', compact('config'));
    }

    public function saveConfig(Request $request)
    {
        $validated = $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer|between(1,65535)',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Update .env file
        $this->updateEnvFile([
            'DB_SID_HOST' => $validated['host'],
            'DB_SID_PORT' => $validated['port'],
            'DB_SID_DATABASE' => $validated['database'],
            'DB_SID_USERNAME' => $validated['username'],
            'DB_SID_PASSWORD' => $validated['password'],
        ]);

        return back()->with('success', 'Konfigurasi SID Retail disimpan! Restart server untuk menerapkan.');
    }

    private function updateEnvFile(array $values): void
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        foreach ($values as $key => $value) {
            if (preg_match("/^{$key}=.*$/m", $envContent)) {
                $envContent = preg_replace(
                    "/^{$key}=.*$/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                $envContent .= "\n{$key}={$value}\n";
            }
        }

        file_put_contents($envFile, $envContent);
    }
}
