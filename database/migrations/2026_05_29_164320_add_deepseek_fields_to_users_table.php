<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('custom_deepseek_api_key')->nullable()->after('custom_openai_api_key');
            $table->string('ai_provider', 20)->default('openai')->after('ai_key_mode');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['custom_deepseek_api_key', 'ai_provider']);
        });
    }
};
