<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('fulfiller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_category_id')->constrained()->restrictOnDelete();
            $table->string('campus', 20)->nullable()->index();

            // What & where
            $table->string('title', 150);
            $table->text('notes')->nullable();
            $table->string('pickup_location', 150);
            $table->string('dropoff_location', 150);
            $table->dateTime('needed_by')->nullable();
            $table->json('items')->nullable();        // [{name, quantity, estimated_price, note}]
            $table->json('print_spec')->nullable();   // {pages, copies, is_color, paper_size, binding, instructions}
            $table->string('document_path')->nullable();
            $table->string('document_name')->nullable();

            // Money
            $table->unsignedInteger('service_fee');
            $table->unsignedInteger('estimated_item_cost')->default(0);
            $table->unsignedInteger('actual_item_cost')->nullable();
            $table->string('receipt_path')->nullable();

            // Direct P2P payment (requester -> fulfiller)
            $table->string('payment_method', 120)->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->string('payment_note', 255)->nullable();
            $table->timestamp('payment_submitted_at')->nullable();
            $table->timestamp('payment_verified_at')->nullable();
            $table->string('payment_rejection_reason', 500)->nullable();

            // Lifecycle
            $table->string('status', 24)->default('open');
            $table->char('completion_pin', 4);
            $table->boolean('needs_refund')->default(false);
            $table->timestamp('matched_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('delivering_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancel_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['status', 'campus', 'needed_by']);
            $table->index(['requester_id', 'status']);
            $table->index(['fulfiller_id', 'status']);
            $table->index(['trip_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
