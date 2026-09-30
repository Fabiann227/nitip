<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 80);
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('fee_min');
            $table->unsignedInteger('fee_default');
            $table->unsignedInteger('fee_max');
            $table->boolean('requires_document')->default(false);
            $table->boolean('has_item_cost')->default(true);
            $table->string('icon', 40)->default('shopping_bag');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_categories');
    }
};
