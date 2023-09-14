<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\AppBaseController;
use App\Http\Repository\CMS\MenuRepository;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Transformers\MenuTransformer;
use App\Models\CMS\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MenuController extends AppBaseController
{
    /** @var MenuRepository */
    private $menuRepository;

    public function __construct(MenuRepository $menuRepo)
    {
        $this->menuRepository = $menuRepo;
    }

    /**
     * Display a listing of the Menu.
     */
    public function index()
    {
        $sourceItem = [
            'Halaman' => [
                '?hal=about' => 'Tentang',
                '?hal=profil' => 'Profile'
            ],
            'Kategori' => Category::all()->pluck('name', 'link')->toArray(),
            'Modul' => [
                '?module=org' => 'Bagan Organisasi',
                '?module=statistik' => 'Statistik',
            ]
        ];
        return view('menus.index', ['menus' => $this->menuRepository->treeJson(), 'sourceItem' => $sourceItem]);
    }

    /**
     * Store a newly created Menu in storage.
     */
    public function store(UpdateMenuRequest $request)
    {
        $input = $request->all();

        $menu = $this->menuRepository->create($input);

        Session::flash('success', 'Menu berhasil disimpan.');

        return redirect(route('menus.index'));
    }

    protected function getOptionItems($id = null)
    {
        return [];
    }
}
