<?php

namespace App\Services;

class PemetaanService extends BaseApiService
{
    public function getAllPoint(array $query = []): array
    {
        return $this->apiRequest('/api/v1/point', $query);
    }

    public function pointLock(array $data, string $id): ?array
    {
        return $this->apiPut("/api/v1/point/lock/{$id}", $data);
    }

    public function pointUpdate(array $data, string $id): ?array
    {
        return $this->apiPut("/api/v1/point/update/{$id}", $data);
    }

    public function pointStore(array $data): ?array
    {
        return $this->apiPost('/api/v1/point/store', $data);
    }

    public function getAllPlan(array $query = []): ?array
    {
        $data = $this->apiGet('/api/v1/plan', $query);

        return is_array($data) ? $data : null;
    }

    public function planStore(array $data): ?array
    {
        return $this->apiPost('/api/v1/plan/store', $data);
    }

    /**
     * Kirim seluruh baris import sekaligus (batch) ke API database gabungan.
     *
     * @param  array<string, mixed>  $payload
     */
    public function planImport(array $payload): array
    {
        $result = $this->apiPost('/api/v1/plan/import', $payload);

        return is_array($result) ? $result : [];
    }

    /**
     * Hapus beberapa lokasi sekaligus (batch) via API database gabungan.
     * Dikirim sebagai query string array `ids[]` pada endpoint `/api/v1/plan/`.
     *
     * @param  array<int, int|string>  $ids
     * @return array<string, mixed>
     */
    public function planBulkDelete(array $ids): array
    {
        $query = implode('&', array_map(
            fn (int|string $id): string => 'ids[]='.urlencode((string) $id),
            array_values($ids),
        ));

        $result = $this->apiDelete('/api/v1/plan/?'.$query);

        return is_array($result) ? $result : [];
    }

    public function planUpdate(array $data, string $id): ?array
    {
        return $this->apiPut("/api/v1/plan/update/{$id}", $data);
    }

    public function planDelete(string $id): bool
    {
        $this->apiDelete("/api/v1/plan/hapus/{$id}");

        return true;
    }

    public function planById(string $id): ?array
    {
        $result = $this->getAllPlan(['filter[id]' => $id]);

        if (is_array($result) && count($result) > 0) {
            return (array) $result[0];
        }

        return null;
    }
}
