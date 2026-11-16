const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', '..', 'public', 'js', 'dataspasial', 'popup-skema.js'), 'utf8');
let changeHandler;
const context = { window: {}, document: { addEventListener(event, handler) { if (event === 'change') changeHandler = handler; } } };
vm.runInNewContext(source, context);

test('popup uses separate accordions and escapes schema data', () => {
    const html = context.window.PopupSkema.render([
        { sektor: '<Sektor>', versi: 1, isian: [{ label: 'Jumlah', nilai: 0, satuan: 'unit' }] },
        { sektor: 'Lain', versi: 2, isian: [{ label: 'Catatan', nilai: '<img src=x>' }] },
    ], false);
    assert.equal((html.match(/<details/g) || []).length, 2);
    assert.equal((html.match(/name="popup-skema-1"/g) || []).length, 2);
    assert.match(html, /&lt;Sektor&gt;/);
    assert.match(html, /<dd>0 unit<\/dd>/);
    assert.match(html, /&lt;img src=x&gt;/);
    assert.doesNotMatch(html, /<img/);
});

test('popup distinguishes missing data from unavailable schemas', () => {
    assert.match(context.window.PopupSkema.render([], false), /Belum ada informasi/);
    assert.match(context.window.PopupSkema.render([], true), /sementara tidak tersedia/);
});

test('popup selects a period per schema and shows source once per period', () => {
    const html = context.window.PopupSkema.render([{
        sektor: 'Pendidikan', periode: ['2026', '2025'], periode_aktif: '2026',
        isian: [
            { label: 'Siswa', nilai_periode: [{ periode: '2026', nilai: 0, sumber: 'impor' }, { periode: '2025', nilai: 12, sumber: '<desa>' }] },
            { label: 'Guru', nilai_periode: [{ periode: '2026', nilai: 3, sumber: 'impor' }] },
        ],
    }], false);
    assert.match(html, /<option value="2026" selected>2026<\/option>/);
    assert.match(html, /data-popup-period="2025" hidden/);
    assert.match(html, /<dt>Siswa<\/dt><dd>0<\/dd>/);
    assert.match(html, /<dt>Siswa<\/dt><dd>12<\/dd>/);
    assert.match(html, /<dt>Guru<\/dt><dd class="dataspasial-popup-field-empty">Belum ada data untuk periode ini<\/dd>/);
    assert.equal((html.match(/Sumber: impor/g) || []).length, 1);
    assert.match(html, /Sumber: &lt;desa&gt;/);
    assert.doesNotMatch(html, /Periode: 2026/);

    const panels = ['2026', '2025'].map((period) => ({
        hidden: period !== '2026',
        getAttribute() { return period; },
    }));
    changeHandler({ target: {
        value: '2025',
        matches() { return true; },
        closest() { return { querySelectorAll() { return panels; } }; },
    } });
    assert.equal(panels[0].hidden, true);
    assert.equal(panels[1].hidden, false);
});

test('location summary escapes information and omits unavailable fields', () => {
    const html = context.window.PopupSkema.renderLokasi({
        nama: '<Lokasi>', kategori: '<Sekolah>', desa: 'Contoh', koordinat: '-8, 115',
    });
    assert.match(html, /&lt;Lokasi&gt;/);
    assert.match(html, /&lt;Sekolah&gt;/);
    assert.match(html, /<dt>Desa<\/dt><dd>Contoh<\/dd>/);
    assert.match(html, /<dt>Koordinat<\/dt><dd>-8, 115<\/dd>/);
    assert.doesNotMatch(html, /Provinsi|<Lokasi>/);
});

test('map popup resizes with the map without changing other popup types', () => {
    const events = {};
    let size = { x: 600, y: 500 };
    const map = { getSize() { return size; }, on(event, handler) { events[event] = handler; } };
    context.window.PopupSkema.ikutiUkuranPeta(map);
    const popup = { options: context.window.PopupSkema.opsiPeta(map), update() {} };
    events.popupopen({ popup });
    size = { x: 250, y: 220 };
    events.resize();
    assert.ok(popup.options.maxWidth <= size.x - 48);
    assert.ok(popup.options.maxHeight <= size.y - 60);
    const other = { options: { className: 'other', maxWidth: 500 }, update() { throw new Error('unrelated popup changed'); } };
    events.popupopen({ popup: other });
    events.resize();
    assert.equal(other.options.maxWidth, 500);
});

test('combined map binds the published schema accordion to a marker', () => {
    let popup = '';
    const map = {
        setView() { return this; },
        hasLayer() { return false; },
        addLayer() {},
        removeLayer() {},
        fitBounds() {},
        getSize() { return { x: 800, y: 500 }; },
        on() {},
    };
    const bounds = { extend() {}, isValid() { return false; } };
    const group = { addTo() { return this; }, addLayer() {}, clearLayers() {} };
    const L = {
        map() { return map; },
        tileLayer() { return { addTo() { return this; } }; },
        layerGroup() { return group; },
        markerClusterGroup() { return group; },
        latLngBounds() { return bounds; },
        control: { layers() { return { addTo() {}, remove() {} }; } },
        marker() {
            return { bindPopup(html) { popup = html; } };
        },
    };
    const mapSource = fs.readFileSync(path.join(__dirname, '..', '..', 'public', 'js', 'dataspasial', 'peta-gabungan.js'), 'utf8');
    const mapContext = { window: { PopupSkema: context.window.PopupSkema }, document: { getElementById() { return null; } }, L };
    vm.runInNewContext(mapSource, mapContext);
    mapContext.window.PetaGabungan.render({
        features: [{
            type: 'Feature',
            geometry: { type: 'Point', coordinates: [115, -8] },
            properties: { nama: 'Sekolah', popup_skema: [{ sektor: 'Pendidikan', versi: 1, isian: [{ label: 'Siswa', nilai: 20 }] }] },
        }],
    }, {});
    assert.match(popup, /Sekolah/);
    assert.match(popup, /<dt>Koordinat<\/dt><dd>-8, 115<\/dd>/);
    assert.match(popup, /<details name="popup-skema-/);
    assert.match(popup, /<dt>Siswa<\/dt><dd>20<\/dd>/);
});

test('presisi map ignores stale responses and reuses popup details when filtering', () => {
    const raw = fs.readFileSync(path.join(__dirname, '..', '..', 'resources', 'views', 'presisi', 'geo_spasial', 'layouts', 'peta', 'coordinate.blade.php'), 'utf8');
    const script = raw.replace(/{{[\s\S]*?}}/g, (tag) => {
        if (tag.includes('databaseGabunganUrl')) return 'https://example.test/api/v1/plan/get-list-coordinate';
        if (tag.includes('presisi.geo-spasial.skema')) return '/presisi/geo-spasial/skema';
        return '/default.png';
    });
    const requests = [];
    const markers = [];
    const element = () => ({
        children: [],
        events: {},
        classList: { add() {} },
        appendChild(child) { this.children.push(child); },
        addEventListener(event, handler) { this.events[event] = handler; },
    });
    const container = element();
    const modal = element();
    const document = { querySelectorAll() { return []; }, getElementById(id) { return id === 'modalContent' ? modal : container; }, createElement: element };
    const $ = () => ({ on() {}, val() { return ''; }, modal() {} });
    $.ajax = (options) => {
        if (String(options.url).includes('get-list-coordinate')) {
            requests.push(options);
            return {};
        }
        const deferred = {
            callbacks: [],
            done(fn) { if (this.result) fn(this.result); else this.callbacks.push(fn); return this; },
            fail() { return this; },
            resolve(result) { this.result = result; this.callbacks.forEach((fn) => fn(result)); },
        };
        requests.push({ options, deferred });
        return deferred;
    };
    const L = {
        Marker: function () {},
        LatLng: function () {},
        marker() {
            const marker = {
                bindPopup(html) { this.content = html; return this; },
                addTo() { markers.push(this); return this; },
                setPopupContent(html) { this.content = html; },
            };
            return marker;
        },
    };
    const page = {
        $, document, L, URL, markerIcon: {},
        map: { eachLayer() {}, panTo() {}, getSize() { return { x: 800, y: 350 }; } },
        window: { PopupSkema: context.window.PopupSkema },
        coordinateRequestSequence: 0,
        popupSkemaRequest: null,
    };
    vm.runInNewContext(script, page);
    const point = { id: 100, lat: -8, lng: 115, nama: '<Lokasi>', desk: '', point: { nama: 'Sekolah' }, config: {} };
    page.GetListCoordinates();
    page.GetListCoordinates();
    requests[1].success([point]);
    requests[0].success([point]);
    assert.equal(markers.length, 1);
    container.children[0].children[0].children[1].events.click();
    assert.match(modal.innerHTML, /Memuat informasi skema/);
    requests[2].deferred.resolve({ lokasi: { 100: { skema: [{ sektor: 'Pendidikan', isian: [{ label: 'Siswa', nilai: 20 }] }] } } });
    assert.match(markers[0].content, /&lt;Lokasi&gt;/);
    assert.match(markers[0].content, /<dt>Koordinat<\/dt><dd>-8, 115<\/dd>/);
    assert.match(markers[0].content, /<dt>Siswa<\/dt><dd>20<\/dd>/);
    assert.match(modal.innerHTML, /&lt;Lokasi&gt;/);
    assert.match(modal.innerHTML, /<dt>Siswa<\/dt><dd>20<\/dd>/);
    page.GetListCoordinates();
    requests[3].success([point]);
    assert.equal(requests.length, 4);
    assert.match(markers[1].content, /Pendidikan/);
});

test('dashboard loads the map initially and independently when dashboard data fails', async () => {
    const elements = {};
    for (const id of ['map-unavailable', 'dasbor-kartu', 'dasbor-tabel-baris', 'dasbor-grafik-kecamatan', 'dasbor-grafik-utama-title']) {
        elements[id] = { innerHTML: '', style: {} };
    }
    elements['peta-filter'] = {};
    elements['peta-reset'] = {};
    const calls = [];
    const renders = [];
    const map = {
        init() {},
        render(data) { renders.push(data); },
        unavailable() { throw new Error('map should be available'); },
    };
    const document = {
        querySelector(selector) { return elements[selector.slice(1)] || null; },
        querySelectorAll() { return []; },
        getElementById(id) { return elements[id] || null; },
    };
    const location = { search: '', pathname: '/dataspasial/dasbor' };
    const page = {
        window: { PetaGabungan: map, location }, document, location, URLSearchParams,
        history: { replaceState() {} },
        console: { error() {} },
        fetch(url) {
            calls.push(url);
            return url.startsWith('/map')
                ? Promise.resolve({ ok: true, json: () => Promise.resolve({ features: [], meta: {} }) })
                : Promise.resolve({ ok: false, status: 503 });
        },
    };
    const source = fs.readFileSync(path.join(__dirname, '..', '..', 'public', 'js', 'dataspasial', 'dasbor.js'), 'utf8');
    vm.runInNewContext(source, page);
    page.window.DasborDataSpasial.init({ petaUrl: '/map', dataUrl: '/data' });
    await new Promise(setImmediate);
    assert.equal(renders.length, 1);
    assert.equal(calls.filter((url) => url.startsWith('/data')).length, 0);

    elements['peta-filter'].onclick();
    await new Promise(setImmediate);
    assert.equal(renders.length, 2);
    assert.match(elements['dasbor-kartu'].innerHTML, /sementara tidak tersedia/);
});
