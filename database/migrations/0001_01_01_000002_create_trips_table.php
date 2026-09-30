<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->foreignId('fulfiller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained()->restrictOnDelete();
            $table->string('campus', 20)->nullable()->index();
            $table->string('destination', 120);
            $table->string('waypoints', 255)->nullable();
            $table->dateTime('departure_at');
            $table->dateTime('closes_at');
            $table->string('transport_mode', 20)->default('walk');
            $table->unsignedTinyInteger('max_slots')->default(3);
            $table->unsignedInteger('service_fee');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['campus', 'status', 'departure_at']);
            $table->index(['fulfiller_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
