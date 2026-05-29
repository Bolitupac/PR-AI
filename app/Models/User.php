<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'github_id',
        'github_username',
        'github_access_token',
        'github_refresh_token',
        'github_token_expires_at',
        'gitlab_id',
        'gitlab_username',
        'gitlab_avatar_url',
        'gitlab_base_url',
        'gitlab_access_token',
        'gitlab_refresh_token',
        'gitlab_token_expires_at',
        'custom_openai_api_key',
        'custom_deepseek_api_key',
        'ai_key_mode',
        'ai_provider',
        'ai_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'github_access_token',
        'github_refresh_token',
        'gitlab_access_token',
        'gitlab_refresh_token',
        'custom_openai_api_key',
        'custom_deepseek_api_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'github_token_expires_at' => 'datetime',
            'gitlab_token_expires_at' => 'datetime',
            'custom_openai_api_key' => 'encrypted',
            'custom_deepseek_api_key' => 'encrypted',
            'ai_preferences' => 'array',
        ];
    }

    public function hasCustomOpenAiKey(): bool
    {
        return trim((string) $this->custom_openai_api_key) !== '';
    }

    public function hasCustomDeepSeekKey(): bool
    {
        return trim((string) $this->custom_deepseek_api_key) !== '';
    }

    public function getMaskedOpenAiKeyAttribute(): string
    {
        $key = trim((string) $this->custom_openai_api_key);
        if ($key === '') {
            return '';
        }

        if (strlen($key) <= 8) {
            return str_repeat('*', strlen($key));
        }

        return substr($key, 0, 4).str_repeat('*', max(4, strlen($key) - 8)).substr($key, -4);
    }

    public function getMaskedDeepSeekKeyAttribute(): string
    {
        $key = trim((string) $this->custom_deepseek_api_key);
        if ($key === '') {
            return '';
        }

        if (strlen($key) <= 8) {
            return str_repeat('*', strlen($key));
        }

        return substr($key, 0, 4).str_repeat('*', max(4, strlen($key) - 8)).substr($key, -4);
    }
}
