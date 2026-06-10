<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('redeem_codes')->insert([
            'code' => 'BETA50',
            'credits' => 50,
            'max_uses' => null,
            'times_used' => 0,
            'expires_at' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('redeem_codes')->where('code', 'BETA50')->delete();
    }
};
