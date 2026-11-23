<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyisipkan item Dasbor dan Dasbor Demografi ke menu setiap group yang sudah ada.
 *
 * Sebelumnya kedua item ini berada di config/adminlte.php. Sekarang sidebar mengambil
 * menu dari group (team.menu / team.menu_order) dengan gate 'can', sehingga data menu
 * group lama perlu diperbarui agar item tetap tampil. Item yang sudah ada dilewati.
 */
return new class extends Migration
{
    /**
     * Role yang menerima menu Dasbor. Team (group) dipilih dari role ini: nama role
     * sama dengan nama team (administrator, kabupaten, superadmin_daerah).
     */
    private array $roleTujuan = ['administrator', 'kabupaten', 'superadmin_daerah'];

    /**
     * ID team yang memiliki salah satu role tujuan.
     */
    private function teamTujuan(): array
    {
        return DB::table('roles')
            ->whereIn('name', $this->roleTujuan)
            ->where('guard_name', 'web')
            ->whereNotNull('team_id')
            ->distinct()
            ->pluck('team_id')
            ->all();
    }

    /**
     * Item yang disisipkan, sama dengan entri di Modul::Menu.
     */
    private function itemDasbor(): array
    {
        return [
            [
                'text' => 'Dasbor',
                'icon' => 'fa fa-home',
                'permission' => 'dasbor',
                'can' => 'dasbor-read',
                'url' => 'dasbor',
            ],
            [
                'text' => 'Dasbor Demografi',
                'icon' => 'fa fa-home',
                'permission' => 'dasbor-demografi',
                'can' => 'dasbor-demografi-read',
                'url' => 'dasbor-demografi',
            ],
        ];
    }

    public function up(): void
    {
        $kolom = ['menu'];
        if (Schema::hasColumn('team', 'menu_order')) {
            $kolom[] = 'menu_order';
        }

        DB::table('team')->select(array_merge(['id'], $kolom))->whereIn('id', $this->teamTujuan())->orderBy('id')->chunkById(100, function ($teams) use ($kolom) {
            foreach ($teams as $team) {
                $update = [];
                foreach ($kolom as $field) {
                    $menu = $this->decode($team->{$field});
                    if ($menu === null) {
                        continue; // null atau bukan JSON valid: biarkan
                    }
                    $baru = $this->sisipkan($menu);
                    if ($baru !== $menu) {
                        $update[$field] = $this->encode($baru);
                    }
                }
                if ($update) {
                    DB::table('team')->where('id', $team->id)->update($update);
                }
            }
        }, 'id');
    }

    public function down(): void
    {
        $kolom = ['menu'];
        if (Schema::hasColumn('team', 'menu_order')) {
            $kolom[] = 'menu_order';
        }

        DB::table('team')->select(array_merge(['id'], $kolom))->whereIn('id', $this->teamTujuan())->orderBy('id')->chunkById(100, function ($teams) use ($kolom) {
            foreach ($teams as $team) {
                $update = [];
                foreach ($kolom as $field) {
                    $menu = $this->decode($team->{$field});
                    if ($menu === null) {
                        continue;
                    }
                    $baru = array_values(array_filter($menu, fn ($item) => ! in_array($item['permission'] ?? null, ['dasbor', 'dasbor-demografi'], true)));
                    if ($baru !== $menu) {
                        $update[$field] = $this->encode($baru);
                    }
                }
                if ($update) {
                    DB::table('team')->where('id', $team->id)->update($update);
                }
            }
        }, 'id');
    }

    /**
     * Sisipkan item Dasbor di awal menu jika belum ada (dicek dari key permission).
     */
    private function sisipkan(array $menu): array
    {
        $sudahAda = collect($menu)->pluck('permission')->filter()->all();
        $tambahan = array_values(array_filter(
            $this->itemDasbor(),
            fn ($item) => ! in_array($item['permission'], $sudahAda, true)
        ));

        return array_merge($tambahan, $menu);
    }

    private function decode($nilai): ?array
    {
        if ($nilai === null || $nilai === '') {
            return null;
        }
        $hasil = is_array($nilai) ? $nilai : json_decode((string) $nilai, true);

        return is_array($hasil) ? $hasil : null;
    }

    private function encode(array $menu): string
    {
        return json_encode($menu, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
};
