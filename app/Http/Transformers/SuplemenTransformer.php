<?php

namespace App\Http\Transformers;

use App\Models\Suplemen;
use League\Fractal\TransformerAbstract;

class SuplemenTransformer extends TransformerAbstract
{
    public function transform(Suplemen $suplemen)
    {
        // Transform data suplemen
        return [
            'id' => $suplemen->id,
            'nama' => $suplemen->nama,
            'sasaran' => unserialize(SASARAN)[$suplemen->sasaran] ?? 'Tidak Diketahui',
            'status' => unserialize(STATUS_SUPLEMEN)[$suplemen->status] ?? 'Tidak Diketahui',
            'keterangan' => $suplemen->keterangan,
            'terdata_count' => $suplemen->terdata_count,
            'aksi' => $this->generateAksiColumn($suplemen),
        ];
    }

    /**
     * Generate "aksi" column for Suplemen data.
     *
     * @param Suplemen $suplemen
     * @return string
     */
    protected function generateAksiColumn(Suplemen $suplemen): string
    {
        $aksi = '';
        $disabled = $suplemen->terdata_count > 0 ? 'disabled' : 'data-target="#confirm-delete"';

        // Rincian Data
        $aksi .= '<a href="#" class="btn bg-purple btn-sm" title="Rincian Data"><i class="fa fa-list-ol"></i></a> ';

            $aksi .= '<a href="#" class="btn bg-navy btn-sm btn-import" title="Impor Data"><i class="fa fa-upload"></i></a> ';
            $aksi .= '<a href="#" class="btn btn-warning btn-sm" title="Tanggapi Pengaduan"><i class="fa fa-pencil"></i></a> ';
        
            $aksi .= '<a href="#" class="btn bg-maroon btn-sm" title="Hapus Data" data-toggle="modal" ' . $disabled . '><i class="fa fa-trash"></i></a> ';

        return $aksi;
    }
}
