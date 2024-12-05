<?php

namespace App\Http\Controllers;

use App\Models\Suplemen;
use App\Models\SuplemenTerdata;
use App\Models\Wilayah;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;

class SuplemenController extends Controller
{
    public function index()
    {
        $list_sasaran = unserialize(SASARAN);

        return view('suplemen.index', compact('list_sasaran'));
    }

    public function form()
    {
        $list_sasaran = unserialize(SASARAN);
        $attributes = unserialize(ATTRIBUTES);

        return view('suplemen.form', compact('list_sasaran', 'attributes'));
    }

    public function detail($id)
    {
        $sasaran = unserialize(SASARAN);
        $suplemen = Suplemen::findOrFail($id);
        $wilayah = Wilayah::treeAccess();

        return view('suplemen.detail', compact('id', 'sasaran', 'suplemen', 'wilayah'));
    }

    public function ekspor($id = 0)
    {
        $data_suplemen['suplemen'] = Suplemen::findOrFail($id)->toArray();
        $data_suplemen['terdata']  = SuplemenTerdata::anggota($data_suplemen['suplemen']['sasaran'], $id)->get()->toArray();

        // Nama file untuk ekspor
        $file_name = $data_suplemen['suplemen']['nama'].'_'.date('d_m_Y'). '.xlsx';

        // Path untuk menyimpan file
        $file_path = storage_path('app/exports/' . $file_name);

        // Pastikan folder 'exports' ada
        $exportDir = storage_path('app/exports');
        if (!file_exists($exportDir)) {
            // Buat folder 'exports' jika belum ada
            mkdir($exportDir, 0775, true);
        }

        // Buat instance writer
        $writer = new Writer();
        $writer->openToFile($file_path); // Gunakan openToFile daripada openToBrowser untuk menyimpan di server

        // Ubah Nama Sheet
        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Peserta');

        // Deklarasi Style
        $border = new Border(
            new BorderPart(Border::TOP, Color::GREEN, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, Color::GREEN, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT, Color::GREEN, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, Color::GREEN, Border::WIDTH_THIN, Border::STYLE_SOLID)
        );

        $headerStyle = (new Style())
            ->setBorder($border)
            ->setBackgroundColor(Color::YELLOW)
            ->setFontBold();

        $footerStyle = (new Style())
            ->setBackgroundColor(Color::LIGHT_GREEN);

        // Cetak Header Tabel
        $values        = ['Peserta', 'Nama', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat', 'Keterangan'];
        $rowFromValues = Row::fromValues($values, $headerStyle);
        $writer->addRow($rowFromValues);

        // Cetak Data Anggota Suplemen
        $data_anggota = $data_suplemen['terdata'];

        foreach ($data_anggota as $data) {
            $cells = [
                $data['nik'],
                strtoupper((string) $data['nama']),
                $data['tempatlahir'],
                tgl_indo_out($data['tanggallahir']),
                strtoupper($data['alamat'] . ' RT ' . $data['rt'] . ' / RW ' . $data['rw'] . ' dusun ' . $data['dusun']),
                empty($data['keterangan']) ? '-' : $data['keterangan'],
            ];

            $singleRow = Row::fromValues($cells);
            $writer->addRow($singleRow);
        }

        // Tambahkan Baris Kosong
        $cells = [
            '###', '', '', '', '', '',
        ];
        $singleRow = Row::fromValues($cells);
        $writer->addRow($singleRow);

        // Cetak Catatan
        $array_catatan = [
            [
                'Catatan:', '', '', '', '', '',
            ],
            [
                '1. Sesuaikan kolom peserta (A) berdasarkan sasaran : - penduduk = nik, - keluarga = no. kk', '', '', '', '', '',
            ],
            [
                '2. Kolom Peserta (A) wajib di isi', '', '', '', '', '',
            ],
            [
                '3. Kolom (B, C, D, E) diambil dari database kependudukan', '', '', '', '', '',
            ],
            [
                '4. Kolom (F) opsional', '', '', '', '', '',
            ],
        ];

        $rows_catatan = [];

        foreach ($array_catatan as $catatan) {
            $rows_catatan[] = Row::fromValues($catatan, $footerStyle);
        }
        $writer->addRows($rows_catatan);

        // Tutup Writer
        $writer->close();

        // Mengirim file ke browser jika perlu
        return response()->download($file_path);
    }

}
