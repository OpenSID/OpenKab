<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        return view('group.index');
    }

    public function create()
    {
        return view('group.form');
    }

    public function edit($id)
    {
        return view('group.form');
    }
}
