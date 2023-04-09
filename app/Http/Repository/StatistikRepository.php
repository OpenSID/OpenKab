<?php

namespace App\Http\Repository;

use App\Models\Rtm;
use App\Models\Bantuan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class StatistikRepository
{
    public function getKategoriStatistik(string $kategori = null): array
    {
        $daftarKategori = match ($kategori) {
            'penduduk' => $this->setKategoriFormat('Penduduk', 'Jenis Kelompok', Penduduk::KATEGORI_STATISTIK),
            'keluarga' => $this->setKategoriFormat('Keluarga', 'Jenis Kelompok', Keluarga::KATEGORI_STATISTIK),
            'rtm' => $this->setKategoriFormat('RTM', 'Jenis Kelompok', Rtm::KATEGORI_STATISTIK),
            'bantuan' => $this->getKategoriBantuan(),
        };

        $detail = request()->input('filter')['detail'] ?? null;

        if ($detail) {
            $daftarKategori = collect($daftarKategori)->filter(function ($item) use ($detail) {
                return $item['id'] == $detail;
            })
            ->values()
            ->toArray();
        }

        return $daftarKategori;
    }

    private function setKategoriFormat(string $judulHalaman = null, string $judulKolomNama = null, array $kategori = []): array
    {
        return collect($kategori)->map(function ($item, $key) use ($judulHalaman, $judulKolomNama) {
            return [
                'id' => $key,
                'nama' => $item,
                'judul_halaman' => $judulHalaman,
                'judul_kolom_nama' => $judulKolomNama,
            ];
        })
        ->values()
        ->toArray();
    }

    private function getKategoriBantuan(): array
    {
        $query = Bantuan::query();
        if (session()->has('desa')) {
            $query->where('config_id', session('desa.id'));
        }

        $bantuanNonKategori = $query->select('id', 'nama', 'sasaran')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'judul_halaman' => 'Bantuan ' . $item->nama,
                'judul_kolom_nama' => 'Sasaran ' . $item->nama_sasaran,
            ];
        });

        return collect(Bantuan::KATEGORI_STATISTIK)->map(function ($item, $key) {
            return [
                'id' => $key,
                'nama' => $item,
                'judul_halaman' => $item,
                'judul_kolom_nama' => 'Jenis Kelompok',
            ];
        })
        ->merge($bantuanNonKategori)
        ->values()
        ->toArray();
    }

    public function getStatistik(array $data = []): array|object
    {
        $header = $data['header'] ?? [];
        $footer = $data['footer'] ?? [];

        if (count($footer) > 0) {
            $setFooter = $this->getHitungFooter($footer);

            if (count($header) > 0) {
                $setHeader = $this->getHitungHeader($header, $setFooter[2]['jumlah']);

                $setFooter = collect($setFooter)->map(function ($item, $key) use ($setHeader) {
                    $item['id'] = $key + $setHeader->pluck('id')->max();
                    return $item;
                });

                return $setHeader->merge($setFooter);
            }

            return $setFooter;
        }

        return [];
    }

    private function getHitungHeader(array $dataHeader = [], int $total = 0): object
    {
        return collect($dataHeader)->map(function ($item, $key) use ($total) {
            return $this->getPresentase($item, $total);
        });
    }

    private function getHitungFooter(array $dataFooter = []): array
    {
        return [
            $this->getPresentase([
                'id'        => 1,
                'nama'      => $dataFooter[0]['nama'],
                'laki_laki' => $dataFooter[0]['laki_laki'],
                'perempuan' => $dataFooter[0]['perempuan'],
            ], $dataFooter[2]['jumlah']),
            $this->getPresentase([
                'id'        => 2,
                'nama'      => $dataFooter[1]['nama'],
                'laki_laki' => $dataFooter[1]['laki_laki'] ?? $dataFooter[2]['laki_laki'] - $dataFooter[0]['laki_laki'],
                'perempuan' => $dataFooter[1]['perempuan'] ?? $dataFooter[2]['perempuan'] - $dataFooter[0]['perempuan'],
            ], $dataFooter[2]['jumlah']),
            $this->getPresentase([
                'id'        => 3,
                'nama'      => $dataFooter[2]['nama'],
                'laki_laki' => $dataFooter[2]['laki_laki'],
                'perempuan' => $dataFooter[2]['perempuan'],
            ], $dataFooter[2]['jumlah']),
        ];
    }

    private function getPresentase($data, $pembagi = null): array
    {
        $data = collect($data)->toArray();
        $data['jumlah'] = $data['laki_laki'] + $data['perempuan'];
        $pembagi = $pembagi ?? $data['jumlah'];
        $data['persentase_jumlah'] = persen($data['jumlah'], $pembagi);
        $data['persentase_laki_laki'] = persen($data['laki_laki'], $pembagi);
        $data['persentase_perempuan'] = persen($data['perempuan'], $pembagi);

        return $data;
    }
}
