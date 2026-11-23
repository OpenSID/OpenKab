<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Enums\StatusEnum;
use App\Models\Team;
use App\Models\User;
use App\Models\UserTeam;
use App\Services\CakupanPenggunaService;
use App\Services\ConfigApiService;
use App\Services\DataSpasialApiService;
use App\Services\PasswordResetByAdminService;
use App\Support\KodeWilayah;
use App\Traits\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    use UploadedFile;

    protected $permission = 'pengaturan-users';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $listPermission = $this->generateListPermission();

        return view('user.index')->with($listPermission);
    }

    public function getUsers(Request $request)
    {
        if ($request->ajax()) {
            $permission = $this->generateListPermission();

            $allKabupaten = (new ConfigApiService)->kabupaten();
            $kabupatenMap = collect($allKabupaten)->keyBy('kode_kabupaten');

            return DataTables::of(User::with(['team'])->visibleForAuthenticatedUser()->get())
                ->addIndexColumn()
                ->addColumn('nama_kabupaten', function ($row) use ($kabupatenMap) {
                    if (empty($row->kode_kabupaten)) {
                        return '-';
                    }

                    return $kabupatenMap->get($row->kode_kabupaten)?->nama_kabupaten ?? '-';
                })
                ->addColumn('aksi', function ($row) use ($permission) {
                    $data = [];
                    if (! auth()->guest()) {
                        if ($permission['canedit']) {
                            $data['edit'] = route('users.edit', $row->id);
                        }
                        if ($permission['canedit'] && $row->id !== auth()->id() && auth()->user()->can('update', $row)) {
                            $data['resetPassword'] = route('users.reset-password', $row->id);
                        }
                        if ($row->id !== User::superAdmin()) {
                            if ($permission['candelete']) {
                                $data['delete'] = route('users.destroy', $row->id);
                            }
                            if ($permission['canedit']) {
                                if ($row->active == StatusEnum::aktif) {
                                    $data['deactive'] = route('users.status', [$row->id, StatusEnum::tidakAktif]);
                                } else {
                                    $data['active'] = route('users.status', [$row->id, StatusEnum::aktif]);
                                }
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
        // dd(UserTeam::get());
        $user = null;

        $groups = Team::withoutAdminUsers()->get();

        $team = false;

        $kabupatens = (new ConfigApiService)->kabupaten();

        $openkab_siapakai = $this->isOpenKabSiapPakai();

        return view('user.create', compact('user', 'groups', 'team', 'kabupatens', 'openkab_siapakai'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\UserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        try {
            $data = $request->validated();

            $currentUser = auth()->user();

            $insertData = [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'company' => $data['company'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'active' => 1,
                'telegram_chat_id' => $data['telegram_chat_id'],
                'kode_kabupaten' => $currentUser->getEffectiveKodeKabupaten($request->input('kode_kabupaten')),
            ];

            if ($request->file('foto')) {
                $this->pathFolder .= '/profile';
                $insertData['foto'] = $this->uploadFile($request, 'foto');
            }

            if ($request->filled('kode_kabupaten')) {
                Session::put('kabupaten.kode_kabupaten', $data['kode_kabupaten']);
            }

            $user = User::create($insertData);

            // joinkan user ke group
            UserTeam::create([
                'id_user' => $user->id,
                'id_team' => $data['group'],
            ]);

            // assign role berdasarkan team
            setPermissionsTeamId($request['group']);
            foreach (Team::find($data['group'])->role as $role) {
                $user->assignRole($role);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::with('team')->where('id', $id)->first();

        // IDOR Prevention: Authorization check
        $this->authorize('update', $user);

        $groups = Team::withoutAdminUsers()->get();
        $team = $user->team->first()->id ?? false;

        $kabupatens = (new ConfigApiService)->kabupaten();

        $openkab_siapakai = $this->isOpenKabSiapPakai();

        $akses = $this->dataAkses($user);

        return view('user.edit', compact('user', 'groups', 'team', 'kabupatens', 'openkab_siapakai', 'akses'));
    }

    /**
     * Data pilihan dan keadaan awal pembatasan hak akses DataSpasial (issue #1319/#1320).
     *
     * @return array{wilayah: array<string, mixed>, sektor: array<string, mixed>, data: array<string, mixed>, sektors: array<int, array{kode: string, nama: string}>}
     */
    private function dataAkses(User $user): array
    {
        $layanan = app(CakupanPenggunaService::class);
        $config = new ConfigApiService;

        $wilayah = $layanan->wilayahAkses($user->id);
        $sektorAkses = $layanan->sektorAkses($user->id);

        $kabupaten = collect($config->kabupaten())
            ->map(fn ($k) => ['kode' => (string) ($k->kode_kabupaten ?? ''), 'nama' => (string) ($k->nama_kabupaten ?? '')])
            ->filter(fn ($k) => $k['kode'] !== '')
            ->values()
            ->all();

        $kecamatan = collect($config->kecamatan())
            ->map(function ($k) {
                $a = is_object($k) ? (array) $k : $k;
                $kode = (string) ($a['kode_kecamatan'] ?? '');

                return ['kode' => $kode, 'nama' => (string) ($a['nama_kecamatan'] ?? $kode), 'kab' => KodeWilayah::kabupaten($kode)];
            })
            ->filter(fn ($k) => $k['kode'] !== '')
            ->values()
            ->all();

        $desa = collect($config->desaConfig())
            ->map(function ($d) {
                $a = is_object($d) ? (array) $d : $d;
                $kode = (string) ($a['kode_desa'] ?? '');

                return ['kode' => $kode, 'nama' => (string) ($a['nama_desa'] ?? $kode), 'kec' => (string) ($a['kode_kecamatan'] ?? KodeWilayah::kecamatan($kode))];
            })
            ->filter(fn ($d) => $d['kode'] !== '')
            ->values()
            ->all();

        $sektor = collect(app(DataSpasialApiService::class)->listSektor()->data ?? [])
            ->map(fn ($s) => ['kode' => (string) ($s['attributes']['kode'] ?? ''), 'nama' => (string) ($s['attributes']['nama'] ?? ($s['attributes']['kode'] ?? ''))])
            ->filter(fn ($s) => $s['kode'] !== '')
            ->values()
            ->all();

        $kode = $wilayah['kode'] ?? null;

        return [
            'wilayah' => [
                'aktif' => $wilayah !== null,
                'level' => $wilayah['level'] ?? null,
                'kode' => $kode,
                // Kabupaten dan kecamatan induk dari kode yang disimpan, lewat helper KodeWilayah.
                'kab' => KodeWilayah::kabupaten($kode),
                'kec' => match ($wilayah['level'] ?? null) {
                    KodeWilayah::KECAMATAN => $kode,
                    KodeWilayah::DESA => KodeWilayah::kecamatan($kode),
                    default => null,
                },
            ],
            'sektor' => [
                'aktif' => $sektorAkses !== null,
                'pilih' => $sektorAkses ?? [],
            ],
            'data' => ['kabupaten' => $kabupaten, 'kecamatan' => $kecamatan, 'desa' => $desa],
            'sektors' => $sektor,
        ];
    }

    /**
     * Validasi input pembatasan hak akses sebelum data pengguna disimpan.
     */
    private function validasiHakAkses(Request $request): ?string
    {
        if ($request->boolean('akses_wilayah_aktif')) {
            $level = (string) $request->input('akses_wilayah_level', '');
            if (! in_array($level, CakupanPenggunaService::LEVEL_WILAYAH, true)) {
                return 'Pilih level wilayah untuk pembatasan wilayah.';
            }
            $kode = (string) $request->input('akses_wilayah_'.$level, '');
            if ($kode === '') {
                return 'Pilih '.$level.' untuk pembatasan wilayah.';
            }
            if (! KodeWilayah::sesuaiLevel($level, $kode)) {
                $panjang = KodeWilayah::PANJANG[$level];

                return 'Kode '.$level.' tidak valid: harus '.$panjang.' digit.';
            }
        }

        if ($request->boolean('akses_sektor_aktif') && $request->input('akses_sektor', []) === []) {
            return 'Pilih minimal satu sektor untuk pembatasan sektor.';
        }

        return null;
    }

    /**
     * Simpan pembatasan hak akses. Pembatasan yang tidak diaktifkan dihapus (kembali bebas).
     */
    private function simpanHakAkses(Request $request, User $user): void
    {
        $layanan = app(CakupanPenggunaService::class);

        if ($request->boolean('akses_wilayah_aktif')) {
            $level = (string) $request->input('akses_wilayah_level');
            $layanan->simpanWilayah($user->id, $level, (string) $request->input('akses_wilayah_'.$level));
        } else {
            $layanan->simpanWilayah($user->id, null, null);
        }

        $layanan->simpanSektor(
            $user->id,
            $request->boolean('akses_sektor_aktif') ? (array) $request->input('akses_sektor', []) : [],
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function profile($id)
    {
        $user = User::find($id);

        // IDOR Prevention: Authorization check
        $this->authorize('view', $user);

        return view('user.profile', compact('user'));
    }

    /**
     * Reset password pengguna oleh administrator. Password sementara ditampilkan
     * sekali lewat flash session dan dikirim ke email pengguna.
     *
     * @return RedirectResponse
     */
    public function resetPassword(Request $request, User $user)
    {
        // IDOR Prevention: Authorization check
        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mereset password akun sendiri.');
        }

        $hasil = app(PasswordResetByAdminService::class)->reset($user, $request->user());

        $redirect = redirect()->route('users.index')
            ->with('password_sementara', $hasil['password']);

        return $hasil['email_terkirim']
            ? $redirect->with('success', "Password {$user->name} berhasil direset. Email pemberitahuan telah dikirim.")
            : $redirect->with('warning', "Password {$user->name} berhasil direset, tetapi email pemberitahuan gagal dikirim. Sampaikan password sementara secara langsung.");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        // IDOR Prevention: Authorization check
        $this->authorize('update', $user);

        // Validasi pembatasan hak akses sebelum data pengguna diubah.
        if ($request->has('akses_form') && ($errorAkses = $this->validasiHakAkses($request))) {
            return back()->withInput()->with('error', $errorAkses);
        }

        try {
            $currentUser = auth()->user();

            $updateData = [
                'name' => $request->get('name'),
                'username' => $request->get('username') ?? $user->username,
                'email' => $request->get('email'),
                'company' => $request->get('company'),
                'phone' => $request->get('phone'),
                'telegram_chat_id' => $request->get('telegram_chat_id'),
                'kode_kabupaten' => $currentUser->getEffectiveKodeKabupaten($request->input('kode_kabupaten')),
            ];

            if ($request->file('foto')) {
                $this->pathFolder .= '/profile';
                $updateData['foto'] = $this->uploadFile($request, 'foto');
            }

            if ($request->filled('kode_kabupaten')) {
                Session::put('kabupaten.kode_kabupaten', $request->get('kode_kabupaten'));
            }

            $user->update($updateData);

            // logika untuk update massal user kabupaten jika role administrator menupdate kode_kabupaten role superadmin_daerah
            setPermissionsTeamId($user->getTeamId());

            $wasSuperadminDaerah = $user->hasRole('superadmin_daerah');
            $wasChangedByAdmin = $currentUser->hasRole('administrator');
            $kodeBaru = $updateData['kode_kabupaten'] ?? null;

            if ($wasChangedByAdmin && $wasSuperadminDaerah && $kodeBaru) {
                $team = Team::where('name', 'kabupaten')->first();

                if ($team) {
                    setPermissionsTeamId($team->id);

                    $roleKabupaten = Role::where([
                        'name' => 'kabupaten',
                        'team_id' => $team->id,
                    ])->first();

                    if ($roleKabupaten) {
                        $kabupatenUsers = User::role('kabupaten')->get();

                        foreach ($kabupatenUsers as $kabupatenUser) {
                            $kabupatenUser->update([
                                'kode_kabupaten' => $kodeBaru,
                            ]);
                        }
                    }
                }
            }

            $routeCurrent = Route::currentRouteName();
            if ($routeCurrent == 'profile.update') {
                return redirect()->route('profile.edit', Auth::id())->with('success', 'Data profil berhasil diubah!');
            }

            // update user team
            $idGroup = $request->get('group');
            $user_team = UserTeam::find($user->id);
            if ($user_team) {
                $user_team->id_team = $idGroup;
                $user_team->save();
            } else {
                UserTeam::create([
                    'id_user' => $user->id,
                    'id_team' => $idGroup,
                ]);
            }

            setPermissionsTeamId($idGroup);

            // assign role berdasarkan team
            foreach (Team::find($idGroup)->role as $role) {
                $user->syncRoles($role);
            }

            if ($request->has('akses_form')) {
                $this->simpanHakAkses($request, $user);
            }

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil diubah!');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        // IDOR Prevention: Authorization check
        $this->authorize('delete', $user);

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
     * @param  int  $id
     * @return Response
     */
    public function status($id, $status, User $user)
    {
        // IDOR Prevention: Authorization check
        $this->authorize('status', $user);

        try {
            $user->where('id', '!=', $user->superAdmin())->findOrFail($id)->update(['active' => $status]);
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('users.index')->with('error', 'Status gagal diubah!');
        }

        return redirect()->route('users.index')->with('success', 'Status berhasil diubah!');
    }
}
