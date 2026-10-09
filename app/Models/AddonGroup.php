<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddonGroup extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['name', 'min_selectable', 'max_selectable', 'is_required'];

    protected $casts = [
        'min_selectable' => 'integer',
        'max_selectable' => 'integer',
        'is_required'    => 'boolean',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(AddonOption::class)->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_addon_group');
    }
}
