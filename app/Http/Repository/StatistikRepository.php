<?php

namespace App\Http\Repository;

use App\Models\Rtm;
use App\Models\Bantuan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Enums\LabelStatistik;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class StatistikRepository
{
    public function getKategoriStatistik(string $kategori = null): array|object
    {
        $daftarKategori = match ($kategori) {
            'penduduk' => $this->setKategoriFormat('Penduduk', 'Jenis Kelompok', Penduduk::KATEGORI_STATISTIK),
            'keluarga' => $this->setKategoriFormat('Keluarga', 'Jenis Kelompok', Keluarga::KATEGORI_STATISTIK),
            'rtm' => $this->setKategoriFormat('RTM', 'Jenis Kelompok', Rtm::KATEGORI_STATISTIK),
            'bantuan' => $this->getKategoriBantuan(),
        };

        $detail = request()->input('filter')['detail'] ?? null;

        if ($detail) {
            $daftarKategori = collect($daftarKategori)->filter(fn($item) => $item['id'] == $detail)
            ->values()
            ->toArray();
        }

        return $daftarKategori;
    }

    private function setKategoriFormat(string $judulHalaman = null, string $judulKolomNama = null, array $kategori = []): array|object
    {
        return collect($kategori)->map(fn($item, $key) => [
            'id' => $key,
            'nama' => $item,
            'judul_halaman' => $judulHalaman,
            'judul_kolom_nama' => $judulKolomNama,
        ])
        ->values()
        ->toArray();
    }

    private function getKategoriBantuan(): array|object
    {
        $query = Bantuan::query();
        if (session()->has('desa')) {
            $query->where('config_id', session('desa.id'));
        }

        $bantuanNonKategori = $query->select('id', 'nama', 'sasaran')->get()->map(fn($item) => [
            'id' => $item->id,
            'nama' => $item->nama,
            'judul_halaman' => 'Bantuan ' . $item->nama,
            'judul_kolom_nama' => 'Sasaran ' . $item->nama_sasaran,
        ]);

        return collect(Bantuan::KATEGORI_STATISTIK)->map(fn($item, $key) => [
            'id' => $key,
            'nama' => $item,
            'judul_halaman' => $item,
            'judul_kolom_nama' => 'Jenis Kelompok',
        ])
        ->merge($bantuanNonKategori)
        ->values()
        ->toArray();
    }

    public function getStatistik(array $data = []): array|object
    {
        $header = $data['header'] ?? [];
        $footer = $data['footer'] ?? [];

        if ((is_countable($footer) ? count($footer) : 0) > 0) {
            $setFooter = $this->getHitungFooter($footer);

            if ((is_countable($header) ? count($header) : 0) > 0) {
                $setHeader = $this->getHitungHeader($header, $setFooter[2]['jumlah']);

                return $setHeader->merge($setFooter);
            }

            return $setFooter;
        }

        return [];
    }

    private function getHitungHeader(array $dataHeader = [], int $total = 0): array|object
    {
        return collect($dataHeader)->map(fn($item, $key) => $this->getPresentase($item, $total));
    }

    private function getHitungFooter(array $dataFooter = []): array|object
    {
        return [
            $this->getPresentase([
                'id'        => LabelStatistik::Jumlah,
                'nama'      => $dataFooter[0]['nama'],
                'laki_laki' => $dataFooter[0]['laki_laki'],
                'perempuan' => $dataFooter[0]['perempuan'],
            ], $dataFooter[2]['jumlah']),
            $this->getPresentase([
                'id'        => LabelStatistik::BelumMengisi,
                'nama'      => $dataFooter[1]['nama'],
                'laki_laki' => $dataFooter[1]['laki_laki'] ?? $dataFooter[2]['laki_laki'] - $dataFooter[0]['laki_laki'],
                'perempuan' => $dataFooter[1]['perempuan'] ?? $dataFooter[2]['perempuan'] - $dataFooter[0]['perempuan'],
            ], $dataFooter[2]['jumlah']),
            $this->getPresentase([
                'id'        => LabelStatistik::Total,
                'nama'      => $dataFooter[2]['nama'],
                'laki_laki' => $dataFooter[2]['laki_laki'],
                'perempuan' => $dataFooter[2]['perempuan'],
            ], $dataFooter[2]['jumlah']),
        ];
    }

    private function getPresentase($data, $pembagi = null): array|object
    {
        $data = collect($data)->toArray();
        $data['jumlah'] = $data['laki_laki'] + $data['perempuan'];
        $pembagi ??= $data['jumlah'];
        $data['persentase_jumlah'] = persen($data['jumlah'], $pembagi);
        $data['persentase_laki_laki'] = persen($data['laki_laki'], $pembagi);
        $data['persentase_perempuan'] = persen($data['perempuan'], $pembagi);

        return $data;
    }
}
