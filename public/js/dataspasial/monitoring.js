/**
 * Halaman Monitoring Desa (KAB-07).
 *
 * State filter → URL (FR-007/FR-012); gating konteks (hanya SEKTOR wajib;
 * kecamatan opsional = Semua Kecamatan); race-guard (hanya hasil terakhir
 * tampil); kartu ringkasan di atas tabel (klik = tampilkan keterangan saja);
 * panel aturan (US2); ekspor CSV (US3). Tanpa filter status/nama desa
 * (dihapus 2026-10-21); tanpa peta.
 */
(function () {
    'use strict';

    var opts = {};
    var state = { sektor: '', kode_kecamatan: '', periode: '', page: 1 };
    var lastToken = 0;
    var aturanCache = { isian: [], write: false };

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

    function qsToken() {
        return document.querySelector('meta[name="csrf-token"]')
            ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    }

    // ------------------------------------------------------------------ URL

    var FILTER_KEYS = ['sektor', 'kode_kecamatan', 'periode'];

    function syncUrl() {
        var p = new URLSearchParams();
        FILTER_KEYS.forEach(function (k) { if (state[k]) p.set(k, state[k]); });
        if (state.page > 1) p.set('page', String(state.page));
        var url = window.location.pathname + (p.toString() ? '?' + p.toString() : '');
        window.history.replaceState(null, '', url);
    }

    function readUrl() {
        var p = new URLSearchParams(window.location.search);
        state.sektor = p.get('sektor') || '';
        state.kode_kecamatan = p.get('kode_kecamatan') || '';
        state.periode = p.get('periode') || '';
        state.page = Math.max(1, parseInt(p.get('page') || '1', 10) || 1);
    }

    function queryAktif() {
        var p = new URLSearchParams();
        p.set('sektor', state.sektor);
        if (state.kode_kecamatan) p.set('kode_kecamatan', state.kode_kecamatan);
        if (state.periode) p.set('periode', state.periode);
        if (state.page > 1) p.set('page', String(state.page));
        return p.toString();
    }

    // ------------------------------------------------------------------ gating

    /** Hanya sektor yang wajib; kecamatan opsional (2026-10-21). */
    function konteksLengkap() {
        return state.sektor !== '';
    }

    function terapkanGating() {
        var lengkap = konteksLengkap();
        show('#monitoring-no-context', !lengkap);
        return lengkap;
    }

    // ------------------------------------------------------------------ render

    function renderKartu(kartu) {
        var sLabel = qs('[data-testid="kartu-sudah-label"]');
        if (sLabel) {
            sLabel.textContent = kartu.sudah_mengisi_label || (kartu.sudah_mengisi + '/' + (kartu.total_desa || 0));
        }
        if (qs('[data-testid="kartu-sudah"]')) qs('[data-testid="kartu-sudah"]').textContent = kartu.sudah_mengisi;
        qs('[data-testid="kartu-belum"]').textContent = kartu.belum_pernah_mengirim;
        qs('[data-testid="kartu-periksa"]').textContent = kartu.perlu_diperiksa;
        show('#monitoring-kartu', true);
    }

    function barKelengkapan(persen) {
        if (persen === null || persen === undefined) {
            return '<span title="Tidak tersedia">—</span>';
        }
        var warna = persen >= 80 ? 'bg-success' : (persen >= 50 ? 'bg-warning' : 'bg-danger');
        return '<div class="progress" style="height:14px">' +
            '<div class="progress-bar ' + warna + '" role="progressbar" style="width:' + persen + '%" ' +
            'aria-valuenow="' + persen + '" aria-valuemin="0" aria-valuemax="100">' + persen + '%</div></div>';
    }    

    function renderTabel(rows) {
        var tb = qs('#monitoring-tbody');
        if (!rows.length) {
            tb.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Tidak ada data untuk pilihan ini.</td></tr>';
            return;
        }
        tb.innerHTML = rows.map(function (r) {
            return '<tr data-kode="' + esc(r.kode_desa) + '" data-testid="baris-desa">' +
                '<td><strong>' + esc(r.nama_desa) + '</strong><br><small class="text-muted">' + esc(r.kode_desa) + '</small></td>' +
                '<td>' + esc(r.nama_kecamatan || r.kode_kecamatan || '—') + '</td>' +
                '<td>' + barKelengkapan(r.kelengkapan_persen) + '</td>' +
                '<td class="text-center">' + (r.skor_kualitas === null ? '<span title="Belum ada aturan berlaku">—</span>' : r.skor_kualitas                    
                ) + '</td>' +
                '<td class="text-center">-</td>' +
                '</tr>';
        }).join('');
    }

    function renderPaginasi(meta) {
        var nav = qs('#monitoring-paginasi');
        if (!nav) return;
        if (meta.total_pages <= 1) { nav.innerHTML = ''; return; }
        var html = '<ul class="pagination pagination-sm mb-0">';
        for (var i = 1; i <= meta.total_pages; i++) {
            html += '<li class="page-item' + (i === meta.page ? ' active' : '') + '">' +
                '<a class="page-link" href="#" data-hal="' + i + '">' + i + '</a></li>';
        }
        html += '</ul> <small class="text-muted ml-2">Total ' + meta.total + ' desa</small>';
        nav.innerHTML = html;
        qsa('#monitoring-paginasi [data-hal]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                state.page = parseInt(a.getAttribute('data-hal'), 10) || 1;
                muatData();
            });
        });
    }

    function isiPeriode(periods, aktif) {
        var sel = qs('#filter_periode');
        if (!sel) return;
        var pilihan = periods && periods.length ? periods : (aktif ? [aktif] : []);
        var current = aktif || '';
        sel.innerHTML = '<option value="">Terbaru</option>' + pilihan.map(function (p) {
            return '<option value="' + esc(p) + '"' + (p === current ? ' selected' : '') + '>' + esc(p) + '</option>';
        }).join('');
        sel.value = aktif && pilihan.indexOf(aktif) >= 0 ? aktif : '';
    }

    // ------------------------------------------------------------------ data

    function muatData() {
        if (!terapkanGating()) {
            qs('#monitoring-tbody').innerHTML = '<tr><td colspan="5" class="text-center text-muted">Pilih sektor terlebih dahulu.</td></tr>';
            show('#monitoring-kartu', false);
            show('#monitoring-empty', false);
            show('#monitoring-unavailable', false);
            return;
        }

        syncUrl();
        var token = ++lastToken;
        show('#monitoring-validation', false);
        show('#monitoring-empty', false);
        show('#monitoring-unavailable', false);
        show('#monitoring-loading', true);

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
                if (token !== lastToken) return; // race-guard: hasil basi dibuang
                show('#monitoring-loading', false);

                if (body.__validation) {
                    var v = qs('#monitoring-validation');
                    v.textContent = body.__validation;
                    show('#monitoring-validation', true);
                    return;
                }

                var meta = body.meta || {};

                if (meta.unavailable) {
                    show('#monitoring-unavailable', true);
                    show('#monitoring-kartu', false);
                    qs('#monitoring-tbody').innerHTML = '<tr><td colspan="5" class="text-center text-muted">Data sementara tidak tersedia.</td></tr>';
                    return;
                }

                isiPeriode(meta.periods, meta.periode);
                var badge = qs('#monitoring-meta-periode');
                if (badge) badge.textContent = meta.periode ? 'Periode ' + meta.periode : '';

                renderKartu(body.kartu || { sudah_mengisi: 0, belum_pernah_mengirim: 0, perlu_diperiksa: 0 });

                if (meta.empty) {
                    show('#monitoring-empty', true);
                    qs('#monitoring-tbody').innerHTML = '<tr><td colspan="5" class="text-center text-muted">Tidak ada data untuk pilihan ini.</td></tr>';
                    qs('#monitoring-paginasi').innerHTML = '';
                    return;
                }

                renderTabel((body.tabel && body.tabel.rows) || []);
                renderPaginasi(meta);
                if (meta.aturan_write) muatAturan();
            })
            .catch(function () {
                if (token !== lastToken) return;
                show('#monitoring-loading', false);
                show('#monitoring-unavailable', true);
            });
    }

    // ------------------------------------------------------------------ aturan (US2)

    function muatAturan() {
        if (!opts.aturanWrite || !state.sektor) return;

        fetch(opts.aturanUrl + '?sektor=' + encodeURIComponent(state.sektor), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : { write: false, aturan: [], isian_angka: [] }; })
            .then(function (body) {
                aturanCache.isian = body.isian_angka || [];
                aturanCache.write = !!body.write;
                renderAturan(body);
                isiFormIsian();
            }).catch(function () { /* panel tetap; muat ulang saat save */ });
    }

    function renderAturan(body) {
        var host = qs('#aturan-list');
        if (!host) return;
        var rules = body.aturan || [];
        var dilewati = {};
        (body.dilewati || []).forEach(function (d) { dilewati[d.key] = d.alasan; });

        if (!rules.length) {
            host.innerHTML = '<span class="text-muted small">Belum ada aturan untuk sektor ini.</span>';
            return;
        }

        host.innerHTML = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead class="thead-light">' +
            '<tr><th>Nama</th><th>Isian</th><th>Hasil</th><th class="text-center">Aktif</th><th class="text-center">Aksi</th></tr></thead><tbody>' +
            rules.map(function (a) {
                var catatan = dilewati[a.key]
                    ? ' <span class="badge badge-secondary" title="' + esc(dilewati[a.key]) + '">dilewati</span>' : '';
                return '<tr data-key="' + esc(a.key) + '">' +
                    '<td>' + esc(a.nama) + catatan + (a.deskripsi ? '<br><small class="text-muted">' + esc(a.deskripsi) + '</small>' : '') + '</td>' +
                    '<td><code>' + esc((a.isian || []).join(' + ')) + '</code></td>' +
                    '<td><code>' + esc(a.hasil) + '</code></td>' +
                    '<td class="text-center">' + (a.aktif ? '<span class="badge badge-success">ya</span>' : '<span class="badge badge-secondary">tidak</span>') + '</td>' +
                    '<td class="text-center"><button type="button" class="btn btn-xs btn-outline-secondary bt-toggle-aturan" ' +
                    'data-key="' + esc(a.key) + '" data-testid="bt-toggle-aturan"><i class="fas fa-power-off"></i></button></td>' +
                    '</tr>';
            }).join('') + '</tbody></table></div>';

        qsa('.bt-toggle-aturan').forEach(function (b) {
            b.addEventListener('click', function () {
                toggleAturan(b.getAttribute('data-key'));
            });
        });
    }

    function isiFormIsian() {
        var selI = qs('#aturan_isian');
        var selH = qs('#aturan_hasil');
        if (!selI || !selH) return;
        var opsi = aturanCache.isian.map(function (i) {
            return '<option value="' + esc(i.kode) + '">' + esc(i.label || i.kode) + '</option>';
        }).join('');
        selI.innerHTML = opsi;
        selH.innerHTML = opsi;
    }

    function csrfHeaders(extra) {
        var h = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': qsToken() };
        Object.keys(extra || {}).forEach(function (k) { h[k] = extra[k]; });
        return h;
    }

    function toggleAturan(key) {
        fetch(opts.aturanToggleUrl.replace('__KEY__', encodeURIComponent(key)), {
            method: 'POST', headers: csrfHeaders({ 'Content-Type': 'application/json' })
        }).then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
            .then(function () { muatAturan(); muatData(); })
            .catch(function () { /* pesan lewat muat ulang panel */ });
    }

    function bindAturanForm() {
        var form = qs('#aturan-form');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var err = qs('#aturan-error');
            show('#aturan-error', false);

            var isian = Array.prototype.slice.call(qs('#aturan_isian').selectedOptions || []).map(function (o) { return o.value; });
            var payload = {
                nama: qs('#aturan_nama').value.trim(),
                sektor: state.sektor,
                isian: isian,
                hasil: qs('#aturan_hasil').value,
                tipe: 'jumlah_sama_dengan',
                aktif: true
            };
            var keyInput = form.querySelector('input[name="key"]');
            if (keyInput && keyInput.value) payload.key = keyInput.value;

            fetch(opts.aturanSimpanUrl, {
                method: 'POST',
                headers: csrfHeaders({ 'Content-Type': 'application/json' }),
                body: JSON.stringify(payload)
            }).then(function (r) {
                return r.json().then(function (body) { return { ok: r.ok, body: body }; });
            }).then(function (res) {
                if (!res.ok) {
                    var msgs = [];
                    var errors = res.body.errors || {};
                    Object.keys(errors).forEach(function (k) { msgs = msgs.concat(errors[k]); });
                    if (!msgs.length) msgs = [res.body.message || 'Aturan gagal disimpan.'];
                    err.textContent = msgs.join(' ');
                    show('#aturan-error', true);
                    return;
                }
                form.reset();
                muatAturan();
                muatData();
            }).catch(function () {
                err.textContent = 'Gagal menyimpan aturan. Coba lagi.';
                show('#aturan-error', true);
            });
        });
    }

    // ------------------------------------------------------------------ ekspor (US3)

    function eksporCsv() {
        if (!terapkanGating()) return;
        var url = opts.eksporUrl + '?' + queryAktif().replace(/(^|&)page=[^&]*/, '').replace(/&&/g, '&');
        fetch(url, { headers: { 'Accept': 'text/csv' } }).then(function (r) {
            if (!r.ok) {
                return r.json().then(function (b) { throw new Error(b.message || 'Ekspor gagal.'); });
            }
            var dispo = r.headers.get('Content-Disposition') || '';
            var nama = 'monitoring-laporan.csv';
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
        }).catch(function (e) {
            alert(e.message || 'Tidak ada data yang cocok dengan saringan.');
        });
    }

    // ------------------------------------------------------------------ init

    function bacaFilterDariForm() {
        state.sektor = (qs('#filter_sektor') || {}).value || '';
        state.kode_kecamatan = (qs('#filter_kecamatan') || {}).value || '';
        state.periode = (qs('#filter_periode') || {}).value || '';
    }

    function isiFilterDariState() {
        if (qs('#filter_sektor')) qs('#filter_sektor').value = state.sektor;
        if (qs('#filter_kecamatan')) qs('#filter_kecamatan').value = state.kode_kecamatan;
    }

    function init(options) {
        opts = options || {};
        readUrl();
        isiFilterDariState();
        terapkanGating();
        bindAturanForm();

        var btFilter = qs('#bt-filter');
        if (btFilter) btFilter.addEventListener('click', function () { bacaFilterDariForm(); state.page = 1; muatData(); });

        var btReset = qs('#bt-reset');
        if (btReset) btReset.addEventListener('click', function () {
            state = { sektor: '', kode_kecamatan: '', periode: '', page: 1 };
            isiFilterDariState();
            if (qs('#filter_periode')) qs('#filter_periode').innerHTML = '<option value="">Terbaru</option>';
            syncUrl();
            muatData();
        });

        var btEkspor = qs('#bt-ekspor-csv');
        if (btEkspor) btEkspor.addEventListener('click', eksporCsv);

        // Dropdown kecamatan (komponen x-dataspasial-dropdown-kecamatan)
        // selesai terisi → pulihkan pilihan URL lalu muat bila sektor sudah ada.
        var selKec = qs('#filter_kecamatan');
        if (selKec) {
            selKec.addEventListener('kecamatan:terisi', function () {
                if (state.kode_kecamatan) selKec.value = state.kode_kecamatan;
                if (konteksLengkap()) muatData();
            });
        }

        // URL restore: jika sektor terpilih, langsung muat.
        if (konteksLengkap()) muatData();
    }

    window.MonitoringDataSpasial = {
        init: init
    };
})();
