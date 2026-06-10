<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedeemCode extends Model
{
    protected $table = 'redeem_codes';

    protected $fillable = [
        'code',
        'credits',
        'max_uses',
        'times_used',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'max_uses' => 'integer',
            'times_used' => 'integer',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isExhausted(): bool
    {
        if ($this->max_uses === null) {
            return false;
        }

        return $this->times_used >= $this->max_uses;
    }

    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return now()->gt($this->expires_at);
    }

    public function isValid(): bool
    {
        return $this->is_active && ! $this->isExhausted() && ! $this->isExpired();
    }
}
