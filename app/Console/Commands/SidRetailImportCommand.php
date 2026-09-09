<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SidRetailImportService;
use Illuminate\Support\Facades\Log;

class SidRetailImportCommand extends Command
{
    protected $signature = 'sid-retail:import {--force : Force the import without confirmation}';

    protected $description = 'Import database from SID Retail (latest.sql)';

    public function handle(SidRetailImportService $service): int
    {
        $force = $this->option('force');

        if (! $force) {
            $this->info('Migrasi Database SID Retail');
            $this->warn('Perhatian: Ini akan menggantikan semua data di database UTE POS yang ada!');
            $this->warn('Semua data yang sudah ada akan dihapus dan diganti dengan data SID Retail.');

            $response = $this->confirm('Apakah Anda yakin ingin melanjutkan? Ini tidak dapat dibatalkan.', default: false);

            if (! $response) {
                $this->info('Migrasi dibatalkan oleh pengguna.');
                return self::SUCCESS;
            }
        }

        try {
            $this->info('Memulai import database SID Retail dari latest.sql...');
            $service->importSidRetailDatabase();

            $this->info('Import database SID Retail selesai!');
            $this->newLine();
            $this->info('Lihat log import di: ' . route('sid-retail.index', absolute: false));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Import database SID Retail gagal: ' . $e->getMessage());
            Log::error('SID Retail Import Command Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}