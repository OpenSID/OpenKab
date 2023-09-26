<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\BantuanRepository;
use App\Http\Repository\ConfigRepository;
use App\Http\Repository\PendudukRepository;
use App\Models\CMS\Article;
use App\Models\CMS\Category;
use App\Models\CMS\Page;
use App\Services\SitemapService;

class PageController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex()
    {
        $totalDesa = 0;
        $configSummary = (new ConfigRepository)->desa()->groupBy('nama_kecamatan')->map(function($item) use (&$totalDesa) {
            $totalDesa += $item->count();
            return $item->pluck('nama_desa', 'kode_desa');
        });

        $bantuanSummary = (new BantuanRepository)->summary();
        $pendudukSummary = (new PendudukRepository)->summary();
        $categoriesItems = [
            ['text' => 'penduduk','value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['text' => 'kecamatan','value' => $configSummary->count() ?? 0, 'icon' => 'web/img/kecamatan.jpg'],
            ['text' => 'desa/kelurahan','value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['text' => 'bantuan','value' => $bantuanSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];
        $listKecamatan = ['' => 'Pilih Kecamatan'] + array_combine($configSummary->keys()->toArray() , $configSummary->keys()->toArray());
        $listDesa = ['' => 'Pilih Desa'] + $configSummary->toArray();
        return view('web.index', compact('categoriesItems', 'listKecamatan', 'listDesa'));
    }

    /**
     * @param \App\Models\Category $category
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCategory(Category $category)
    {
        return view('web.articles', [
            'title' => $category->title,
            'description' => $category->description,
            'articles' => Article::where('category_id', $category->id)->paginate(4),
        ]);
    }

    /**
     * @param \App\Models\Page $page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPage(Page $page)
    {
        return view('web.page', ['object' => $page]);
    }

    /**
     * @param \App\Models\Article $article
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getArticle(Article $article)
    {
        return view('web.article', ['object' => $article]);
    }

    /**
     * @param \App\Base\Services\SitemapService $sitemapService
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getSitemap(SitemapService $sitemapService)
    {
        return $sitemapService->render();
    }
}
