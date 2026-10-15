<?php

declare(strict_types=1);

namespace App\Http\Requests\DataSpasial;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi penambahan sektor baru (FR-007f, spec Q5).
 *
 * ⚠️ **Kode sektor TIDAK pernah divalidasi atau diisi di sini** — kode
 * dibangkitkan oleh sisi API dan dijamin unik global (research.md R4).
 * Formulir hanya memuat kolom nama.
 *
 * ⚠️ **Keunikan nama sektor diperiksa oleh API, BUKAN di sini** (spec Q13).
 * FormRequest ini hanya menjaga bentuk: nama wajib diisi, bukan spasi saja,
 * dan maksimal 100 karakter. Bila nama sudah dipakai, API membalas `422` dan
 * pesannya diterjemahkan apa adanya (FR-021).
 *
 * ⚠️ **UTANG KONTRAK U1** — endpoint `POST /dataspasial/sektor` belum ada di
 * kontrak v0.1.0 maupun di API yang hidup.
 */
class StoreSektorRequest extends FormRequest
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
