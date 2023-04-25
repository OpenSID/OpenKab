<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Berita extends BaseModel
{
    /** {@inheritdoc} */
    protected $table = 'artikel';

    /** {@inheritdoc} */
    protected $with = [
        'config',
    ];

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function Config()
    {
        return $this->belongsTo(Config::class, 'config_id')->withDefault();
    }
}
