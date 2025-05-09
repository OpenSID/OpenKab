<?php

namespace App\Services;

class PekerjaanService extends BaseApiService
{
    public function count()
    {
        $data = $this->apiRequestSimple('/api/v1/pekerjaan/count');
        if (! $data) {
            return collect([]);
        }

        return $data;
    }
}
