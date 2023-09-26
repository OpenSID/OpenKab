<!-- Navbar Start -->
<div class="container-fluid nav-bar bg-transparent">

    <nav id="menu-navbar" class="navbar navbar-expand-lg bg-white navbar-light py-0 px-4">
        <a href="/" class="navbar-brand d-flex align-items-center text-center">
            <div class="p-2 me-2">
                <img class="img-fluid img-logo" src="{{ $identitasAplikasi['logo'] ? Storage::url('img/'.$identitasAplikasi['logo']) : asset('assets/img/opensid_logo.png') }}" alt="Icon">
            </div>
            <h1 class="m-0 text-logo">{{ $identitasAplikasi['nama_aplikasi'] }}</h1>
        </a>
        <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav ms-auto">
                {!! generateMenu((new \App\Http\Repository\CMS\MenuRepository)->tree()) !!}
            </ul>
            <a href="/login" class="btn btn-login px-3 d-none d-lg-flex">Login</a>
        </div>
    </nav>
</div>
<!-- Navbar End -->
