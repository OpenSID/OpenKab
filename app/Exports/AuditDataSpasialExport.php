<?php

declare(strict_types=1);

namespace App\Exports;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Ekspor log entri audit DataSpasial (KAB-08) ke berkas Excel (.xlsx).
 *
 * Kolom berkas **sama persis** dengan kolom tabel di layar (FR: "Isi ekspor
 * sama dengan tampilan di layar") — nilai diambil dari baris yang sudah
 * dinormalisasi service, bukan dihitung ulang di sini.
 */
class AuditDataSpasialExport
{
    public const SHEET_ENTRI = 'Log Entri';

    /**
     * @param  array<int, array<string, mixed>>  $baris
     * @param  array<string, mixed>  $filter
     */
    public function __construct(
        private readonly array $baris,
        private readonly array $filter = [],
        private readonly bool $terpotong = false,
    ) {}

    public function write(string $filePath): void
    {
        $options = new Options;
        $options->SHOULD_PRESERVE_EMPTY_ROWS = true;

        $writer = new Writer($options);
        $writer->openToFile($filePath);
        $writer->getCurrentSheet()->setName(self::SHEET_ENTRI);

        $headerStyle = (new Style)->setFontBold()->setBackgroundColor(Color::YELLOW);
        $writer->addRow(Row::fromValues([
            'Waktu', 'Pelaku', 'Sumber', 'Aksi', 'Sasaran', 'Perubahan',
        ], $headerStyle));

        foreach ($this->baris as $baris) {
            $writer->addRow(Row::fromValues([
                (string) ($baris['waktu'] ?? ''),                
                (string) ($baris['pelaku_nama'] ?? ''),
                (string) ($baris['sumber_label'] ?? ''),                
                (string) ($baris['aksi_label'] ?? ''),
                trim(((string) ($baris['sektor_nama'] ?? '')).' '.((string) ($baris['sektor_kode'] ?? ''))),
                $this->teksPerubahan($baris),
            ]));
        }

        $writer->close();
    }

    /**
     * Teks ringkas perubahan (sebelum → sesudah) per isian.
     *
     * @param  array<string, mixed>  $baris
     */
    private function teksPerubahan(array $baris): string
    {
        $perubahan = $baris['perubahan'] ?? [];

        if (! is_array($perubahan) || $perubahan === []) {
            return '';
        }

        $potongan = [];

        foreach (array_slice($perubahan, 0, 5) as $item) {
            $potongan[] = sprintf(
                '%s: %s → %s',
                (string) ($item['label'] ?? ''),
                (string) ($item['sebelum'] ?? '—'),
                (string) ($item['sesudah'] ?? '—')
            );
        }

        return implode('; ', $potongan);
    }
}
