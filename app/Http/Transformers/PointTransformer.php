<?php

namespace App\Http\Transformers;

use App\Models\Point;
use League\Fractal\TransformerAbstract;

class PointTransformer extends TransformerAbstract
{
    public function transform(Point $point)
    {
        return [
            'id' => $point->id,
            'nama' => $point->nama,
            'enabled' => $point->enabled == '1' ? 'Ya' : 'Tidak',
            'path_simbol' => '<img src="'.asset('assets/img/gis/lokasi/point/'.$point->simbol).'" />', // Menggunakan asset()
            'aksi' => $this->generateAksiColumn($point),
        ];
    }

    /**
     * Generate "aksi" column for Point data.
     */
    protected function generateAksiColumn(Point $point): string
    {
        $aksi = '';
        $delete = $point->terdata_count > 0 ? '' : '<button type="button" class="btn btn-danger btn-sm hapus mr-1" data-id="'.$point->id.'" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>';

        $edit = $point->terdata_count > 0 ? '' : '<a href="'.url('point/form', $point->id).'" class="btn btn-warning btn-sm" title="Edit Data"><i class="fa fa-pencil"></i></a> ';

        // Rincian Data
        if (! $point->parent) {
            $aksi .= '<a href="'.url('point/rincian', $point->id).'" class="btn bg-purple btn-sm" title="Rincian Data"><i class="fa fa-list-ol"></i></a> ';
        }

        $aksi .= $delete;
        if ($point->enabled == Point::LOCK) {
            $aksi .= '<a href="'.url('point/lock').'/'.$point->id.'/'.Point::UNLOCK.'" class="btn bg-navy btn-sm" title="Nonaktifkan"><i class="fa fa-unlock"></i></a> ';
        } else {
            $aksi .= '<a href="'.url('point/lock').'/'.$point->id.'/'.Point::LOCK.'" class="btn bg-navy btn-sm" title="Aktifkan"><i class="fa fa-lock"></i></a> ';
        }
        $aksi .= $edit;

        return $aksi;
    }
}
