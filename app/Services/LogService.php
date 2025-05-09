<?php

namespace App\Services;

class LogService extends BaseApiService
{
    public function generateLogPenduduk($config_id)
    {
        $data = $this->apiRequestSimple("/api/v1/log/penduduk/{$config_id}");
        if (! $data) {
            return collect([]);
        }

        return $data;
    }

    public function generateLogKeluarga($config_id)
    {
        $data = $this->apiRequestSimple("/api/v1/log/keluarga/{$config_id}");
        if (! $data) {
            return collect([]);
        }

        return $data;
    }
}
