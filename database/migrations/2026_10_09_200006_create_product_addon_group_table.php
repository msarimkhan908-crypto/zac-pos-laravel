<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_addon_group', function (Blueprint $table) {
            $table->uuid('product_id');
            $table->uuid('addon_group_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['product_id', 'addon_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_addon_group');
    }
};
