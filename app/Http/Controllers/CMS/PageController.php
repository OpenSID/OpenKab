<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\AppBaseController;
use App\Http\Repository\CMS\PageRepository;
use App\Http\Requests\CreatePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Transformers\PageTransformer;
use App\Models\CMS\Page;
use App\Models\Enums\StatusEnum;
use App\Traits\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PageController extends AppBaseController
{
    use UploadedFile;

    /** @var PageRepository */
    private $pageRepository;

    public function __construct(PageRepository $pageRepo)
    {
        $this->pageRepository = $pageRepo;
        $this->pathFolder = 'uploads/pages';
    }

    /**
     * Display a listing of the Page.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->fractal($this->pageRepository->listPage(), new PageTransformer, 'pages')->respond();
        }

        return view('pages.index');
    }

    /**
     * Show the form for creating a new Page.
     */
    public function create()
    {
        return view('pages.create', $this->getOptionItems())->with('page', new Page(['state' => StatusEnum::aktif]));
    }

    /**
     * Store a newly created Halaman in storage.
     */
    public function store(CreatePageRequest $request)
    {
        $input = $request->all();
        if ($request->file('foto')) {
            $this->pathFolder .= '/profile';
            $input['thumbnail'] = $this->uploadFile($request, 'foto');
        }
        $this->pageRepository->create($input);

        Session::flash('success', 'Halaman berhasil disimpan.');

        return redirect(route('pages.index'));
    }

    /**
     * Display the specified Page.
     */
    public function show($id)
    {
        $page = $this->pageRepository->find($id);

        if (empty($page)) {
            Session::flash('error', 'Halaman tidak ditemukan');

            return redirect(route('pages.index'));
        }

        return view('pages.show')->with('page', $page);
    }

    /**
     * Show the form for editing the specified Page.
     */
    public function edit($id)
    {
        $page = $this->pageRepository->find($id);

        if (empty($page)) {
            Session::flash('error', 'Halaman tidak ditemukan');

            return redirect(route('pages.index'));
        }

        return view('pages.edit', $this->getOptionItems($id))->with('page', $page);
    }

    /**
     * Update the specified Halaman in storage.
     */
    public function update($id, UpdatePageRequest $request)
    {
        $page = $this->pageRepository->find($id);

        if (empty($page)) {
            Session::flash('error', 'Halaman tidak ditemukan');

            return redirect(route('pages.index'));
        }
        $input = $request->all();
        $removeThumbnail = $request->get('remove_thumbnail');
        if ($request->file('foto')) {
            $input['thumbnail'] = $this->uploadFile($request, 'foto');
        } else {
            if ($removeThumbnail) {
                $input['thumbnail'] = null;
            }
        }
        $page = $this->pageRepository->update($input, $id);

        Session::flash('success', 'Halaman berhasil diupdate.');

        return redirect(route('pages.index'));
    }

    /**
     * Remove the specified Halaman from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $page = $this->pageRepository->find($id);

        if (empty($page)) {
            Session::flash('error', 'Halaman tidak ditemukan');

            return redirect(route('pages.index'));
        }

        $this->pageRepository->delete($id);
        if (request()->ajax()) {
            return $this->sendSuccess('Halaman berhasil dihapus.');
        }
        Session::flash('success', 'Halaman berhasil dihapus.');

        return redirect(route('pages.index'));
    }

    protected function getOptionItems($id = null)
    {
        return [
            'stateItem' => Page::STATE_STRING,
        ];
    }
}
