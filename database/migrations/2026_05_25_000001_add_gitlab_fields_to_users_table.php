<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gitlab_id')->nullable()->unique()->after('github_token_expires_at');
            $table->string('gitlab_username')->nullable()->after('gitlab_id');
            $table->string('gitlab_avatar_url')->nullable()->after('gitlab_username');
            $table->string('gitlab_base_url')->nullable()->after('gitlab_avatar_url');
            $table->text('gitlab_access_token')->nullable()->after('gitlab_base_url');
            $table->text('gitlab_refresh_token')->nullable()->after('gitlab_access_token');
            $table->timestamp('gitlab_token_expires_at')->nullable()->after('gitlab_refresh_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'gitlab_id',
                'gitlab_username',
                'gitlab_avatar_url',
                'gitlab_base_url',
                'gitlab_access_token',
                'gitlab_refresh_token',
                'gitlab_token_expires_at',
            ]);
        });
    }
};
