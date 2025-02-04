<?php

namespace App\Http\Repository;

use App\Models\Keuangan;
use App\Models\LaporanSinkronisasi;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class KeuanganRepository
{
    public function apbdes()
    {   // pakai join agar bisa disorting berdasarkan nama desa dan uraian template
        return  QueryBuilder::for(Keuangan::selectRaw('keuangan.*, config.nama_desa, keuangan_template.uraian')
                ->join('config', 'keuangan.config_id', '=', 'config.id')
                ->join('keuangan_template', 'keuangan.template_uuid', '=', 'keuangan_template.uuid')
                ->whereHas('template', static fn ($query) => $query->apbdes()))
            ->allowedFilters([
                AllowedFilter::exact('config_id'),
                AllowedFilter::exact('tahun'),
                AllowedFilter::callback('kode_kecamatan', function ($query, $value) {
                    $query->whereHas('desa', static fn ($query) => $query->where('kode_kecamatan', $value));
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('template_uuid', 'like', "%{$value}%");
                        $query->orWhere('keuangan_template.uraian', 'like', "%{$value}%");
                        $query->orWhere('tahun', 'like', "{$value}%");
                        $query->orWhere('config.nama_desa', 'like', "%{$value}%");
                    });
                }),
            ])->allowedSorts([
                'config_id',
                'tahun',
                'anggaran',
                'realisasi',
                'template_uuid',
                'keuangan_template.uraian',
                'config.nama_desa',
            ])
            ->jsonPaginate();
    }

    public function laporan_apbdes()
    {   // pakai join agar bisa disorting berdasarkan nama desa dan uraian template
        return  QueryBuilder::for(LaporanSinkronisasi::selectRaw('laporan_sinkronisasi.*, config.nama_desa, config.website')
                ->join('config', 'laporan_sinkronisasi.config_id', '=', 'config.id')
                ->apbdes())

            ->allowedFilters([
                AllowedFilter::exact('config_id'),
                AllowedFilter::exact('tahun'),
                AllowedFilter::callback('kode_kecamatan', function ($query, $value) {
                    $query->whereHas('desa', static fn ($query) => $query->where('kode_kecamatan', $value));
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('judul', 'like', "%{$value}%");
                        $query->orWhere('semester', 'like', "{$value}%");
                        $query->orWhere('tahun', 'like', "{$value}%");
                        $query->orWhere('config.nama_desa', 'like', "%{$value}%");
                    });
                }),
            ])->allowedSorts([
                'config_id',
                'tahun',
                'semester',
                'judul',
                'nama_file',
                'kirim',
                'created_at',
                'updated_at',
                'config.nama_desa',
            ])
            ->jsonPaginate();
    }
}
