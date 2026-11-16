<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hak akses sektor DataSpasial per pengguna (issue OpenSID/OpenKab#1320).
 */
class UserSectorAccess extends Model
{
    protected $table = 'user_sector_access';

    protected $fillable = ['user_id', 'sektor_kode'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
