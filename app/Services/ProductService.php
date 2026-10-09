<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

class ProductService
{
    public static function duplicate(Product $product): Product
    {
        $product->loadMissing(['variants', 'addonGroups']);

        $copy = $product->replicate(['image']);
        $copy->name = 'Copy of ' . $product->name;
        $copy->sku  = Product::generateSku();
        $copy->save();

        foreach ($product->variants as $variant) {
            $copy->variants()->create([
                'name'       => $variant->name,
                'price'      => $variant->price,
                'is_default' => $variant->is_default,
                'sort_order' => $variant->sort_order,
            ]);
        }

        $addonIds = $product->addonGroups->pluck('id')->all();
        if ($addonIds) {
            $copy->addonGroups()->attach($addonIds);
        }

        return $copy->fresh(['variants', 'addonGroups', 'category']);
    }
}
