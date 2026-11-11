const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', '..', 'public', 'js', 'dataspasial', 'schema-editor.js'), 'utf8');
const context = { window: { addEventListener() {} } };
vm.runInNewContext(
    source.replace(/^export /gm, '') + '\nglobalThis.editor = { bacaDariDom, terapkanAturanTipe, renderPratinjau, jenisPenyimpanan };',
    context,
);

function fixture(opsi, tipe = 'pilihan') {
    const tombol = {
        disabled: false,
        textContent: '',
        classList: { toggle(_name, hidden) { tombol.hidden = hidden; } },
    };
    const controls = {
        '.input-label': { value: 'Jenis sekolah' },
        '.input-tipe': { value: tipe },
        '.input-satuan': { value: '', disabled: false },
        '.btn-atur-opsi': tombol,
        '.sumber-nilai': { dataset: { sumber: 'manual' } },
    };
    const row = {
        dataset: { opsi: JSON.stringify(opsi) },
        querySelector(selector) { return controls[selector] || null; },
    };
    return {
        row,
        controls,
        tombol,
        tbody: { dataset: { bolehUbah: '1' }, querySelectorAll() { return [row]; } },
    };
}

test('pilihan tersimpan dibaca dan perubahan pilihan tercermin dalam state dan pratinjau', () => {
    const { row, tbody, tombol } = fixture(['Negeri', 'Swasta & Umum']);
    context.editor.terapkanAturanTipe(tbody);
    assert.equal(tombol.textContent, 'Atur pilihan (2)');
    assert.equal(tombol.hidden, false);

    row.dataset.opsi = JSON.stringify(['Negeri', '<Swasta & Umum>']);
    const state = context.editor.bacaDariDom(tbody);
    assert.equal(JSON.stringify(state[0].opsi), JSON.stringify(['Negeri', '<Swasta & Umum>']));
    const preview = { innerHTML: '' };
    context.editor.renderPratinjau(preview, state);
    assert.match(preview.innerHTML, /&lt;Swasta &amp; Umum&gt;/);
    assert.doesNotMatch(preview.innerHTML, /<Swasta/);
});

test('tombol pilihan mengikuti tipe dan status editor', () => {
    const { tbody, controls, tombol } = fixture([], 'teks');
    context.editor.terapkanAturanTipe(tbody);
    assert.equal(tombol.hidden, true);
    controls['.input-tipe'].value = 'pilihan';
    tbody.dataset.bolehUbah = '0';
    context.editor.terapkanAturanTipe(tbody);
    assert.equal(tombol.hidden, false);
    assert.equal(tombol.disabled, true);
});

test('perubahan nama saja disimpan terpisah, perubahan isian tetap menjadi draf', () => {
    const awal = [{ label: 'Jumlah', opsi: ['A'] }];
    assert.equal(context.editor.jenisPenyimpanan(false, 'Lama', 'Baru', awal, [{ label: 'Jumlah', opsi: ['A'] }]), 'nama');
    assert.equal(context.editor.jenisPenyimpanan(false, 'Lama', 'Baru', awal, [{ label: 'Jumlah', opsi: ['B'] }]), 'draf');
    assert.equal(context.editor.jenisPenyimpanan(false, 'Lama', 'Lama', awal, awal), 'tidak-berubah');
    assert.equal(context.editor.jenisPenyimpanan(true, 'Lama', 'Baru', awal, awal), 'draf');
});
