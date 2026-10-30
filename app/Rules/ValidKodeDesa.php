<?php

namespace App\Rules;

use App\Services\ConfigApiService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidKodeDesa implements ValidationRule
{
    /**
     * Pastikan kode desa ada pada daftar desa API database gabungan.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        // Sesi desa (di-set server-side oleh WilayahMiddleware) dipercaya.
        if ((string) session('desa.kode_desa') === (string) $value) {
            return;
        }

        $exists = collect((new ConfigApiService)->desa())
            ->contains(fn ($desa) => (string) ($desa->kode_desa ?? '') === (string) $value);

        if (! $exists) {
            $fail('Desa yang dipilih tidak valid.');
        }
    }
}
