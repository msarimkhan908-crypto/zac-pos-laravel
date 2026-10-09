<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'sku', 'barcode', 'description',
        'price', 'cost_price', 'image', 'sort_order',
        'is_active', 'is_available', 'prints_on_kot', 'prep_note',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'cost_price'    => 'decimal:2',
        'sort_order'    => 'integer',
        'is_active'     => 'boolean',
        'is_available'  => 'boolean',
        'prints_on_kot' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function addonGroups(): BelongsToMany
    {
        return $this->belongsToMany(AddonGroup::class, 'product_addon_group')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function getThumbUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }
        $thumb = str_replace('products/', 'products/thumbs/', $this->image);
        return Storage::disk('public')->exists($thumb)
            ? Storage::disk('public')->url($thumb)
            : Storage::disk('public')->url($this->image);
    }

    public static function generateSku(): string
    {
        do {
            $sku = 'SKU-' . strtoupper(Str::random(6));
        } while (static::withTrashed()->where('sku', $sku)->exists());

        return $sku;
    }
}
