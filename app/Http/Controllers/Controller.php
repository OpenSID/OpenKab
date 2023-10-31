<?php

namespace App\Http\Controllers;

use App\Enums\Modul;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $permission;

    protected function generateListPermission()
    {
        $listPermission = [];
        if ($this->permission) {
            foreach (Modul::permision as $item) {
                $listPermission['can'.$item] = auth()->user()->can($this->permission.'-'.$item) ?? 0;
            }
        }

        return $listPermission;
    }
}
