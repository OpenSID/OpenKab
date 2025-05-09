<?php

namespace App\Services;

class KelasSosialService extends BaseApiService
{
    public function count()
    {
        $data = $this->apiRequestSimple('/api/v1/kelas-sosial/count');
        if (! $data) {
            return collect([]);
        }

        return $data;
    }
}
