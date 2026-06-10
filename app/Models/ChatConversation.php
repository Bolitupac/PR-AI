<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'public_id',
        'title',
        'provider',
        'model',
        'active_audit_context',
    ];

    protected static function booted(): void
    {
        static::creating(function (ChatConversation $conversation) {
            if (empty($conversation->public_id) && \Illuminate\Support\Facades\Schema::hasColumn('chat_conversations', 'public_id')) {
                $conversation->public_id = Str::random(24);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        // Fallback to 'id' if public_id column doesn't exist yet (migration not run)
        if (\Illuminate\Support\Facades\Schema::hasColumn('chat_conversations', 'public_id')) {
            return 'public_id';
        }
        return 'id';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')->oldest();
    }
}
