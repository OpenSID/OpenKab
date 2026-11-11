const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', '..', 'public', 'js', 'dataspasial', 'terbit-dialog.js'), 'utf8');

function fixture(responses) {
    const elements = new Map();
    const element = (selector) => {
        if (!elements.has(selector)) {
            const hidden = new Set(['d-none']);
            elements.set(selector, {
                textContent: '', disabled: false, value: '', dataset: {},
                getAttribute() { return ''; },
                classList: {
                    add(name) { hidden.add(name); },
                    remove(name) { hidden.delete(name); },
                    contains(name) { return hidden.has(name); },
                },
            });
        }
        return elements.get(selector);
    };
    const requests = [];
    let destination;
    const context = {
        document: { readyState: 'loading', addEventListener() {}, querySelector: element },
        window: {
            addEventListener() {},
            jQuery() { return { modal() {} }; },
            confirm() { return true; },
            location: { assign(url) { destination = url; } },
        },
        fetch: async (url, options) => {
            requests.push({ url, body: JSON.parse(options.body) });
            const response = responses.shift();
            return { ok: response.ok, status: response.status, json: async () => response.data };
        },
    };
    vm.runInNewContext(source + '\nglobalThis.actions = { bukaDialog, batalkanDraf };', context);
    return { element, requests, actions: context.actions, destination: () => destination };
}

test('draf tanpa perubahan menawarkan pembatalan lalu kembali ke versi terbit', async () => {
    const f = fixture([
        { ok: false, status: 422, data: { tanpa_perubahan: true, nomor_draf: 2, nomor_terbit: 1, message: 'Draf identik.' } },
        { ok: true, status: 200, data: { success: true } },
    ]);
    await f.actions.bukaDialog('pendidikan');
    assert.equal(f.element('[data-testid="batalkan-draf-wrap"]').classList.contains('d-none'), false);
    assert.equal(f.element('[data-testid="terbitan-detail"]').classList.contains('d-none'), true);
    await f.actions.batalkanDraf();
    assert.equal(f.requests[1].url, '/dataspasial/skema/draf/batalkan');
    assert.deepEqual(f.requests[1].body, { sektor: 'pendidikan', nomor_draf: 2, nomor_terbit: 1 });
    assert.equal(f.destination(), '/dataspasial/skema?sektor=pendidikan');
});

test('penolakan pembatalan tetap ditampilkan dan tidak berpindah halaman', async () => {
    const f = fixture([
        { ok: false, status: 422, data: { tanpa_perubahan: true, nomor_draf: 2, nomor_terbit: 1 } },
        { ok: false, status: 422, data: { message: 'Draf memiliki perubahan.' } },
    ]);
    await f.actions.bukaDialog('pendidikan');
    await f.actions.batalkanDraf();
    assert.equal(f.element('[data-testid="terbitan-gagal"]').textContent, 'Draf memiliki perubahan.');
    assert.equal(f.element('[data-testid="btn-batalkan-draf"]').disabled, false);
    assert.equal(f.destination(), undefined);
});
