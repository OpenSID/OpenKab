<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
        $pendudukSummary = 0;
        $configSummary = 0;
        $bantuanSummary = 0;
        $categoriesItems = [
            ['key' => 'penduduk', 'text' => 'penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'kabupaten', 'text' => 'kabupaten', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'kecamatan', 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'text' => 'desa/kelurahan', 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'bantuan', 'text' => 'bantuan', 'value' => $bantuanSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];
        $listKabupaten = ['' => 'Pilih Kabupaten'];
        $listKecamatan = ['' => 'Pilih Kecamatan'];
        $listDesa = ['' => 'Pilih Desa'];

        return view('web.index', compact('categoriesItems', 'listKecamatan', 'listDesa', 'listKabupaten'));
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
