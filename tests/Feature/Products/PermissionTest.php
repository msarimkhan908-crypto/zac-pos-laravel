<?php

namespace Tests\Feature\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_products_page(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
    }

    public function test_cashier_cannot_access_products_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'cashier']))
            ->get(route('products.index'))
            ->assertForbidden();
    }

    public function test_kitchen_cannot_access_products_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kitchen']))
            ->get(route('products.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_products_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('products.index'))
            ->assertOk();
    }

    public function test_cashier_cannot_access_product_create_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'cashier']))
            ->get(route('products.create'))
            ->assertForbidden();
    }

    public function test_admin_can_access_product_create_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('products.create'))
            ->assertOk();
    }

    public function test_cashier_cannot_access_product_edit_page(): void
    {
        $product = Product::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'cashier']))
            ->get(route('products.edit', $product->id))
            ->assertForbidden();
    }

    public function test_kitchen_cannot_access_product_edit_page(): void
    {
        $product = Product::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'kitchen']))
            ->get(route('products.edit', $product->id))
            ->assertForbidden();
    }
}
