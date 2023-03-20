<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\User;
use App\Enums\Status;
use Illuminate\Http\Request;

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
                        $data['edit_url']   = route('users.edit', $row->id);
                        $data['delete_url'] = route('users.destroy', $row->id);
                        // if ($row->status == Status::Aktif) {
                        //     $data['suspend_url'] = route('users.status', [$row->id, Status::TidakAktif]);
                        // } else {
                        //     $data['active_url'] = route('users.status', [$row->id, Status::Aktif]);
                        // }
                    }

                    return view('partials.aksi', $data);
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Update status resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function lock($id, $status)
    {
        try {
            User::findOrFail($id)->update(['status' => $status]);
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('data.User.index')->with('error', 'Status User gagal diubah!');
        }

        return redirect()->route('data.User.index')->with('success', 'Status User berhasil diubah!');
    }
}
