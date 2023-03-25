<?php

namespace App\Models\Gabungan;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Config extends BaseModelDBGabungan
{
    protected $table = 'config';

    /**
     * Interact with kode_desa
     * @return Attribute
     */
    protected function kodeDesa() : Attribute
    {
        return Attribute::make(
            fn($item) => preg_replace('/^(\d{2})(\d{2})(\d{2})(\d{4})$/', '$1.$2.$3.$4', $item) // "35.06.18.2010"
        );
    }
}
