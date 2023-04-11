<?php

namespace App\Models;

use App\Models\Enums\SasaranEnum;
use App\Models\Traits\ConfigIdTrait;
use Illuminate\Database\Eloquent\Model;

class BantuanPeserta extends Model
{
    use ConfigIdTrait;

    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'program_peserta';

    /** {@inheritdoc} */
    protected $with = [
        'bantuan',
    ];

    /**
     * Define a one-to-many relationship.
     *
     * @return BelongsTo
     */
    public function bantuan()
    {
        return $this->belongsTo(Bantuan::class, 'program_id');
    }
}
