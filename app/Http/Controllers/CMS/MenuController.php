<?php

namespace App\Http\Controllers\CMS;

use App\Models\Rtm;
use App\Models\Bantuan;
use App\Models\CMS\Page;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\CMS\Category;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Controllers\AppBaseController;
use App\Http\Repository\CMS\MenuRepository;

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
                '/module/unduhan' => 'Daftar Unduhan',
                'statistik-penduduk' => 'Statistik Penduduk',
                'statistik-keluarga' => 'Statistik Keluarga',
                'statistik-bantuan' => 'Statistik Bantuan',
                'statistik-rtm' => 'Statistik RTM',
            ],
            'penduduk' => collect(['/presisi/statistik/penduduk/semua' => 'Semua Statistik Penduduk'])
                ->merge(
                    collect(Penduduk::KATEGORI_STATISTIK)->mapWithKeys(function ($item, $key) {
                        return ["/presisi/statistik/penduduk/{$key}" => ucwords("Statistik Penduduk {$item}")];
                    })
                )->toArray(),
            
            'keluarga' => collect(['/presisi/statistik/keluarga/semua' => 'Semua Statistik Keluarga'])
                ->merge(
                    collect(Keluarga::KATEGORI_STATISTIK)->mapWithKeys(function ($item, $key) {
                        return ["/presisi/statistik/keluarga/{$key}" => ucwords("Statistik Keluarga {$item}")];
                    })
                )->toArray(),
            
            'bantuan' => collect(['/presisi/statistik/bantuan/semua' => 'Semua Statistik Bantuan'])
                ->merge(
                    collect(Bantuan::KATEGORI_STATISTIK)->mapWithKeys(function ($item, $key) {
                        return ["/presisi/statistik/bantuan/{$key}" => ucwords("Statistik Bantuan {$item}")];
                    })
                )->toArray(),
            
            'rtm' => collect(['/presisi/statistik/rtm/semua' => 'Semua Statistik Rtm'])
                ->merge(
                    collect(Rtm::KATEGORI_STATISTIK)->mapWithKeys(function ($item, $key) {
                        return ["/presisi/statistik/rtm/{$key}" => ucwords("Statistik Rtm {$item}")];
                    })
                )->toArray(),
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
