<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CMS\Article;
use App\Services\ArtikelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ArtikelController extends Controller
{
    protected ArtikelService $artikelService;

    public function __construct(ArtikelService $artikelService)
    {
        $this->artikelService = $artikelService;
    }

    /**
     * Tampilkan daftar artikel gabungan (OpenKab & OpenSID).
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $search = $request->get('search', '');
        $categoryId = $request->get('kategori', '');

        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
            $filters['filter[search]'] = $search;
        }
        if (!empty($categoryId)) {
            $filters['kategori'] = $categoryId;
            $filters['filter[id_kategori]'] = $categoryId;
        }

        // Ambil data gabungan melalui service
        $combinedArticles = $this->artikelService->getCombinedArticles($filters);

        // Pagination menggunakan LengthAwarePaginator
        $page = max((int) $request->get('page', 1), 1);
        $perPage = 6;
        $offset = ($page - 1) * $perPage;
        $itemsForCurrentPage = $combinedArticles->slice($offset, $perPage)->values();

        $articles = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $combinedArticles->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('web.artikel.index', [
            'title' => 'Artikel Berita',
            'articles' => $articles,
            'search' => $search,
            'categoryId' => $categoryId,
        ]);
    }

    /**
     * Endpoint JSON untuk widget Artikel Terbaru di Beranda.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function terbaru(Request $request): JsonResponse
    {
        $limit = max((int) $request->get('limit', 6), 1);
        $articles = $this->artikelService->getArtikelTerbaru($limit);

        return response()->json([
            'status' => 'success',
            'data' => $articles,
        ]);
    }

    /**
     * Tampilkan detail artikel OpenSID (dengan fallback ke artikel CMS lokal jika ID/slug cocok).
     *
     * @param int|string $id
     * @return View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        $article = is_numeric($id) ? $this->artikelService->artikelById((int) $id) : null;

        if ($article && isset($article->enabled) && $article->enabled == 1) {
            return view('web.artikel.show', [
                'object' => $article,
            ]);
        }

        // Fallback: Cek apakah ID/slug merujuk pada artikel CMS OpenKab lokal
        $localArticle = Article::where('id', $id)
            ->orWhere('slug', $id)
            ->first();

        if ($localArticle && $localArticle->state == Article::PUBLISH && $localArticle->published_at <= now()) {
            return redirect()->route('article', ['aSlug' => $localArticle->slug]);
        }

        abort(404, 'Artikel tidak ditemukan atau tidak aktif');
    }
}

