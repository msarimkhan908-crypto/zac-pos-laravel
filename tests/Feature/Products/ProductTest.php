<?php

namespace Tests\Feature\Products;

use App\Livewire\Products\ProductForm;
use App\Livewire\Products\ProductList;
use App\Models\AddonGroup;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_product_list_renders_for_admin(): void
    {
        $this->actingAs($this->admin())
            ->get(route('products.index'))
            ->assertOk()
            ->assertSeeLivewire(ProductList::class);
    }

    public function test_admin_can_create_product(): void
    {
        $cat = Category::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'Classic Burger')
            ->set('categoryId', $cat->id)
            ->set('price', '8.99')
            ->set('sku', 'BRG-TEST')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name'        => 'Classic Burger',
            'sku'         => 'BRG-TEST',
            'category_id' => $cat->id,
        ]);
    }

    public function test_product_name_is_required(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', '')
            ->set('price', '8.99')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_price_must_be_numeric(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'Burger')
            ->set('price', 'not-a-number')
            ->call('save')
            ->assertHasErrors(['price']);
    }

    public function test_price_must_not_be_negative(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'Burger')
            ->set('price', '-1')
            ->call('save')
            ->assertHasErrors(['price']);
    }

    public function test_sku_must_be_unique(): void
    {
        Product::factory()->create(['sku' => 'SKU-DUP']);

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'Another Product')
            ->set('price', '5.00')
            ->set('sku', 'SKU-DUP')
            ->call('save')
            ->assertHasErrors(['sku']);
    }

    public function test_sku_unique_ignores_own_record_on_edit(): void
    {
        $product = Product::factory()->create(['sku' => 'SKU-EDIT']);

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class, ['productId' => $product->id])
            ->set('sku', 'SKU-EDIT')
            ->call('save')
            ->assertHasNoErrors(['sku']);
    }

    public function test_deleted_product_sku_can_be_reused(): void
    {
        $deleted = Product::factory()->create(['sku' => 'SKU-DEL']);
        $deleted->delete();

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'New Product')
            ->set('price', '5.00')
            ->set('sku', 'SKU-DEL')
            ->call('save')
            ->assertHasNoErrors(['sku']);
    }

    public function test_admin_can_edit_product(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name']);

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class, ['productId' => $product->id])
            ->assertSet('name', 'Old Name')
            ->set('name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'New Name']);
    }

    public function test_toggle_active_flips_flag(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->call('toggleActive', $product->id);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false]);
    }

    public function test_toggle_available_flips_flag(): void
    {
        $product = Product::factory()->create(['is_available' => true]);

        Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->call('toggleAvailable', $product->id);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_available' => false]);
    }

    public function test_duplicate_creates_copy_with_different_sku(): void
    {
        $product = Product::factory()->create(['name' => 'Original', 'sku' => 'SKU-ORIG']);

        Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->call('duplicate', $product->id);

        $this->assertDatabaseHas('products', ['name' => 'Copy of Original']);
        $copy = Product::where('name', 'Copy of Original')->first();
        $this->assertNotEquals('SKU-ORIG', $copy->sku);
    }

    public function test_delete_soft_deletes_product(): void
    {
        $product = Product::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->call('delete', $product->id);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_search_filters_by_name(): void
    {
        Product::factory()->create(['name' => 'Classic Burger']);
        Product::factory()->create(['name' => 'Chicken Wings']);

        $component = Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->set('search', 'Classic');

        $component->assertSee('Classic Burger');
        $component->assertDontSee('Chicken Wings');
    }

    public function test_search_filters_by_sku(): void
    {
        Product::factory()->create(['name' => 'Burger', 'sku' => 'SKU-ABC']);
        Product::factory()->create(['name' => 'Fries',  'sku' => 'SKU-XYZ']);

        Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->set('search', 'SKU-ABC')
            ->assertSee('Burger')
            ->assertDontSee('Fries');
    }

    public function test_category_filter_works(): void
    {
        $catA = Category::factory()->create(['name' => 'Cat A']);
        $catB = Category::factory()->create(['name' => 'Cat B']);
        Product::factory()->create(['name' => 'Product A', 'category_id' => $catA->id]);
        Product::factory()->create(['name' => 'Product B', 'category_id' => $catB->id]);

        Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->set('categoryFilter', $catA->id)
            ->assertSee('Product A')
            ->assertDontSee('Product B');
    }

    public function test_status_filter_shows_only_inactive(): void
    {
        Product::factory()->create(['name' => 'Active P',   'is_active' => true]);
        Product::factory()->create(['name' => 'Inactive P', 'is_active' => false]);

        Livewire::actingAs($this->admin())
            ->test(ProductList::class)
            ->set('statusFilter', 'inactive')
            ->assertSee('Inactive P')
            ->assertDontSee('Active P');
    }

    public function test_addon_groups_are_synced_on_save(): void
    {
        $group = AddonGroup::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'Test Burger')
            ->set('price', '9.00')
            ->set('selectedAddonGroupIds', [$group->id])
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::where('name', 'Test Burger')->first();
        $this->assertTrue($product->addonGroups->contains($group->id));
    }
}
