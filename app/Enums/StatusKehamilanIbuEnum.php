<?php

namespace App\Enums;

class StatusKehamilanIbuEnum
{
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

    public static function getNamaById(int $id): ?string
    {
        foreach (self::STATUS_KEHAMILAN_IBU as $item) {
            if ($item['id'] === $id) {
                return $item['nama'];
            }
        }
        return null;
    }

    public static function getSimbolById(int $id): ?string
    {
        foreach (self::STATUS_KEHAMILAN_IBU as $item) {
            if ($item['id'] === $id) {
                return $item['simbol'];
            }
        }
        return null;
    }
}
