<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\ImporDataSpasialTemplateExport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Menyusun berkas templat impor DataSpasial (XLSX) beserta data fasilitas
 * yang sudah ada sebagai baris awal.
 */
class ImporTemplateService
{
    /** Batas halaman per koleksi, agar tidak memanggil API tanpa henti. */
    private const MAKS_HALAMAN = 500;

    private const UKURAN_HALAMAN = 100;

    public function __construct(
        private DataSpasialApiService $dataspasial,
        private ConfigApiService $configApiService,
    ) {}

    /**
     * Bangun templat dan kembalikan path berkas sementara.
     */
    public function bangun(string $sektor, ?string $kecamatan, string $periode): string
    {
        $desaList = $this->daftarDesa($kecamatan);
        $items = $this->itemIsian($sektor);
        $fasilitas = $this->fasilitasBerjalan($sektor, $kecamatan, $periode, $desaList);

        $tempFile = tempnam(sys_get_temp_dir(), 'tmpl_');
        (new ImporDataSpasialTemplateExport($desaList, $items, $sektor, $periode, $fasilitas))->write($tempFile);

        return $tempFile;
    }

    /**
     * @return array<int, array{kode_desa: string, nama_desa: string}>
     */
    public function daftarDesa(?string $kecamatan): array
    {
        $result = $kecamatan
            ? $this->configApiService->desaConfig(['filter[kode_kecamatan]' => $kecamatan])
            : $this->configApiService->desaConfig();

        if ($result instanceof Collection) {
            $result = $result->toArray();
        }

        if (! is_array($result)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($desa) {
            if (is_object($desa)) {
                $kode = (string) ($desa->kode_desa ?? $desa->kode ?? '');
                $nama = (string) ($desa->nama_desa ?? $desa->nama ?? '');

                return $kode === '' ? null : ['kode_desa' => $kode, 'nama_desa' => $nama];
            }
            if (is_array($desa)) {
                $attrs = $desa['attributes'] ?? [];
                $kode = (string) ($desa['kode_desa'] ?? $desa['kode'] ?? ($attrs['kode_desa'] ?? ($attrs['kode'] ?? '')));
                $nama = (string) ($desa['nama_desa'] ?? $desa['nama'] ?? ($attrs['nama_desa'] ?? ($attrs['nama'] ?? '')));

                return $kode === '' ? null : ['kode_desa' => $kode, 'nama_desa' => $nama];
            }

            return null;
        }, $result)));
    }

    /**
     * @return array<int, mixed>
     */
    private function itemIsian(string $sektor): array
    {
        $data = $this->dataspasial->listSkema(['sektor' => $sektor])->data;
        if (empty($data)) {
            return [];
        }

        $first = is_array($data[0]) ? $data[0] : $data;
        $attrs = $first['attributes'] ?? $first;

        if (isset($attrs['isian']) && is_array($attrs['isian'])) {
            return $attrs['isian'];
        }

        if (isset($attrs['fields']) && is_array($attrs['fields'])) {
            return $attrs['fields'];
        }

        return [];
    }

    /**
     * Fasilitas yang sudah ada beserta nilai isiannya, hanya untuk desa dalam cakupan.
     * Jika API tidak terbaca, templat tetap dibuat tanpa baris data.
     *
     * @param  array<int, array{kode_desa: string, nama_desa: string}>  $desaList
     * @return array<int, array{kode_desa: string, kode_fasilitas: string, nama_fasilitas: string, nilai: array<string, mixed>}>
     */
    private function fasilitasBerjalan(string $sektor, ?string $kecamatan, string $periode, array $desaList): array
    {
        // Kode desa => nama desa, dipakai untuk format "kode - nama" yang sama dengan dropdown.
        $cakupan = [];
        foreach ($desaList as $desa) {
            $cakupan[$desa['kode_desa']] = $desa['nama_desa'];
        }
        if ($cakupan === []) {
            return [];
        }

        // API fasilitas hanya menghormati sektor_id (bukan kode sektor).
        $wilayah = array_filter(
            ['sektor_id' => $this->sektorId($sektor), 'kode_kecamatan' => $kecamatan],
            static fn ($v) => $v !== null && $v !== ''
        );

        $daftarFasilitas = $this->semuaHalaman(
            fn (int $halaman) => $this->dataspasial->listFasilitas($wilayah + $this->halaman($halaman), segar: true)
        );
        if ($daftarFasilitas === null) {
            Log::warning('DataSpasial: data fasilitas untuk templat tidak terbaca', ['sektor' => $sektor]);

            return [];
        }

        $fasilitasById = $this->petaFasilitas($daftarFasilitas, $sektor, $cakupan);
        if ($fasilitasById === []) {
            return [];
        }

        // API hanya menerima filter periode berformat YYYY-MM, sehingga filter tahun dilakukan di sini.
        $daftarNilai = $this->semuaHalaman(
            fn (int $halaman) => $this->dataspasial->listNilai($wilayah + $this->halaman($halaman), segar: true)
        );
        
        if ($daftarNilai === null) {
            Log::warning('DataSpasial: nilai fasilitas untuk templat tidak terbaca', ['sektor' => $sektor]);
        }

        $tahun = substr($periode, 0, 4);
        $waktuTerpakai = [];

        foreach ($daftarNilai ?? [] as $item) {
            $atribut = $this->atribut($item);
            $fasilitasId = (string) ($atribut['fasilitas_id'] ?? '');
            $kodeIsian = (string) ($atribut['isian_kode'] ?? '');
            $nilai = $atribut['nilai'] ?? null;

            if (! isset($fasilitasById[$fasilitasId]) || $kodeIsian === '' || $nilai === null || $nilai === '') {
                continue;
            }

            if (! str_starts_with((string) ($atribut['periode'] ?? ''), $tahun)) {
                continue;
            }

            // Bila ada beberapa nilai untuk isian yang sama, yang paling baru diinput yang dipakai.
            $kunci = ImporDataSpasialTemplateExport::headerKey($kodeIsian);
            $waktu = (string) ($atribut['waktu_input'] ?? '');
            if (isset($waktuTerpakai[$fasilitasId][$kunci]) && $waktuTerpakai[$fasilitasId][$kunci] > $waktu) {
                continue;
            }

            $waktuTerpakai[$fasilitasId][$kunci] = $waktu;
            $fasilitasById[$fasilitasId]['nilai'][$kunci] = $nilai;
        }

        return array_values($fasilitasById);
    }

    /**
     * @param  array<int, array<string, mixed>>  $daftarFasilitas
     * @param  array<string, string>  $cakupan  Kode desa => nama desa.
     * @return array<string, array{kode_desa: string, kode_fasilitas: string, nama_fasilitas: string, nilai: array<string, mixed>}>
     */
    private function petaFasilitas(array $daftarFasilitas, string $sektor, array $cakupan): array
    {
        $peta = [];
        foreach ($daftarFasilitas as $item) {
            $atribut = $this->atribut($item);
            $kodeDesa = (string) ($atribut['wilayah']['kode_desa'] ?? '');
            if (! array_key_exists($kodeDesa, $cakupan)) {
                continue;
            }

            // Filter sektor di sisi aplikasi: API bisa mengembalikan fasilitas sektor lain.
            if ((string) ($atribut['sektor']['kode'] ?? '') !== $sektor) {
                continue;
            }

            // Id fasilitas berada di level item JSON:API, bukan di attributes.
            $id = (string) ($item['id'] ?? $atribut['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $namaDesa = (string) $cakupan[$kodeDesa];
            $kodeFasilitas = (string) ($atribut['kode_fasilitas'] ?? '');
            $peta[$id] = [
                // Format "kode - nama" sama dengan daftar dropdown pada sheet Daftar Desa.
                'kode_desa' => $namaDesa !== '' ? $kodeDesa.' - '.$namaDesa : $kodeDesa,
                'kode_fasilitas' => $kodeFasilitas !== '' ? $kodeFasilitas : $id,
                'nama_fasilitas' => (string) ($atribut['nama'] ?? ''),
                'nilai' => [],
            ];
        }

        return $peta;
    }

    /**
     * Ambil seluruh halaman koleksi. Mengembalikan null bila salah satu halaman tidak terbaca.
     *
     * @param  callable(int): DataSpasialResult  $fetch
     * @return array<int, array<string, mixed>>|null
     */
    private function semuaHalaman(callable $fetch): ?array
    {
        $semua = [];
        $halaman = 1;
        $totalHalaman = 1;

        do {
            $result = $fetch($halaman);
            if ($result->isUnavailable()) {
                return null;
            }

            foreach ($result->data as $item) {
                $semua[] = $item;
            }

            $totalHalaman = (int) ($result->meta['pagination']['total_pages'] ?? 1);
            $halaman++;
        } while ($halaman <= $totalHalaman && $halaman <= self::MAKS_HALAMAN);

        return $semua;
    }

    /**
     * Id sektor (ds_sektor.id) dari kodenya. Null bila tidak ditemukan.
     */
    private function sektorId(string $kode): ?string
    {
        foreach ($this->dataspasial->listSektor()->data as $item) {
            $atribut = $this->atribut($item);
            if ((string) ($atribut['kode'] ?? '') === $kode) {
                return (string) ($item['id'] ?? $atribut['id'] ?? '');
            }
        }

        return null;
    }

    /**
     * @return array{page: array{size: int, number: int}}
     */
    private function halaman(int $nomor): array
    {
        return ['page' => ['size' => self::UKURAN_HALAMAN, 'number' => $nomor]];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function atribut(array $item): array
    {
        return is_array($item['attributes'] ?? null) ? $item['attributes'] : $item;
    }
}
