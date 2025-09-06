<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\AppBaseController;
use App\Http\Repository\CMS\CategoryRepository;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Transformers\CategoryTransformer;
use App\Models\CMS\Article;
use App\Models\CMS\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CategoryController extends AppBaseController
{
    /** @var CategoryRepository */
    private $categoryRepository;

    protected $permission = 'website-categories';

    public function __construct(CategoryRepository $categoryRepo)
    {
        $this->categoryRepository = $categoryRepo;
    }

    /**
     * Display a listing of the Category.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->fractal($this->categoryRepository->listCategory(), new CategoryTransformer, 'categories')->respond();
        }
        $listPermission = $this->generateListPermission();

        return view('categories.index')->with($listPermission);
    }

    /**
     * Show the form for creating a new Category.
     */
    public function create()
    {
        return view('categories.create', $this->getOptionItems())->with('category', new Category(['status' => 1]));
    }

    /**
     * Store a newly created Category in storage.
     */
    public function store(CreateCategoryRequest $request)
    {
        $input = $request->all();

        $category = $this->categoryRepository->create($input);

        Session::flash('success', 'Kategori berhasil disimpan.');

        return redirect(route('categories.index'));
    }

    /**
     * Display the specified Category.
     */
    public function show($id)
    {
        $category = $this->categoryRepository->find($id);

        if (empty($category)) {
            Session::flash('error', 'Kategori tidak ditemukan');

            return redirect(route('categories.index'));
        }

        return view('categories.show')->with('category', $category);
    }

    /**
     * Show the form for editing the specified Category.
     */
    public function edit($id)
    {
        $category = $this->categoryRepository->find($id);

        if (empty($category)) {
            Session::flash('error', 'Kategori tidak ditemukan');

            return redirect(route('categories.index'));
        }

        return view('categories.edit', $this->getOptionItems($id))->with('category', $category);
    }

    /**
     * Update the specified Category in storage.
     */
    public function update($id, UpdateCategoryRequest $request)
    {
        $category = $this->categoryRepository->find($id);

        if (empty($category)) {
            Session::flash('error', 'Kategori tidak ditemukan');

            return redirect(route('categories.index'));
        }

        $category = $this->categoryRepository->update($request->all(), $id);

        Session::flash('success', 'Kategori berhasil diperbarui.');

        return redirect(route('categories.index'));
    }

    /**
     * Remove the specified Category from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $category = $this->categoryRepository->find($id);
        $article = Article::whereCategoryId($id)->count();

        if ($article) {
            if (request()->ajax()) {
                return $this->sendError('Masih ada artikel pada kategori tersebut', 201);
            }
            Session::flash('error', 'Masih ada artikel pada kategori tersebut');

            return redirect(route('categories.index'));
        }

        if (empty($category)) {
            Session::flash('error', 'Kategori tidak ditemukan');

            return redirect(route('categories.index'));
        }

        $this->categoryRepository->delete($id);

        if (request()->ajax()) {
            return $this->sendSuccess('Kategori berhasil dihapus.');
        }
        Session::flash('success', 'Kategori berhasil dihapus.');

        return redirect(route('categories.index'));
    }

    protected function getOptionItems($id = null)
    {
        return [];
    }
}
