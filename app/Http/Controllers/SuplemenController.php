<?php

namespace App\Http\Controllers;

use App\Services\SuplemenService;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class SuplemenController extends Controller
{
    protected SuplemenService $service;

    public function __construct(SuplemenService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $list_sasaran = unserialize(SASARAN);

        return view('suplemen.index', compact('list_sasaran'));
    }

    public function form($id = '')
    {
        $list_sasaran = unserialize(SASARAN);
        $attributes = unserialize(ATTRIBUTES);

        if ($id) {
            $action = 'Ubah';
            $form_action = '/api/v1/suplemen/update/'.$id;
            $suplemen = $this->service->suplemenById($id);
            
            return view('suplemen.edit', compact('list_sasaran', 'attributes', 'action', 'form_action', 'suplemen'));
        } else {
            $action = 'Tambah';
            $form_action = '/api/v1/suplemen';
            $suplemen = null;

            return view('suplemen.form', compact('list_sasaran', 'attributes', 'action', 'form_action', 'suplemen'));
        }
    }

    public function detail($id)
    {
        $sasaran = unserialize(SASARAN);
        $suplemen = $this->service->suplemenById($id);

        return view('suplemen.detail', compact('id', 'sasaran', 'suplemen'));
    }

    public function ekspor($id = 0)
    {
        $suplemen = $this->service->suplemenById($id);
        if (! $suplemen) {
            abort(404);
        }

        $data_suplemen['suplemen'] = (array) $suplemen;
        $data_suplemen['terdata'] = $this->service->terdata($suplemen->sasaran, $id)->toArray();

        $formIsian = is_array($suplemen->form_isian) ? $suplemen->form_isian : json_decode($suplemen->form_isian, true);

        $file_name = $suplemen->nama.'_'.date('d_m_Y').'.xlsx';
        $file_path = storage_path('app/exports/'.$file_name);

        $exportDir = storage_path('app/exports');
        if (! file_exists($exportDir)) {
            mkdir($exportDir, 0775, true);
        }

        $writer = new Writer;
        $writer->openToFile($file_path);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Peserta');

        $border = new Border(
            new BorderPart(Border::TOP, Color::GREEN, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, Color::GREEN, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT, Color::GREEN, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, Color::GREEN, Border::WIDTH_THIN, Border::STYLE_SOLID)
        );

        $headerStyle = (new Style)
            ->setBorder($border)
            ->setBackgroundColor(Color::YELLOW)
            ->setFontBold();

        $footerStyle = (new Style)
            ->setBackgroundColor(Color::LIGHT_GREEN);

        $values = ['Peserta', 'Nama', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat', 'Keterangan'];

        if ($formIsian) {
            foreach ($formIsian as $field) {
                $values[] = $field['label_kode'];
            }
        }

        $rowFromValues = Row::fromValues($values, $headerStyle);
        $writer->addRow($rowFromValues);

        $data_anggota = $data_suplemen['terdata'];

        foreach ($data_anggota as $data) {
            $cells = [
                $data['nik'],
                strtoupper((string) $data['nama']),
                $data['tempatlahir'],
                tgl_indo_out($data['tanggallahir']),
                strtoupper($data['alamat'].' RT '.$data['rt'].' / RW '.$data['rw'].' dusun '.$data['dusun']),
                empty($data['keterangan']) ? '-' : $data['keterangan'],
            ];

            $dataFormIsian = is_array($data['data_form_isian']) ? $data['data_form_isian'] : json_decode($data['data_form_isian'], true);

            if ($formIsian) {
                foreach ($formIsian as $field) {
                    $kode = $field['nama_kode'];
                    $cells[] = isset($dataFormIsian[$kode]) ? $dataFormIsian[$kode] : 'Tidak Ada Data';
                }
            }

            $singleRow = Row::fromValues($cells);
            $writer->addRow($singleRow);
        }

        $cells = [
            '###', '', '', '', '', '',
        ];
        $singleRow = Row::fromValues($cells);
        $writer->addRow($singleRow);

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

        $writer->close();

        return response()->download($file_path);
    }

    public function daftar($id = 0, $aksi = '')
    {
        if ($id > 0) {
            $suplemen = $this->service->suplemenById($id);
            if (! $suplemen) {
                abort(404);
            }

            $data['suplemen'] = (array) $suplemen;
            $data['terdata'] = $this->service->terdata($suplemen->sasaran, $id)->toArray();
            $data['sasaran'] = unserialize(SASARAN);

            $terdataPertama = $this->service->terdata($suplemen->sasaran, $id)->first();

            if ($terdataPertama && ! empty($terdataPertama->config_id)) {
                $config = $this->service->configDesa(['filter[id]' => $terdataPertama->config_id])->first();
                $data['nama_desa'] = $config->nama_desa ?? '';
                $data['nama_kecamatan'] = $config->nama_kecamatan ?? '';
                $data['nama_kabupaten'] = $config->nama_kabupaten ?? '';
            } else {
                $data['nama_desa'] = '';
                $data['nama_kecamatan'] = '';
                $data['nama_kabupaten'] = '';
            }

            $data['aksi'] = $aksi;

            $data['file'] = 'Laporan Suplemen '.$data['suplemen']['nama'];
            $data['isi'] = 'suplemen.cetak';
            $data['letak_ttd'] = ['2', '2', '3'];

            return view('layouts.components.format_cetak', $data);
        }

        return false;
    }
}
