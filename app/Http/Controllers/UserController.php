<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\User;
use App\Enums\Status;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
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
            $data = User::get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('aksi', function ($row) {
                    if (! auth()->guest()) {
                        $data['edit']   = route('users.edit', $row->id);
                        $data['delete'] = route('users.destroy', $row->id);
                        if ($row->active == Status::Aktif) {
                            $data['deactive'] = route('users.status', [$row->id, Status::TidakAktif]);
                        } else {
                            $data['active'] = route('users.status', [$row->id, Status::Aktif]);
                        }
                    }

                    return view('partials.aksi', $data);
                })
                ->editColumn('active', function ($row) {
                    if ($row->active == Status::TidakAktif) {
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
        return view('user.create', compact('user'));
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
            User::create($request->all());
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
    public function edit(User $user)
    {
        return view('user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        try {
            $user->update($request->all());
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
     * @param  int $id
     * @return Response
     */
    public function status($id, $status)
    {
        try {
            User::findOrFail($id)->update(['active' => $status]);
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('users.index')->with('error', 'Status gagal diubah!');
        }

        return redirect()->route('users.index')->with('success', 'Status berhasil diubah!');
    }
}
