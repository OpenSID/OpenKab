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
            'penduduk' => collect(['/module/penduduk' => 'Semua Statistik Penduduk'])
                ->merge(
                    collect(Penduduk::KATEGORI_STATISTIK)->mapWithKeys(function ($item, $key) {
                        return ["/module/penduduk/{$key}" => ucwords("Statistik Penduduk {$item}")];
                    })
                )->toArray(),
            
            'keluarga' => collect(['/module/keluarga' => 'Semua Statistik Keluarga'])
                ->merge(
                    collect(Keluarga::KATEGORI_STATISTIK)->mapWithKeys(function ($item, $key) {
                        return ["/module/keluarga/{$key}" => ucwords("Statistik Keluarga {$item}")];
                    })
                )->toArray(),
            
            'bantuan' => collect(['/module/bantuan' => 'Semua Statistik Bantuan'])
                ->merge(
                    collect(Bantuan::KATEGORI_STATISTIK)->mapWithKeys(function ($item, $key) {
                        return ["/module/bantuan/{$key}" => ucwords("Statistik Bantuan {$item}")];
                    })
                )->toArray(),
            
            'rtm' => collect(['/module/rtm' => 'Semua Statistik Rtm'])
                ->merge(
                    collect(Rtm::KATEGORI_STATISTIK)->mapWithKeys(function ($item, $key) {
                        return ["/module/rtm/{$key}" => ucwords("Statistik Rtm {$item}")];
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

        $menu = $this->menuRepository->create($input);

        Session::flash('success', 'Menu berhasil disimpan.');

        return redirect(route('menus.index'));
    }

    protected function getOptionItems($id = null)
    {
        return [];
    }
}
