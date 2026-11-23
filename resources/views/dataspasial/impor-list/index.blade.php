@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')

    @php
        $kecData = [];
        if (isset($kecamatans)) {
            $kecData = is_object($kecamatans) && method_exists($kecamatans, 'data')
                ? ($kecamatans->data ?? [])
                : (is_array($kecamatans) ? ($kecamatans['data'] ?? $kecamatans) : []);
        }
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3></div>
                <div class="card-body">
                    <form id="form-filter" onsubmit="return false;">
                        <div class="row">
                            <div class="col-lg col-md-4 col-sm-6 mb-2">
                                <label for="sektor_id">Sektor</label>
                                <select id="sektor_id" class="form-control form-control-sm">
                                    <option value="">— pilih sektor —</option>
                                    @foreach($sektors as $s)
                                        @php $a = $s['attributes'] ?? []; @endphp
                                        <option value="{{ $s['id'] ?? '' }}" data-kode="{{ $a['kode'] ?? '' }}">{{ $a['nama'] ?? ($a['kode'] ?? $s['id']) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg col-md-4 col-sm-6 mb-2">
                                <label for="versi">Versi Skema</label>
                                <select id="versi" class="form-control form-control-sm" disabled>
                                    <option value="">Semua Versi</option>
                                </select>
                            </div>
                            <div class="col-lg col-md-4 col-sm-6 mb-2">
                                <label for="kode_kecamatan">Kecamatan</label>
                                <select id="kode_kecamatan" class="form-control form-control-sm">
                                    <option value="">Semua Kecamatan</option>
                                    @foreach($kecData as $k)
                                        @php
                                            $attr = is_object($k) ? (array) $k : ($k['attributes'] ?? $k);
                                            $kode = $attr['kode_kecamatan'] ?? ($attr['kode'] ?? ($attr['id'] ?? ''));
                                            $nama = $attr['nama_kecamatan'] ?? ($attr['nama'] ?? ($kode ?: '-'));
                                        @endphp
                                        <option value="{{ $kode }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg col-md-4 col-sm-6 mb-2">
                                <label for="q">Cari Desa / Isian</label>
                                <input type="text" id="q" class="form-control form-control-sm" maxlength="100" placeholder="Nama desa atau isian">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-12 mb-2">
                                <button type="button" id="btn-reset" class="btn btn-secondary btn-block">
                                    <span class="fas fa-ban"></span> Reset
                                </button>
                            </div>
                            <div class="col-md-6 col-12 mb-2">
                                <button type="button" id="btn-terapkan" class="btn btn-primary btn-block">
                                    <span class="fas fa-search"></span> Tampilkan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row" id="ringkasan" style="display:none;">
                <div class="col-md-4"><div class="info-box"><div class="info-box-content"><span class="info-box-text">Total baris</span><span class="info-box-number" id="r-baris">0</span></div></div></div>
                <div class="col-md-4"><div class="info-box"><div class="info-box-content"><span class="info-box-text">Jumlah desa</span><span class="info-box-number" id="r-desa">0</span></div></div></div>
                <div class="col-md-4"><div class="info-box"><div class="info-box-content"><span class="info-box-text">Total nilai angka</span><span class="info-box-number" id="r-angka">0</span></div></div></div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0">
                    <div id="pesan" class="p-3 text-muted">Pilih sektor, lalu tekan Terapkan.</div>
                    <table class="table table-hover table-sm mb-0" id="tabel-daftar" style="display:none;">
                        <thead>
                            <tr>
                                <th>Kecamatan</th>
                                <th>Desa</th>
                                <th>Fasilitas</th>
                                <th>Jenis</th>
                                <th>Isian</th>
                                <th class="text-right">Nilai</th>
                                <th>Periode</th>
                                <th>Waktu masuk</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-daftar"></tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center" id="paginasi" style="display:none;">
                    <button type="button" class="btn btn-default btn-sm" id="btn-prev">&laquo; Sebelumnya</button>
                    <span id="info-halaman" class="small text-muted"></span>
                    <button type="button" class="btn btn-default btn-sm" id="btn-next">Berikutnya &raquo;</button>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script nonce="{{ csp_nonce() }}">
(function () {
    // Tanpa jQuery: layout ini tidak memuat jQuery.
    // Path relatif: route() memakai APP_URL, yang bisa berbeda dengan host tempat halaman dibuka.
    var urlData = @json(parse_url(route('dataspasial.impor.daftar.data'), PHP_URL_PATH));
    var urlVersi = @json(parse_url(route('dataspasial.impor.daftar.versi'), PHP_URL_PATH));
    var halaman = 1;
    var totalHalaman = 1;

    function el(id) { return document.getElementById(id); }

    function tampil(id, on) { var e = el(id); if (e) { e.style.display = on ? '' : 'none'; } }

    function teks(v) {
        var d = document.createElement('div');
        d.textContent = v == null ? '' : String(v);
        return d.innerHTML;
    }

    function nilaiSelect(id) { return el(id) ? el(id).value : ''; }

    function kondisiFilter() {
        var p = { sektor_id: nilaiSelect('sektor_id'), page: halaman };
        var versi = nilaiSelect('versi');
        if (versi) { p.versi = versi; }
        var kec = nilaiSelect('kode_kecamatan');
        if (kec) { p.kode_kecamatan = kec; }
        var q = el('q') ? el('q').value.trim() : '';
        if (q) { p.q = q; }
        return p;
    }

    function tampilPesan(pesan) {
        tampil('tabel-daftar', false);
        tampil('paginasi', false);
        tampil('ringkasan', false);
        el('pesan').textContent = pesan;
        tampil('pesan', true);
    }

    function muat() {
        if (!nilaiSelect('sektor_id')) {
            tampilPesan('Pilih sektor terlebih dahulu.');
            return;
        }
        el('pesan').textContent = 'Memuat data...';
        tampil('pesan', true);

        var query = Object.keys(kondisiFilter()).map(function (k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(kondisiFilter()[k]);
        }).join('&');

        fetch(urlData + '?' + query, { headers: { 'Accept': 'application/json' } })
            .then(function (r) {
                if (r.status === 403) { return { __galat: 'Anda tidak memiliki akses ke sektor ini.' }; }
                if (!r.ok) { throw new Error('http ' + r.status); }
                return r.json();
            })
            .then(function (res) {
                if (res.__galat) { tampilPesan(res.__galat); return; }
                if (res.meta && res.meta.tanpa_cakupan) {
                    tampilPesan('Akun Anda belum memiliki hak akses wilayah dan sektor. Hubungi admin untuk mengatur hak akses.');
                    return;
                }
                if (res.meta && res.meta.unavailable) {
                    tampilPesan(res.meta.message || 'Data sementara tidak tersedia.');
                    return;
                }

                var baris = res.data || [];
                var pag = res.meta.pagination;
                totalHalaman = pag.total_pages || 1;

                el('r-baris').textContent = res.ringkasan.total_baris;
                el('r-desa').textContent = res.ringkasan.total_desa;
                el('r-angka').textContent = res.ringkasan.total_nilai_angka;
                tampil('ringkasan', true);

                if (baris.length === 0) {
                    tampilPesan('Tidak ada data yang cocok dengan filter.');
                    return;
                }

                var html = '';
                baris.forEach(function (b) {
                    var nilai = b.nilai === null ? '—' : teks(b.nilai);
                    html += '<tr>'
                        + '<td>' + teks(b.kode_kecamatan || '-') + '</td>'
                        + '<td>' + teks(b.nama_desa || b.kode_desa || '-') + '</td>'
                        + '<td>' + teks(b.nama_fasilitas || '-') + '</td>'
                        + '<td>' + teks(b.jenis_fasilitas || '-') + '</td>'
                        + '<td>' + teks(b.isian_label) + (b.isian_satuan ? ' <small class="text-muted">(' + teks(b.isian_satuan) + ')</small>' : '') + '</td>'
                        + '<td class="text-right">' + nilai + '</td>'
                        + '<td>' + teks(b.periode) + '</td>'
                        + '<td>' + teks(b.waktu_input || '-') + '</td>'
                        + '</tr>';
                });
                el('tbody-daftar').innerHTML = html;
                tampil('tabel-daftar', true);
                tampil('pesan', false);
                tampil('paginasi', true);
                el('info-halaman').textContent = 'Halaman ' + halaman + ' dari ' + totalHalaman + ' (' + pag.total + ' baris)';
                el('btn-prev').disabled = halaman <= 1;
                el('btn-next').disabled = halaman >= totalHalaman;
            })
            .catch(function () {
                tampilPesan('Data sementara tidak tersedia.');
            });
    }

    // Dropdown versi skema mengikuti sektor yang dipilih.
    function muatVersi() {
        var sel = el('versi');
        sel.innerHTML = '<option value="">Semua versi</option>';
        sel.disabled = true;
        var sektor = nilaiSelect('sektor_id');
        if (!sektor) { return; }

        fetch(urlVersi + '?sektor_id=' + encodeURIComponent(sektor), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : { data: [] }; })
            .then(function (res) {
                (res.data || []).forEach(function (v) {
                    var o = document.createElement('option');
                    o.value = v.id;
                    o.textContent = 'Versi ' + v.nomor_versi + (v.status ? ' (' + v.status + ')' : '');
                    sel.appendChild(o);
                });
                sel.disabled = false;
            })
            .catch(function () { sel.disabled = false; });
    }

    el('sektor_id').addEventListener('change', function () { muatVersi(); });
    el('btn-terapkan').addEventListener('click', function () { halaman = 1; muat(); });
    el('btn-reset').addEventListener('click', function () {
        el('form-filter').reset();
        halaman = 1;
        tampilPesan('Pilih sektor, lalu tekan Terapkan.');
    });
    el('btn-prev').addEventListener('click', function () { if (halaman > 1) { halaman--; muat(); } });
    el('btn-next').addEventListener('click', function () { if (halaman < totalHalaman) { halaman++; muat(); } });
})();
</script>
@endpush
