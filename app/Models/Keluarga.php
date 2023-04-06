<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Keluarga extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'tweb_keluarga';

    /**
     * {@inheritDoc}
     */
    protected $with = [
        'kelasSosial',
    ];

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function kelasSosial()
    {
        return $this->belongsTo(KeluargaSejahtera::class, 'kelas_sosial')->withDefault();
    }
}
