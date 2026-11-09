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
 * ⚠️ UTANG KONTRAK U6 — endpoint tersebut belum ada di kontrak v0.1.0 maupun
 * di API yang hidup.
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
            'catatan_perubahan' => ['nullable', 'string', 'max:500'],
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
            'catatan_perubahan.max' => 'Catatan perubahan maksimal 500 karakter.',
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
        ];
    }
}
