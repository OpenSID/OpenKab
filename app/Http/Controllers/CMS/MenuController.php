<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\AppBaseController;
use App\Http\Repository\CMS\MenuRepository;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\CMS\Category;
use App\Models\CMS\Page;
use Illuminate\Support\Facades\Session;

class MenuController extends AppBaseController
{
    /** @var MenuRepository */
    private $menuRepository;

    protected $permission = 'website-menu';

    public function __construct(MenuRepository $menuRepo)
    {
        $this->menuRepository = $menuRepo;
    }

    /**
     * Display a listing of the Menu.
     */
    public function index()
    {
        $type = 1;
        if (request('type') != null){
            $type = request('type');
        }
        $sourceItem = [
            'Halaman' => Page::activePublished()->get()->pluck('title', 'link')->toArray(),
            'Kategori' => Category::all()->pluck('name', 'link')->toArray(),
            'Modul' => [
                '/module/org' => 'Bagan Organisasi',
                '/module/statistik' => 'Statistik',
                '/module/unduhan' => 'Daftar Unduhan',
            ],
        ];
        $listPermission = $this->generateListPermission();

        return view('menus.index', ['menus' => $this->menuRepository->treeJson(), 'sourceItem' => $sourceItem])->with($listPermission);
    }

    /**
     * Store a newly created Menu in storage.
     */
    public function store(UpdateMenuRequest $request)
    {
        $input = $request->all();

        $menu_type = $input["menu_type"] == null ? 1 : $input["menu_type"];

        $menu = $this->menuRepository->create($input);

        Session::flash('success', 'Menu berhasil disimpan.');
        if ($menu_type == 1){
            return redirect(route('menus.index'));
        }
        else if ($menu_type == 2){
            return redirect(route('menus.index') . "?type=" .$menu_type);
        }
        else{
            return redirect(route('menus.index'));
        }
    }

    protected function getOptionItems($id = null)
    {
        return [];
    }
}
