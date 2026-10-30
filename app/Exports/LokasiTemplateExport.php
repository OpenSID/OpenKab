<?php

namespace App\Exports;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use ZipArchive;

class LokasiTemplateExport
{
    /**
     * Kolom data lokasi per baris. `Nama Desa` adalah kolom pertama (dropdown).
     *
     * @var array<int, array{key: string, label: string, required: bool}>
     */
    public const COLUMNS = [
        ['key' => 'desa', 'label' => 'Nama Desa', 'required' => true],
        ['key' => 'nama', 'label' => 'Nama Lokasi', 'required' => true],
        ['key' => 'lat', 'label' => 'Latitude', 'required' => true],
        ['key' => 'lng', 'label' => 'Longitude', 'required' => true],
        ['key' => 'keterangan', 'label' => 'Keterangan', 'required' => false],
    ];

    public const SHEET_DATA = 'Data Lokasi';

    public const SHEET_DESA = 'Daftar Desa';

    /**
     * Jumlah baris konteks sebelum baris header.
     */
    private const CONTEXT_ROWS = 5;

    /**
     * @param  array<int, array{kode_desa: string, nama_desa: string}>  $desaList
     */
    public function __construct(
        private array $desaList,
        private string $namaJenis,
        private string $namaKategori,
    ) {}

    public function write(string $filePath): void
    {
        $options = new Options;
        $options->SHOULD_PRESERVE_EMPTY_ROWS = true;

        $writer = new Writer($options);
        $writer->openToFile($filePath);

        $headerStyle = (new Style)->setFontBold()->setBackgroundColor(Color::YELLOW);
        $infoLabelStyle = (new Style)->setFontBold();
        $noteStyle = new Style;

        $writer->getCurrentSheet()->setName(self::SHEET_DATA);

        // Baris konteks (Jenis/Kategori). Nama Desa TIDAK di sini (kolom per baris).
        $writer->addRow(Row::fromValues(['Jenis Lokasi', $this->namaJenis], $infoLabelStyle));
        $writer->addRow(Row::fromValues(['Kategori Lokasi', $this->namaKategori]));
        $writer->addRow(Row::fromValues(['']));
        $writer->addRow(Row::fromValues([
            'Catatan: Pilih Nama Desa pada tiap baris data (dropdown). Kode desa ditentukan otomatis oleh sistem saat import.',
        ]));
        $writer->addRow(Row::fromValues(['']));

        // Header kolom data.
        $writer->addRow(Row::fromValues(
            array_map(fn (array $column): string => $column['label'], self::COLUMNS)
        ));

        // Sheet referensi desa.
        $writer->addNewSheetAndMakeItCurrent();
        $writer->getCurrentSheet()->setName(self::SHEET_DESA);
        $writer->addRow(Row::fromValues(['Kode Desa', 'Nama Desa'], (new Style)->setFontBold()));

        foreach ($this->desaList as $desa) {
            $writer->addRow(Row::fromValues([
                (string) ($desa['kode_desa'] ?? ''),
                (string) ($desa['nama_desa'] ?? ''),
            ]));
        }

        $writer->close();

        $this->injectDesaDropdown($filePath);
    }

    /**
     * Tambahkan data validation (dropdown) Nama Desa pada kolom pertama data.
     * OpenSpout belum mendukung data validation, sehingga XML xlsx diproses manual.
     */
    protected function injectDesaDropdown(string $filePath): void
    {
        if ($this->desaList === []) {
            return;
        }

        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            return;
        }

        $sheetPath = 'xl/worksheets/sheet1.xml';
        $xml = $zip->getFromName($sheetPath);

        if ($xml === false) {
            $zip->close();

            return;
        }

        $dataStartRow = self::CONTEXT_ROWS + 2; // baris setelah header
        $lastRow = $dataStartRow + 999;
        $lastDesaRow = count($this->desaList) + 1;
        $formula = sprintf("'%s'!\$B\$2:\$B\$%d", self::SHEET_DESA, $lastDesaRow);

        $validation = '<dataValidations count="1"><dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="1" errorStyle="stop" errorTitle="Nama Desa tidak valid" error="Pilih nama desa dari daftar." sqref="A'.$dataStartRow.':A'.$lastRow.'"><formula1>'
            .htmlspecialchars($formula, ENT_XML1 | ENT_QUOTES, 'UTF-8')
            .'</formula1></dataValidation></dataValidations>';

        // Sisipkan setelah </sheetData> tanpa preg_replace (agar `$` pada formula
        // tidak ditafsirkan sebagai backreference regex).
        $pos = strpos($xml, '</sheetData>');

        if ($pos !== false) {
            $xml = substr_replace($xml, '</sheetData>'.$validation, $pos, strlen('</sheetData>'));
            $zip->addFromString($sheetPath, $xml);
        }

        $zip->close();
    }
}
