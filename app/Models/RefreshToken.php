<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'refresh_token',
        'access_token_id',
        'ip_address',
        'user_agent',
        'expires_at',
        'is_revoked',
        'revoked_at',
        'revoked_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_revoked' => 'boolean',
    ];

    /**
     * Get the user that owns the refresh token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the refresh token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the refresh token is valid (not expired and not revoked).
     */
    public function isValid(): bool
    {
        return ! $this->is_revoked && ! $this->isExpired();
    }

    /**
     * Revoke the refresh token.
     */
    public function revoke(string $reason = null): void
    {
        $this->update([
            'is_revoked' => true,
            'revoked_at' => now(),
            'revoked_reason' => $reason,
        ]);
    }
}
