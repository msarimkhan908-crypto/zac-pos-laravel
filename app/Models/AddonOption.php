<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddonOption extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['addon_group_id', 'name', 'extra_price', 'sort_order'];

    protected $casts = [
        'extra_price' => 'decimal:2',
        'sort_order'  => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(AddonGroup::class);
    }
}
