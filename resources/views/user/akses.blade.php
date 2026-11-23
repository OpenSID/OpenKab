<div class="col-12">
    <hr>
    <h5 class="mb-1">Pembatasan Hak Akses DataSpasial</h5>
    <p class="text-muted small mb-3">
        Secara default pengguna dapat melihat <strong>semua wilayah</strong> dan <strong>semua sektor</strong>.
        Aktifkan pembatasan di bawah hanya jika pengguna perlu dibatasi.
    </p>
    <input type="hidden" name="akses_form" value="1">

    {{-- ===== Wilayah ===== --}}
    <div class="card card-outline card-secondary mb-3">
        <div class="card-body">
            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" class="custom-control-input" id="akses_wilayah_aktif" name="akses_wilayah_aktif" value="1"
                    @checked($akses['wilayah']['aktif'])>
                <label class="custom-control-label" for="akses_wilayah_aktif"><strong>Batasi hak akses wilayah</strong></label>
            </div>

            <div id="akses_wilayah_panel" class="pl-4" style="display: none;">
                <div class="form-group">
                    <label for="akses_wilayah_level">Level wilayah</label>
                    <select id="akses_wilayah_level" name="akses_wilayah_level" class="form-control" style="max-width: 320px;">
                        <option value="">— pilih level —</option>
                        <option value="kabupaten">Kabupaten</option>
                        <option value="kecamatan">Kecamatan</option>
                        <option value="desa">Desa</option>
                    </select>
                </div>

                <div class="form-group akses-baris" data-level="kabupaten,kecamatan,desa" style="display:none;">
                    <label for="akses_wilayah_kabupaten">Kabupaten</label>
                    <select id="akses_wilayah_kabupaten" name="akses_wilayah_kabupaten" class="form-control" style="max-width: 420px;">
                        <option value="">— pilih kabupaten —</option>
                    </select>
                </div>

                <div class="form-group akses-baris" data-level="kecamatan,desa" style="display:none;">
                    <label for="akses_wilayah_kecamatan">Kecamatan</label>
                    <select id="akses_wilayah_kecamatan" name="akses_wilayah_kecamatan" class="form-control" style="max-width: 420px;">
                        <option value="">— pilih kecamatan —</option>
                    </select>
                </div>

                <div class="form-group akses-baris" data-level="desa" style="display:none;">
                    <label for="akses_wilayah_desa">Desa</label>
                    <select id="akses_wilayah_desa" name="akses_wilayah_desa" class="form-control" style="max-width: 420px;">
                        <option value="">— pilih desa —</option>
                    </select>
                </div>
                <small class="form-text text-muted">Satu pilihan wilayah per pengguna. Kecamatan atau desa mengikuti kabupaten/kecamatan induknya.</small>
            </div>
        </div>
    </div>

    {{-- ===== Sektor ===== --}}
    <div class="card card-outline card-secondary mb-3">
        <div class="card-body">
            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" class="custom-control-input" id="akses_sektor_aktif" name="akses_sektor_aktif" value="1"
                    @checked($akses['sektor']['aktif'])>
                <label class="custom-control-label" for="akses_sektor_aktif"><strong>Batasi hak akses sektor</strong></label>
            </div>

            <div id="akses_sektor_panel" class="pl-4" style="display: none;">
                @forelse ($sektors as $s)
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="sektor_{{ $s['kode'] }}" name="akses_sektor[]"
                            value="{{ $s['kode'] }}" @checked(in_array($s['kode'], $akses['sektor']['pilih'], true))>
                        <label class="custom-control-label" for="sektor_{{ $s['kode'] }}">{{ $s['nama'] }} <small class="text-muted">({{ $s['kode'] }})</small></label>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Daftar sektor tidak tersedia saat ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
(function () {
    var data = @json($akses['data']);
    var init = @json($akses['wilayah']);

    var $aktif = document.getElementById('akses_wilayah_aktif');
    var $panel = document.getElementById('akses_wilayah_panel');
    var $level = document.getElementById('akses_wilayah_level');
    var $kab = document.getElementById('akses_wilayah_kabupaten');
    var $kec = document.getElementById('akses_wilayah_kecamatan');
    var $desa = document.getElementById('akses_wilayah_desa');
    var $sektorAktif = document.getElementById('akses_sektor_aktif');
    var $sektorPanel = document.getElementById('akses_sektor_panel');

    function isiOpsi(select, daftar, nilai, placeholder) {
        select.innerHTML = '';
        var kosong = document.createElement('option');
        kosong.value = '';
        kosong.textContent = placeholder;
        select.appendChild(kosong);
        daftar.forEach(function (item) {
            var o = document.createElement('option');
            o.value = item.kode;
            o.textContent = item.nama + ' (' + item.kode + ')';
            if (item.kode === nilai) { o.selected = true; }
            select.appendChild(o);
        });
    }

    function tampilBaris() {
        var lv = $level.value;
        document.querySelectorAll('.akses-baris').forEach(function (el) {
            el.style.display = lv && el.getAttribute('data-level').split(',').indexOf(lv) !== -1 ? '' : 'none';
        });
    }

    function segarKecamatan(kabKode, kecNilai) {
        var daftar = data.kecamatan.filter(function (k) { return k.kab === kabKode; });
        isiOpsi($kec, daftar, kecNilai, '— pilih kecamatan —');
    }

    function segarDesa(kecKode, desaNilai) {
        var daftar = data.desa.filter(function (d) { return d.kec === kecKode; });
        isiOpsi($desa, daftar, desaNilai, '— pilih desa —');
    }

    function segarSemua(awal) {
        var kab = awal ? init.kab : $kab.value;
        var kec = awal ? init.kec : $kec.value;
        var desaNilai = awal ? init.kode : '';
        isiOpsi($kab, data.kabupaten, kab, '— pilih kabupaten —');
        segarKecamatan(kab, awal ? kec : '');
        segarDesa(awal ? kec : '', desaNilai);
    }

    $kab.addEventListener('change', function () { segarKecamatan($kab.value, ''); segarDesa('', ''); });
    $kec.addEventListener('change', function () { segarDesa($kec.value, ''); });
    $level.addEventListener('change', function () { tampilBaris(); });

    function togglePanel() {
        $panel.style.display = $aktif.checked ? '' : 'none';
        $sektorPanel.style.display = $sektorAktif.checked ? '' : 'none';
    }
    $aktif.addEventListener('change', togglePanel);
    $sektorAktif.addEventListener('change', togglePanel);

    // Keadaan awal dari data tersimpan.
    $level.value = init.level || '';
    segarSemua(true);
    if (init.kode && init.level === 'kabupaten') { $kab.value = init.kode; }
    if (init.level === 'kecamatan') { $kab.value = init.kab; $kec.value = init.kode; }
    if (init.level === 'desa') { $kab.value = init.kab; $kec.value = init.kec; $desa.value = init.kode; }
    tampilBaris();
    togglePanel();
})();
</script>
