<?php

namespace Tests\Feature\Products;

use App\Livewire\Products\AddonGroupManager;
use App\Livewire\Products\ProductForm;
use App\Models\AddonGroup;
use App\Models\AddonOption;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VariantAndAddonTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    // ── Variants ──────────────────────────────────────────────────────────────

    public function test_can_add_variants_when_creating_product(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'Burger')
            ->set('price', '8.99')
            ->call('addVariant')
            ->set('variants.0.name', 'Small')
            ->set('variants.0.price', '7.99')
            ->call('addVariant')
            ->set('variants.1.name', 'Large')
            ->set('variants.1.price', '10.99')
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::where('name', 'Burger')->first();
        $this->assertCount(2, $product->variants);
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'name' => 'Small']);
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'name' => 'Large']);
    }

    public function test_variant_name_is_required(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'Burger')
            ->set('price', '8.99')
            ->call('addVariant')
            ->set('variants.0.name', '')
            ->set('variants.0.price', '5.00')
            ->call('save')
            ->assertHasErrors(['variants.0.name']);
    }

    public function test_variant_price_must_be_numeric(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'Burger')
            ->set('price', '8.99')
            ->call('addVariant')
            ->set('variants.0.name', 'Small')
            ->set('variants.0.price', 'bad')
            ->call('save')
            ->assertHasErrors(['variants.0.price']);
    }

    public function test_can_remove_variant(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->call('addVariant')
            ->call('addVariant')
            ->assertCount('variants', 2)
            ->call('removeVariant', 0)
            ->assertCount('variants', 1);
    }

    public function test_editing_product_loads_existing_variants(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name'       => 'Medium',
            'price'      => 9.99,
            'is_default' => true,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class, ['productId' => $product->id])
            ->assertSet('variants.0.name', 'Medium');
    }

    public function test_removed_variants_are_deleted_on_save(): void
    {
        $product = Product::factory()->create();
        $varA    = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Small', 'price' => 5]);
        $varB    = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Large', 'price' => 9]);

        // Load product, remove first variant, save
        $component = Livewire::actingAs($this->admin())
            ->test(ProductForm::class, ['productId' => $product->id]);

        // Remove first variant
        $component->call('removeVariant', 0)->call('save')->assertHasNoErrors();

        $this->assertDatabaseMissing('product_variants', ['id' => $varA->id]);
        $this->assertDatabaseHas('product_variants', ['id' => $varB->id]);
    }

    // ── Add-on groups ─────────────────────────────────────────────────────────

    public function test_can_create_addon_group(): void
    {
        Livewire::actingAs($this->admin())
            ->test(AddonGroupManager::class)
            ->call('openCreate')
            ->set('name', 'Extras')
            ->set('minSelectable', 0)
            ->set('maxSelectable', 2)
            ->call('addOption')
            ->set('options.0.name', 'Extra Cheese')
            ->set('options.0.extra_price', '1.00')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('addon_groups', ['name' => 'Extras']);
        $group = AddonGroup::where('name', 'Extras')->first();
        $this->assertDatabaseHas('addon_options', ['addon_group_id' => $group->id, 'name' => 'Extra Cheese']);
    }

    public function test_addon_group_name_is_required(): void
    {
        Livewire::actingAs($this->admin())
            ->test(AddonGroupManager::class)
            ->call('openCreate')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_addon_option_name_is_required(): void
    {
        Livewire::actingAs($this->admin())
            ->test(AddonGroupManager::class)
            ->call('openCreate')
            ->set('name', 'Test Group')
            ->call('addOption')
            ->set('options.0.name', '')
            ->set('options.0.extra_price', '0.00')
            ->call('save')
            ->assertHasErrors(['options.0.name']);
    }

    public function test_can_edit_addon_group(): void
    {
        $group = AddonGroup::factory()->create(['name' => 'Old Name']);

        Livewire::actingAs($this->admin())
            ->test(AddonGroupManager::class)
            ->call('openEdit', $group->id)
            ->assertSet('name', 'Old Name')
            ->set('name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('addon_groups', ['id' => $group->id, 'name' => 'New Name']);
    }

    public function test_can_delete_addon_group(): void
    {
        $group = AddonGroup::factory()->create();
        AddonOption::factory()->create(['addon_group_id' => $group->id]);

        Livewire::actingAs($this->admin())
            ->test(AddonGroupManager::class)
            ->call('delete', $group->id);

        $this->assertSoftDeleted('addon_groups', ['id' => $group->id]);
        $this->assertDatabaseMissing('addon_options', ['addon_group_id' => $group->id]);
    }

    public function test_product_addon_group_assignment_persists(): void
    {
        $product = Product::factory()->create();
        $groupA  = AddonGroup::factory()->create();
        $groupB  = AddonGroup::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class, ['productId' => $product->id])
            ->set('selectedAddonGroupIds', [$groupA->id, $groupB->id])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_addon_group', ['product_id' => $product->id, 'addon_group_id' => $groupA->id]);
        $this->assertDatabaseHas('product_addon_group', ['product_id' => $product->id, 'addon_group_id' => $groupB->id]);
    }
}
