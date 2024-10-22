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
    public function __invoke(string $moduleName, string $moduleCategory = null)
    {
        $content = match ($moduleName) {
            'unduhan' => $this->downloadModule(),
            'org' => $this->orgModule(),
            'statistik' => $this->statistikModule(),
            default => ''
        };

        if (empty($content)) {
            return redirect()->route('web.index', ['statistik' => $moduleName, 'kategori' => $moduleCategory]);
        }

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

    private function statistikModule()
    {
    }
}
