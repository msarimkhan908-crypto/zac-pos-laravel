<?php

namespace Tests\Feature\Products;

use App\Livewire\Products\ProductList;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BulkActionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_bulk_activate_sets_all_selected_active(): void
    {
        $p1 = Product::factory()->create(['is_active' => false]);
        $p2 = Product::factory()->create(['is_active' => false]);
        $p3 = Product::factory()->create(['is_active' => false]);

        Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->set('selectedIds', [$p1->id, $p2->id])
            ->call('bulkActivate');

        $this->assertDatabaseHas('products', ['id' => $p1->id, 'is_active' => true]);
        $this->assertDatabaseHas('products', ['id' => $p2->id, 'is_active' => true]);
        $this->assertDatabaseHas('products', ['id' => $p3->id, 'is_active' => false]);
    }

    public function test_bulk_deactivate_sets_all_selected_inactive(): void
    {
        $p1 = Product::factory()->create(['is_active' => true]);
        $p2 = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->set('selectedIds', [$p1->id, $p2->id])
            ->call('bulkDeactivate');

        $this->assertDatabaseHas('products', ['id' => $p1->id, 'is_active' => false]);
        $this->assertDatabaseHas('products', ['id' => $p2->id, 'is_active' => false]);
    }

    public function test_bulk_mark_sold_out_sets_unavailable(): void
    {
        $p1 = Product::factory()->create(['is_available' => true]);
        $p2 = Product::factory()->create(['is_available' => true]);

        Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->set('selectedIds', [$p1->id, $p2->id])
            ->call('bulkMarkSoldOut');

        $this->assertDatabaseHas('products', ['id' => $p1->id, 'is_available' => false]);
        $this->assertDatabaseHas('products', ['id' => $p2->id, 'is_available' => false]);
    }

    public function test_bulk_delete_soft_deletes_selected(): void
    {
        $p1 = Product::factory()->create();
        $p2 = Product::factory()->create();
        $p3 = Product::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->set('selectedIds', [$p1->id, $p2->id])
            ->call('bulkDelete');

        $this->assertSoftDeleted('products', ['id' => $p1->id]);
        $this->assertSoftDeleted('products', ['id' => $p2->id]);
        $this->assertDatabaseHas('products', ['id' => $p3->id, 'deleted_at' => null]);
    }

    public function test_bulk_action_clears_selection_after_operation(): void
    {
        $p1 = Product::factory()->create(['is_active' => false]);

        $component = Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->set('selectedIds', [$p1->id])
            ->call('bulkActivate');

        $component->assertSet('selectedIds', []);
    }

    public function test_select_all_selects_visible_products(): void
    {
        Product::factory()->count(3)->create();

        $component = Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->set('selectAll', true);

        $ids = $component->get('selectedIds');
        $this->assertCount(3, $ids);
    }

    public function test_cashier_bulk_actions_are_blocked_by_policy(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $product = Product::factory()->create(['is_active' => true]);

        // Cashier cannot even mount the component (Gate::authorize throws)
        $this->actingAs($cashier)
            ->get(route('products.index'))
            ->assertForbidden();

        // DB unchanged
        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => true]);
    }
}
