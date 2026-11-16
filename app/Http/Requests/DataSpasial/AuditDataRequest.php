<?php

declare(strict_types=1);

namespace App\Http\Requests\DataSpasial;

use App\Enums\SumberEntri;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Validasi filter halaman Log Entri Audit (KAB-08).
 *
 * Semua filter bersifat opsional — halaman boleh dibuka tanpa filter apa pun
 * (menampilkan log terbaru). Galat memakai bentuk kontrak yang sama dengan
 * Monitoring: `meta.validation` (bukan object `errors` bawaan).
 */
class AuditDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi ditegakkan middleware `permission:` pada route.
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'sumber' => ['nullable', 'string', 'in:'.implode(',', SumberEntri::values())],
            'aksi' => ['nullable', 'string', 'max:50'],
            'pelaku' => ['nullable', 'string', 'max:255'],
            'sektor' => ['nullable', 'string', 'max:10'],
            'kode_kecamatan' => ['nullable', 'string', 'max:20', 'regex:/^[0-9.]+$/'],
            'kode_desa' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'waktu_dari' => ['nullable', 'date'],
            'waktu_sampai' => ['nullable', 'date', 'after_or_equal:waktu_dari'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sumber.in' => 'Sumber harus salah satu dari: opensid, openkab.',
            'kode_kecamatan.regex' => 'Kode kecamatan harus berupa angka.',
            'kode_desa.regex' => 'Kode desa harus berupa angka.',
            'waktu_dari.date' => 'Waktu dari harus berupa tanggal yang valid.',
            'waktu_sampai.date' => 'Waktu sampai harus berupa tanggal yang valid.',
            'waktu_sampai.after_or_equal' => 'Waktu sampai harus sama atau setelah waktu dari.',
            'per_page.max' => 'Jumlah baris per halaman maksimal 100.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'meta' => ['validation' => implode(' ', $validator->errors()->all())],
        ], 422));
    }
}
