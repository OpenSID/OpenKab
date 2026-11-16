<?php

declare(strict_types=1);

namespace App\Http\Requests\DataSpasial;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi penerbitan draf skema (FR-041, FR-042).
 *
 * ⚠️ **Hanya memvalidasi bentuk, bukan keunikan.** Pencemian keunikan Against
 * data yang dimiliki API TIDAK dilakukan di sini — penamaan dan keunikan
 * menjadi tanggung jawab sisi API (spec Q13/Q14). FormRequest ini hanya
 * menjaga agar permintaan yang bentuknya salah tidak pernah dikirim.
 *
 * ⚠️ `sektor` yang dikirim adalah **kode** sektor karena `{sektor}` pada path
 * `POST /dataspasial/skema/{sektor}/terbit` adalah kode sektor.
 *
 * ⚠️ Endpoint `POST /dataspasial/skema/{sektor}/terbit` **sudah ada** di API yang
 * hidup (verifikasi 2026-10-16): `409` bila tidak ada draf aktif, `404` bila sektor tidak
 * dikenal — lihat `specs/005-penerbitan-adopsi-skema/contracts/penerbitan-adopsi-api.md` §6.
 *
 * ⚠️ `diterbitkan_oleh` **tidak pernah** divalidasi di sini maupun dikirim ke API:
 * identitas penerbit diisi server dari pengguna terautentikasi agar jejak audit tidak
 * dapat dipalsukan dari antarmuka (FR-019).
 */
class TerbitSkemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sektor' => ['required', 'string', 'max:100'],
            'skema' => ['nullable', 'integer', 'min:1'],
            // FR-018: catatan perubahan wajib diisi (5–255 karakter) — jejak
            // perubahan versi tidak boleh kosong.
            'catatan_perubahan' => ['required', 'string', 'min:5', 'max:255'],
            // FR-031/FR-032: pengakuan bentrok. Opsional di sini karena
            // keharusannya bergantung pada hasil peringatan di layar; lapisan
            // controller menolak bila pengakuan diwajibkan tetapi absen.
            'pengakuan_bentrok' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sektor.required' => 'Sektor wajib diisi.',
            'skema.integer' => 'Skema tidak valid.',
            'skema.min' => 'Skema tidak valid.',
            'catatan_perubahan.required' => 'Catatan perubahan wajib diisi.',
            'catatan_perubahan.min' => 'Catatan perubahan minimal 5 karakter.',
            'catatan_perubahan.max' => 'Catatan perubahan maksimal 255 karakter.',
            'pengakuan_bentrok.boolean' => 'Pengakuan bentrok tidak valid.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'sektor' => 'sektor',
            'skema' => 'skema',
            'catatan_perubahan' => 'catatan perubahan',
            'pengakuan_bentrok' => 'pengakuan bentrok',
        ];
    }
}
