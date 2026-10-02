<?php

namespace App\Models\Sso;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenKabSsoToken extends Model
{
    protected $guarded = [];

    protected $table = 'openkab_sso_tokens';

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Konsumsi token secara atomik (sekali pakai).
     * Hanya berhasil bila jti dikenal dan belum pernah digunakan.
     */
    public static function consumeAtomic(string $jti, ?string $ip = null): bool
    {
        return (bool) static::query()
            ->where('jti', $jti)
            ->whereNull('used_at')
            ->update([
                'used_at' => now(),
                'consumed_by_ip' => $ip,
            ]);
    }

    /**
     * Ambil token yang masih aktif (belum dipakai, belum kedaluwarsa) berdasarkan fingerprint.
     */
    public static function activeByFingerprint(string $fingerprint): ?self
    {
        return static::query()
            ->where('token_fingerprint', $fingerprint)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }
}
