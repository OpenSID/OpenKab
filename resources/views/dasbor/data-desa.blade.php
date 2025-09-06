<div class="col-md-12">
    <div class="card rounded-0 elevation-0">
        <div class="card-header bg-white">{{ config('app.sebutanDesa') }} Aktif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="summary-penduduk">
                    <thead>
                        <tr>
                            <th class="padat">No</th>
                            <th>{{ config('app.sebutanDesa') }}</th>
                            <th>Kecamatan</th>
                            <th class="padat">Jumlah <br>Penduduk</th>
                            <th class="padat">Jumlah <br>Surat Tercetak</th>
                            <th class="padat">Jumlah <br>Artikel</th>
                            <th class="padat">Jumlah <br>Pengunjung Website</th>
                            <th class="padat">Terakhir <br>Akses Login</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
