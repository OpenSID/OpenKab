<div class="col-md-12">
    <div class="card rounded-0 border-0 shadow-none">
        <!-- <div class="card-header bg-white">Data {{ config('app.sebutanDesa') }}</div> -->
        <div class="card-body">
            <!-- <div class="table-responsive">
                <table class="table table-striped" id="summary-penduduk">
                    <thead>
                        <tr>
                            <th class="padat">No</th>
                            <th>{{ config('app.sebutanDesa') }}</th>
                            <th>Kecamatan</th>
                            <th class="padat">Jumlah Penduduk</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div> -->
            <div class="row">
                <!-- Filter Kategori -->
                <div id="filter-container" class="col-md-3">
                    <div class=" card rounded-0">
                        <div class="card-header">
                            <span class="text-muted">DAFTAR KATEGORI</span>


                        </div>
                        <ul class="nav nav-pills flex-column" id="nav-kategori">
                            @foreach ($kategori as $key => $sub)
                                <li class="nav-item active">
                                    <a href="javascript:;" class="nav-link rounded-0" data-key="{{ $sub->id }}"
                                        data-name="{{ $sub->nama }}">
                                        <i class="fas fa-inbox"></i> {{ $sub->nama }}
                                    </a>
                                </li>
                            @endforeach



                        </ul>
                    </div><br>
                </div>
                <div class="col-md-9">
                    <input type="text" id="search-keyword" class="form-control mb-3 rounded-0"
                        placeholder="Masukan kata kunci">

                    <!-- Kontainer Kartu -->
                    <div id="card-container">
                        <!-- Kartu akan di-generate melalui JavaScript -->
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>
</div>
