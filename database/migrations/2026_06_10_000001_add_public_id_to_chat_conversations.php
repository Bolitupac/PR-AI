<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->string('public_id', 32)->unique()->nullable()->after('id');
        });

        // Generate public_id for existing conversations
        $conversations = \App\Models\ChatConversation::whereNull('public_id')->get();
        foreach ($conversations as $conversation) {
            $conversation->public_id = Str::random(24);
            $conversation->save();
        }

        // Now make it non-nullable
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->string('public_id', 32)->unique()->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropColumn('public_id');
        });
    }
};
