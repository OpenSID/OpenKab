<?php

namespace App\Http\Controllers;

use App\Http\Repository\DepartmentRepository;
use App\Http\Repository\EmployeeRepository;
use App\Http\Repository\PositionRepository;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Transformers\EmployeeTransformer;
use App\Traits\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class EmployeeController extends AppBaseController
{
    use UploadedFile;

    /** @var EmployeeRepository */
    private $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepo)
    {
        $this->employeeRepository = $employeeRepo;
        $this->pathFolder = 'uploads/employees';
    }

    /**
     * Display a listing of the Employee.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->fractal($this->employeeRepository->listEmployee(), new EmployeeTransformer, 'employees')->respond();
        }

        return view('employees.index');
    }

    /**
     * Show the form for creating a new Employee.
     */
    public function create()
    {
        return view('employees.create', $this->getOptionItems())->with('employee', null);
    }

    /**
     * Store a newly created Employee in storage.
     */
    public function store(CreateEmployeeRequest $request)
    {
        $input = $request->all();
        if ($request->file('foto')) {
            $input['foto'] = $this->uploadFile($request, 'foto');
        }
        $employee = $this->employeeRepository->create($input);

        Session::flash('success', 'Pegawai berhasil disimpan.');

        return redirect(route('employees.index'));
    }

    /**
     * Display the specified Employee.
     */
    public function show($id)
    {
        $employee = $this->employeeRepository->find($id);

        if (empty($employee)) {
            Session::flash('error', 'Pegawai tidak ditemukan');

            return redirect(route('employees.index'));
        }

        return view('employees.show', $this->getOptionItems($id))->with('employee', $employee);
    }

    /**
     * Show the form for editing the specified Employee.
     */
    public function edit($id)
    {
        $employee = $this->employeeRepository->find($id);

        if (empty($employee)) {
            Session::flash('error', 'Pegawai tidak ditemukan');

            return redirect(route('employees.index'));
        }

        return view('employees.edit', $this->getOptionItems($id))->with('employee', $employee);
    }

    /**
     * Update the specified Employee in storage.
     */
    public function update($id, UpdateEmployeeRequest $request)
    {
        $employee = $this->employeeRepository->find($id);

        if (empty($employee)) {
            Session::flash('error', 'Pegawai tidak ditemukan');

            return redirect(route('employees.index'));
        }

        $input = $request->all();
        if ($request->file('foto')) {
            $input['foto'] = $this->uploadFile($request, 'foto');
        }

        $employee = $this->employeeRepository->update($input, $id);

        Session::flash('success', 'Pegawai berhasil diupdate.');

        return redirect(route('employees.index'));
    }

    /**
     * Remove the specified Employee from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $employee = $this->employeeRepository->find($id);

        if (empty($employee)) {
            Session::flash('error', 'Pegawai tidak ditemukan');

            return redirect(route('employees.index'));
        }

        $this->employeeRepository->delete($id);
        if (request()->ajax()) {
            return $this->sendSuccess('Pegawai berhasil dihapus.');
        }
        Session::flash('success', 'Pegawai berhasil dihapus.');

        return redirect(route('employees.index'));
    }

    protected function getOptionItems($id = null)
    {
        $departments = (new Collection(['' => 'Pilih departement']))->union((new DepartmentRepository)->pluck());
        $positions = (new Collection(['' => 'Pilih jabatan']))->union((new PositionRepository)->pluck());

        return compact('departments', 'positions');
    }
}
