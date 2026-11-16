<?php

declare(strict_types=1);

namespace App\Http\Requests\DataSpasial;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi filter Daftar Data Hasil Impor (fitur 009).
 *
 * Otorisasi ditegakkan middleware `permission:dataspasial-impor-read` pada route.
 */
class ListImporRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'sektor_id' => ['required', 'integer', 'min:1'],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'],
            'versi' => ['nullable', 'integer', 'min:1'],
            'kode_kecamatan' => ['nullable', 'string', 'max:10'],
            'kode_desa' => ['nullable', 'string', 'max:10'],
            'isian_kode' => ['nullable', 'array'],
            'isian_kode.*' => ['string', 'max:100'],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
