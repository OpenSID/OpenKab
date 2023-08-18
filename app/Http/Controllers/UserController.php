<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Enums\StatusEnum;
use App\Models\Team;
use App\Models\User;
use App\Models\UserTeam;
use App\Traits\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    use UploadedFile;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('user.index');
    }

    public function getUsers(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(User::with('team')->get())
                ->addIndexColumn()
                ->addColumn('aksi', function ($row) {
                    if (! auth()->guest()) {
                        $data['edit'] = route('users.edit', $row->id);
                        if ($row->id !== User::superAdmin()) {
                            $data['delete'] = route('users.destroy', $row->id);
                            if ($row->active == StatusEnum::aktif) {
                                $data['deactive'] = route('users.status', [$row->id, StatusEnum::tidakAktif]);
                            } else {
                                $data['active'] = route('users.status', [$row->id, StatusEnum::aktif]);
                            }
                        }
                    }

                    return view('partials.aksi', $data);
                })
                ->editColumn('active', function ($row) {
                    if ($row->active == StatusEnum::tidakAktif) {
                        return '<span class="badge badge-danger">Tidak Aktif</span>';
                    } else {
                        return '<span class="badge badge-success">Aktif</span>';
                    }
                })
                ->rawColumns(['active'])
                ->make();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = null;
        $groups = Team::get();
        $team = false;

        return view('user.create', compact('user', 'groups', 'team'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\UserRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        try {
            $data = $request->validated();
            $insertData = [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'company' => $data['company'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'active' => 1,
            ];

            if($request->file('foto')){
                $this->pathFolder .= '/profile';
                $insertData['foto'] = $this->uploadFile($request, 'foto');
            }
            $user = User::create($insertData);

            // joinkan user ke group
            UserTeam::create([
                'id_user' => $user->id,
                'id_team' => $data['group'],
            ]);

            // assign role berdasarkan team
            setPermissionsTeamId($request['group']);
            foreach ($user->team->first()->menu as $menu) {
                $user->assignRole($menu['role']);
                if (isset($menu['submenu'])) {
                    foreach ($menu['submenu'] as $submenu) {
                        $user->assignRole($submenu['role']);
                    }
                }
            }

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan!');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::with('team')->where('id', $id)->first();
        $groups = Team::get();
        $team = $user->team->first()->id ?? false;

        return view('user.edit', compact('user', 'groups', 'team'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function profile($id)
    {
        $user = User::find($id);

        return view('user.profile', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        try {
            $updateData = [
                'name' => $request->get('name'),
                'username' => $request->get('username') ?? $user->username,
                'email' => $request->get('email'),
                'company' => $request->get('company'),
                'phone' => $request->get('phone'),
            ];
            if($request->file('foto')){
                $this->pathFolder .= '/profile';
                $updateData['foto'] = $this->uploadFile($request, 'foto');
            }
            $user->update($updateData);

            $routeCurrent = Route::currentRouteName();
            if ( $routeCurrent == 'profile.update') {
                return redirect()->route('profile.edit', Auth::id())->with('success', 'Data profil berhasil diubah!');
            }

            // update user team
            $user_team = UserTeam::find($user->id);
            if($user_team){
                setPermissionsTeamId($user_team->id_team);
                $user->roles()->detach();
                $user_team->id_team = $data['group'];
                $user_team->save();

                foreach ($user->team->first()->menu as $menu) {
                    $user->assignRole($menu['role']);
                    if (isset($menu['submenu'])) {
                        foreach ($menu['submenu'] as $submenu) {
                            $user->assignRole($submenu['role']);
                        }
                    }
                }
            }


            setPermissionsTeamId($request['group']);
            // assign role berdasarkan team

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil diubah!');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('users.index')->with('error', 'Pengguna gagal dihapus!');
        }

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus!');
    }

    /**
     * Update status resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function status($id, $status, User $user)
    {
        try {
            $user->where('id', '!=', $user->superAdmin())->findOrFail($id)->update(['active' => $status]);
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('users.index')->with('error', 'Status gagal diubah!');
        }

        return redirect()->route('users.index')->with('success', 'Status berhasil diubah!');
    }
}
