<?php

namespace App\Http\Controllers\Api;

use App\Enums\Modul;
use App\Http\Repository\TeamRepository;
use App\Http\Requests\TeamRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
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

    public function create(TeamRequest $request)
    {
        $data = $request->all();
        // buat team
        $team = Team::create([
            'name' => $data['nama'],
            'menu' => $data['menu'],
        ]);
        Role::firstOrCreate(
            [
                'name' => $data['nama'],
                'team_id' => $team->id,
                'guard_name' => 'web',
            ]
        );

        setPermissionsTeamId($team->id);

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
        // buat role
        $role = Role::create(
            [
                'name' => $data['name'],
                'team_id' => $team['id'],
                'guard_name' => 'web',
            ]
        );
        $permissions = $this->collectPermissions($data);

        $role->syncPermissions($permissions);
        activity('data-log')->event('created')->withProperties($request)->log('Pengaturan Group');

        return response()->json([
            'success' => true,
        ], Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        // ambil
        $data = $request->all();
        Team::where('id', $id)->update([
            'name' => $data['name'],
            'menu' => $data['menu'],
        ]);

        $role = Role::firstOrCreate(
            [
                'name' => $data['name'],
                'team_id' => $id,
                'guard_name' => 'web',
            ]
        );

        // set team
        setPermissionsTeamId($id);
        $permissions = $this->collectPermissions($data);

        $role->syncPermissions($permissions);
        activity('data-log')->event('updated')->withProperties($request)->log('Pengaturan Group');

        return response()->json([
            'success' => true,
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        return $this->fractal($this->team->listTeam()->with(['role', 'role.permissions'])->where('id', $id)->first(), function ($team) {
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
        activity('data-log')->event('deleted')->withProperties($request)->log('Pengaturan Group');

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

    private function collectPermissions($data)
    {
        $permissions = [];
        foreach ($data['menu'] as $main_menu) {
            foreach (Modul::permision as $permission) {
                $permissionName = $main_menu['permission'].'-'.$permission;
                Permission::findOrCreate($permissionName, 'web');
                if (isset($main_menu[$permissionName])) {
                    if ($main_menu[$permissionName] == 'true') {
                        $permissions[] = $permissionName;
                    }
                }
            }
            if (isset($main_menu['submenu'])) {
                foreach ($main_menu['submenu'] as $sub_menu) {
                    foreach (Modul::permision as $permission) {
                        $permissionName = $sub_menu['permission'].'-'.$permission;
                        Permission::findOrCreate($permissionName, 'web');
                        if (isset($sub_menu[$permissionName])) {
                            if ($sub_menu[$permissionName] == 'true') {
                                $permissions[] = $permissionName;
                            }
                        }
                    }
                }
            }
        }

        return $permissions;
    }
}
