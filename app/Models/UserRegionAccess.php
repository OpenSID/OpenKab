<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hak akses wilayah pengguna (struktur sama dengan API-Database-Gabungan).
 */
class UserRegionAccess extends Model
{
    protected $table = 'user_region_access';

    protected $fillable = ['user_id', 'level', 'region_code'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
