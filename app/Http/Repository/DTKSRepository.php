<?php

namespace App\Http\Repository;

use App\Models\DTKS;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DTKSRepository
{
    public function index()
    {
        return QueryBuilder::for(DTKS::class)
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
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('rtm.kepalaKeluarga.nik'),
                AllowedFilter::callback('search', static function ($query, $value) {
                    $query->whereRelation('rtm.kepalaKeluarga', 'nik', 'like', "%{$value}%");
                }),
            ])
            ->allowedSorts([
                'id',
            ])
            ->filterKecamatan()
            ->filterDesa()
            ->jsonPaginate();
    }
}
