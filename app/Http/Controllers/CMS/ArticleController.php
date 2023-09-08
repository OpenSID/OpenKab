<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\AppBaseController;
use App\Http\Repository\CMS\ArticleRepository;
use App\Http\Requests\CreateArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Transformers\ArticleTransformer;
use App\Models\CMS\Article;
use App\Models\CMS\Category;
use App\Traits\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ArticleController extends AppBaseController
{
    use UploadedFile;

    /** @var ArticleRepository */
    private $articleRepository;

    public function __construct(ArticleRepository $articleRepo)
    {
        $this->articleRepository = $articleRepo;
        $this->pathFolder = 'uploads/articles';
    }

    /**
     * Display a listing of the Article.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->fractal($this->articleRepository->listArticle(), new ArticleTransformer, 'articles')->respond();
        }

        return view('articles.index');
    }

    /**
     * Show the form for creating a new Article.
     */
    public function create()
    {
        return view('articles.create', $this->getOptionItems())->with(['article' => new Article()]);
    }

    /**
     * Store a newly created Artikel in storage.
     */
    public function store(CreateArticleRequest $request)
    {
        $input = $request->all();
        if($request->file('foto')){
            $input['thumbnail'] = $this->uploadFile($request, 'foto');
        }
        $this->articleRepository->create($input);

        Session::flash('success', 'Artikel berhasil disimpan.');

        return redirect(route('articles.index'));
    }

    /**
     * Display the specified Article.
     */
    public function show($id)
    {
        $article = $this->articleRepository->find($id);

        if (empty($article)) {
            Session::flash('error', 'Artikel tidak ditemukan');

            return redirect(route('articles.index'));
        }

        return view('articles.show')->with('article', $article);
    }

    /**
     * Show the form for editing the specified Article.
     */
    public function edit($id)
    {
        $article = $this->articleRepository->find($id);
        if (empty($article)) {
            Session::flash('error', 'Artikel tidak ditemukan');

            return redirect(route('articles.index'));
        }

        return view('articles.edit', $this->getOptionItems($id))->with('article', $article);
    }

    /**
     * Update the specified Artikel in storage.
     */
    public function update($id, UpdateArticleRequest $request)
    {
        $article = $this->articleRepository->find($id);

        if (empty($article)) {
            Session::flash('error', 'Artikel tidak ditemukan');

            return redirect(route('articles.index'));
        }
        $input = $request->all();
        $removeThumbnail = $request->get('remove_thumbnail');
        if($request->file('foto')){
            $input['thumbnail'] = $this->uploadFile($request, 'foto');
        } else {
            if ($removeThumbnail){
                $input['thumbnail'] = null;
            }
        }
        $article = $this->articleRepository->update($input, $id);

        Session::flash('success', 'Artikel berhasil diupdate.');

        return redirect(route('articles.index'));
    }

    /**
     * Remove the specified Artikel from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $article = $this->articleRepository->find($id);

        if (empty($article)) {
            Session::flash('error', 'Artikel tidak ditemukan');

            return redirect(route('articles.index'));
        }

        $this->articleRepository->delete($id);
        if (request()->ajax()) {
            return $this->sendSuccess('Artikel berhasil dihapus.');
        }
        Session::flash('success', 'Artikel berhasil dihapus.');

        return redirect(route('articles.index'));
    }

    protected function getOptionItems($id = null)
    {
        return [
            'categories' => Category::pluck('slug', 'id'),
            'stateItem' => Article::STATE_STRING,
        ];
    }
}
