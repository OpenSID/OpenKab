<?php

namespace App\Services;

class PendidikanService extends BaseApiService
{

    public function countPendidikanKK()
    {
        $data = $this->apiRequestSimple('/api/v1/pendidikan-kk/count');
        if(!$data) {
            return collect([]);
        }
        return $data;
    }

    public function countPendidikan()
    {
        $data = $this->apiRequestSimple('/api/v1/pendidikan/count');
        if(!$data) {
            return collect([]);
        }
        return $data;
    }
}