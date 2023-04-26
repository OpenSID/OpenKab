<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DokumenHidup extends BaseModel
{
    use ConfigIdTrait;
    use HasFactory;

    /** {@inheritdoc} */
    protected $table = 'dokumen_hidup';

    /** {@inheritdoc} */
    protected $casts = [
        'attr' => 'json',
    ];
}
