<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OpenDKController extends AppBaseController
{
    protected $permission = 'pengaturan-opendk';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the Setting.
     */
    public function index(Request $request)
    {
        $listPermission = $this->generateListPermission();

        return view('opendk.index')->with($listPermission);
    }

    protected function getOptionItems($id = null)
    {
        return [];
    }
}
