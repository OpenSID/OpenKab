<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\AppBaseController;
use App\Http\Repository\CMS\SlideRepository;
use App\Http\Requests\CreateSlideRequest;
use App\Http\Requests\UpdateSlideRequest;
use App\Http\Transformers\SlideTransformer;
use App\Models\CMS\Slide;
use App\Traits\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SlideController extends AppBaseController
{
    use UploadedFile;

    /** @var SlideRepository */
    private $slideRepository;

    protected $permission = 'website-slider';

    public function __construct(SlideRepository $slideRepo)
    {
        $this->slideRepository = $slideRepo;
        $this->pathFolder = 'uploads/slides';
    }

    /**
     * Display a listing of the Slide.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->fractal($this->slideRepository->listSlide(), new SlideTransformer, 'slides')->respond();
        }
        $listPermission = $this->generateListPermission();

        return view('slides.index')->with($listPermission);
    }

    /**
     * Show the form for creating a new Slide.
     */
    public function create()
    {
        return view('slides.create', $this->getOptionItems())->with('slide', new Slide(['state' => 1]));
    }

    /**
     * Store a newly created Slide in storage.
     */
    public function store(CreateSlideRequest $request)
    {
        $input = $request->all();
        if ($request->file('foto')) {
            $input['thumbnail'] = $this->uploadFile($request, 'foto');
        }

        $this->slideRepository->create($input);

        Session::flash('success', 'Slide berhasil disimpan.');

        return redirect(route('slides.index'));
    }

    /**
     * Display the specified Slide.
     */
    public function show($id)
    {
        $slide = $this->slideRepository->find($id);

        if (empty($slide)) {
            Session::flash('error', 'Slide tidak ditemukan');

            return redirect(route('slides.index'));
        }

        return view('slides.show')->with('slide', $slide);
    }

    /**
     * Show the form for editing the specified Slide.
     */
    public function edit($id)
    {
        $slide = $this->slideRepository->find($id);

        if (empty($slide)) {
            Session::flash('error', 'Slide tidak ditemukan');

            return redirect(route('slides.index'));
        }

        return view('slides.edit', $this->getOptionItems($id))->with('slide', $slide);
    }

    /**
     * Update the specified Slide in storage.
     */
    public function update($id, UpdateSlideRequest $request)
    {
        $slide = $this->slideRepository->find($id);

        if (empty($slide)) {
            Session::flash('error', 'Slide tidak ditemukan');

            return redirect(route('slides.index'));
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
        $slide = $this->slideRepository->update($input, $id);

        Session::flash('success', 'Slide berhasil diupdate.');

        return redirect(route('slides.index'));
    }

    /**
     * Remove the specified Slide from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $slide = $this->slideRepository->find($id);

        if (empty($slide)) {
            Session::flash('error', 'Slide tidak ditemukan');

            return redirect(route('slides.index'));
        }

        $this->slideRepository->delete($id);
        if (request()->ajax()) {
            return $this->sendSuccess('Slide berhasil dihapus.');
        }
        Session::flash('success', 'Slide berhasil dihapus.');

        return redirect(route('slides.index'));
    }

    protected function getOptionItems($id = null)
    {
        return [];
    }
}
