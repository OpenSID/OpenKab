<?php

declare(strict_types=1);

namespace App\Http\Requests\DataSpasial;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Validasi konteks halaman Monitoring (FR-003: sektor + kecamatan wajib).
 *
 * Galat 422 memakai bentuk kontrak `contracts/monitoring-view-contract.md` §2
 * (`meta.validation`) — bukan object `errors` bawaan.
 */
class MonitoringDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi ditegakkan middleware `permission:` pada route.
        return true;
    }

    /**
     * `kode_kecamatan` OPSIONAL (keputusan 2026-10-21): kosong = "Semua
     * Kecamatan". Hanya `sektor` yang wajib. Tanpa filter status/nama desa.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'sektor' => ['required', 'string', 'max:10'],
            'kode_kecamatan' => ['nullable', 'string', 'max:20'],
            'periode' => ['nullable', 'string', 'date_format:Y-m'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sektor.required' => 'Pilih sektor terlebih dahulu.',
            'periode.date_format' => 'Periode harus dalam format YYYY-MM.',
            'per_page.max' => 'Jumlah baris per halaman maksimal 200.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'meta' => ['validation' => implode(' ', $validator->errors()->all())],
        ], 422));
    }
}
