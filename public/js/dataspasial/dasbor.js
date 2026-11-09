(function (window) {
  'use strict';

  var state = { kecamatan: '', desa: '', sektor: '', periode: '', indikator: '', page: 1, last: 0 };
  var charts = { kec: null, kat: null };
  var FILTER_KEYS = ['kecamatan', 'desa', 'sektor', 'periode', 'indikator'];

  function qs(sel) { return document.querySelector(sel); }
  function qsa(sel) { return Array.prototype.slice.call(document.querySelectorAll(sel)); }
  function num(v) { var n = parseFloat(v); return isNaN(n) ? 0 : n; }
  function setText(id, html) { var el = document.getElementById(id); if (el) el.innerHTML = html; }
  function esc(v) {
    return String(v === null || v === undefined ? '' : v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  function fmt(n) { return n === null || n === undefined ? '-' : Number(n).toLocaleString('id-ID'); }

  function fillSelect(sel, items, current, placeholder) {
    if (!sel) return;
    sel.innerHTML = '<option value="">' + (placeholder || '') + '</option>';
    (items || []).forEach(function (x) {
      var o = document.createElement('option');
      o.value = x.value;
      o.textContent = x.label;
      sel.appendChild(o);
    });
    if (current !== undefined && current !== null) {
      sel.value = current;
    }
  }

  function cardHtml(rows) {
    var h = '';
    rows.forEach(function (r) {
      h += '<div class="col-md-3 mb-2"><div class="card card-sm"><div class="card-body">' +
        '<div class="text-muted small">' + (r.label || r.kode) + '</div>' +
        '<h5>' + fmt(r.nilai) + '</h5>' +
        (r.keterangan ? '<div class="text-muted" data-testid="kartu-keterangan" style="font-size:.8rem">' + esc(r.keterangan) + '</div>' : '') +
        '<small>' + (r.satuan || '') + ' (' + (r.ukuran || 'total') + ')</small>' +
        '</div></div></div>';
    });
    return h || '<div class="col-12 text-muted small">Tidak ada data</div>';
  }

  function tableHtml(rows) {
    var start = (state.page - 1) * 10, end = Math.min(rows.length, start + 10), slice = rows.slice(start, end);
    var h = '';
    slice.forEach(function (r) {
      h += '<tr><td>' + (r.label || r.kode) + '</td><td>' + (r.satuan || '-') + '</td><td>' + (r.ukuran || '-') +
        '</td><td>' + fmt(r.nilai) + '</td><td>' + (r.tersedia ? 'Ya' : 'Tidak') + '</td></tr>';
    });
    return h;
  }

  function pagi(total) {
    var pages = Math.max(1, Math.ceil(total / 10)), ul = qs('#dasbor-paginasi');
    if (!ul) return;
    var h = '';
    for (var p = 1; p <= pages; p++) {
      h += '<li class="page-item ' + (p === state.page ? 'active' : '') + '"><a class="page-link" href="#" data-page="' + p + '">' + p + '</a></li>';
    }
    ul.innerHTML = h;
    qsa('#dasbor-paginasi .page-link').forEach(function (a) {
      a.onclick = function (e) { e.preventDefault(); state.page = parseInt(a.dataset.page, 10); load(); };
    });
  }

  function renderCharts(g) {
    if (!window.ApexCharts) return;
    if (charts.kec) charts.kec.destroy();
    if (charts.kat) charts.kat.destroy();
    var kc = (Array.isArray(g.per_kecamatan) && g.per_kecamatan.length) ? g.per_kecamatan : (Array.isArray(g.per_desa) ? g.per_desa : []);
    var titleEl = document.getElementById('dasbor-grafik-utama-title');
    var isPerDesa = Array.isArray(g.per_desa) && g.per_desa.length && !(Array.isArray(g.per_kecamatan) && g.per_kecamatan.length);
    if (titleEl) {
      var indLabel = null;
      var indSel = qs('#filter_indikator');
      if (indSel && indSel.options && indSel.selectedIndex >= 0) {
        indLabel = indSel.options[indSel.selectedIndex].textContent;
      }
      if (indLabel && indLabel !== 'Pilih Indikator' && indLabel !== 'Tidak ada isian indikator pada sektor ini' && indLabel !== 'Pilih sektor terlebih dahulu') {
        titleEl.textContent = indLabel;
      } else {
        titleEl.textContent = isPerDesa ? 'Grafik per Desa' : 'Grafik per Kecamatan';
      }
    }
    charts.kec = new ApexCharts(document.getElementById('dasbor-grafik-kecamatan'), {
      series: [{ name: 'Nilai', data: kc.map(function (x) { return num(x.nilai); }) }],
      chart: {
        type: 'bar', height: 240,
        events: {
          dataPointSelection: function (ev, chart, opts) {
            var d = kc[opts.dataPointIndex];
            if (!d) return;
            if (isPerDesa) {
              return;
            }
            state.kecamatan = state.kecamatan === d.kode ? '' : d.kode;
            if (!state.kecamatan) state.desa = '';
            var selKec = qs('#filter_kecamatan');
            if (selKec) selKec.value = state.kecamatan;
            loadDesa(false);
            state.page = 1;
            load();
          }
        }
      },
      xaxis: { categories: kc.map(function (x) { return x.label; }) }
    });
    charts.kec.render();
  }

  function renderChartsHint(text) {
    if (charts.kec) { charts.kec.destroy(); charts.kec = null; }
    if (charts.kat) { charts.kat.destroy(); charts.kat = null; }
    var elK = document.getElementById('dasbor-grafik-kecamatan');
    if (elK) elK.innerHTML = '<div class="text-muted small p-2">' + esc(text) + '</div>';
    var titleEl = document.getElementById('dasbor-grafik-utama-title');
    if (titleEl) titleEl.textContent = 'Grafik Indikator';
  }

  function populateFilters(meta) {
    var perSel = qs('#filter_periode');
    if (perSel && Array.isArray(meta.periods)) {
      fillSelect(perSel, meta.periods.map(function (p) {
        return { value: p, label: p };
      }), state.periode, 'Terbaru');
    }
    var kecSel = qs('#filter_kecamatan');
    if (kecSel) kecSel.value = state.kecamatan;
    var desaSel = qs('#filter_desa');
    if (desaSel) desaSel.value = state.desa;
    var sektorSel = qs('#filter_sektor');
    if (sektorSel) sektorSel.value = state.sektor;
  }

  function loadKecamatan() {
    var opts = window.DasborDataSpasial.opts;
    var kab = opts.identitasOpenkab;
    if (!kab) {
      return;
    }
    fetch(opts.databaseGabunganUrl + '/api/v1/statistik-web/get-list-kecamatan/' + kab, {
      headers: { 'Accept': 'application/json' }
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        fillSelect(qs('#filter_kecamatan'), (data || []).map(function (item) {
          return { value: item.kode_kecamatan, label: item.nama_kecamatan };
        }), state.kecamatan, 'Semua Kecamatan');
        loadDesa(true);
      })
      .catch(function () { /* noop */ });
  }

  function loadDesa(keepCurrent) {
    var opts = window.DasborDataSpasial.opts;
    var sel = qs('#filter_desa');
    if (!sel) return;
    if (!state.kecamatan) {
      state.desa = '';
      fillSelect(sel, [], '', 'Semua Desa');
      return;
    }
    if (!keepCurrent) state.desa = '';
    fetch(opts.databaseGabunganUrl + '/api/v1/statistik-web/get-list-desa/' + state.kecamatan, {
      headers: { 'Accept': 'application/json' }
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        fillSelect(sel, (data || []).map(function (item) {
          return { value: item.kode_desa, label: item.nama_desa };
        }), state.desa, 'Semua Desa');
        state.desa = sel.value;
      })
      .catch(function () { state.desa = ''; fillSelect(sel, [], '', 'Semua Desa'); });
  }

  function syncUrl() {
    var p = new URLSearchParams();
    FILTER_KEYS.forEach(function (k) { if (state[k]) p.set(k, state[k]); });
    if (state.page > 1) p.set('page', state.page);
    var s = p.toString();
    history.replaceState(null, null, s ? '?' + s : window.location.pathname);
  }

  function load() {
    var rid = ++state.last;
    var params = new URLSearchParams();
    FILTER_KEYS.forEach(function (k) { if (state[k]) params.set(k, state[k]); });
    if (state.kecamatan) params.set('kode_kecamatan', state.kecamatan);
    if (state.desa) params.set('kode_desa', state.desa);
    fetch(window.DasborDataSpasial.opts.dataUrl + '?' + params.toString(), {
      headers: { 'Accept': 'application/json' }, credentials: 'same-origin'
    })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (rid !== state.last) return;
        setText('dasbor-kartu', cardHtml(j.kartu || []));
        setText('dasbor-tabel-baris', tableHtml((j.tabel && j.tabel.rows) || []));
        pagi((j.tabel && j.tabel.rows) ? j.tabel.rows.length : 0);
        populateFilters((j.meta) || {});

        // Dropdown indikator bergantung pada sektor yang dipilih:
        // diisi dari isian (tampil_indikator) sektor terpilih; tanpa sektor → belum tersedia.
        var sel = qs('#filter_indikator');
        if (sel) {
          if (!state.sektor) {
            state.indikator = '';
            fillSelect(sel, [], '', 'Pilih sektor terlebih dahulu');
            sel.disabled = true;
            renderChartsHint('Pilih sektor untuk menampilkan grafik indikator.');
          } else {
            sel.disabled = false;
            var daftar = j.indikator || [];
            if (!daftar.length) {
              state.indikator = '';
              fillSelect(sel, [], '', 'Tidak ada isian indikator pada sektor ini');
              renderChartsHint('Tidak ada isian indikator pada sektor ini.');
            } else {
              fillSelect(sel, daftar.map(function (x) {
                return { value: x.kode, label: x.label };
              }), state.indikator, 'Pilih Indikator');
              if (!sel.value) {
                state.indikator = daftar[0].kode;
                sel.value = state.indikator;
              } else {
                state.indikator = sel.value;
              }
              renderCharts(j.grafik || {});
            }
          }
        }
        syncUrl();

        if (window.PetaGabungan) {
          var petaParams = new URLSearchParams();
          if (state.kecamatan) petaParams.set('kode_kecamatan', state.kecamatan);
          if (state.desa) petaParams.set('kode_desa', state.desa);
          if (state.sektor) petaParams.set('sektor', state.sektor);
          if (state.periode) petaParams.set('periode', state.periode);
          fetch(window.DasborDataSpasial.opts.petaUrl + '?' + petaParams.toString(), { credentials: 'same-origin' })
            .then(function (x) { return x.json(); })
            .then(function (p) {
              if (p.meta && p.meta.unavailable) {
                if (typeof window.PetaGabungan.unavailable === 'function') window.PetaGabungan.unavailable(p.meta.message);
                return;
              }
              if (typeof window.PetaGabungan.init === 'function' && !window._dasborPetaInit) {
                try { window.PetaGabungan.init({ mapId: 'map' }); window._dasborPetaInit = true; } catch (e) { /* noop */ }
              }
              if (typeof window.PetaGabungan.render === 'function') window.PetaGabungan.render(p, p.meta);
              if (p.meta && p.meta.empty && typeof window.PetaGabungan.empty === 'function') window.PetaGabungan.empty();
            })
            .catch(function () { if (typeof window.PetaGabungan.unavailable === 'function') window.PetaGabungan.unavailable(); });
        }
      })
      .catch(function () { });
  }

  function clearDashboard() {
    setText('dasbor-kartu', '<div class="col-12 text-muted small">-</div>');
    setText('dasbor-tabel-baris', '');
    var ul = qs('#dasbor-paginasi');
    if (ul) ul.innerHTML = '';
    renderChartsHint('Pilih filter dan klik Tampilkan untuk melihat data.');
    if (window.PetaGabungan && typeof window.PetaGabungan.clear === 'function') {
      try { window.PetaGabungan.clear(); } catch (e) { /* noop */ }
    } else if (window.PetaGabungan && typeof window.PetaGabungan.unavailable === 'function') {
      try { window.PetaGabungan.unavailable('Pilih filter dan klik Tampilkan untuk melihat data.'); } catch (e) { /* noop */ }
    }
  }

  window.DasborDataSpasial = {
    init: function (o) {
      this.opts = o;
      var q = new URLSearchParams(location.search);
      state.kecamatan = q.get('kecamatan') || '';
      state.desa = q.get('desa') || '';
      state.sektor = q.get('sektor') || '';
      state.periode = q.get('periode') || '';
      state.indikator = q.get('indikator') || '';
      state.page = parseInt(q.get('page'), 10) || 1;

      var f = qs('#peta-filter');
      if (f) f.onclick = function () { state.page = 1; load(); };

      var r = qs('#peta-reset');
      if (r) r.onclick = function () {
        state = { kecamatan: '', desa: '', sektor: '', periode: '', indikator: '', page: 1, last: state.last };
        FILTER_KEYS.forEach(function (k) { var el = qs('#filter_' + k); if (el) { el.value = ''; if (k === 'indikator') { el.disabled = true; fillSelect(el, [], '', 'Pilih sektor terlebih dahulu'); } } });
        var selInd = qs('#filter_indikator');
        if (selInd) { selInd.disabled = true; fillSelect(selInd, [], '', 'Pilih sektor terlebih dahulu'); }
        loadDesa(false);
        clearDashboard();
        history.replaceState(null, null, window.location.pathname);
      };

      FILTER_KEYS.forEach(function (k) {
        var el = qs('#filter_' + k);
        if (!el) return;
        el.addEventListener('change', function () {
          state[k] = el.value;
          if (k === 'kecamatan') { state.desa = ''; loadDesa(false); }
          if (k === 'desa') state.desa = el.value;
          if (k === 'sektor') {
            state.indikator = '';
            var si2 = qs('#filter_indikator');
            if (si2) {
              Array.from(si2.options).forEach(function (opt, idx) {
                if (idx === 0) return;
                var sk = opt.getAttribute('data-sektor');
                opt.style.display = (!state.sektor || sk === state.sektor) ? '' : 'none';
                if (opt.selected && (!state.sektor || sk !== state.sektor)) {
                  opt.selected = false;
                }
              });
              si2.disabled = false;
              si2.value = '';
            }
            renderChartsHint('Pilih indikator dan klik Tampilkan.');
          }
          state.page = 1;
        });
      });

      var cp = qs('#bt-salin-tautan');
      if (cp) {
        var asli = cp.innerHTML;
        cp.onclick = function () {
          syncUrl();
          var url = window.location.href;
          var done = function () {
            cp.innerHTML = '<span class="fas fa-check"></span> Tersalin';
            setTimeout(function () { cp.innerHTML = asli; }, 2000);
          };
          var fb = function () {
            var ta = document.createElement('textarea');
            ta.value = url;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); } catch (e) { /* noop */ }
            document.body.removeChild(ta);
            done();
          };
          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(done, fb);
          } else { fb(); }
        };
      }

      var ex = qs('#bt-ekspor');
      if (ex) ex.onclick = function () {
        var p = new URLSearchParams();
        FILTER_KEYS.forEach(function (k) { if (state[k]) p.set(k, state[k]); });
        window.location = o.exportUrl + '?' + p.toString();
      };

      loadKecamatan();
      var selIndInit = qs('#filter_indikator');
      if (selIndInit) {
        selIndInit.disabled = false;
        Array.from(selIndInit.options).forEach(function (opt, idx) {
          if (idx === 0) return;
          var sk = opt.getAttribute('data-sektor');
          opt.style.display = (!state.sektor || sk === state.sektor) ? '' : 'none';
        });
        selIndInit.value = state.indikator || '';
      }
      var selSekInit = qs('#filter_sektor');
      if (selSekInit) selSekInit.value = state.sektor || '';
      var kecInit = qs('#filter_kecamatan');
      if (kecInit) kecInit.value = state.kecamatan || '';
      var desaInit = qs('#filter_desa');
      if (desaInit) desaInit.value = state.desa || '';
      var perInit = qs('#filter_periode');
      if (perInit) perInit.value = state.periode || '';
      clearDashboard();
      var hasQuery = location.search && location.search.length > 1;
      if (hasQuery) {
        state.page = 1;
        load();
      }
    }
  };
})(window);
