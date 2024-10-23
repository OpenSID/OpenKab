
     <div class="card container bg-c2 border-0 shadow-none">
            <div class="dashboard-header d-flex justify-content-between align-items-center mt-1">
                <h2>     
                  {{ $identitasAplikasi['nama_aplikasi'] }}</h2>
            <div>
            <div style="position: relative;" class="ml-3">
                <!-- <i class="fa fa-calendar" style="position: absolute; top: 50%; left: 10px; transform: translateY(-50%);"></i>
                <input id="reportrange" type="text" style="padding-left: 30px; padding-right: 30px; width: 100%; border: 1px solid #ccc;"/>
                <i class="fa fa-caret-down" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%);"></i>
                 -->
             
            </div>
        </div>
    </div>
    <!-- Tombol untuk Desktop -->
<div class="btn-group mt-2 mb-2 d-none d-md-flex flex-wrap">
  
    
    <a type="button" class="btn bg-white p-2 text-muted {{ Route::getCurrentRoute()->getName() == 'presisi.index' ? 'active' : '' }}" href="{{ url('presisi') }}">
        <i class="fa-solid fa-chart-column"></i> Demografi
    </a>

    <!-- Dropdown Sosial -->
    <div class="dropdown bg-white pl-3 pr-2">
        <button class="btn bg-white p-2 dropdown-toggle text-muted rounded-0 {{ Route::getCurrentRoute()->getName() == 'presisi.sosial' ? 'active' : '' }}" type="button" id="sosialDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-table"></i> Sosial
        </button>
        <div class="dropdown-menu" aria-labelledby="sosialDropdown">
            <a class="dropdown-item {{ Route::getCurrentRoute()->getName() == 'presisi.sosial' ? 'active' : '' }}" href="{{ url('presisi/sosial') }}">Data Kemiskinan</a>
            <a class="dropdown-item" href="#">Program Bantuan</a>
        </div>
    </div>

    <!-- Dropdown Kependudukan -->
    <div class="dropdown bg-white pl-3 pr-2">
        <button class="btn bg-white p-2 dropdown-toggle text-muted rounded-0 {{ Route::getCurrentRoute()->getName() == 'presisi.kependudukan' ? 'active' : '' }}" type="button" id="kependudukanDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-book"></i> Kependudukan
        </button>
        <div class="dropdown-menu" aria-labelledby="kependudukanDropdown">
            <a class="dropdown-item {{ Route::getCurrentRoute()->getName() == 'presisi.kependudukan' ? 'active' : '' }}" href="{{ url('presisi/kependudukan') }}">Statistik Penduduk</a>
            <a class="dropdown-item" href="#">Statistik Keluarga</a>
            <a class="dropdown-item" href="#">Statistik RTM</a>
        </div>
    </div>

    <a type="button" class="btn bg-white p-2 text-muted {{ Route::getCurrentRoute()->getName() == 'presisi.ekonomi' ? 'active' : '' }}" href="{{ url('presisi/ekonomi') }}">
        <i class="fa fa-solid fa-briefcase"></i> Ekonomi
    </a>
    
    <a type="button" class="btn bg-white p-2 text-muted {{ Route::getCurrentRoute()->getName() == 'presisi.kesehatan' ? 'active' : '' }}" href="{{ url('presisi/kesehatan') }}">
        <i class="fa fa-solid fa-camera-retro pl-1"></i> E-Stunting
    </a>

    <button type="button" class="btn bg-white p-2 text-muted">
        <i class="fas fa-map"></i> Geo Spasial
    </button>
</div>

<!-- Dropdown untuk Mobile -->
<div class="dropdown d-md-none mt-2 mb-2">
    <button class="btn btn-block btn-info dropdown-toggle" type="button" id="mobileMenuDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="fas fa-circle text-success text-sm"></i> Menu
    </button>
    <div class="dropdown-menu" aria-labelledby="mobileMenuDropdown">
        <!-- <a class="dropdown-item" href="{{ url('presisi') }}">
            <i class="fas fa-circle text-success text-sm"></i> Terkini
        </a> -->
        <a class="dropdown-item {{ Route::getCurrentRoute()->getName() == 'presisi.index' ? 'active' : '' }}" href="{{ url('presisi') }}">
            <i class="fa-solid fa-chart-column"></i> Demografi
        </a>
        <a class="dropdown-item {{ Route::getCurrentRoute()->getName() == 'presisi.sosial' ? 'active' : '' }}" href="{{ url('presisi/sosial') }}">
            <i class="fas fa-table"></i> Sosial - Data Kemiskinan
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