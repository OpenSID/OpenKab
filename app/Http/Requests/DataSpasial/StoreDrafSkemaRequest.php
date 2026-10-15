<?php

declare(strict_types=1);

namespace App\Http\Requests\DataSpasial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi penyimpanan draf skema (FR-017 … FR-021).
 *
 * Semua aturan di sini berjalan **sebelum** request dikirim ke API (FR-020),
 * sehingga API tidak pernah menerima kode isian bentrok atau tidak valid dari
 * pengguna ini.
 *
 * ⚠️ **Batas validasi**: tabel isian berada di API Satu Data, bukan di MySQL
 * OpenKab. Karena itu `Rule::unique()` **tidak dapat dipakai** — keunikan kode
 * dalam satu draf diperiksa dengan closure terhadap array badan yang sama
 * (FR-018: unik per sektor/draf, boleh dipakai ulang lintas versi).
 */
class StoreDrafSkemaRequest extends FormRequest
{
    /**
     * Otorisasi ditegakkan lewat `permission:dataspasial-skema-write` pada route.
     */
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
            'catatan_perubahan' => ['nullable', 'string', 'max:500'],
            'nomor_versi' => ['nullable', 'integer', 'min:1'],

            // FR-019: sebuah sektor tidak boleh memiliki nol isian.
            'isian' => ['required', 'array', 'min:1'],

            // FR-017/Q14: `kode` adalah nilai TURUNAN dari `label`, bukan
            // ketikan admin. Aturan di bawah TIDAK lagi menilai "apa yang admin
            // ketik" — ia memverifikasi INTEGRITAS nilai turunan itu, karena
            // FR-018 tetap mewajibkan kode hasil bangkit memenuhi pola
            // snake_case. Bila label tidak menghasilkan slug yang sah, kode
            // turunan akan kosong atau diawali angka dan ditolak di sini.
            'isian.*.kode' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'isian.*.label' => ['required', 'string', 'max:255'],
            'isian.*.tipe' => ['required', 'string', Rule::in(['angka', 'teks', 'pilihan', 'ya_tidak', 'tanggal'])],
            'isian.*.wajib' => ['boolean'],
            'isian.*.satuan' => ['nullable', 'string', 'max:50'],
            // FR-019: isian wajib tipe `pilihan` harus punya ≥1 butir.
            'isian.*.opsi' => ['nullable', 'array'],
            'isian.*.opsi.*' => ['string', 'max:100'],
            'isian.*.urutan' => ['required', 'integer', 'min:1'],

            // ⚠️ ASUMSI U2 (U3) — penanda tampilan & pemetaan belum ada pada
            // kontrak v0.1.0. Field di sini memakai NAMA INTERNAL; pemetaan ke
            // nama asumsi kontrak hanya terjadi di `SkemaEditorService`
            // (research.md R12).
            'isian.*.popupPeta' => ['boolean'],
            'isian.*.sebagaiFilter' => ['boolean'],
            'isian.*.sebagaiIndikator' => ['boolean'],
            // ⚠️ VERIFIKASI LANGSUNG: `sumber` adalah ENUM API
            // (`openSID`/`dataPresisi`/`impor`), bukan kode sektor.
            'isian.*.sumber' => ['nullable', 'string', Rule::in(['openSID', 'dataPresisi', 'impor'])],
            'isian.*.rujukan' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Aturan yang tidak bisa dinyatakan sebagai rule Laravel biasa.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->tolakKodeBentrok($validator);
            $this->tolakPilihanKosong($validator);
        });
    }

    /**
     * FR-018/Q17 — `label` dan `kode` yang sama lebih dari satu kali di dalam
     * SATU versi yang sedang disusun.
     *
     * ⚠️ Cakupannya **satu versi** (`sektor` + `nomor_versi`), bukan lintas
     * versi: satu request hanya berisi satu versi, dan draf berikutnya disalin
     * dari versi sebelumnya sehingga label yang sama harus boleh dipakai lagi
     * pada versi yang lebih baru (Q17).
     */
    private function tolakKodeBentrok(Validator $validator): void
    {
        $isian = $this->input('isian');

        if (! is_array($isian)) {
            return;
        }

        $terlihatKode = [];
        $terlihatLabel = [];

        foreach ($isian as $index => $baris) {
            if (! is_array($baris)) {
                continue;
            }

            $kode = trim((string) ($baris['kode'] ?? ''));

            if ($kode !== '') {
                if (isset($terlihatKode[$kode])) {
                    $validator->errors()->add(
                        "isian.{$index}.kode",
                        "Kode isian \"{$kode}\" sudah dipakai isian lain pada versi ini. "
                        .'Ubah label isian agar kode berbeda — kode tidak diberi akhiran angka otomatis.'
                    );
                } else {
                    $terlihatKode[$kode] = true;
                }
            }

            $label = trim((string) ($baris['label'] ?? ''));

            if ($label !== '') {
                if (isset($terlihatLabel[$label])) {
                    $validator->errors()->add(
                        "isian.{$index}.label",
                        "Label isian \"{$label}\" sudah dipakai isian lain pada versi ini. "
                        .'Label isian harus unik di dalam satu versi.'
                    );
                } else {
                    $terlihatLabel[$label] = true;
                }
            }
        }
    }

    /**
     * FR-019 — isian (wajib maupun tidak) tipe `pilihan` wajib punya butir.
     */
    private function tolakPilihanKosong(Validator $validator): void
    {
        $isian = $this->input('isian');

        if (! is_array($isian)) {
            return;
        }

        foreach ($isian as $index => $baris) {
            if (! is_array($baris) || ($baris['tipe'] ?? null) !== 'pilihan') {
                continue;
            }

            $opsi = array_values(array_filter(
                is_array($baris['opsi'] ?? null) ? $baris['opsi'] : [],
                static fn ($item): bool => is_string($item) && trim($item) !== '',
            ));

            if ($opsi === []) {
                $validator->errors()->add(
                    "isian.{$index}.opsi",
                    'Isian tipe "pilihan" harus memiliki minimal satu butir pilihan.'
                );
            }
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'isian.*.kode.required' => 'Kode isian wajib diisi (dibangkitkan otomatis dari label).',
            'isian.*.kode.max' => 'Kode isian maksimal 100 karakter.',
            'isian.required' => 'Sektor harus memiliki minimal satu isian.',
            'isian.min' => 'Sektor harus memiliki minimal satu isian.',
            'sektor.required' => 'Sektor wajib dipilih.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'isian' => 'isian',
            'isian.*.kode' => 'kode isian',
            'isian.*.label' => 'label isian',
            'isian.*.tipe' => 'tipe isian',
            'isian.*.opsi' => 'pilihan',
            'isian.*.urutan' => 'urutan',
        ];
    }
}
