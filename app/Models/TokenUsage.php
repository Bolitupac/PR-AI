<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenUsage extends Model
{
    protected $table = 'token_usage';

    protected $fillable = [
        'user_id',
        'usage_date',
        'week_key',
        'request_count',
        'tokens_used',
    ];

    protected function casts(): array
    {
        return [
            'usage_date' => 'date',
            'request_count' => 'integer',
            'tokens_used' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
