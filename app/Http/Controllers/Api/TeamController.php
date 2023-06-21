<?php

namespace App\Http\Controllers\Api;

use App\Enums\Modul;
use App\Http\Repository\TeamRepository;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class TeamController extends Controller
{
    public function __construct(protected TeamRepository $team)
    {
    }

    public function index()
    {
        return $this->fractal($this->team->listTeam()->jsonPaginate(), function ($team) {
            return $team->toArray();
        }, 'team')->respond();
    }

    public function create(Request $request)
    {
        $data = $request->all();
        // buat team
        $team = Team::create([
            'name' => $data['nama'],
            'menu' => $data['menu'],
        ]);

        setPermissionsTeamId($team->id);

        foreach ($data['menu'] as $menu1) {
            Role::create(
                [
                    'name' => $menu1['role'],
                    'team_id' => $team->id,
                    'guard_name' => 'web',
                ]
            );
        }

        return response()->json([
            'success' => true,
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $menu = $data['menu'];

        // buat team terlebih dahulu
        $team = Team::create([
            'name' => $data['name'],
            'menu' => $menu,
        ]);
        setPermissionsTeamId($team->id);

        foreach ($menu as $main_menu) {
            // buat role
            Role::create(
                [
                    'name' => $main_menu['role'],
                    'team_id' => $team['id'],
                    'guard_name' => 'web',
                ]
            );

            if (isset($main_menu['submenu'])) {
                foreach ($main_menu['submenu'] as $sub_menu) {

                    Role::create(
                        [
                            'name' => $sub_menu['role'],
                            'team_id' => $team['id'],
                            'guard_name' => 'web',
                        ]
                    );
                }
            }
        }

        return response()->json([
            'success' => true,
        ], Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        // ambil

        $users = User::with('team')
        ->whereHas('team', function ($q) use ($id) {
            return $q->where('id', $id);
        })->get();

        $data = $request->all();
        Team::where('id', $id)->update([
            'name' => $data['name'],
            'menu' => $data['menu'],
        ]);

        // hapus role
        Role::where('team_id', $id)->delete();

        // set team
        setPermissionsTeamId($id);

        foreach ($data['menu'] as $main_menu) {
            // buat role
            $role = Role::create(
                [
                    'name' => $main_menu['role'],
                    'team_id' => $id,
                    'guard_name' => 'web',
                ]
            );

            foreach ($users as $user) {
                $user->assignRole($role->id);
            }

            if (isset($main_menu['submenu'])) {
                foreach ($main_menu['submenu'] as $sub_menu) {
                    $role = Role::create(
                        [
                            'name' => $sub_menu['role'],
                            'team_id' => $id,
                            'guard_name' => 'web',
                        ]
                    );
                    foreach ($users as $user) {
                        $user->assignRole($role->id);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        return $this->fractal($this->team->listTeam()->where('id', $id)->first(), function ($team) {
            return $team->toArray();
        }, 'team')->respond();
    }

    public function delete(Request $request)
    {
        $id = (int) $request->id;
        // cek user pada team tersebut
        $hitung_pengguna = User::with('team')
        ->whereHas('team', function ($q) use ($id) {
            return $q->where('id', $id);
        })->count();

        if ($hitung_pengguna > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Group tidak kosong, Silahkan kosongkan terlebih dahulu.',
            ], Response::HTTP_OK);
        }

        Team::Where('id', $id)->delete();

        return response()->json([
            'success' => true,
        ], Response::HTTP_OK);
    }

    public function menu()
    {
        return response()->json([
            'success' => true,
            'data' => Modul::Menu,
        ], Response::HTTP_OK);
    }
}
