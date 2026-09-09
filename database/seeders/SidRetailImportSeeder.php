<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\SidRetailImportService;
use Illuminate\Support\Facades\Log;

class SidRetailImportSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting SID Retail import from latest.sql...');
        
        try {
            $service = app(SidRetailImportService::class);
            $service->importSidRetailDatabase();
            
            $this->command->info('SID Retail import completed successfully!');
            $this->command->info('Check the import logs at: ' . route('sid-retail.index'));
        } catch (\Throwable $e) {
            $this->command->error('SID Retail import failed: ' . $e->getMessage());
            Log::error('SID Retail Import Seeder Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
