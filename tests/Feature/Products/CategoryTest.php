<?php

namespace Tests\Feature\Products;

use App\Livewire\Products\CategoryList;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_category_list_renders_for_admin(): void
    {
        $this->actingAs($this->admin())
            ->get(route('products.index', ['tab' => 'categories']))
            ->assertOk()
            ->assertSeeLivewire(CategoryList::class);
    }

    public function test_admin_can_create_category(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CategoryList::class)
            ->call('openCreate')
            ->set('name', 'Burgers')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Burgers', 'slug' => 'burgers']);
    }

    public function test_category_slug_is_auto_generated(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CategoryList::class)
            ->call('openCreate')
            ->set('name', 'Fries and Sides')
            ->call('save');

        $this->assertDatabaseHas('categories', ['slug' => 'fries-and-sides']);
    }

    public function test_duplicate_slug_gets_suffix(): void
    {
        Category::factory()->create(['name' => 'Burgers', 'slug' => 'burgers']);

        Livewire::actingAs($this->admin())
            ->test(CategoryList::class)
            ->call('openCreate')
            ->set('name', 'Burgers')
            ->call('save');

        $this->assertDatabaseHas('categories', ['slug' => 'burgers-1']);
    }

    public function test_category_name_is_required(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CategoryList::class)
            ->call('openCreate')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_admin_can_edit_category(): void
    {
        $cat = Category::factory()->create(['name' => 'Drinks', 'slug' => 'drinks']);

        Livewire::actingAs($this->admin())
            ->test(CategoryList::class)
            ->call('openEdit', $cat->id)
            ->assertSet('name', 'Drinks')
            ->set('name', 'Cold Drinks')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['id' => $cat->id, 'name' => 'Cold Drinks']);
    }

    public function test_admin_can_delete_category_without_products(): void
    {
        $cat = Category::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(CategoryList::class)
            ->call('confirmDelete', $cat->id)
            ->assertSet('deleteError', '')
            ->call('delete');

        $this->assertSoftDeleted('categories', ['id' => $cat->id]);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $cat     = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $cat->id]);

        Livewire::actingAs($this->admin())
            ->test(CategoryList::class)
            ->call('confirmDelete', $cat->id)
            ->call('delete')
            ->assertSet('deleteError', 'This category has products. Move or delete them first.');

        $this->assertDatabaseHas('categories', ['id' => $cat->id, 'deleted_at' => null]);
    }

    public function test_admin_can_toggle_category_active_state(): void
    {
        $cat = Category::factory()->create(['is_active' => true]);

        Livewire::actingAs($this->admin())
            ->test(CategoryList::class)
            ->call('toggleActive', $cat->id);

        $this->assertDatabaseHas('categories', ['id' => $cat->id, 'is_active' => false]);
    }

    public function test_move_up_decrements_sort_order(): void
    {
        $a = Category::factory()->create(['sort_order' => 1]);
        $b = Category::factory()->create(['sort_order' => 2]);

        Livewire::actingAs($this->admin())
            ->test(CategoryList::class)
            ->call('moveUp', $b->id);

        $this->assertDatabaseHas('categories', ['id' => $b->id, 'sort_order' => 1]);
        $this->assertDatabaseHas('categories', ['id' => $a->id, 'sort_order' => 2]);
    }

    public function test_move_down_increments_sort_order(): void
    {
        $a = Category::factory()->create(['sort_order' => 1]);
        $b = Category::factory()->create(['sort_order' => 2]);

        Livewire::actingAs($this->admin())
            ->test(CategoryList::class)
            ->call('moveDown', $a->id);

        $this->assertDatabaseHas('categories', ['id' => $a->id, 'sort_order' => 2]);
        $this->assertDatabaseHas('categories', ['id' => $b->id, 'sort_order' => 1]);
    }

    public function test_invalid_color_is_rejected(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CategoryList::class)
            ->call('openCreate')
            ->set('name', 'Test')
            ->set('color', 'not-a-color')
            ->call('save')
            ->assertHasErrors(['color']);
    }
}
