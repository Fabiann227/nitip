<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role', 20)->default('student')->index();
            $table->string('name', 120);
            $table->string('email')->unique();
            $table->string('nim', 30)->nullable();
            $table->string('campus', 20)->nullable()->index();
            $table->string('whatsapp_number', 20)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar_path')->nullable();
            $table->string('bio', 500)->nullable();

            // Direct P2P payment destination (one per user).
            $table->string('payment_type', 20)->nullable();
            $table->string('payment_provider', 60)->nullable();
            $table->string('payment_account_number', 60)->nullable();
            $table->string('payment_account_name', 100)->nullable();
            $table->string('payment_qris_path')->nullable();

            // Email verification OTP.
            $table->string('otp_code_hash')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->unsignedTinyInteger('otp_attempts')->default(0);
            $table->timestamp('otp_sent_at')->nullable();

            $table->boolean('is_suspended')->default(false);
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason', 500)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['campus', 'nim']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
