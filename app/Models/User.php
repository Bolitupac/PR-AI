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
        'custom_openai_api_key',
        'ai_key_mode',
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
        'custom_openai_api_key',
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
            'custom_openai_api_key' => 'encrypted',
            'ai_preferences' => 'array',
        ];
    }

    public function hasCustomOpenAiKey(): bool
    {
        return trim((string) $this->custom_openai_api_key) !== '';
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
}
