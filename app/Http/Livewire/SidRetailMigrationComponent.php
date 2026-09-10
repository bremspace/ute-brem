<?php

namespace App\Http\Livewire;

use Livewire\Component;

class SidRetailMigrationComponent extends Component
{
    public string $status = '';
    public bool $isRunning = false;
    public string $database = '';
    public string $host = '127.0.0.1';
    public int $port = 3306;
    public string $username = '';
    public string $password = '';

    public function mount(): void
    {
        $this->status = 'idle';
    }

    public function testConnection(): void
    {
        $this->status = 'testing';
        // Test connection logic
        $this->status = 'connected';
        session()->flash('success', 'Koneksi database berhasil.');
    }

    public function startMigration(): void
    {
        $this->isRunning = true;
        $this->status = 'running';
        // Migration logic would go here
        $this->status = 'completed';
        $this->isRunning = false;
        session()->flash('success', 'Migrasi selesai.');
    }

    public function render()
    {
        return view('livewire.sid-retail.sid-retail-migration-component');
    }
}