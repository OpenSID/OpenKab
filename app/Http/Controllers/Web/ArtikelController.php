<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ArtikelService;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    protected ArtikelService $artikelService;

    public function __construct(ArtikelService $artikelService)
    {
        $this->artikelService = $artikelService;
    }

    /**
     * Tampilkan daftar artikel OpenSID.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $categoryId = $request->get('kategori', '');

        $filters = [];
        if (!empty($search)) {
            $filters['filter[search]'] = $search;
        }
        if (!empty($categoryId)) {
            $filters['filter[id_kategori]'] = $categoryId;
        }

        // Ambil data melalui service
        // Format Pagination API json format
        $filters['page[number]'] = $request->get('page', 1);
        $filters['page[size]'] = 6;
        $filters['sort'] = '-tgl_upload'; // Terurut berdasarkan tanggal terbaru

        // Caching ditangani oleh ArtikelService
        $articles = $this->artikelService->artikel($filters);

        // Filter out disabled articles just in case API returns them
        $articles = $articles->filter(function ($item) {
            return isset($item->enabled) && $item->enabled == 1;
        });

        return view('web.artikel.index', [
            'title' => 'Artikel Berita',
            'articles' => $articles,
            'search' => $search,
            'categoryId' => $categoryId
        ]);
    }

    /**
     * Tampilkan detail artikel OpenSID.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $article = $this->artikelService->artikelById($id);

        if (!$article || !isset($article->enabled) || $article->enabled == 0) {
            abort(404, 'Artikel tidak ditemukan atau tidak aktif');
        }

        return view('web.artikel.show', [
            'object' => $article
        ]);
    }
}
