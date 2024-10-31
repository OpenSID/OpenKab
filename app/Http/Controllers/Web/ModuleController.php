<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\DownloadRepository;
use App\Models\Department;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(string $moduleName)
    {

        $segments = explode('/', $moduleName);

        $firstSegment = $segments[0] ?? null;

        $content = match ($firstSegment) {
            'unduhan' => $this->downloadModule(),
            'org' => $this->orgModule(),
            'statistik' => $this->statistikModule($moduleName),
            default => 'Module not found'
        };

        $moduleName = $firstSegment;

        return view('web.module', compact('content', 'moduleName'));
    }

    private function downloadModule()
    {
        return (new DownloadRepository)->publicDownload();
    }

    private function orgModule()
    {
        $tree = Department::with(['employees' => function ($q) {
            return $q->select(['department_id', 'name', 'foto', 'position_id', 'identity_number'])->with(['position']);
        }])->select(['id', 'name', 'parent_id', '_lft', '_rgt'])->get()->toTree();

        return $tree;
    }

    private function statistikModule(string $moduleName)
    {
        $segments = explode('/', $moduleName);

        if (count($segments) == 3) {
            return $segments;
        }
    }
}
