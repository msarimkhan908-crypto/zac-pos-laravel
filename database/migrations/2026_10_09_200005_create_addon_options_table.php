<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addon_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('addon_group_id')->index();
            $table->string('name');
            $table->decimal('extra_price', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addon_options');
    }
};
