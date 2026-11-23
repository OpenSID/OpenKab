/**
 * Halaman Status Desa DataSpasial (KAB-09 / API-09).
 *
 * Tabel desa dimuat lewat AJAX (filter kecamatan + status, paginasi); tombol
 * Nonaktifkan/Aktifkan membuka modal konfirmasi yang mengirim form POST biasa
 * (CSRF dari @csrf) sehingga hasilnya tampil sebagai flash message.
 *
 * Tombol massal "Nonaktifkan/Aktifkan Semua Desa" memakai cakupan filter kecamatan
 * (tanpa filter = se-kabupaten) dan jumlah terdampak dari `ringkasan_status`
 * (seluruh cakupan, bukan hanya halaman yang tampil).
 */
(function () {
    'use strict';

    var opts = {};
    var state = { kode_kecamatan: '', status: '', page: 1 };
    var ringkasanStatus = null;
    var lastToken = 0;

    function qs(sel) { return document.querySelector(sel); }

    function esc(s) {
        return String(s === null || s === undefined ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function show(id, on) {
        var el = qs(id);
        if (el) el.style.display = on ? '' : 'none';
    }

    function kolom() { return opts.bolehUbah ? 6 : 5; }

    function readUrl() {
        var p = new URLSearchParams(window.location.search);
        state.kode_kecamatan = p.get('kode_kecamatan') || '';
        state.status = p.get('status') || '';
        state.page = Math.max(1, parseInt(p.get('page') || '1', 10) || 1);
    }

    function syncUrl() {
        var p = new URLSearchParams();
        if (state.kode_kecamatan) p.set('kode_kecamatan', state.kode_kecamatan);
        if (state.status) p.set('status', state.status);
        if (state.page > 1) p.set('page', String(state.page));
        window.history.replaceState(null, '', window.location.pathname + (p.toString() ? '?' + p.toString() : ''));
    }

    function lencana(r) {
        return '<span class="badge badge-' + esc(r.warna_lencana) + '" data-testid="lencana-status">' + esc(r.status_label) + '</span>';
    }

    function tombolAksi(r) {
        if (!opts.bolehUbah) return '';
        var label = r.isian_aktif ? 'Nonaktifkan' : 'Aktifkan';
        var kelas = r.isian_aktif ? 'btn-outline-danger' : 'btn-outline-success';
        var ikon = r.isian_aktif ? 'fa-lock' : 'fa-lock-open';
        return '<td class="text-center"><button type="button" class="btn btn-xs ' + kelas + ' tombol-ubah-status" ' +
            'data-testid="tombol-ubah-status" data-id="' + esc(r.config_id) + '" data-nama="' + esc(r.nama_desa) + '" ' +
            'data-aktif="' + (r.isian_aktif ? '1' : '0') + '"><i class="fas ' + ikon + '"></i> ' + label + '</button></td>';
    }

    function renderTabel(baris) {
        var tb = qs('#status-desa-tbody');
        if (!baris.length) {
            tb.innerHTML = '<tr><td colspan="' + kolom() + '" class="text-center text-muted">Tidak ada desa untuk pilihan ini.</td></tr>';
            return;
        }
        tb.innerHTML = baris.map(function (r) {
            var oleh = r.dinonaktifkan_oleh ? '<br><small class="text-muted">oleh ' + esc(r.dinonaktifkan_oleh) + '</small>' : '';
            return '<tr data-testid="baris-status-desa" data-config-id="' + esc(r.config_id) + '">' +
                '<td><strong>' + esc(r.nama_desa) + '</strong><br><small class="text-muted">' + esc(r.kode_desa) + '</small></td>' +
                '<td>' + esc(r.nama_kecamatan || '—') + '</td>' +
                '<td class="text-center">' + lencana(r) + '</td>' +
                '<td>' + esc(r.alasan_nonaktif || '—') + '</td>' +
                '<td>' + (r.isian_aktif ? '—' : esc(r.dinonaktifkan_pada_label) + oleh) + '</td>' +
                tombolAksi(r) +
                '</tr>';
        }).join('');
    }

    // ------------------------------------------------------------- massal

    function namaKecamatanTerpilih() {
        var kec = qs('#filter_kecamatan_status');
        if (!kec || !state.kode_kecamatan || kec.selectedIndex < 0) return '';
        return (kec.options[kec.selectedIndex].text || '').trim();
    }

    function teksCakupan() {
        return state.kode_kecamatan ? 'di Kecamatan ' + (namaKecamatanTerpilih() || state.kode_kecamatan) : 'se-kabupaten';
    }

    /** Jumlah desa yang akan berubah: nonaktifkan → desa aktif, aktifkan → desa nonaktif. */
    function jumlahTerdampak(aktifkan) {
        if (!ringkasanStatus) return 0;
        return aktifkan ? (ringkasanStatus.nonaktif || 0) : (ringkasanStatus.aktif || 0);
    }

    /**
     * Label tombol tetap pendek ("Nonaktifkan Semua" / "Aktifkan Semua"); jumlah desa dan cakupan
     * hanya ditampilkan di modal. Cakupan tetap terlihat lewat tooltip.
     */
    function renderTombolMassal() {
        document.querySelectorAll('.tombol-status-massal').forEach(function (tombol) {
            var aktifkan = tombol.getAttribute('data-aktif') === '1';
            var n = jumlahTerdampak(aktifkan);
            tombol.disabled = !ringkasanStatus || n === 0;
            tombol.title = !ringkasanStatus
                ? 'Jumlah desa belum dapat dihitung'
                : (n === 0
                    ? 'Tidak ada desa yang perlu diubah pada cakupan ini'
                    : (aktifkan ? 'Aktifkan' : 'Nonaktifkan') + ' semua desa ' + teksCakupan());
        });
    }

    function bukaModalMassal(aktifkan) {
        var form = qs('#form-status-massal');
        if (!form) return;
        var n = jumlahTerdampak(aktifkan);
        var total = ringkasanStatus ? ringkasanStatus.total : 0;

        qs('#massal-aktif').value = aktifkan ? '1' : '0';
        qs('#massal-kode-kecamatan').value = state.kode_kecamatan;
        qs('#massal-nama-kecamatan').value = state.kode_kecamatan ? namaKecamatanTerpilih() : '';
        qs('#massal-status-filter').value = state.status;
        qs('#judul-modal-massal').textContent = aktifkan ? 'Aktifkan Semua Desa' : 'Nonaktifkan Semua Desa';
        qs('#teks-modal-massal').textContent = aktifkan
            ? n + ' dari ' + total + ' desa ' + teksCakupan() + ' akan dapat kembali mengisi data spasial.'
            : n + ' dari ' + total + ' desa ' + teksCakupan() + ' tidak akan dapat menambah, mengubah, atau mengisi data spasial sampai diaktifkan kembali.';

        var alasan = qs('#massal-alasan');
        alasan.value = '';
        alasan.required = !aktifkan;
        show('#grup-alasan-massal', !aktifkan);
        show('#catatan-desa-baru', !aktifkan);

        var konfirmasi = qs('#massal-konfirmasi');
        konfirmasi.checked = false;
        var simpan = qs('#tombol-simpan-massal');
        simpan.disabled = true;
        simpan.textContent = aktifkan ? 'Aktifkan Semua' : 'Nonaktifkan Semua';
        simpan.className = 'btn ' + (aktifkan ? 'btn-success' : 'btn-danger');

        if (window.jQuery) window.jQuery('#modal-status-massal').modal('show');
    }

    function renderPaginasi(meta) {
        var nav = qs('#status-desa-paginasi');
        if (!nav) return;
        if (!meta || meta.total_pages <= 1) { nav.innerHTML = ''; return; }
        var html = '<ul class="pagination pagination-sm m-0">';
        for (var i = 1; i <= meta.total_pages; i++) {
            html += '<li class="page-item' + (i === meta.page ? ' active' : '') + '">' +
                '<a href="#" class="page-link" data-page="' + i + '">' + i + '</a></li>';
        }
        nav.innerHTML = html + '</ul>';
    }

    function muat() {
        var token = ++lastToken;
        syncUrl();
        show('#status-desa-loading', true);
        show('#status-desa-empty', false);
        show('#status-desa-unavailable', false);

        var p = new URLSearchParams();
        if (state.kode_kecamatan) p.set('kode_kecamatan', state.kode_kecamatan);
        if (state.status) p.set('status', state.status);
        p.set('page', String(state.page));

        fetch(opts.dataUrl + '?' + p.toString(), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (res) { return res.json(); })
            .then(function (body) {
                if (token !== lastToken) return;
                show('#status-desa-loading', false);
                var meta = body.meta || {};
                ringkasanStatus = body.ringkasan_status || null;
                renderTombolMassal();
                if (meta.unavailable) {
                    show('#status-desa-unavailable', true);
                    renderTabel([]);
                    renderPaginasi(null);
                    return;
                }
                show('#status-desa-empty', !!meta.empty);
                renderTabel(body.baris || []);
                renderPaginasi(meta);
                var ringkas = qs('#ringkasan-nonaktif');
                if (ringkas) {
                    var n = (body.ringkasan || {}).nonaktif || 0;
                    ringkas.textContent = n + ' desa nonaktif di halaman ini';
                    ringkas.style.display = n > 0 ? '' : 'none';
                }
            })
            .catch(function () {
                if (token !== lastToken) return;
                show('#status-desa-loading', false);
                show('#status-desa-unavailable', true);
                ringkasanStatus = null;
                renderTombolMassal();
            });
    }

    function bukaModal(id, nama, aktifSekarang) {
        var form = qs('#form-status-desa');
        if (!form) return;
        var menonaktifkan = aktifSekarang;
        form.setAttribute('action', form.getAttribute('data-action').replace('__ID__', encodeURIComponent(id)));
        qs('#input-aktif').value = menonaktifkan ? '0' : '1';
        qs('#input-kode-kecamatan').value = state.kode_kecamatan;
        qs('#input-status-filter').value = state.status;
        qs('#judul-modal-status').textContent = menonaktifkan ? 'Nonaktifkan Pengisian Desa' : 'Aktifkan Kembali Pengisian Desa';
        qs('#teks-modal-status').textContent = menonaktifkan
            ? 'Operator ' + nama + ' tidak akan dapat menambah, mengubah, atau mengisi data spasial sampai diaktifkan kembali.'
            : 'Operator ' + nama + ' akan dapat kembali mengisi data spasial.';
        var alasan = qs('#input-alasan');
        alasan.value = '';
        alasan.required = menonaktifkan;
        show('#grup-alasan', menonaktifkan);
        var tombol = qs('#tombol-simpan-status');
        tombol.textContent = menonaktifkan ? 'Nonaktifkan' : 'Aktifkan';
        tombol.className = 'btn ' + (menonaktifkan ? 'btn-danger' : 'btn-success');
        if (window.jQuery) window.jQuery('#modal-status-desa').modal('show');
    }

    function init(o) {
        opts = o || {};
        readUrl();

        var kec = qs('#filter_kecamatan_status');
        var status = qs('#filter_status_desa');
        if (kec) { kec.value = state.kode_kecamatan; kec.addEventListener('change', function () { state.kode_kecamatan = kec.value; state.page = 1; muat(); }); }
        if (status) { status.value = state.status; status.addEventListener('change', function () { state.status = status.value; state.page = 1; muat(); }); }

        document.addEventListener('click', function (e) {
            var halaman = e.target.closest('#status-desa-paginasi [data-page]');
            if (halaman) {
                e.preventDefault();
                state.page = parseInt(halaman.getAttribute('data-page'), 10) || 1;
                muat();
                return;
            }
            var tombol = e.target.closest('.tombol-ubah-status');
            if (tombol) {
                bukaModal(tombol.getAttribute('data-id'), tombol.getAttribute('data-nama'), tombol.getAttribute('data-aktif') === '1');
                return;
            }
            var massal = e.target.closest('.tombol-status-massal');
            if (massal && !massal.disabled) {
                bukaModalMassal(massal.getAttribute('data-aktif') === '1');
            }
        });

        // Tombol simpan massal hanya aktif setelah konfirmasi dicentang
        var konfirmasi = qs('#massal-konfirmasi');
        if (konfirmasi) {
            konfirmasi.addEventListener('change', function () {
                qs('#tombol-simpan-massal').disabled = !konfirmasi.checked;
            });
        }

        muat();
    }

    window.StatusDesaDataSpasial = { init: init };
})();
