<?php

declare(strict_types=1);

namespace App\Http\Requests\DataSpasial;

/**
 * Nonaktifkan / aktifkan pengisian Data Spasial seluruh desa pada cakupan filter (KAB-09 massal).
 *
 * Cakupan = kabupaten pengguna, dipersempit `kode_kecamatan` dari filter halaman. Karena berdampak ke
 * banyak desa sekaligus, pengguna wajib mencentang konfirmasi.
 */
class UbahStatusDesaMassalRequest extends UbahStatusDesaRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'kode_kecamatan' => ['nullable', 'string', 'max:20', 'regex:/^[0-9.]+$/'],
            'nama_kecamatan' => ['nullable', 'string', 'max:150'],
            'konfirmasi' => ['accepted'],
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'kode_kecamatan.regex' => 'Kode kecamatan harus berupa angka.',
            'konfirmasi.accepted' => 'Centang konfirmasi untuk mengubah status banyak desa sekaligus.',
        ]);
    }

    public function kodeKecamatan(): ?string
    {
        $kode = $this->validated('kode_kecamatan');

        return is_string($kode) && trim($kode) !== '' ? trim($kode) : null;
    }

    /**
     * Nama kecamatan untuk pesan flash (hanya tampilan; cakupan ditentukan kode_kecamatan).
     */
    public function namaKecamatan(): ?string
    {
        if ($this->kodeKecamatan() === null) {
            return null;
        }

        $nama = $this->validated('nama_kecamatan');

        return is_string($nama) && trim($nama) !== '' ? trim($nama) : $this->kodeKecamatan();
    }
}
