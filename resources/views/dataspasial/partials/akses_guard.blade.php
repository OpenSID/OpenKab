{{--
    Penjaga dropdown hak akses DataSpasial (issue #1320).

    Dropdown sektor, kabupaten, kecamatan, dan desa pada sejumlah halaman diisi lewat AJAX dari browser,
    sehingga server tidak bisa memfilternya saat render. Skrip ini menghapus opsi di luar hak akses pengguna
    setiap kali opsi ditambahkan. Penegakan sebenarnya tetap di server (middleware dan controller).
--}}
@php
    $uidAkses = auth()->id();
    $layananAkses = app(\App\Services\CakupanPenggunaService::class);
    // Kode induk wilayah pengguna dihitung di server (App\Support\KodeWilayah), bukan di browser.
    $wilayahAkses = $uidAkses ? $layananAkses->wilayahAkses((int) $uidAkses) : null;
    if ($wilayahAkses !== null) {
        $wilayahAkses['kab'] = \App\Support\KodeWilayah::kabupaten($wilayahAkses['kode']);
        $wilayahAkses['kec'] = \App\Support\KodeWilayah::kecamatan($wilayahAkses['kode']);
    }
    $aksesJs = [
        'sektor' => $uidAkses ? $layananAkses->sektorAkses((int) $uidAkses) : null,
        'wilayah' => $wilayahAkses,
    ];
@endphp
<script nonce="{{ csp_nonce() }}">
(function () {
    var akses = @json($aksesJs);
    if (akses.sektor === null && akses.wilayah === null) {
        return;
    }

    // Opsi dropdown dibandingkan dengan awalan kodenya (4 = kabupaten, 6 = kecamatan), sama dengan KodeWilayah.
    function izinKabupaten(v) {
        if (!akses.wilayah) { return true; }
        return akses.wilayah.level === 'kabupaten'
            ? v === akses.wilayah.kode
            : v === akses.wilayah.kab;
    }

    function izinKecamatan(v) {
        if (!akses.wilayah) { return true; }
        var w = akses.wilayah;
        if (w.level === 'kabupaten') { return v.substr(0, 4) === w.kab; }
        if (w.level === 'kecamatan') { return v === w.kode; }
        return v === w.kec;
    }

    function izinDesa(v) {
        if (!akses.wilayah) { return true; }
        var w = akses.wilayah;
        if (w.level === 'kabupaten') { return v.substr(0, 4) === w.kab; }
        if (w.level === 'kecamatan') { return v.substr(0, 6) === w.kode; }
        return v === w.kode;
    }

    function izinOpsi(select, v, opt) {
        if (v === '') { return true; }
        var nama = ((select.id || '') + ' ' + (select.name || '')).toLowerCase();
        if (nama.indexOf('sektor') !== -1) {
            // Value opsi bisa berupa id sektor; kode sektor ada di data-kode bila tersedia.
            var kode = (opt && opt.getAttribute('data-kode')) || v;
            return akses.sektor === null || akses.sektor.indexOf(kode) !== -1;
        }
        if (nama.indexOf('kecamatan') !== -1) { return izinKecamatan(v); }
        if (nama.indexOf('desa') !== -1) { return izinDesa(v); }
        if (nama.indexOf('kabupaten') !== -1) { return izinKabupaten(v); }

        return true;
    }

    function saringSemua() {
        document.querySelectorAll('select').forEach(function (select) {
            Array.prototype.slice.call(select.options).forEach(function (opt) {
                if (!izinOpsi(select, opt.value, opt)) { opt.remove(); }
            });
        });
    }

    saringSemua();
    new MutationObserver(saringSemua).observe(document.body, { childList: true, subtree: true });
})();
</script>
