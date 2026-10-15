<?php

declare(strict_types=1);

namespace App\Http\Requests\DataSpasial;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi perubahan sektor yang sudah ada (FR-007d-5, FR-007d-6, spec Q10).
 *
 * ⚠️ **Kode sektor TIDAK pernah divalidasi atau dikirim** — kode dibangkitkan
 * server dan bersifat baca-saja (FR-007d-5). Formulir hanya memuat nama & ikon.
 *
 * ⚠️ **Keunikan nama sektor diperiksa oleh API, BUKAN di sini** (spec Q13).
 * FormRequest ini hanya menjaga bentuk. Bila nama sudah dipakai sektor lain,
 * API membalas `422` dan pesannya diterjemahkan apa adanya (FR-021).
 *
 * ⚠️ **UTANG KONTRAK U5** — `PUT /dataspasial/sektor/{id}` belum ada di kontrak
 * v0.1.0 maupun di API yang hidup.
 */
class UpdateSektorRequest extends FormRequest
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
            'nama' => ['required', 'string', 'max:100', 'regex:/^[^\s](.*[^\s])?$/'],
            'ikon' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama sektor wajib diisi.',
            'nama.regex' => 'Nama sektor tidak boleh diawali atau diakhiri spasi.',
            'nama.max' => 'Nama sektor maksimal 100 karakter.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['nama' => 'nama sektor'];
    }
}
