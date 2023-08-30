<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Controllers\AppBaseController;
use App\Http\Repository\DepartmentRepository;
use App\Http\Transformers\DepartmentTransformer;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Collection;

class DepartmentController extends AppBaseController
{
    /** @var DepartmentRepository $departmentRepository*/
    private $departmentRepository;

    public function __construct(DepartmentRepository $departmentRepo)
    {
        $this->departmentRepository = $departmentRepo;
    }

    /**
     * Display a listing of the Department.
     */
    public function index(Request $request)
    {
        if ($request->ajax()){
            $departments = $this->departmentRepository->paginate(10);
            return $this->fractal($this->departmentRepository->listDepartment(), new DepartmentTransformer, 'departments')->respond();
        }

        return view('departments.index');
    }


    /**
     * Show the form for creating a new Department.
     */
    public function create()
    {
        return view('departments.create', $this->getOptionItems());
    }

    /**
     * Store a newly created Department in storage.
     */
    public function store(CreateDepartmentRequest $request)
    {
        $input = $request->all();

        $department = $this->departmentRepository->create($input);

        Flash::success('Department saved successfully.');

        return redirect(route('departments.index'));
    }

    /**
     * Display the specified Department.
     */
    public function show($id)
    {
        $department = $this->departmentRepository->find($id);

        if (empty($department)) {
            Flash::error('Department not found');

            return redirect(route('departments.index'));
        }

        return view('departments.show')->with('department', $department);
    }

    /**
     * Show the form for editing the specified Department.
     */
    public function edit($id)
    {
        $department = $this->departmentRepository->find($id);

        if (empty($department)) {
            Flash::error('Department not found');

            return redirect(route('departments.index'));
        }

        return view('departments.edit', $this->getOptionItems($id))->with('department', $department);
    }

    /**
     * Update the specified Department in storage.
     */
    public function update($id, UpdateDepartmentRequest $request)
    {
        $department = $this->departmentRepository->find($id);

        if (empty($department)) {
            Flash::error('Department not found');

            return redirect(route('departments.index'));
        }

        $department = $this->departmentRepository->update($request->all(), $id);

        Flash::success('Department updated successfully.');

        return redirect(route('departments.index'));
    }

    /**
     * Remove the specified Department from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $department = $this->departmentRepository->find($id);

        if (empty($department)) {
            Flash::error('Department not found');

            return redirect(route('departments.index'));
        }

        $this->departmentRepository->delete($id);
        if(request()->ajax()){
            return $this->sendSuccess('Role deleted successfully.');
        }
        Flash::success('Department deleted successfully.');

        return redirect(route('departments.index'));
    }

    protected function getOptionItems($id = null){
        $parents = (new Collection(['' => 'Pilih departement']))->union((new DepartmentRepository)->pluck());
        if ($id) {
            $parents->forget($id);
        }

        return compact('parents');
    }
}
