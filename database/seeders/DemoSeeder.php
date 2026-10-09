<?php

namespace Database\Seeders;

use App\Models\AddonGroup;
use App\Models\AddonOption;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Add-on groups ──────────────────────────────────────────────────

        $extras = AddonGroup::create([
            'name'           => 'Extras',
            'min_selectable' => 0,
            'max_selectable' => 3,
            'is_required'    => false,
        ]);
        foreach ([
            ['Extra Cheese',   1.00],
            ['Extra Sauce',    0.50],
            ['Double Patty',   2.00],
            ['Fried Egg',      0.75],
        ] as [$name, $price]) {
            AddonOption::create(['addon_group_id' => $extras->id, 'name' => $name, 'extra_price' => $price, 'sort_order' => 0]);
        }

        $removeItems = AddonGroup::create([
            'name'           => 'Remove Items',
            'min_selectable' => 0,
            'max_selectable' => 5,
            'is_required'    => false,
        ]);
        foreach (['No Onions', 'No Lettuce', 'No Tomato', 'No Pickles', 'No Mayo'] as $i => $name) {
            AddonOption::create(['addon_group_id' => $removeItems->id, 'name' => $name, 'extra_price' => 0, 'sort_order' => $i]);
        }

        $dressings = AddonGroup::create([
            'name'           => 'Dressing',
            'min_selectable' => 0,
            'max_selectable' => 2,
            'is_required'    => false,
        ]);
        foreach ([
            ['Ketchup',  0.00],
            ['Mustard',  0.00],
            ['Mayo',     0.50],
            ['BBQ',      0.50],
            ['Sriracha', 0.50],
        ] as $i => [$name, $price]) {
            AddonOption::create(['addon_group_id' => $dressings->id, 'name' => $name, 'extra_price' => $price, 'sort_order' => $i]);
        }

        $drinkAddons = AddonGroup::create([
            'name'           => 'Ice & Sugar',
            'min_selectable' => 0,
            'max_selectable' => 2,
            'is_required'    => false,
        ]);
        foreach (['No Ice', 'Less Sugar', 'Extra Sugar'] as $i => $name) {
            AddonOption::create(['addon_group_id' => $drinkAddons->id, 'name' => $name, 'extra_price' => 0, 'sort_order' => $i]);
        }

        // ── Categories ─────────────────────────────────────────────────────

        $burgers  = Category::create(['name' => 'Burgers',          'slug' => 'burgers',          'sort_order' => 1, 'is_active' => true, 'color' => '#f97316']);
        $fries    = Category::create(['name' => 'Fries & Sides',    'slug' => 'fries-and-sides',   'sort_order' => 2, 'is_active' => true, 'color' => '#eab308']);
        $drinks   = Category::create(['name' => 'Drinks',           'slug' => 'drinks',            'sort_order' => 3, 'is_active' => true, 'color' => '#3b82f6']);
        $desserts = Category::create(['name' => 'Desserts',         'slug' => 'desserts',          'sort_order' => 4, 'is_active' => true, 'color' => '#ec4899']);

        // ── Products ───────────────────────────────────────────────────────

        // --- Burgers (5 products) ---
        $classic = Product::create([
            'category_id' => $burgers->id,
            'name'        => 'Classic Burger',
            'sku'         => 'BRG-001',
            'price'       => 8.99,
            'cost_price'  => 3.20,
            'description' => 'Beef patty, lettuce, tomato, onion, pickles and house sauce.',
            'is_active'   => true, 'is_available' => true, 'prints_on_kot' => true, 'sort_order' => 1,
        ]);
        $this->addVariants($classic, [
            ['Single', 8.99, true],
            ['Double', 11.99, false],
            ['Triple', 14.99, false],
        ]);
        $classic->addonGroups()->attach([$extras->id, $removeItems->id, $dressings->id]);

        $chicken = Product::create([
            'category_id' => $burgers->id,
            'name'        => 'Crispy Chicken Burger',
            'sku'         => 'BRG-002',
            'price'       => 9.49,
            'description' => 'Crispy fried chicken thigh, coleslaw and garlic mayo.',
            'is_active'   => true, 'is_available' => true, 'prints_on_kot' => true, 'sort_order' => 2,
        ]);
        $this->addVariants($chicken, [
            ['Regular', 9.49, true],
            ['Spicy',   9.49, false],
        ]);
        $chicken->addonGroups()->attach([$extras->id, $removeItems->id]);

        $veggie = Product::create([
            'category_id' => $burgers->id,
            'name'        => 'Veggie Burger',
            'sku'         => 'BRG-003',
            'price'       => 8.49,
            'description' => 'Black bean patty with avocado and chipotle mayo.',
            'is_active'   => true, 'is_available' => true, 'prints_on_kot' => true, 'sort_order' => 3,
        ]);
        $veggie->addonGroups()->attach([$removeItems->id, $dressings->id]);

        $mushroom = Product::create([
            'category_id' => $burgers->id,
            'name'        => 'Mushroom Swiss Burger',
            'sku'         => 'BRG-004',
            'price'       => 10.49,
            'description' => 'Beef patty topped with sautéed mushrooms and Swiss cheese.',
            'is_active'   => true, 'is_available' => true, 'prints_on_kot' => true, 'sort_order' => 4,
        ]);
        $mushroom->addonGroups()->attach([$extras->id, $removeItems->id]);

        // --- Fries & Sides (2 products) ---
        $friesProduct = Product::create([
            'category_id' => $fries->id,
            'name'        => 'French Fries',
            'sku'         => 'SIDE-001',
            'price'       => 3.49,
            'is_active'   => true, 'is_available' => true, 'prints_on_kot' => true, 'sort_order' => 1,
        ]);
        $this->addVariants($friesProduct, [
            ['Small',  3.49, true],
            ['Medium', 4.49, false],
            ['Large',  5.49, false],
        ]);

        Product::create([
            'category_id' => $fries->id,
            'name'        => 'Onion Rings',
            'sku'         => 'SIDE-002',
            'price'       => 4.49,
            'is_active'   => true, 'is_available' => true, 'prints_on_kot' => true, 'sort_order' => 2,
        ]);

        // --- Drinks (3 products) ---
        $cola = Product::create([
            'category_id' => $drinks->id,
            'name'        => 'Coca-Cola',
            'sku'         => 'DRK-001',
            'price'       => 2.49,
            'is_active'   => true, 'is_available' => true, 'prints_on_kot' => false, 'sort_order' => 1,
        ]);
        $this->addVariants($cola, [
            ['Can 330ml',    2.49, true],
            ['Bottle 500ml', 3.49, false],
        ]);
        $cola->addonGroups()->attach([$drinkAddons->id]);

        $water = Product::create([
            'category_id' => $drinks->id,
            'name'        => 'Mineral Water',
            'sku'         => 'DRK-002',
            'price'       => 1.49,
            'is_active'   => true, 'is_available' => true, 'prints_on_kot' => false, 'sort_order' => 2,
        ]);
        $this->addVariants($water, [
            ['330ml', 1.49, true],
            ['500ml', 1.99, false],
        ]);

        Product::create([
            'category_id' => $drinks->id,
            'name'        => 'Orange Juice',
            'sku'         => 'DRK-003',
            'price'       => 3.49,
            'is_active'   => true, 'is_available' => true, 'prints_on_kot' => false, 'sort_order' => 3,
        ]);

        // --- Desserts (2 products) ---
        $shake = Product::create([
            'category_id' => $desserts->id,
            'name'        => 'Chocolate Shake',
            'sku'         => 'DST-001',
            'price'       => 4.99,
            'is_active'   => true, 'is_available' => true, 'prints_on_kot' => true, 'sort_order' => 1,
        ]);
        $this->addVariants($shake, [
            ['Regular', 4.99, true],
            ['Large',   6.49, false],
        ]);

        Product::create([
            'category_id' => $desserts->id,
            'name'        => 'Brownie',
            'sku'         => 'DST-002',
            'price'       => 3.49,
            'description' => 'Warm chocolate brownie with vanilla ice cream.',
            'is_active'   => true, 'is_available' => true, 'prints_on_kot' => true, 'sort_order' => 2,
        ]);
    }

    private function addVariants(Product $product, array $variants): void
    {
        foreach ($variants as [$name, $price, $isDefault]) {
            ProductVariant::create([
                'product_id' => $product->id,
                'name'       => $name,
                'price'      => $price,
                'is_default' => $isDefault,
                'sort_order' => 0,
            ]);
        }
    }
}
