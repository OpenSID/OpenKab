<?php

namespace App\Models;

class IbuHamil extends BaseModel
{
    /**
     * Static data status kehamilan ibu.
     *
     * @var array
     */
    public const STATUS_KEHAMILAN_IBU = [
        [
            'id' => 1,
            'simbol' => 'N',
            'nama' => 'Normal (N)',
        ],
        [
            'id' => 2,
            'simbol' => 'Risti',
            'nama' => 'Risiko Tinggi (Risti)',
        ],
        [
            'id' => 3,
            'simbol' => 'KEK',
            'nama' => 'Kekurangan Energi Kronis (KEK)',
        ],
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ibu_hamil';

    /**
     * The table update parameter.
     *
     * @var string
     */
    public $primaryKey = 'id_ibu_hamil';

    /**
     * The guarded with the model.
     *
     * @var array
     */
    protected $guarded = [];

    public function kia()
    {
        return $this->belongsTo(KIA::class, 'kia_id');
    }

    public function scopeFilter($query, array $filters)
    {
        if (! empty($filters['bulan'])) {
            $query->whereMonth('ibu_hamil.created_at', $filters['bulan']);
        }

        if (! empty($filters['tahun'])) {
            $query->whereYear('ibu_hamil.created_at', $filters['tahun']);
        }

        if (! empty($filters['posyandu'])) {
            $query->where('posyandu_id', $filters['posyandu']);
        }

        return $query;
    }
}
