<?php

namespace App\Http\Controllers;

use App\Http\Repository\DepartmentRepository;
use App\Http\Requests\CreateDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Transformers\DepartmentTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class DepartmentController extends AppBaseController
{
    /** @var DepartmentRepository */
    private $departmentRepository;

    protected $permission = 'organisasi-departemen';

    public function __construct(DepartmentRepository $departmentRepo)
    {
        $this->departmentRepository = $departmentRepo;
    }

    /**
     * Display a listing of the Department.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->fractal($this->departmentRepository->listDepartment(), new DepartmentTransformer, 'departments')->respond();
        }
        $listPermission = $this->generateListPermission();

        return view('departments.index')->with($listPermission);
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

        Session::flash('success', 'Departemen berhasil disimpan.');

        return redirect(route('departments.index'));
    }

    /**
     * Display the specified Department.
     */
    public function show($id)
    {
        $department = $this->departmentRepository->find($id);

        if (empty($department)) {
            Session::flash('error', 'Departemen tidak ditemukan');

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
            Session::flash('error', 'Departemen tidak ditemukan');

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
            Session::flash('error', 'Departemen tidak ditemukan');

            return redirect(route('departments.index'));
        }

        $department = $this->departmentRepository->update($request->all(), $id);

        Session::flash('success', 'Departemen berhasil diupdate.');

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
            Session::flash('error', 'Departemen tidak ditemukan');

            return redirect(route('departments.index'));
        }

        $this->departmentRepository->delete($id);
        if (request()->ajax()) {
            return $this->sendSuccess('Departemen berhasil dihapus.');
        }
        Session::flash('success', 'Departemen berhasil dihapus.');

        return redirect(route('departments.index'));
    }

    protected function getOptionItems($id = null)
    {
        $parents = (new Collection(['' => 'Pilih departement']))->union((new DepartmentRepository)->pluck());
        if ($id) {
            $parents->forget($id);
        }

        return compact('parents');
    }
}
