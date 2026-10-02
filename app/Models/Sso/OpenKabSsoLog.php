<?php

namespace App\Models\Sso;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Catatan audit SSO yang bersifat append-only.
 * Baris tidak boleh di-update maupun di-delete oleh aplikasi.
 */
class OpenKabSsoLog extends Model
{
    /** @var bool izin internal untuk pembersihan resmi oleh super admin */
    protected static bool $forceDelete = false;

    protected $guarded = [];

    protected $table = 'openkab_sso_logs';

    protected $casts = [
        'attempt_time' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Log audit SSO bersifat append-only dan tidak dapat diubah.');
        });

        static::deleting(function () {
            if (! static::$forceDelete) {
                throw new LogicException('Log audit SSO bersifat append-only dan tidak dapat dihapus.');
            }
        });
    }

    /**
     * Hapus baris untuk pembersihan resmi (super admin).
     * Selalu menulis jejak activitylog agar tetap dapat diaudit.
     */
    public function forceDeleteForAudit(): void
    {
        static::$forceDelete = true;

        try {
            activity('sso-audit')
                ->causedBy(auth()->user())
                ->performedOn($this)
                ->withProperties([
                    'admin_id' => $this->admin_id,
                    'desa_id' => $this->desa_id,
                    'attempt_time' => $this->attempt_time?->toDateTimeString(),
                ])
                ->log('Log audit SSO dihapus secara paksa');

            $this->delete();
        } finally {
            static::$forceDelete = false;
        }
    }

    public function scopeForAuditDashboard(Builder $query): Builder
    {
        return $query->latest('attempt_time');
    }
}
