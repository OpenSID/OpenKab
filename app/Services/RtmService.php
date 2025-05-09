<?php

namespace App\Services;

class RtmService extends BaseApiService
{
    public function store($data)
    {
        $data = $this->apiPost('/api/v1/rtm/store', $data);
        if (! $data) {
            return collect([]);
        }

        return $data;
    }
}
