<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Http\Controllers\AppBaseController;
use App\Http\Repository\PositionRepository;
use App\Http\Transformers\PositionTransformer;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Collection;

class PositionController extends AppBaseController
{
    /** @var PositionRepository $positionRepository*/
    private $positionRepository;

    public function __construct(PositionRepository $positionRepo)
    {
        $this->positionRepository = $positionRepo;
    }

    /**
     * Display a listing of the Position.
     */
    public function index(Request $request)
    {
        if ($request->ajax()){
            $positions = $this->positionRepository->paginate(10);
            return $this->fractal($this->positionRepository->listPosition(), new PositionTransformer, 'positions')->respond();
        }

        return view('positions.index');
    }


    /**
     * Show the form for creating a new Position.
     */
    public function create()
    {
        return view('positions.create', $this->getOptionItems());
    }

    /**
     * Store a newly created Position in storage.
     */
    public function store(CreatePositionRequest $request)
    {
        $input = $request->all();

        $position = $this->positionRepository->create($input);

        Flash::success('Position saved successfully.');

        return redirect(route('positions.index'));
    }

    /**
     * Display the specified Position.
     */
    public function show($id)
    {
        $position = $this->positionRepository->find($id);

        if (empty($position)) {
            Flash::error('Position not found');

            return redirect(route('positions.index'));
        }

        return view('positions.show')->with('position', $position);
    }

    /**
     * Show the form for editing the specified Position.
     */
    public function edit($id)
    {
        $position = $this->positionRepository->find($id);

        if (empty($position)) {
            Flash::error('Position not found');

            return redirect(route('positions.index'));
        }

        return view('positions.edit', $this->getOptionItems($id))->with('position', $position);
    }

    /**
     * Update the specified Position in storage.
     */
    public function update($id, UpdatePositionRequest $request)
    {
        $position = $this->positionRepository->find($id);

        if (empty($position)) {
            Flash::error('Position not found');

            return redirect(route('positions.index'));
        }

        $position = $this->positionRepository->update($request->all(), $id);

        Flash::success('Position updated successfully.');

        return redirect(route('positions.index'));
    }

    /**
     * Remove the specified Position from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $position = $this->positionRepository->find($id);

        if (empty($position)) {
            Flash::error('Position not found');

            return redirect(route('positions.index'));
        }

        $this->positionRepository->delete($id);
        if(request()->ajax()){
            return $this->sendSuccess('Role deleted successfully.');
        }
        Flash::success('Position deleted successfully.');

        return redirect(route('positions.index'));
    }

    protected function getOptionItems($id = null){
        $parents = (new Collection(['' => 'Pilih posisi']))->union((new PositionRepository)->pluck());
        if ($id) {
            $parents->forget($id);
        }

        return compact('parents');
    }
}
