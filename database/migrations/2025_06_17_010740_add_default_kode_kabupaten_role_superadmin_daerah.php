<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $superadminDaerah = User::where('email', 'daerah@opendesa.id')->first();

        if ($superadminDaerah) {
            $superadminDaerah->update([
                'kode_kabupaten' => session('kabupaten.kode_kabupaten') ?? null,
            ]);

            $team = Team::where('name', 'kabupaten')->first();

            if ($team) {
                // Set team ID agar semua query Spatie team-aware
                setPermissionsTeamId($team->id);

                // Pastikan role kabupaten memang ada di team tersebut
                $roleKabupaten = Role::where([
                    'name' => 'kabupaten',
                    'team_id' => $team->id,
                ])->first();

                if ($roleKabupaten) {
                    // Ambil user yang punya role kabupaten di team ini
                    $kabupatenUsers = User::role('kabupaten')->get();

                    foreach ($kabupatenUsers as $user) {
                        $user->update([
                            'kode_kabupaten' => $superadminDaerah->kode_kabupaten,
                        ]);
                    }
                } else {
                    logger()->warning("Role 'kabupaten' tidak ditemukan di team_id {$team->id}");
                }
            } else {
                logger()->warning("Superadmin daerah tidak punya team_id");
            }
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
