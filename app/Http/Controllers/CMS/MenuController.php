<?php

namespace App\Http\Controllers\CMS;

use App\Enums\KategoriSasaranBantuanEnum;
use App\Enums\KesehatanAnakEnum;
use App\Enums\PendudukKategoriStatistikEnum;
use App\Enums\RtmKategoriStatistikEnum;
use App\Enums\SasaranStatistikKeluargaEnum;
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
        if (request('type') != null) {
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
                'statistik-kesehatan' => 'Statistik Kesehatan',
            ],
            'penduduk' => collect(['/module/penduduk' => 'Semua Statistik Penduduk'])
                ->merge(
                    collect(PendudukKategoriStatistikEnum::KATEGORI_STATISTIK)->mapWithKeys(function ($item, $key) {
                        return ["/module/penduduk/{$key}" => ucwords("Statistik Penduduk {$item}")];
                    })
                )->toArray(),

            'keluarga' => collect(['/module/keluarga' => 'Semua Statistik Keluarga'])
                ->merge(
                    collect(SasaranStatistikKeluargaEnum::KATEGORI_STATISTIK)->mapWithKeys(function ($item, $key) {
                        return ["/module/keluarga/{$key}" => ucwords("Statistik Keluarga {$item}")];
                    })
                )->toArray(),

            'bantuan' => collect(['/module/bantuan' => 'Semua Statistik Bantuan'])
                ->merge(
                    collect(KategoriSasaranBantuanEnum::KATEGORI_STATISTIK)->mapWithKeys(function ($item, $key) {
                        return ["/module/bantuan/{$key}" => ucwords("Statistik Bantuan {$item}")];
                    })
                )->toArray(),

            'rtm' => collect(['/module/rtm' => 'Semua Statistik Rtm'])
                ->merge(
                    collect(RtmKategoriStatistikEnum::KATEGORI_STATISTIK)->mapWithKeys(function ($item, $key) {
                        return ["/module/rtm/{$key}" => ucwords("Statistik Rtm {$item}")];
                    })
                )->toArray(),

            'kesehatan' => collect(['/module/kesehatan' => 'Semua Statistik Kesehatan'])
                ->merge(
                    collect(KesehatanAnakEnum::KATEGORI_STATISTIK)->mapWithKeys(function ($item, $key) {
                        return ["/module/kesehatan/{$key}" => ucwords("Statistik Kesehatan {$item}")];
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

        $menu_type = $input['menu_type'] == null ? 1 : $input['menu_type'];

        $menu = $this->menuRepository->create($input);

        Session::flash('success', 'Menu berhasil disimpan.');
        if ($menu_type == 1) {
            return redirect(route('menus.index'));
        } elseif ($menu_type == 2) {
            return redirect(route('menus.index').'?type='.$menu_type);
        } else {
            return redirect(route('menus.index'));
        }
    }

    protected function getOptionItems($id = null)
    {
        return [];
    }
}
