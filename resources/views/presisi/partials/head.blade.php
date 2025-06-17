
     <div class="card container bg-c2 border-0 shadow-none">
            <div class="dashboard-header d-flex justify-content-between align-items-center mt-1">
                <h2>
                    @php
                    $currentPath = '/' . Request::path();

                    if (Illuminate\Support\Str::contains($currentPath, '/module/')) {
                        $pathParts = explode('/', $currentPath);

                        // Buat ulang URL hanya dengan bagian kedua dan ketiga, misal: /presisi/keluarga
                        $newUrl = '/presisi/' . ($pathParts[2] ?? ''); 
                        if($menu = App\Models\CMS\Menu::where('url', $newUrl)->where('menu_type', 2)->orderBy('id', 'desc')->first()) {
                            echo $menu->name.' - ';
                        }
                    }else{
                        echo App\Models\CMS\Menu::where('url', str_replace('statistik-', '/presisi/', Request::path()))->where('menu_type', 2)->orderBy('id', 'desc')->first()->name ?? '';
                    }
                    @endphp

                    {{App\Models\CMS\Menu::where('url', '/'.Request::path())->where('menu_type', 2)->orderBy('id', 'desc')->first()->name ?? ''}}
                </h2>
            <div>
            <!-- <div style="position: relative;" class="ml-3">
                <i class="fa fa-calendar" style="position: absolute; top: 50%; left: 10px; transform: translateY(-50%);"></i>
                <input id="reportrange" type="text" style="padding-left: 30px; padding-right: 30px; width: 100%; border: 1px solid #ccc;"/>
                <i class="fa fa-caret-down" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%);"></i>
            </div> -->
        </div>
    </div>
    <!-- Tombol untuk Desktop -->
<div class="btn-group mt-2 mb-2 d-none d-md-flex flex-wrap">
    {{-- <button type="button" class="btn bg-white p-2 mr-1 text-muted">
        <span class="c-badge-small rounded-circle">
            <i class="fas fa-circle text-success text-sm"></i>
        </span> Terkini
    </button>

    <a type="button" class="btn bg-white p-2 text-muted {{ Route::getCurrentRoute()->getName() == 'presisi.index' ? 'active' : '' }}" href="{{ url('presisi') }}">
        <i class="fa-solid fa-chart-column"></i> Demografi
    </a>

    <!-- Dropdown Sosial -->
    <div class="dropdown bg-white pl-3 pr-2">
        <button class="btn bg-white p-2 dropdown-toggle text-muted rounded-0 {{ Route::getCurrentRoute()->getName() == 'presisi.bantuan' ? 'active' : '' }} {{ Route::getCurrentRoute()->getName() == 'presisi.sosial' ? 'active' : '' }}" type="button" id="sosialDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-table"></i> Sosial
        </button>
        <div class="dropdown-menu" aria-labelledby="sosialDropdown">
            <a class="dropdown-item {{ Route::getCurrentRoute()->getName() == 'presisi.bantuan' ? 'active' : '' }}" href="{{ url('presisi/bantuan') }}">Program Bantuan</a>
        </div>
    </div>

    <!-- Dropdown Kependudukan -->
    <div class="dropdown bg-white pl-3 pr-2">
        <button class="btn bg-white p-2 dropdown-toggle text-muted rounded-0 {{ Route::getCurrentRoute()->getName() == 'presisi.rtm' ? 'active' : '' }} {{ Route::getCurrentRoute()->getName() == 'presisi.kependudukan' ? 'active' : '' }}" type="button" id="kependudukanDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <button class="btn bg-white p-2 dropdown-toggle text-muted rounded-0 {{ Route::getCurrentRoute()->getName() == 'presisi.keluarga' ? 'active' : '' }} {{ Route::getCurrentRoute()->getName() == 'presisi.kependudukan' ? 'active' : '' }}" type="button" id="kependudukanDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-book"></i> Kependudukan
        </button>
        <div class="dropdown-menu" aria-labelledby="kependudukanDropdown">
            <a class="dropdown-item {{ Route::getCurrentRoute()->getName() == 'presisi.kependudukan' ? 'active' : '' }}" href="{{ url('presisi/kependudukan') }}">Statistik Penduduk</a>
            
            <a class="dropdown-item {{ Route::getCurrentRoute()->getName() == 'presisi.rtm' ? 'active' : '' }}" href="{{ url('presisi/rtm') }}">Statistik RTM</a>
            <a class="dropdown-item {{ Route::getCurrentRoute()->getName() == 'presisi.keluarga' ? 'active' : '' }}" href="{{ url('presisi/keluarga') }}">Statistik Keluarga</a>
            
        </div>
    </div>

    <a type="button" class="btn bg-white p-2 text-muted {{ Route::getCurrentRoute()->getName() == 'presisi.kesehatan' ? 'active' : '' }}" href="{{ url('presisi/kesehatan') }}">
        <i class="fa fa-solid fa-camera-retro pl-1"></i> E-Stunting
    </a>

    <button type="button" class="btn bg-white p-2 text-muted">
        <i class="fas fa-map"></i> Geo Spasial
    </button> --}}

    {!! generateMenuPresisi((new \App\Http\Repository\CMS\MenuRepository)->tree(2)) !!}
</div>

<!-- Dropdown untuk Mobile -->
<div class="dropdown d-md-none mt-2 mb-2">
    <button class="btn btn-primary dropdown-toggle" type="button" id="mobileMenuDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="fas fa-circle text-success text-sm"></i> Terkini
    </button>
    <div class="dropdown-menu" aria-labelledby="mobileMenuDropdown">
        <!-- <a class="dropdown-item" href="{{ url('presisi') }}">
            <i class="fas fa-circle text-success text-sm"></i> Terkini
        </a> -->
        <a class="dropdown-item {{ Route::getCurrentRoute()->getName() == 'presisi.index' ? 'active' : '' }}" href="{{ url('presisi') }}">
            <i class="fa-solid fa-chart-column"></i> Demografi
        </a>
        <a class="dropdown-item" href="#"><i class="fas fa-table"></i> Sosial - Program Bantuan</a>
        <a class="dropdown-item {{ Route::getCurrentRoute()->getName() == 'presisi.kependudukan' ? 'active' : '' }}" href="{{ url('presisi/kependudukan') }}">
            <i class="fas fa-book"></i> Kependudukan - Statistik Penduduk
        </a>
        <a class="dropdown-item" href="#"><i class="fas fa-book"></i> Kependudukan - Statistik Keluarga</a>
        <a class="dropdown-item" href="#"><i class="fas fa-book"></i> Kependudukan - Statistik RTM</a>
        <a class="dropdown-item" href="#">
            <i class="fa fa-solid fa-briefcase"></i> Ekonomi
        </a>
        <a class="dropdown-item {{ Route::getCurrentRoute()->getName() == 'presisi.kesehatan' ? 'active' : '' }}" href="{{ url('presisi/kesehatan') }}">
            <i class="fa fa-solid fa-camera-retro"></i> E-Stunting
        </a>
        <a class="dropdown-item" href="#">
            <i class="fas fa-map"></i> Geo Spasial
        </a>
    </div>
</div>

</div>
