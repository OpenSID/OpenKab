<?php

namespace App\Models\Sso;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DesaSsoConfig extends Model
{
    protected $guarded = [];

    protected $table = 'desa_sso_configs';

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /**
     * Validasi dasar URL OpenSID. Pengecekan skema https dilakukan oleh
     * OpenSidUrlResolver sesuai environment.
     */
    public static function validateUrl(string $url): bool
    {
        $parsed = parse_url($url);

        if (! $parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
            return false;
        }

        return in_array($parsed['scheme'], ['https', 'http'], true);
    }
}
