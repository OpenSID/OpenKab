<!-- Navbar Start -->
<div class="container-fluid nav-bar bg-transparent">
    <nav class="navbar navbar-expand-lg bg-white navbar-light py-0 px-4">
        <a href="index.html" class="navbar-brand d-flex align-items-center text-center">
            <div class="p-2 me-2">
                <img class="img-fluid img-logo" src="{{ asset('web/img/logo.png') }}" alt="Icon">
            </div>
            <h1 class="m-0 text-logo">{{ config('app.name')}}</h1>
        </a>
        <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto">
                <a href="index.html" class="nav-item nav-link active">Beranda</a>
                <a href="tentangkami.html" class="nav-item nav-link">Tentang Kami</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Informasi</a>
                    <div class="dropdown-menu rounded-0 m-0">
                        <a href="informasi.html" class="dropdown-item">Event</a>
                        <a href="informasi.html" class="dropdown-item">Kegiatan</a>
                    </div>
                </div>
                <a href="unduhan.html" class="nav-item nav-link">Daftar Unduhan</a>
            </div>
            <a href="#" class="btn btn-login px-3 d-none d-lg-flex">Login</a>
        </div>
    </nav>
</div>
<!-- Navbar End -->
