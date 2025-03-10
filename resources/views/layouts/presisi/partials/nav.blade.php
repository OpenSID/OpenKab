<!-- Navbar Start -->
<nav id="menu-navbar" class="navbar navbar-expand-lg bg-white navbar-light py-0 px-4">
    <a href="/" class="navbar-brand d-flex align-items-center text-center">
        <div class="p-2 me-2">
            <img class="img-fluid img-logo" src="{{ $identitasAplikasi['logo'] ? Storage::url('img/'.$identitasAplikasi['logo']) : asset('assets/img/opensid_logo.png') }}" alt="Icon">
        </div>
        <h2 class="m-0 text-logo">{{ $identitasAplikasi['nama_aplikasi'] }}<br><p class="text-black h6">Dasbor Kabupaten</p></h2>
    </a>
    <button type="button" class="m-only btn btn-outline-primary px-3" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <a href="/login" class="">Login</a>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav ms-auto">
        </ul>
        @auth
        <a href="{{ route('dasbor') }}" class="btn btn-outline-primary px-3 d-none d-lg-flex">Dashboard</a>
        @else
        <a href="/login" class="btn btn-outline-primary px-3 d-none d-lg-flex">Login</a>
        @endauth
    </div>
</nav>
<!-- Navbar End -->
