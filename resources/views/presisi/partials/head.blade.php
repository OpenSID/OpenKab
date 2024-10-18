            <div class="card container bg-c2 border-0 shadow-none">
                <div class="dashboard-header d-flex justify-content-between align-items-center mt-1">
                    <h2>{{ config('app.namaAplikasi') }}</h2>
                    <div>
                    <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                <i class="fa fa-calendar"></i>&nbsp;
                <span></span> <i class="fa fa-caret-down"></i>
            </div>
        </div>
        <input type="text" name="filter" value="" class="form-control datepicker" id="filter">

    </div>
    <div class="btn-group mt-2 mb-2">
        <button type="button" class="btn bg-white p-2 mr-1 text-muted"><span class="c-badge-small rounded-circle"><i class="fas fa-circle text-success text-sm"></i></span> Terkini</button>
        <a type="button" class="btn bg-white p-2 text-muted {{ Route::getCurrentRoute()->getName()== 'presisi.index' ? 'active' : '' }}" href="{{ url('presisi') }}"><i class="fa-solid fa-chart-column"></i> Demografi</a>
        
        <!-- Dropdown Sosial -->
        <div class="dropdown pr-5 bg-white">
            <button class="btn bg-white p-2 dropdown-toggle text-muted rounded-0 {{ Route::getCurrentRoute()->getName()== 'presisi.sosial' ? 'active' : '' }}" type="button" id="sosialDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-table"></i> Sosial
            </button>
            <div class="dropdown-menu" aria-labelledby="sosialDropdown">
                <a class="dropdown-item {{ Route::getCurrentRoute()->getName()== 'presisi.sosial' ? 'active' : '' }} " href="{{ url('presisi/sosial') }}">Data Kemiskinan</a>
                <a class="dropdown-item" href="#">Program Bantuan</a>
            </div>
        </div>

        <!-- Dropdown Kependudukan -->
        <div class="dropdown pr-5 bg-white">
            <button class="btn bg-white p-2 dropdown-toggle text-muted rounded-0 {{ Route::getCurrentRoute()->getName()== 'presisi.kependudukan' ? 'active' : '' }}" type="button" id="kependudukanDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-book"></i> Kependudukan
            </button>
            <div class="dropdown-menu" aria-labelledby="kependudukanDropdown">
                <a class="dropdown-item {{ Route::getCurrentRoute()->getName()== 'presisi.kependudukan' ? 'active' : '' }}" href="{{ url('presisi/kependudukan') }}">Statistik Penduduk</a>
                <a class="dropdown-item" href="#">Statistik Keluarga</a>
                <a class="dropdown-item" href="#">Statistik RTM</a>
            </div>
        </div>

        <a type="button" class="btn bg-white p-2 text-muted {{ Route::getCurrentRoute()->getName()== 'presisi.ekonomi' ? 'active' : '' }}" href="{{ url('presisi/ekonomi') }}"><i class="fa fa-solid fa-briefcase"></i> Ekonomi</a>
        <a type="button" class="btn bg-white p-2 text-muted {{ Route::getCurrentRoute()->getName()== 'presisi.kesehatan' ? 'active' : '' }} " href="{{ url('presisi/kesehatan') }}"><i class="fa fa-solid fa-camera-retro"></i> E-Stunting</a>
        <button type="button" class="btn bg-white p-2 text-muted"><i class="fas fa-map"></i> Geo Spasial</button>
    </div>
</div>