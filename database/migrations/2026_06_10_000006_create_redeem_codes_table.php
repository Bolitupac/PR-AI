<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redeem_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->unsignedInteger('credits');
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('times_used')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_redeemed_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('redeem_code_id')->constrained('redeem_codes')->cascadeOnDelete();
            $table->timestamp('redeemed_at')->useCurrent();
            $table->unique(['user_id', 'redeem_code_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_redeemed_codes');
        Schema::dropIfExists('redeem_codes');
    }
};
