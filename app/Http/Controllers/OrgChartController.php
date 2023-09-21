<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class OrgChartController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $tree = Department::with(['employees' => function($q){
            return $q->select(['department_id', 'name', 'foto', 'position_id', 'identity_number'])->with(['position']);
        }])->select(['id','name','parent_id','_lft', '_rgt'])->get()->toTree();
        return view('orgchart.index', compact('tree'));
    }
}
