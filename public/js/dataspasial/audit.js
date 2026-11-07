/**
 * Halaman Log Entri Audit DataSpasial (KAB-08 / API-08).
 *
 * State filter → URL; race-guard (hanya hasil terakhir yang tampil);
 * badge sumber OpenKab/OpenSID; kartu ringkasan per sumber; detail baris
 * (perbandingan sebelum/sesudah per isian); ekspor Excel dari filter aktif.
 */
(function () {
    'use strict';

    var opts = {};
    var state = {
        sumber: '',
        aksi: '',
        pelaku: '',
        sektor: '',
        kode_kecamatan: '',
        kode_desa: '',
        waktu_dari: '',
        waktu_sampai: '',
        page: 1
    };
    var lastToken = 0;

    function qs(sel) { return document.querySelector(sel); }
    function qsa(sel) { return Array.prototype.slice.call(document.querySelectorAll(sel)); }

    function esc(s) {
        return String(s === null || s === undefined ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function show(id, on) {
        var el = qs(id);
        if (el) el.style.display = on ? '' : 'none';
    }

    // ------------------------------------------------------------------ URL

    var FILTER_KEYS = ['sumber', 'aksi', 'pelaku', 'sektor', 'kode_kecamatan', 'kode_desa', 'waktu_dari', 'waktu_sampai'];

    function syncUrl() {
        var p = new URLSearchParams();
        FILTER_KEYS.forEach(function (k) { if (state[k]) p.set(k, state[k]); });
        if (state.page > 1) p.set('page', String(state.page));
        var url = window.location.pathname + (p.toString() ? '?' + p.toString() : '');
        window.history.replaceState(null, '', url);
    }

    function readUrl() {
        var p = new URLSearchParams(window.location.search);
        FILTER_KEYS.forEach(function (k) { state[k] = p.get(k) || ''; });
        state.page = Math.max(1, parseInt(p.get('page') || '1', 10) || 1);
    }

    function queryAktif(bolehPage) {
        var p = new URLSearchParams();
        FILTER_KEYS.forEach(function (k) { if (state[k]) p.set(k, state[k]); });
        if (bolehPage !== false && state.page > 1) p.set('page', String(state.page));
        return p.toString();
    }

    // ------------------------------------------------------------------ form

    function bacaFilterDariForm() {
        state.sumber = (qs('#filter_sumber') || {}).value || '';
        state.aksi = (qs('#filter_aksi') || {}).value || '';
        state.pelaku = ((qs('#filter_pelaku') || {}).value || '').trim();
        state.sektor = (qs('#filter_sektor_audit') || {}).value || '';
        state.kode_kecamatan = (qs('#filter_kecamatan_audit') || {}).value || '';
        state.kode_desa = (qs('#filter_desa_audit') || {}).value || '';
        state.waktu_dari = (qs('#filter_waktu_dari') || {}).value || '';
        state.waktu_sampai = (qs('#filter_waktu_sampai') || {}).value || '';
    }

    function isiFilterDariState() {
        if (qs('#filter_sumber')) qs('#filter_sumber').value = state.sumber;
        if (qs('#filter_aksi')) qs('#filter_aksi').value = state.aksi;
        if (qs('#filter_pelaku')) qs('#filter_pelaku').value = state.pelaku;
        if (qs('#filter_sektor_audit')) qs('#filter_sektor_audit').value = state.sektor;
        if (qs('#filter_kecamatan_audit')) qs('#filter_kecamatan_audit').value = state.kode_kecamatan;
        if (qs('#filter_desa_audit')) qs('#filter_desa_audit').value = state.kode_desa;
        if (qs('#filter_waktu_dari')) qs('#filter_waktu_dari').value = state.waktu_dari;
        if (qs('#filter_waktu_sampai')) qs('#filter_waktu_sampai').value = state.waktu_sampai;
    }

    /** Batasi pilihan desa sesuai kecamatan terpilih. */
    function saringDesaByKecamatan() {
        var sel = qs('#filter_desa_audit');
        if (!sel) return;

        var kec = (qs('#filter_kecamatan_audit') || {}).value || state.kode_kecamatan || '';
        state.kode_kecamatan = kec;

        var aktif = sel.value;
        var opsi = Array.prototype.slice.call(sel.options);
        var tetapAda = false;

        opsi.forEach(function (o) {
            if (o.value === '') return;
            var kodeKec = o.getAttribute('data-kode-kecamatan') || '';
            var cocok = !kec || kodeKec === kec;
            o.hidden = !cocok;
            o.disabled = !cocok;
            if (cocok && o.value === aktif) tetapAda = true;
        });

        if (!tetapAda) {
            sel.value = '';
            state.kode_desa = '';
        } else {
            state.kode_desa = sel.value;
        }
    }

    // ------------------------------------------------------------------ render

    function renderKartu(kartu) {
        var elKab = qs('#audit-kartu-openkab');
        var elSid = qs('#audit-kartu-opensid');
        var elTotal = qs('#audit-kartu-total');

        if (elKab) elKab.textContent = kartu.openkab || 0;
        if (elSid) elSid.textContent = kartu.opensid || 0;
        if (elTotal) elTotal.textContent = kartu.total || 0;
    }

    function badgeSumber(r) {
        var label = r.sumber_label || '—';
        var kelas = r.sumber_badge || 'badge-secondary';
        return '<span class="badge ' + esc(kelas) + '" data-testid="badge-sumber">' + esc(label) + '</span>';
    }

    function teksPerubahan(r) {
        var perubahan = r.perubahan || [];
        if (!perubahan.length) {
            return '<span class="text-muted">—</span>';
        }

        return perubahan.map(function (p) {
            return '<div><small class="text-muted">' + esc(p.label) + '</small><br>' +
                '<del>' + esc(p.sebelum) + '</del> → <ins>' + esc(p.sesudah) + '</ins></div>';
        }).join('');
    }

    function renderTabel(rows) {
        var tb = qs('#audit-tbody');
        if (!tb) return;

        if (!rows.length) {
            tb.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Tidak ada data untuk pilihan ini.</td></tr>';
            return;
        }

        tb.innerHTML = rows.map(function (r) {
            return '<tr data-id="' + esc(r.id) + '" data-testid="baris-audit">' +
                '<td><small>' + esc(r.waktu) + '</small></td>' +
                '<td>' + esc(r.pelaku_nama || '—') + '</td>' +
                '<td>' + badgeSumber(r) + '</td>' +
                '<td>' + esc(r.aksi_label || '—') + '</td>' +
                '<td>' + esc(r.sektor_nama || r.sektor_kode || '—') + (r.periode ? '<br><small class="text-muted">' + esc(r.periode) + '</small>' : '') + '</td>' +
                '<td>' + teksPerubahan(r) + '</td>' +
                '<td class="text-center">' +
                '<button type="button" class="btn btn-xs btn-outline-primary bt-detail-audit" data-id="' + esc(r.id) + '" ' +
                'data-testid="bt-detail-audit" title="Lihat detail perubahan"><i class="fas fa-search"></i></button>' +
                '</td>' +
                '</tr>';
        }).join('');

        qsa('.bt-detail-audit').forEach(function (b) {
            b.addEventListener('click', function () {
                bukaDetail(b.getAttribute('data-id'));
            });
        });
    }

    function renderPaginasi(meta) {
        var nav = qs('#audit-paginasi');
        if (!nav) return;

        if (meta.total_pages <= 1) {
            nav.innerHTML = '<small class="text-muted">Total ' + (meta.total || 0) + ' entri</small>';
            return;
        }

        var html = '<ul class="pagination pagination-sm mb-0">';
        for (var i = 1; i <= meta.total_pages; i++) {
            html += '<li class="page-item' + (i === meta.page ? ' active' : '') + '">' +
                '<a class="page-link" href="#" data-hal="' + i + '">' + i + '</a></li>';
        }
        html += '</ul> <small class="text-muted ml-2">Total ' + (meta.total || 0) + ' entri</small>';
        nav.innerHTML = html;

        qsa('#audit-paginasi [data-hal]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                state.page = parseInt(a.getAttribute('data-hal'), 10) || 1;
                muatData();
            });
        });
    }

    // ------------------------------------------------------------------ data

    function muatData() {
        syncUrl();

        var token = ++lastToken;
        show('#audit-validation', false);
        show('#audit-empty', false);
        show('#audit-unavailable', false);
        show('#audit-loading', true);

        fetch(opts.dataUrl + '?' + queryAktif(), { headers: { 'Accept': 'application/json' } })
            .then(function (r) {
                if (r.status === 422) {
                    return r.json().then(function (body) {
                        return { __validation: (body.meta && body.meta.validation) || 'Pilihan filter tidak valid.' };
                    });
                }
                if (!r.ok) throw new Error('http ' + r.status);
                return r.json();
            })
            .then(function (body) {
                if (token !== lastToken) return;
                show('#audit-loading', false);

                if (body.__validation) {
                    var v = qs('#audit-validation');
                    v.textContent = body.__validation;
                    show('#audit-validation', true);
                    return;
                }

                var meta = body.meta || {};

                if (meta.unavailable) {
                    show('#audit-unavailable', true);
                    qs('#audit-tbody').innerHTML = '<tr><td colspan="8" class="text-center text-muted">Data sementara tidak tersedia.</td></tr>';
                    return;
                }

                renderKartu(body.kartu || {});

                if (meta.empty) {
                    show('#audit-empty', true);
                    qs('#audit-tbody').innerHTML = '<tr><td colspan="8" class="text-center text-muted">Tidak ada data untuk pilihan ini.</td></tr>';
                    qs('#audit-paginasi').innerHTML = '';
                    return;
                }

                renderTabel((body.tabel && body.tabel.rows) || []);
                renderPaginasi(meta);
            })
            .catch(function () {
                if (token !== lastToken) return;
                show('#audit-loading', false);
                show('#audit-unavailable', true);
            });
    }

    function bukaDetail(id) {
        var body = qs('#audit-detail-body');
        if (!body) return;

        body.innerHTML = '<div class="text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat detail…</div>';

        if (window.jQuery) {
            window.jQuery('#modal-detail-audit').modal('show');
        } else if (window.bootstrap && window.bootstrap.Modal) {
            new window.bootstrap.Modal(document.getElementById('modal-detail-audit')).show();
        }

        var url = String(opts.detailUrl || '').replace('__ID__', encodeURIComponent(id));

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
            .then(function (res) {
                if (res.meta && res.meta.unavailable) {
                    body.innerHTML = '<div class="alert alert-warning mb-0">Data sementara tidak tersedia</div>';
                    return;
                }
                body.innerHTML = renderDetail(res.detail || {});
            })
            .catch(function () {
                body.innerHTML = '<div class="alert alert-warning mb-0">Data sementara tidak tersedia</div>';
            });
    }

    function renderDetail(d) {
        var meta = [
            ['Waktu', d.waktu],
            ['Sumber', d.sumber_label],
            ['Pelaku', d.pelaku_nama],
            ['Desa', (d.nama_desa || '—') + (d.kode_desa ? ' (' + d.kode_desa + ')' : '')],
            ['Kecamatan', d.nama_kecamatan],
            ['Sektor', d.sektor_nama || d.sektor_kode],
            ['Aksi', d.aksi_label],
            ['Periode', d.periode]
        ];

        var html = '<table class="table table-sm table-bordered mb-3"><tbody>';
        meta.forEach(function (m) {
            html += '<tr><th style="width:160px">' + esc(m[0]) + '</th><td>' + esc(m[1] || '—') + '</td></tr>';
        });
        html += '</tbody></table>';

        html += '<h6>Perubahan per isian</h6>';
        var perubahan = d.perubahan || [];

        if (!perubahan.length) {
            html += '<p class="text-muted mb-0">Tidak ada perubahan per isian pada entri ini.</p>';
        } else {
            html += '<div class="table-responsive"><table class="table table-sm table-bordered" data-testid="tabel-detail-audit">' +
                '<thead class="thead-light"><tr><th>Isian</th><th>Sebelum</th><th>Sesudah</th></tr></thead><tbody>';
            perubahan.forEach(function (p) {
                html += '<tr><td>' + esc(p.label) + '</td><td><del>' + esc(p.sebelum) + '</del></td>' +
                    '<td><ins>' + esc(p.sesudah) + '</ins></td></tr>';
            });
            html += '</tbody></table></div>';
        }

        return html;
    }

    // ------------------------------------------------------------------ ekspor

    function eksporExcel() {
        var url = opts.exportUrl + '?' + queryAktif(false);

        fetch(url, { headers: { 'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' } })
            .then(function (r) {
                if (!r.ok) {
                    return r.json().then(function (b) { throw new Error(b.message || 'Ekspor gagal.'); });
                }
                var dispo = r.headers.get('Content-Disposition') || '';
                var nama = 'log-entri-dataspasial.xlsx';
                var m = /filename="?([^";]+)"?/.exec(dispo);
                if (m) nama = m[1];
                return r.blob().then(function (blob) {
                    var a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = nama;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    URL.revokeObjectURL(a.href);
                });
            })
            .catch(function (e) {
                var pesan = e.message || 'Tidak ada data yang cocok dengan saringan.';
                if (typeof window.Swal !== 'undefined') {
                    window.Swal.fire({ icon: 'info', text: pesan });
                    return;
                }
                alert(pesan);
            });
    }

    // ------------------------------------------------------------------ init

    function init(options) {
        opts = options || {};
        readUrl();
        isiFilterDariState();
        saringDesaByKecamatan();

        var btFilter = qs('#bt-filter-audit');
        if (btFilter) btFilter.addEventListener('click', function () {
            bacaFilterDariForm();
            state.page = 1;
            muatData();
        });

        var btReset = qs('#bt-reset-audit');
        if (btReset) btReset.addEventListener('click', function () {
            state = {
                sumber: '', aksi: '', pelaku: '', sektor: '',
                kode_kecamatan: '', kode_desa: '', waktu_dari: '', waktu_sampai: '',
                page: 1
            };
            isiFilterDariState();
            saringDesaByKecamatan();
            muatData();
        });

        var btEkspor = qs('#bt-ekspor-audit');
        if (btEkspor) btEkspor.addEventListener('click', eksporExcel);

        var selKec = qs('#filter_kecamatan_audit');
        if (selKec) {
            selKec.addEventListener('change', function () {
                state.kode_kecamatan = selKec.value;
                saringDesaByKecamatan();
            });
            selKec.addEventListener('kecamatan:terisi', function () {
                if (state.kode_kecamatan) selKec.value = state.kode_kecamatan;
                saringDesaByKecamatan();
            });
        }

        var selDesa = qs('#filter_desa_audit');
        if (selDesa) {
            selDesa.addEventListener('change', function () {
                state.kode_desa = selDesa.value;
            });
        }

        var form = qs('#audit-filter');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                bacaFilterDariForm();
                state.page = 1;
                muatData();
            });
        }

        // Pastikan filter desa mengikuti kecamatan saat dropdown sudah siap
        saringDesaByKecamatan();
        setTimeout(saringDesaByKecamatan, 0);

        muatData();
    }

    window.AuditDataSpasial = {
        init: init
    };
})();
