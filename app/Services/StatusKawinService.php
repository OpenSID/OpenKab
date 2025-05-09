<?php

namespace App\Services;

class StatusKawinService extends BaseApiService
{
    public function count()
    {
        $data = $this->apiRequestSimple('/api/v1/status-kawin/count');
        if(!$data) {
            return collect([]);
        }
        return $data;
    }
}