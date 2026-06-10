<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('token_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('usage_date');
            $table->string('week_key', 10);
            $table->unsignedInteger('request_count')->default(0);
            $table->unsignedBigInteger('tokens_used')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'usage_date']);
            $table->index(['user_id', 'week_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('token_usage');
    }
};
