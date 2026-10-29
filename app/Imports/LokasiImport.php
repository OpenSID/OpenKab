<?php

namespace App\Imports;

use App\Exports\LokasiTemplateExport;
use App\Support\Coordinate;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;
use Throwable;

class LokasiImport
{
    /**
     * Hasil pembacaan & validasi file import.
     *
     * @var array{meta: array<string, string>, rows: array<int, array<string, mixed>>, errors: array<int, array<string, mixed>>, valid: array<int, array<string, mixed>>}
     */
    protected array $result = [
        'meta' => [],
        'rows' => [],
        'errors' => [],
        'valid' => [],
    ];

    public function __construct() {}

    /**
     * Baca dan validasi file template (multi-desa: `Nama Desa` per baris).
     *
     * @return array{meta: array<string, string>, rows: array<int, array<string, mixed>>, errors: array<int, array<string, mixed>>, valid: array<int, array<string, mixed>>}
     */
    public function process(string $filePath): array
    {
        $this->result = [
            'meta' => [],
            'rows' => [],
            'errors' => [],
            'valid' => [],
        ];

        if (! is_file($filePath)) {
            $this->result['errors'][] = ['row' => 0, 'message' => 'File tidak ditemukan.'];

            return $this->result;
        }

        try {
            $sheetRows = $this->readSheet($filePath);
        } catch (Throwable $e) {
            report($e);
            $this->result['errors'][] = ['row' => 0, 'message' => 'Format file tidak dapat dibaca. Pastikan menggunakan template yang benar (.xlsx).'];

            return $this->result;
        }

        $this->parse($sheetRows);

        return $this->result;
    }

    /**
     * @return array<int, array<int, string>>
     */
    protected function readSheet(string $filePath): array
    {
        $options = new Options;
        $options->SHOULD_PRESERVE_EMPTY_ROWS = true;

        $reader = new Reader($options);
        $reader->open($filePath);

        $rows = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                $values = [];
                foreach ($row->getCells() as $cell) {
                    $value = $cell->getValue();
                    if ($value instanceof \DateTimeInterface) {
                        $value = $value->format('Y-m-d');
                    }
                    $values[] = is_scalar($value) || $value === null ? trim((string) $value) : '';
                }
                $rows[(int) $rowNumber] = $values;
            }

            break; // Hanya sheet pertama ("Data Lokasi").
        }

        $reader->close();

        return $rows;
    }

    /**
     * @param  array<int, array<int, string>>  $sheetRows
     */
    protected function parse(array $sheetRows): void
    {
        $metaKeys = [
            'jenis lokasi' => 'jenis',
            'kategori lokasi' => 'kategori',
        ];

        $labelToKey = [];
        foreach (LokasiTemplateExport::COLUMNS as $column) {
            $labelToKey[strtolower($column['label'])] = $column['key'];
        }

        $headerRowNumber = null;

        foreach ($sheetRows as $rowNumber => $cells) {
            $first = strtolower(trim($cells[0] ?? ''));

            if (isset($metaKeys[$first])) {
                $this->result['meta'][$metaKeys[$first]] = trim($cells[1] ?? '');

                continue;
            }

            if ($first === 'nama desa') {
                $headerRowNumber = $rowNumber;
                break;
            }
        }

        if ($headerRowNumber === null) {
            $this->result['errors'][] = ['row' => 0, 'message' => 'Baris header template tidak ditemukan. Gunakan template yang diunduh dari sistem.'];

            return;
        }

        // Petakan kolom header ke key.
        $headerCells = $sheetRows[$headerRowNumber];
        $columnMap = [];
        foreach ($headerCells as $index => $label) {
            $key = $labelToKey[strtolower(trim($label))] ?? null;
            if ($key !== null) {
                $columnMap[$index] = $key;
            }
        }

        if (! in_array('desa', $columnMap, true) || ! in_array('nama', $columnMap, true) || ! in_array('lat', $columnMap, true) || ! in_array('lng', $columnMap, true)) {
            $this->result['errors'][] = ['row' => $headerRowNumber, 'message' => 'Kolom wajib template tidak lengkap (Nama Desa, Nama Lokasi, Latitude, Longitude).'];

            return;
        }

        foreach ($sheetRows as $rowNumber => $cells) {
            if ($rowNumber <= $headerRowNumber) {
                continue;
            }

            $data = [];
            $isBlank = true;
            foreach ($columnMap as $index => $key) {
                $value = trim($cells[$index] ?? '');
                if ($value !== '') {
                    $isBlank = false;
                }
                $data[$key] = $value;
            }

            if ($isBlank) {
                continue;
            }

            $data['_row'] = $rowNumber;
            $errors = $this->validateRow($data);

            $this->result['rows'][] = $data;

            if ($errors === []) {
                $this->result['valid'][] = $data;
            } else {
                $this->result['errors'][] = ['row' => $rowNumber, 'messages' => $errors];
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, string>
     */
    protected function validateRow(array $data): array
    {
        $errors = [];

        if (trim((string) ($data['desa'] ?? '')) === '') {
            $errors[] = 'Nama Desa wajib diisi.';
        }

        $nama = trim((string) ($data['nama'] ?? ''));

        if ($nama === '') {
            $errors[] = 'Nama Lokasi wajib diisi.';
        } elseif (mb_strlen($nama) > 50) {
            $errors[] = 'Nama Lokasi maksimal 50 karakter.';
        }

        if (! $this->isValidCoordinate($data['lat'] ?? '', Coordinate::MIN_LAT, Coordinate::MAX_LAT)) {
            $errors[] = 'Latitude tidak valid (harus berada di wilayah Indonesia '.Coordinate::MIN_LAT.' s.d. '.Coordinate::MAX_LAT.').';
        }

        if (! $this->isValidCoordinate($data['lng'] ?? '', Coordinate::MIN_LNG, Coordinate::MAX_LNG)) {
            $errors[] = 'Longitude tidak valid (harus berada di wilayah Indonesia '.Coordinate::MIN_LNG.' s.d. '.Coordinate::MAX_LNG.').';
        }

        return $errors;
    }

    protected function isValidCoordinate(string $value, float $min, float $max): bool
    {
        if ($value === '' || ! is_numeric($value)) {
            return false;
        }

        $number = (float) $value;

        return $number >= $min && $number <= $max;
    }
}
