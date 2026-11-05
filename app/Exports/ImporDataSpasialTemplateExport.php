<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use ZipArchive;

class ImporDataSpasialTemplateExport
{
    public const SHEET_DATA = 'Data Impor';
    public const SHEET_DESA = 'Daftar Desa';
    public const SHEET_PETUNJUK = 'Petunjuk';

    private const CONTEXT_ROWS = 6;

    public function __construct(
        private mixed $desaList,
        private array $items,
        private string $sektor = '',
        private string $periode = '',
    ) {}

    public function write(string $filePath): void
    {
        $options = new Options;
        $options->SHOULD_PRESERVE_EMPTY_ROWS = true;

        $writer = new Writer($options);
        $writer->openToFile($filePath);

        $infoLabelStyle = (new Style)->setFontBold();

        $writer->getCurrentSheet()->setName(self::SHEET_PETUNJUK);
        $writer->addRow(Row::fromValues(['PETUNJUK PENGISIAN TEMPLATE IMPOR DATASPASIAL'], $infoLabelStyle));
        $writer->addRow(Row::fromValues(['']));
        $writer->addRow(Row::fromValues(['1. Isi kolom sesuai dengan data yang dimiliki.']));
        $writer->addRow(Row::fromValues(['2. Pilih desa pada kolom pertama dari daftar dropdown (jika tersedia).']));
        $writer->addRow(Row::fromValues(['3. Isi kode_fasilitas dan nama_fasilitas sesuai data.']));
        $writer->addRow(Row::fromValues(['4. Kolom lainnya diisi sesuai skema terbit untuk sektor yang dipilih.']));
        $writer->addRow(Row::fromValues(['5. Jangan mengubah nama sheet atau header kolom.']));
        if ($this->sektor) {
            $writer->addRow(Row::fromValues(['']));
            $writer->addRow(Row::fromValues(['Sektor: '.$this->sektor]));
        }

        $writer->addNewSheetAndMakeItCurrent();
        $writer->getCurrentSheet()->setName(self::SHEET_DATA);
        $writer->addRow(Row::fromValues(['TEMPLATE IMPOR DATASPASIAL'], $infoLabelStyle));
        $writer->addRow(Row::fromValues(['Sektor', $this->sektor]));
        if ($this->periode) {
            $writer->addRow(Row::fromValues(['Periode', $this->periode]));
        }
        $writer->addRow(Row::fromValues(['']));
        $writer->addRow(Row::fromValues(['Catatan: Pilih desa dari dropdown pada kolom pertama.']));
        $writer->addRow(Row::fromValues(['']));
        $writer->addRow(Row::fromValues(['']));

        $header = ['kode_desa', 'kode_fasilitas', 'nama_fasilitas'];
        foreach ($this->items as $item) {
            if (is_array($item)) {
                $key = $item['kode'] ?? $item['key'] ?? $item['id'] ?? $item['nama'] ?? $item['label'] ?? 'field';
            } else {
                $key = (string) $item;
            }
            $key = preg_replace('/[^A-Za-z0-9_]/', '_', strtolower((string) $key));
            $header[] = $key;
        }
        $headerStyle = (new Style)->setFontBold()->setBackgroundColor(Color::YELLOW);
        $writer->addRow(Row::fromValues($header), $headerStyle);

        $writer->addNewSheetAndMakeItCurrent();
        $writer->getCurrentSheet()->setName(self::SHEET_DESA);
        $writer->addRow(Row::fromValues(['Kode Desa', 'Nama Desa', 'Kode - Nama'], (new Style)->setFontBold()));

        $desaList = $this->desaList;
        if (is_object($desaList) && method_exists($desaList, 'toArray')) {
            $desaList = $desaList->toArray();
        }
        if (! is_array($desaList)) {
            $desaList = [];
        }

        foreach ($desaList as $desa) {
            if (is_object($desa)) {
                if (method_exists($desa, 'toArray')) {
                    $desa = $desa->toArray();
                } else {
                    $desa = (array) $desa;
                }
            }
            if (is_array($desa)) {
                $attrs = $desa['attributes'] ?? [];
                if (is_array($attrs)) {
                    $kode = $desa['kode_desa'] ?? $desa['kode'] ?? ($attrs['kode_desa'] ?? ($attrs['kode'] ?? ''));
                    $nama = $desa['nama_desa'] ?? $desa['nama'] ?? ($attrs['nama_desa'] ?? ($attrs['nama'] ?? ''));
                } else {
                    $kode = $desa['kode_desa'] ?? $desa['kode'] ?? '';
                    $nama = $desa['nama_desa'] ?? $desa['nama'] ?? '';
                }
            } else {
                continue;
            }
            $writer->addRow(Row::fromValues([(string) $kode, (string) $nama, (string) $kode.' - '.(string) $nama]));
        }

        $writer->close();
        $this->injectDesaDropdown($filePath);
    }

    protected function injectDesaDropdown(string $filePath): void
    {
        $desaList = $this->desaList;
        if (is_object($desaList) && method_exists($desaList, 'toArray')) {
            $desaList = $desaList->toArray();
        }
        if (! is_array($desaList) || $desaList === []) {
            return;
        }
        $this->desaList = $desaList; // normalize for count below

        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            return;
        }

        $sheetPath = 'xl/worksheets/sheet2.xml';
        $xml = $zip->getFromName($sheetPath);
        if ($xml === false) {
            $sheetPath = 'xl/worksheets/sheet1.xml';
            $xml = $zip->getFromName($sheetPath);
        }
        if ($xml === false) {
            $zip->close();
            return;
        }

        $dataStartRow = self::CONTEXT_ROWS + 2;
        $lastRow = $dataStartRow + 999;
        $lastDesaRow = count($this->desaList) + 1;
        $formula = sprintf("'%s'!\$C\$2:\$C\$%d", self::SHEET_DESA, $lastDesaRow);

        $validation = '<dataValidations count="1"><dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="1" errorStyle="stop" errorTitle="Desa tidak valid" error="Pilih desa dari daftar." sqref="A'.$dataStartRow.':A'.$lastRow.'"><formula1>'
            .htmlspecialchars($formula, ENT_XML1 | ENT_QUOTES, 'UTF-8')
            .'</formula1></dataValidation></dataValidations>';

        $pos = strpos($xml, '</sheetData>');
        if ($pos !== false) {
            $formulaBlock = '';
            for ($r = $dataStartRow; $r <= $dataStartRow + 49; $r++) {
                $formulaBlock .= '<row r="'.$r.'"><c r="B'.$r.'" t="str"><f>IFERROR(MID(A'.$r.',FIND(" - ",A'.$r.')+3,255),"")</f></c></row>';
            }
            $xml = substr_replace($xml, '</sheetData>'.$formulaBlock.$validation, $pos, strlen('</sheetData>'));
            $zip->addFromString($sheetPath, $xml);
        } else {
            $zip->close();
            return;
        }

        $zip->close();
    }
}
