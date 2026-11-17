<?php

declare(strict_types=1);

namespace App\Http\Requests\DataSpasial;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Nonaktifkan / aktifkan pengisian Data Spasial satu desa (KAB-09).
 *
 * Alasan wajib saat menonaktifkan agar operator desa membaca sebabnya di OpenSID.
 */
class UbahStatusDesaRequest extends FormRequest
{
    public const PANJANG_ALASAN = 500;

    public function authorize(): bool
    {
        // Otorisasi ditegakkan middleware `permission:dataspasial-desa-write` pada route.
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $aturanAlasan = $this->has('aktif') && $this->boolean('aktif')
            ? ['nullable', 'string', 'max:'.self::PANJANG_ALASAN]
            : ['required', 'string', 'max:'.self::PANJANG_ALASAN];

        return [
            'aktif' => ['required', 'boolean'],
            'alasan' => $aturanAlasan,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'aktif.required' => 'Status yang diminta tidak dikenal.',
            'alasan.required' => 'Alasan wajib diisi saat menonaktifkan pengisian desa.',
            'alasan.max' => 'Alasan maksimal '.self::PANJANG_ALASAN.' karakter.',
        ];
    }

    public function aktif(): bool
    {
        return $this->boolean('aktif');
    }

    public function alasan(): ?string
    {
        $alasan = $this->validated('alasan');

        return is_string($alasan) && trim($alasan) !== '' ? trim($alasan) : null;
    }
}
