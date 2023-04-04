<?php

namespace App\Models;

use App\Models\Enums\SasaranEnum;
use Illuminate\Database\Eloquent\Model;

class Bantuan extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'program';

    /** {@inheritdoc} */
    protected $casts = [
        // 'sasaran' => SasaranEnum::class,
    ];

    /**
     * Define a one-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function peserta()
    {
        return $this->hasMany(BantuanPeserta::class, 'program_id');
    }
}
