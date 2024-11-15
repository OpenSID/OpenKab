<?php

namespace App\Http\Repository;

use App\Models\Dtks;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class DTKSRepository
{
    public function index()
    {
        return QueryBuilder::for(Dtks::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('rtm.kepalaKeluarga.nik'),
            ])
            ->with([
                'rtm',
                'rtm.kepalaKeluarga' => static function ($builder): void {
                    // override all items within the $with property in Penduduk
                    $builder->withOnly('keluarga');
                },
                'rtm.anggota' => static function ($builder): void {
                    // override all items within the $with property in Penduduk
                    $builder->withOnly(['keluarga', 'pekerjaan', 'pendidikanKK']);
                    // hanya ambil data anggota yg masih hidup (tweb_penduduk)
                    $builder->where('status_dasar', 1);
                },
            ])
            ->jsonPaginate();
    }
}