<div class="container">
    <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s">
        <h1 class="mb-3">Pejabat Daerah</h1>
    </div>
    <div class="row g-4">
        @forelse ((new App\Http\Repository\EmployeeRepository)->all() as $employee)
        <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
            <div class="pejabat-item rounded overflow-hidden">
                <div class="pejabat-relative">
                    <img class="img-thumbnail rounded-circle" src="{{ $employee->foto ? Storage::url($employee->foto) : asset('assets/img/avatar.png') }}" alt="">
                </div>
                <div class="text-center p-4 mt-3">
                    <h5 class="fw-bold mb-0">{{ $employee->name }}</h5>
                    <small>{{ $employee->position?->name }} {{ $employee->department?->name }}</small>
                </div>
            </div>
        </div>
        @empty
        @endforelse
    </div>
</div>
