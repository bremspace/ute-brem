<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Branch;
use App\Models\Category;
use App\Models\LocationRack;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchTransferTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Location $sourceLocation;
    protected Location $targetLocation;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Admin',
            'description' => 'Administrator'
        ]);
        
        $viewPermission = Permission::create([
            'name' => 'master.product_stocks.view',
            'display_name' => 'View Stock',
            'description' => 'Can view product stocks',
            'module' => 'master'
        ]);
        $editPermission = Permission::create([
            'name' => 'master.product_stocks.edit',
            'display_name' => 'Edit Stock',
            'description' => 'Can edit product stocks',
            'module' => 'master'
        ]);
        
        $role->assignPermission($viewPermission);
        $role->assignPermission($editPermission);
        $this->user->assignRole($role);

        // Create branches
        $this->sourceBranch = Branch::create(['name' => 'Cabang Pusat', 'code' => 'PST-TEST', 'is_main' => true, 'is_active' => true]);
        $this->targetBranch = Branch::create(['name' => 'Cabang Surabaya', 'code' => 'SBY-TEST', 'is_main' => false, 'is_active' => true]);

        // Create locations linked to branches
        $this->sourceLocation = Location::create(['name' => 'Gudang Utama', 'code' => 'GDG', 'branch_id' => $this->sourceBranch->id, 'is_active' => true]);
        $this->targetLocation = Location::create(['name' => 'Toko Cabang', 'code' => 'TKO', 'branch_id' => $this->targetBranch->id, 'is_active' => true]);

        $category = Category::create([
            'name' => 'LCD Screen',
            'slug' => 'lcd-screen',
        ]);

        // Create product with initial stock
        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'LCD Screen iPhone 12',
            'product_code' => 'LCD-IPH12',
            'slug' => 'lcd-screen-iphone-12',
            'sku' => 'LCD-IPH12-SKU',
            'purchase_price' => 500000,
            'selling_price' => 750000,
        ]);

        ProductStock::create([
            'product_id' => $this->product->id,
            'location_id' => $this->sourceLocation->id,
            'quantity' => 100,
            'damaged_quantity' => 0,
        ]);
        
        $this->product->update(['stock_global' => 100]);
    }

    public function test_guest_cannot_access_branch_transfers(): void
    {
        $this->get(route('branch-transfers.index'))->assertRedirect(route('login'));
    }

    public function test_can_create_draft_branch_transfer(): void
    {
        $response = $this->actingAs($this->user)->post(route('branch-transfers.store'), [
            'source_branch_id' => $this->sourceBranch->id,
            'target_branch_id' => $this->targetBranch->id,
            'source_location_id' => $this->sourceLocation->id,
            'target_location_id' => $this->targetLocation->id,
            'notes' => 'Test transfer',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity_sent' => 20,
                    'notes' => '20 units'
                ]
            ]
        ]);

        $response->assertRedirect(route('branch-transfers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('branch_transfers', [
            'source_branch_id' => $this->sourceBranch->id,
            'target_branch_id' => $this->targetBranch->id,
            'source_location_id' => $this->sourceLocation->id,
            'target_location_id' => $this->targetLocation->id,
            'status' => 'draft',
        ]);
    }

    public function test_can_ship_branch_transfer_and_deduct_source_stock(): void
    {
        // 1. Create transfer
        $this->actingAs($this->user)->post(route('branch-transfers.store'), [
            'source_branch_id' => $this->sourceBranch->id,
            'target_branch_id' => $this->targetBranch->id,
            'source_location_id' => $this->sourceLocation->id,
            'target_location_id' => $this->targetLocation->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity_sent' => 20,
                ]
            ]
        ]);

        $transfer = \App\Models\BranchTransfer::first();

        // 2. Ship it
        $response = $this->actingAs($this->user)->post(route('branch-transfers.ship', $transfer));
        $response->assertSessionHas('success');

        $transfer->refresh();
        $this->assertEquals('in_transit', $transfer->status);

        // 3. Source stock should be reduced
        $sourceStock = ProductStock::where('product_id', $this->product->id)
            ->where('location_id', $this->sourceLocation->id)
            ->first();
        
        $this->assertEquals(80, (float)$sourceStock->quantity);
    }

    public function test_can_receive_branch_transfer_with_discrepancy(): void
    {
        // 1. Create and Ship
        $this->actingAs($this->user)->post(route('branch-transfers.store'), [
            'source_branch_id' => $this->sourceBranch->id,
            'target_branch_id' => $this->targetBranch->id,
            'source_location_id' => $this->sourceLocation->id,
            'target_location_id' => $this->targetLocation->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity_sent' => 20,
                ]
            ]
        ]);

        $transfer = \App\Models\BranchTransfer::first();
        $this->actingAs($this->user)->post(route('branch-transfers.ship', $transfer));

        // 2. Receive 18 units (2 units lost/damaged)
        $item = $transfer->items()->first();
        $response = $this->actingAs($this->user)->post(route('branch-transfers.receive', $transfer), [
            'items' => [
                $item->id => [
                    'quantity_received' => 18,
                ]
            ]
        ]);

        $response->assertRedirect(route('branch-transfers.show', $transfer));
        $response->assertSessionHas('success');

        $transfer->refresh();
        $this->assertEquals('completed', $transfer->status);

        // 3. Target stock should be updated
        $targetStock = ProductStock::where('product_id', $this->product->id)
            ->where('location_id', $this->targetLocation->id)
            ->first();
        
        $this->assertEquals(18, (float)$targetStock->quantity);

        // 4. Global stock should be 80 (source) + 18 (target) = 98 (2 lost)
        $this->product->refresh();
        $this->assertEquals(98, (float)$this->product->stock_global);
    }

    public function test_can_cancel_in_transit_transfer_and_revert_stock(): void
    {
        // 1. Create and Ship
        $this->actingAs($this->user)->post(route('branch-transfers.store'), [
            'source_branch_id' => $this->sourceBranch->id,
            'target_branch_id' => $this->targetBranch->id,
            'source_location_id' => $this->sourceLocation->id,
            'target_location_id' => $this->targetLocation->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity_sent' => 20,
                ]
            ]
        ]);

        $transfer = \App\Models\BranchTransfer::first();
        $this->actingAs($this->user)->post(route('branch-transfers.ship', $transfer));

        // Source stock should be 80
        $this->assertEquals(80, (float)ProductStock::where('product_id', $this->product->id)->where('location_id', $this->sourceLocation->id)->first()->quantity);

        // 2. Cancel it
        $response = $this->actingAs($this->user)->post(route('branch-transfers.cancel', $transfer));
        $response->assertSessionHas('success');

        $transfer->refresh();
        $this->assertEquals('cancelled', $transfer->status);

        // 3. Source stock should revert to 100
        $this->assertEquals(100, (float)ProductStock::where('product_id', $this->product->id)->where('location_id', $this->sourceLocation->id)->first()->quantity);
    }
}
