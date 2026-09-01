<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupRestoreTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Admin',
            'description' => 'Administrator'
        ]);
        $permission = Permission::create([
            'name' => 'management.settings.printer',
            'display_name' => 'Settings',
            'description' => 'Manage Settings',
            'module' => 'management'
        ]);
        $role->assignPermission($permission);
        $this->user->assignRole($role);
    }

    protected function tearDown(): void
    {
        if (Storage::exists('backups')) {
            Storage::deleteDirectory('backups');
        }
        parent::tearDown();
    }

    public function test_guest_cannot_access_backup_endpoints(): void
    {
        $this->post(route('settings.backup.create'))->assertRedirect(route('login'));
        $this->post(route('settings.backup.restore', ['filename' => 'test.sql']))->assertRedirect(route('login'));
        $this->get(route('settings.backup.download', ['filename' => 'test.sql']))->assertRedirect(route('login'));
        $this->delete(route('settings.backup.delete', ['filename' => 'test.sql']))->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_cannot_access_backup_endpoints(): void
    {
        $nonAdmin = User::factory()->create();

        $this->actingAs($nonAdmin)->post(route('settings.backup.create'))->assertStatus(302)->assertSessionHas('error');
        $this->actingAs($nonAdmin)->post(route('settings.backup.restore', ['filename' => 'test.sql']))->assertStatus(302)->assertSessionHas('error');
        $this->actingAs($nonAdmin)->get(route('settings.backup.download', ['filename' => 'test.sql']))->assertStatus(302)->assertSessionHas('error');
        $this->actingAs($nonAdmin)->delete(route('settings.backup.delete', ['filename' => 'test.sql']))->assertStatus(302)->assertSessionHas('error');
    }

    public function test_authorized_user_can_create_backup(): void
    {
        $response = $this->actingAs($this->user)->post(route('settings.backup.create'));

        $response->assertRedirect(route('settings.index', ['tab' => 'backup']));
        $response->assertSessionHas('success');

        $files = Storage::files('backups');
        $this->assertCount(1, $files);
        
        $filename = basename($files[0]);
        $this->assertStringContainsString('backup_', $filename);
    }

    public function test_authorized_user_can_download_backup(): void
    {
        // 1. Create a backup first
        $this->actingAs($this->user)->post(route('settings.backup.create'));
        $files = Storage::files('backups');
        $filename = basename($files[0]);

        // 2. Download it
        $response = $this->actingAs($this->user)->get(route('settings.backup.download', ['filename' => $filename]));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=' . $filename);
    }

    public function test_authorized_user_can_restore_backup(): void
    {
        // 1. Create a backup first
        $this->actingAs($this->user)->post(route('settings.backup.create'));
        $files = Storage::files('backups');
        $filename = basename($files[0]);

        // 2. Restore it
        $response = $this->actingAs($this->user)->post(route('settings.backup.restore', ['filename' => $filename]));

        $response->assertRedirect(route('settings.index', ['tab' => 'backup']));
        $response->assertSessionHas('success');
    }

    public function test_authorized_user_can_delete_backup(): void
    {
        // 1. Create a backup first
        $this->actingAs($this->user)->post(route('settings.backup.create'));
        $files = Storage::files('backups');
        $filename = basename($files[0]);
        $this->assertTrue(Storage::exists('backups/' . $filename));

        // 2. Delete it
        $response = $this->actingAs($this->user)->delete(route('settings.backup.delete', ['filename' => $filename]));

        $response->assertRedirect(route('settings.index', ['tab' => 'backup']));
        $response->assertSessionHas('success');

        // Assert file no longer exists
        $this->assertFalse(Storage::exists('backups/' . $filename));
    }
}
