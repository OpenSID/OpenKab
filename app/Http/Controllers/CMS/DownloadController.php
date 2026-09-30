<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\AppBaseController;
use App\Http\Repository\DownloadRepository;
use App\Http\Requests\CreateDownloadRequest;
use App\Http\Requests\UpdateDownloadRequest;
use App\Http\Transformers\DownloadTransformer;
use App\Models\Enums\StatusEnum;
use App\Traits\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DownloadController extends AppBaseController
{
    use UploadedFile;

    /** @var DownloadRepository */
    private $downloadRepository;

    protected $permission = 'website-downloads';

    public function __construct(DownloadRepository $downloadRepo)
    {
        $this->downloadRepository = $downloadRepo;
        $this->pathFolder = 'uploads/downloads';
    }

    /**
     * Display a listing of the Download.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->fractal($this->downloadRepository->listDownload(), new DownloadTransformer, 'downloads')->respond();
        }
        $listPermission = $this->generateListPermission();

        return view('downloads.index')->with($listPermission);
    }

    /**
     * Show the form for creating a new Download.
     */
    public function create()
    {
        return view('downloads.create', $this->getOptionItems());
    }

    /**
     * Store a newly created Download in storage.
     */
    public function store(CreateDownloadRequest $request)
    {
        $input = $request->all();
        
        try {
            if ($request->file('download_file')) {
                // Upload as generic file (not image)
                $input['url'] = $this->uploadFile($request, 'download_file', null, 5120);
            }

            $this->downloadRepository->create($input);

            Session::flash('success', 'File berhasil disimpan.');

            return redirect(route('downloads.index'));
        } catch (\RuntimeException $e) {
            // Convert to validation error so it appears in $errors
            return redirect()->back()
                ->withInput()
                ->withErrors(['download_file' => 'Gagal mengunggah file: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            Session::flash('error', 'Terjadi kesalahan saat menyimpan file. Silakan coba lagi.');
            report($e);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified Download.
     */
    public function show($id)
    {
        $download = $this->downloadRepository->find($id);

        if (empty($download)) {
            Session::flash('error', 'File download tidak ditemukan');

            return redirect(route('downloads.index'));
        }

        return view('downloads.show')->with('download', $download);
    }

    /**
     * Show the form for editing the specified Download.
     */
    public function edit($id)
    {
        $download = $this->downloadRepository->find($id);

        if (empty($download)) {
            Session::flash('error', 'File download tidak ditemukan');

            return redirect(route('downloads.index'));
        }

        return view('downloads.edit', $this->getOptionItems($id))->with('download', $download);
    }

    /**
     * Update the specified Download in storage.
     */
    public function update($id, UpdateDownloadRequest $request)
    {
        $download = $this->downloadRepository->find($id);

        if (empty($download)) {
            Session::flash('error', 'File download tidak ditemukan');

            return redirect(route('downloads.index'));
        }
        
        $input = $request->all();
        
        try {
            if ($request->file('download_file')) {
                // Upload as generic file (not image)
                $input['url'] = $this->uploadFile($request, 'download_file', null, 5120);
            }
            
            $download = $this->downloadRepository->update($input, $id);

            Session::flash('success', 'File berhasil diupdate.');

            return redirect(route('downloads.index'));
        } catch (\RuntimeException $e) {
            // Convert to validation error so it appears in $errors
            return redirect()->back()
                ->withInput()
                ->withErrors(['download_file' => 'Gagal mengunggah file: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            Session::flash('error', 'Terjadi kesalahan saat mengupdate file. Silakan coba lagi.');
            report($e);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified Download from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $download = $this->downloadRepository->find($id);

        if (empty($download)) {
            Session::flash('error', 'File download tidak ditemukan');

            return redirect(route('downloads.index'));
        }

        $this->downloadRepository->delete($id);
        if (request()->ajax()) {
            return $this->sendSuccess('File download berhasil dihapus.');
        }
        Session::flash('success', 'File download berhasil dihapus.');

        return redirect(route('downloads.index'));
    }

    protected function getOptionItems($id = null)
    {
        return [
            'stateItem' => [StatusEnum::aktif => 'Ya', StatusEnum::tidakAktif => 'Tidak'],
        ];
    }
}
