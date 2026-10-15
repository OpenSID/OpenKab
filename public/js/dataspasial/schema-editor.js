/**
 * Editor Skema Kabupaten — KAB-02 (OpenSID/OpenKab#1297)
 *
 * ATURAN YANG WAJIB DIPATUHI (research.md R12):
 * - Nama field ASUMSI kontrak API **DILARANG** muncul di berkas ini, termasuk
 *   pada nama input form. Yang dipakai hanya NAMA INTERNAL (`popupPeta`,
 *   `sebagaiFilter`, `sebagaiIndikator`, `sumber`, `rujukan`).
 * - Yang boleh menyebut nama asumsi hanya `app/Services/SkemaEditorService.php`
 *   dan `app/Services/DataSpasialApiService.php`.
 * - Di sini hanya boleh dipakai NAMA INTERNAL: `popupPeta`, `sebagaiFilter`,
 *   `sebagaiIndikator`, `sumber`.
 *
 * Sumber kebenaran tunggal untuk editor + pratinjau adalah `stateIsian` (R7):
 * pratinjau merender dari state yang SAMA dengan form, sehingga perubahan yang
 * belum disimpan tetap terlihat (FR-035).
 *
 * Seret-urut memakai NATIVE HTML5 Drag & Drop (R8) — jQuery UI TIDAK terpasang
 * pada `node_modules/`, jadi `$(...).sortable()` tidak boleh dipakai.
 */

const KODE_ISIAN_PATTERN = /^[a-z][a-z0-9_]*$/;

/** Tipe yang kolom opsinya aktif (cerminan `TipeIsian::memakaiPilihan()`). */
const TIPE_PILIHAN = 'pilihan';

/** Tipe yang kolom satuannya relevan (cerminan `TipeIsian::memakaiSatuan()`). */
const TIPE_SATUAN = 'angka';

/**
 * Bangkitkan `kode` isian dari `label` (FR-017, spec Q14).
 *
 * ⚠️ Ini adalah **satu-satunya** tempat yang membuat `kode`. Admin tidak lagi
 * mengetik kode; kolomnya baca-saja dan terisi otomatis.
 *
 * ⚠️ **TIDAK** menambahkan akhiran angka saat kode bentrok (Q14/C). Label
 * duplikat justru harus DITOLAK supaya admin memperbaikinya (lihat
 * `validasiKodeLokal()`).
 *
 * @param {string} label
 * @returns {string} kode *snake_case*, atau string kosong bila label tidak
 *                      menghasilkan slug yang sah.
 */
export function slugKodeDariLabel(label) {
    const dasar = String(label || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')      // buang tanda diakritik
        .replace(/[^a-z0-9]+/g, '_')           // non-alnum → _
        .replace(/_+/g, '_')                   // rapikan _
        .replace(/^_|_$/g, '');                // rapikan tepian

    // ⚠️ FR-018: pola sah diawali huruf KECIL (bukan angka).
    if (dasar === '' || !/^[a-z]/.test(dasar)) {
        return '';
    }

    return dasar;
}

export function isKodeIsianValid(kode) {
    return KODE_ISIAN_PATTERN.test(String(kode || '').trim());
}

/**
 * Buat baris isian kosong dengan bentuk yang sama dengan `form-isian.blade.php`.
 *
 * @param {number} urutan
 */
function barisBaru(urutan) {
    return {
        kode: '',
        label: '',
        tipe: 'teks',
        wajib: false,
        satuan: '',
        opsi: [],
        urutan,
        popupPeta: false,
        sebagaiFilter: false,
        sebagaiIndikator: false,
        sumber: 'manual',
        sumber: 'manual',
        aturanHitung: null,
        rujukan: null,
    };
}

/**
 * Baca seluruh baris yang sedang tampil di DOM menjadi bentuk state.
 *
 * DOM adalah sumber pembacaan; `stateIsian` dipakai untuk pratinjau & serialisasi.
 *
 * @param {HTMLElement} tbody
 */
function bacaDariDom(tbody) {
    return Array.from(tbody.querySelectorAll('tr[data-testid="baris-isian"], tr.baris-baru'))
        .map((tr, index) => {
            const nilai = (selector) => tr.querySelector(selector);
            const opsi = nilai('.input-opsi');
            const sel = nilai('.sumber-nilai');

            return {
                // ⚠️ FR-017/Q14 — `kode` SELALU diturunkan ulang dari `label`,
                // tidak pernah dipercaya apa adanya dari input baca-saja.
                kode: slugKodeDariLabel(nilai('.input-label')?.value ?? ''),
                label: nilai('.input-label')?.value?.trim() ?? '',
                tipe: nilai('.input-tipe')?.value ?? 'teks',
                wajib: Boolean(nilai('.input-wajib')?.checked),
                satuan: nilai('.input-satuan')?.value?.trim() ?? '',
                opsi: opsi && !opsi.disabled
                    ? Array.from(opsi.selectedOptions).map((o) => o.value).filter(Boolean)
                    : [],
                urutan: index + 1,
                popupPeta: Boolean(nilai('.input-popup-peta')?.checked),
                sebagaiFilter: Boolean(nilai('.input-filter')?.checked),
                sebagaiIndikator: Boolean(nilai('.input-indikator')?.checked),
                sumber: sel?.dataset?.sumber ?? 'manual',
                // ⚠️ `dataset` memakai camelCase: `data-aturan-hitung` →
                // `dataset.aturanHitung`. Akses `dataset?.aturan-hitung`
                // akan diparse sebagai pengurangan (`aturan - hitung`) dan
                // melempar ReferenceError setiap kali serialisasi berjalan.
                // Belum ada sumber yang menulis atribut ini, jadi default null.
                aturanHitung: sel?.dataset?.aturanHitung ?? null,
                rujukan: sel?.dataset?.rujukan || null,
            };
        });
}

/**
 * Terapkan aturan kolom  tipe (FR-010).
 *
 * @param {HTMLElement} tbody
 */
function terapkanAturanTipe(tbody) {
    tbody.querySelectorAll('tr').forEach((tr) => {
        const tipe = tr.querySelector('.input-tipe')?.value ?? 'teks';
        const opsi = tr.querySelector('.input-opsi');
        const satuan = tr.querySelector('.input-satuan');
        const bolehUbah = tbody.dataset.bolehUbah === '1';

        if (opsi) {
            opsi.disabled = !bolehUbah || tipe !== TIPE_PILIHAN;
        }

        if (satuan) {
            satuan.disabled = !bolehUbah || tipe !== TIPE_SATUAN;
        }
    });
}

/**
 * Render pratinjau dari state yang SAMA dengan form (FR-034/FR-035, R7).
 *
 * @param {HTMLElement} wadah
 * @param {Array<object>} isian
 */
function renderPratinjau(wadah, isian) {
    if (!wadah) {
        return;
    }

    if (isian.length === 0) {
        wadah.innerHTML = '<p class="text-muted text-center small mb-0">'
            + 'Belum ada isian. Pratinjau akan muncul setelah isian pertama ditambahkan.</p>';

        return;
    }

    wadah.innerHTML = isian.map((baris) => {
        const terkunci = baris.sumber && baris.sumber !== 'manual';
        const wajib = baris.wajib
            ? '<span class="text-danger ml-1" title="Wajib diisi">*</span>'
            : '';

        let kontrol;

        if (terkunci) {
            kontrol = '<div class="form-control input-group-sm" style="background:#f1f2f3;'
                + 'border:1px dashed #adb5bd;min-height:calc(1.5em + .5rem + 2px)">'
                + '<span class="text-muted"><i class="fas fa-lock mr-1"></i>'
                + 'Diisi dari sumber: ' + (baris.sumber || 'pemetaan') + '</span></div>';
        } else if (baris.tipe === TIPE_PILIHAN) {
            kontrol = '<select class="form-control form-control-sm" disabled>'
                + '<option>— pilih —</option>'
                + baris.opsi.map((o) => `<option>${o}</option>`).join('')
                + '</select>';
        } else if (baris.tipe === 'ya_tidak') {
            kontrol = '<select class="form-control form-control-sm" disabled>'
                + '<option>Ya</option><option>Tidak</option></select>';
        } else if (baris.tipe === 'tanggal') {
            kontrol = '<input type="date" class="form-control form-control-sm" disabled>';
        } else {
            kontrol = '<input type="' + (baris.tipe === TIPE_SATUAN ? 'number' : 'text')
                + '" class="form-control form-control-sm" disabled placeholder="'
                + (baris.satuan || 'isi nilai') + '">';
        }

        const catatanTersembunyi = terkunci && !baris.rujukan
            ? '<small class="text-muted d-block mt-1">Nilai belum tersedia dari sumber.</small>'
            : '';

        const penanda = [];
        if (baris.popupPeta) { penanda.push('<span class="badge badge-info mr-1">Peta</span>'); }
        if (baris.sebagaiFilter) { penanda.push('<span class="badge badge-secondary mr-1">Filter</span>'); }
        if (baris.sebagaiIndikator) { penanda.push('<span class="badge badge-primary mr-1">Indikator</span>'); }

        return '<div class="mb-2 pb-2 border-bottom" data-testid="pratinjau-baris">'
            + '<label class="mb-1 small font-weight-bold">' + (baris.label || '(tanpa label)')
            + wajib + '</label>'
            + kontrol
            + (penanda.length ? '<div class="mt-1">' + penanda.join('') + '</div>' : '')
            + catatanTersembunyi
            + '</div>';
    }).join('');
}

/**
 * Serialisasi state ke input tersembunyi milik form POST (FR-029).
 *
 * ⚠️ Nama key di sini mengikuti aturan validasi `StoreDrafSkemaRequest`;
 * pemetaan ke NAMA ASUMSI kontrak terjadi di `SkemaEditorService`.
 *
 * @param {Array<object>} isian
 */
function serialisasiKeForm(isian) {
    const wadah = document.getElementById('wadah-input-isian');
    const form = document.getElementById('form-draf');

    if (!wadah || !form) {
        return;
    }

    wadah.innerHTML = '';

    isian.forEach((baris, index) => {
        const tambah = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `isian[${index}][${name}]`;
            input.value = value ?? '';
            wadah.appendChild(input);
        };

        tambah('kode', baris.kode);
        tambah('label', baris.label);
        tambah('tipe', baris.tipe);
        tambah('wajib', baris.wajib ? '1' : '0');
        tambah('satuan', baris.satuan || '');
        tambah('urutan', baris.urutan);

        if (baris.tipe === TIPE_PILIHAN) {
            baris.opsi.forEach((opsi) => tambah(`opsi[]`, opsi));
        }

        // ⚠️ Nama INTERNAL. Pemetaan ke nama field asumsi kontrak API hanya
        // terjadi di `SkemaEditorService::composeDrafPayload()` (research.md R12).
        tambah('popupPeta', baris.popupPeta ? '1' : '0');
        tambah('sebagaiFilter', baris.sebagaiFilter ? '1' : '0');
        tambah('sebagaiIndikator', baris.sebagaiIndikator ? '1' : '0');

        // ⚠️ `sumber` berisi ENUM API (`openSID`/`dataPresisi`/`impor`),
        // bukan kode sektor — hasil verifikasi langsung ke API (§6a.5).
        if (baris.sumber && baris.sumber !== 'manual') {
            tambah('sumber', baris.sumber);
            tambah('rujukan', baris.rujukan || '');
        }
    });
}

/**
 * Validasi keunikan di sisi klien untuk umpan balik INSTAN (FR-018, Q14/Q17).
 *
 * ⚠️ Ini HANYA untuk UX. Satu-satunya penentu tetap validasi server
 * (FR-020: API tidak boleh pernah menerima kode bentrok dari pengguna ini).
 *
 * ⚠️ Cakupannya **satu versi** yang sedang disunting — baris di luar tabel ini
 * (versi sebelumnya) tidak diperiksa (Q17).
 *
 * ⚠️ Yang dibandingkan adalah `label` (bukan kode) supaya pesan dapat
 * langsung ditindaklanjuti admin; kode turunan ikut terisi Unique karena
 * diturunkan dari label (Q14).
 *
 * @param {HTMLElement} tbody
 */
function validasiKodeLokal(tbody) {
    const labelDilihat = new Map();

    tbody.querySelectorAll('tr').forEach((tr) => {
        const inputLabel = tr.querySelector('.input-label');
        const galat = tr.querySelector('.galat-kode');

        if (!inputLabel) {
            return;
        }

        galat?.remove();
        inputLabel.classList.remove('is-invalid');

        const label = inputLabel.value.trim();

        if (label === '') {
            return;
        }

        // ⚠️ Kolom kode tidak lagi ada di tabel (FR-017/Q14); kodenya hanya
        // dihitung saat serialisasi (`bacaDariDom`), bukan ditampilkan.
        const kode = slugKodeDariLabel(label);

        let pesan = '';

        if (kode === '') {
            pesan = 'Label ini tidak menghasilkan kode yang sah. Gunakan huruf dan angka, contoh: "Jumlah Siswa".';
        } else if (labelDilihat.has(kode)) {
            pesan = `Label "${label}" sudah dipakai isian lain pada versi ini. `
                + 'Ubah labelnya — kode tidak dibuat otomatis dengan akhiran angka.';
        } else {
            labelDilihat.set(kode, true);
        }

        if (pesan === '') {
            return;
        }

        inputLabel.classList.add('is-invalid');
        const info = document.createElement('small');
        info.className = 'galat-kode text-danger d-block';
        info.textContent = pesan;
        inputLabel.parentElement.appendChild(info);
    });
}

/**
 * Seret-urut NATIVE HTML5 Drag & Drop (FR-012, research.md R8).
 *
 * @param {HTMLElement} tbody
 * @param {Function} onMutasi
 */
function pasangSeretUrutan(tbody, onMutasi) {
    if (tbody.dataset.bolehUbah !== '1') {
        return;
    }

    let yangDiseret = null;

    tbody.addEventListener('dragstart', (event) => {
        const tr = event.target.closest('tr');

        if (!tr) {
            return;
        }

        yangDiseret = tr;
        tr.classList.add('baris-sedang-diseret');
        event.dataTransfer.effectAllowed = 'move';
        // Firefox membutuhkan dataTransfer berisi data agar drag dimulai.
        event.dataTransfer.setData('text/plain', '');
    });

    tbody.addEventListener('dragover', (event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';

        const target = event.target.closest('tr');

        if (!target || target === yangDiseret || !yangDiseret) {
            return;
        }

        // Sisipkan sebelum / sesudah posisi tetikus.
        const kotak = target.getBoundingClientRect();
        const setelah = event.clientY - kotak.top > kotak.height / 2;

        target.parentNode.insertBefore(
            yangDiseret,
            setelah ? target.nextSibling : target
        );
    });

    tbody.addEventListener('dragend', () => {
        yangDiseret?.classList.remove('baris-sedang-diseret');
        yangDiseret = null;
        perbaruiNomorUrutan(tbody);
        onMutasi();
    });

    tbody.addEventListener('drop', (event) => event.preventDefault());
}

/**
 * Tulis ulang kolom `#` menjadi 1..N (FR-012).
 *
 * @param {HTMLElement} tbody
 */
function perbaruiNomorUrutan(tbody) {
    tbody.querySelectorAll('tr').forEach((tr, index) => {
        tr.dataset.index = String(index);

        const sel = tr.querySelector('.urutan');

        if (sel) {
            sel.textContent = String(index + 1);
        }
    });
}

/**
 * Konfirmasi "sudah ada draf berjalan" (FR-007g/FR-007h, spec Q6).
 *
 * @param {number} RedirectKe
 * @returns {Promise<boolean>}
 */
function konfirmasiDrafAda(RedirectKe) {
    if (typeof window.Swal === 'undefined') {
        if (window.confirm('Sektor ini sudah memiliki draf berjalan. Buka draf tersebut?')) {
            window.location.href = RedirectKe;
        }

        return Promise.resolve(false);
    }

    return window.Swal.fire({
        title: 'Draf sudah ada',
        text: 'Sektor ini sudah memiliki draf berjalan. Buka draf tersebut?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Buka draf',
        cancelButtonText: 'Batal',
    }).then((hasil) => {
        if (hasil.isConfirmed) {
            window.location.href = RedirectKe;
        }

        return false;
    });
}

export function mountSchemaEditor() {
    const akar = document.querySelector('[data-status-editor]');
    const tbody = document.getElementById('isi-tabel-isian');

    if (!akar || !tbody) {
        return;
    }

    const wadahPratinjau = document.getElementById('wadah-pratinjau');
    const template = document.getElementById('template-baris-isian');
    const bolehUbah = tbody.dataset.bolehUbah === '1';
    const adaDrafBerjalan = Boolean(document.querySelector('[data-testid="item-skema"][data-status="draf"]'));
    const sektorKode = akar.dataset.sektor || '';
    const skemaAktif = akar.dataset.skema || '';

    let dirty = false;

    const mutasi = () => {
        dirty = true;
        perbaruiNomorUrutan(tbody);
        terapkanAturanTipe(tbody);
        validasiKodeLokal(tbody);
        renderPratinjau(wadahPratinjau, bacaDariDom(tbody));
        // FR-045 — perubahan baru membuat penerbitan tidak boleh dipakai.
        perbaruiTombolTerbit();
    };

    const pasangKonfirmasi = () => {
        if (!dirty) {
            return true;
        }

        const pesan = 'Ada perubahan yang belum disimpan. Tinggalkan halaman?';

        return window.confirm(pesan);
    };

    // -----------------------------------------------------------------------
    // Tambah isian (US1#13, US2)
    // -----------------------------------------------------------------------
    document.getElementById('btn-tambah-isian')?.addEventListener('click', () => {
        if (!template) {
            return;
        }

        tbody.appendChild(template.content.cloneNode(true));
        perbaruiNomorUrutan(tbody);
        mutasi();

        // ⚠️ FR-017/Q14 — `kode` kini baca-saja, jadi fokus awal ke ke `label`
        // yang menjadi sumber kebenaran kode.
        tbody.querySelector('tr:last-child .input-label')?.focus();
    });

    // -----------------------------------------------------------------------
    // Hapus isian (FR-014, US2#4/#6)
    // -----------------------------------------------------------------------
    tbody.addEventListener('click', (event) => {
        const tombol = event.target.closest('.btn-hapus-isian');

        if (!tombol) {
            return;
        }

        const tr = tombol.closest('tr');
        // ⚠️ Q14 — admin tidak lagi mengetik `kode`, jadi konfirmasi menyebut
        // LABEL yang mereka lihat di tabel. `data-kode` dipakai sebagai cadangan
        // untuk baris yang labelnya kosong.
        const label = tr?.querySelector('.input-label')?.value?.trim()
            || tombol.dataset.kode
            || '(tanpa label)';
        const terpetakan = tombol.dataset.terpetakan === '1';

        const pesan = terpetakan
            ? `Hapus isian "${label}"? Pemetaan sumber nilainya juga akan dilepas.`
            : `Hapus isian "${label}"?`;

        if (!window.confirm(pesan)) {
            return;
        }

        tr?.remove();
        perbaruiNomorUrutan(tbody);
        mutasi();
    });

    // -----------------------------------------------------------------------
    // Tambah sektor (US1#8)
    // -----------------------------------------------------------------------
    const formSektor = document.getElementById('form-tambah-sektor');

    document.getElementById('btn-tambah-sektor')?.addEventListener('click', () => {
        formSektor?.classList.toggle('d-none');
        document.getElementById('input-nama-sektor')?.focus();
    });

    document.getElementById('btn-batal-sektor')?.addEventListener('click', () => {
        formSektor?.classList.add('d-none');
    });

    // -----------------------------------------------------------------------
    // Ubah sektor (US7, FR-007d-4 … FR-007d-6)
    //
    // ⚠️ KODE SEKTOR TIDAK DIKIRIM — form hanya memuat `nama` + `ikon`
    // (FR-007d-5). Daftar sektor tidak ikut dikirim karena keunikan nama
    // diperiksa API (spec Q13).
    // -----------------------------------------------------------------------
    const formUbahSektor = document.getElementById('form-ubah-sektor');

    const tutupFormUbahSektor = () => {
        formUbahSektor?.classList.add('d-none');
    };

    document.querySelectorAll('.btn-ubah-sektor').forEach((tombol) => {
        tombol.addEventListener('click', (event) => {
            // Item sektor memakai `list-group-item-action` sehingga klik pada
            // elemen anak ikut memicu navigasi — klik tombol harus dihentikan.
            event.preventDefault();
            event.stopPropagation();

            const item = tombol.closest('[data-testid="item-sektor"]');
            const template = formUbahSektor?.dataset.urlTemplate;

            if (!item || !template) {
                return;
            }

            const id = item.dataset.sektorId || '';

            if (id === '') {
                return;
            }

            if (formUbahSektor) {
                formUbahSektor.action = template.replace('__ID__', id);
                formUbahSektor.classList.remove('d-none');

                // Kode hanya ditampilkan sebagai informasi, bukan input.
                const labelKode = formUbahSektor.querySelector('[data-testid="ubah-sektor-kode"]');
                if (labelKode) {
                    labelKode.textContent = item.dataset.sektorKode || '';
                }

                const inputNama = formUbahSektor.querySelector('#input-ubah-nama-sektor');
                if (inputNama) {
                    inputNama.value = item.dataset.sektorNama || '';
                }

                const inputIkon = formUbahSektor.querySelector('#input-ubah-ikon-sektor');
                if (inputIkon) {
                    inputIkon.value = item.dataset.sektorIkon || '';
                }

                inputNama?.focus();
            }
        });
    });

    document.getElementById('btn-batal-ubah-sektor')?.addEventListener('click', tutupFormUbahSektor);

    // Menutup form ubah bila admin membuka form tambah (hanya satu form terbuka).
    document.getElementById('btn-tambah-sektor')?.addEventListener('click', tutupFormUbahSektor);

    // -----------------------------------------------------------------------
    // Tambah skema baru (US1#9, Q6/R6)
    // -----------------------------------------------------------------------
    document.getElementById('btn-tambah-skema')?.addEventListener('click', async () => {
        if (!sektorKode) {
            return;
        }

        // Draf sudah berjalan → konfirmasi, JANGAN buat draf kedua (FR-007g).
        if (adaDrafBerjalan) {
            await konfirmasiDrafAda(`${window.location.pathname}?sektor=${encodeURIComponent(sektorKode)}`);

            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            ?? document.querySelector('input[name="_token"]')?.value
            ?? '';

        try {
            const respons = await fetch('/dataspasial/skema/draf', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ sektor: sektorKode, isian: [] }),
            });

            if (respons.status === 422) {
                window.alert('Sektor ini sudah memiliki draf berjalan.');

                return;
            }

            if (!respons.ok) {
                window.alert('Data sementara tidak tersedia. Silakan coba lagi nanti.');

                return;
            }

            window.location.href = `${window.location.pathname}?sektor=${encodeURIComponent(sektorKode)}`;
        } catch (error) {
            window.alert('Data sementara tidak tersedia. Silakan coba lagi nanti.');
        }
    });

    // -----------------------------------------------------------------------
    // Simpan draf (US3) — pemicu konfirmasi yang sama (FR-007h)
    // -----------------------------------------------------------------------
    document.getElementById('btn-simpan-draf')?.addEventListener('click', async () => {
        if (!bolehUbah) {
            return;
        }

        // FR-007i: bila admin SEDANG menyunting draf itu sendiri, konfirmasi
        // tidak boleh muncul.
        const menyuntingDrafIni = Boolean(skemaAktif)
            && document.querySelector(`[data-testid="item-skema"][data-skema-id="${skemaAktif}"][data-status="draf"]`);

        if (adaDrafBerjalan && !menyuntingDrafIni) {
            await konfirmasiDrafAda(`${window.location.pathname}?sektor=${encodeURIComponent(sektorKode)}&skema=${skemaAktif}`);

            return;
        }

        const isian = bacaDariDom(tbody);

        if (isian.length === 0) {
            window.alert('Sektor harus memiliki minimal satu isian.');

            return;
        }

        // ⚠️ FR-018/Q14/Q17 — yang dibandingkan adalah `label`; kode turunan
        // otomatis unik kalau label-nya unik. Cakupannya satu versi ini saja.
        const labelBentrok = isian
            .map((b) => b.label)
            .filter((label, i, arr) => label !== '' && arr.indexOf(label) !== i);

        if (labelBentrok.length > 0) {
            window.alert(`Label "${labelBentrok[0]}" dipakai lebih dari satu isian pada versi ini.`);

            return;
        }

        const kodeKosong = isian.find((b) => b.kode === '');

        if (kodeKosong) {
            window.alert(`Label "${kodeKosong.label}" tidak menghasilkan kode yang sah. Perbaiki labelnya.`);

            return;
        }

        serialisasiKeForm(isian);
        dirty = false;
        perbaruiTombolTerbit();
        document.getElementById('form-draf')?.submit();
    });

    // -----------------------------------------------------------------------
    // Terbitkan draf (US8, FR-041 … FR-045)
    // -----------------------------------------------------------------------
    const tombolTerbit = document.getElementById('btn-terbitan');
    const formTerbit = document.getElementById('form-terbit');

    // ⚠️ Keadaan awal berasal dari server (FR-041): tombol hanya aktif bila
    // skema berstatus `draf`. Untuk status `terbit` tombol **tetap** nonaktif —
    // dirty-guard hanya boleh MENONAKAN tombol, tidak boleh menghidupkannya.
    const bolehTerbitAwal = tombolTerbit ? !tombolTerbit.disabled : false;

    /**
     * FR-045 — penerbitan menghasilkan versi immutable, jadi isian yang belum
     * disimpan tidak boleh diterbitkan. Tombol dinonaktifkan selama `dirty`.
     */
    const perbaruiTombolTerbit = () => {
        if (!tombolTerbit || !bolehTerbitAwal) {
            return;
        }

        if (dirty) {
            tombolTerbit.disabled = true;
            tombolTerbit.title = 'Simpan draf dulu — ada perubahan yang belum disimpan.';

            return;
        }

        tombolTerbit.disabled = false;
        tombolTerbit.removeAttribute('title');
    };

    // Panggil sekali setelah DOM siap agar tombol sesuai keadaan awal.
    perbaruiTombolTerbit();

    tombolTerbit?.addEventListener('click', () => {
        if (!formTerbit || !sektorKode) {
            return;
        }

        // FR-045 — pengaman kedua di sisi klien.
        if (dirty) {
            window.alert('Simpan draf dulu. Ada perubahan yang belum disimpan.');

            return;
        }

        const item = skemaAktif
            ? document.querySelector(`[data-testid="item-skema"][data-skema-id="${skemaAktif}"]`)
            : null;
        const namaSektor = document.querySelector('[data-testid="item-sektor"][data-sektor-kode="' + sektorKode + '"]')
            ?.dataset?.sektorNama || sektorKode;
        const nomorVersi = item?.dataset?.nomorVersi || '?';

        // FR-042 — konfirmasi menyebut sektor & nomor versi secara eksplisit.
        const pesan = `Terbitkan versi ${nomorVersi} pada sektor "${namaSektor}"?\n\n`
            + 'Setelah diterbitkan, versi ini menjadi immutable dan tidak dapat diubah. '
            + 'Desa yang sudah memakai versi lama tidak ikut berubah.';

        if (!window.confirm(pesan)) {
            return;
        }

        const inputCatatan = formTerbit.querySelector('[name="catatan_perubahan"]');
        if (inputCatatan) {
            inputCatatan.value = window.prompt('Catatan perubahan (opsional)', '') || '';
        }

        formTerbit.submit();
    });

    // -----------------------------------------------------------------------
    // Pemetaan isian ke sumber = daftar sektor (US4, FR-022/FR-023/FR-024)
    // -----------------------------------------------------------------------
    const modal = document.getElementById('modal-pilih-sumber');
    let barisDipilih = null;

    const tulisPemetaan = (tr, sumber, nama) => {
        const sel = tr.querySelector('.sumber-nilai');

        if (!sel) {
            return;
        }

        sel.dataset.sumber = sumber;
        sel.dataset.rujukan = '';

        const tombol = sel.querySelector('.btn-pilih-sumber-baris');
        const badge = tombol?.querySelector('.badge')
            ?? sel.querySelector('.badge');

        if (!badge) {
            return;
        }

        if (sumber && sumber !== 'manual') {
            badge.className = 'badge badge-info';
            badge.innerHTML = '<i class="fas fa-lock mr-1"></i>' + nama;
        } else {
            badge.className = 'badge badge-secondary';
            badge.textContent = 'Manual';
        }
    };

    tbody.addEventListener('click', (event) => {
        const tombol = event.target.closest('.btn-pilih-sumber-baris');

        if (!tombol || !modal) {
            return;
        }

        barisDipilih = tombol.closest('tr');

        const sel = barisDipilih.querySelector('.sumber-nilai');
        const label = document.getElementById('sumber-kode-isian');

        if (label) {
            label.textContent = barisDipilih.querySelector('.input-label')?.value || '(tanpa label)';
        }

        const sudahAda = sel?.dataset.sumber && sel.dataset.sumber !== 'manual';
        const tombolLepas = document.getElementById('btn-lepas-pemetaan');

        if (tombolLepas) {
            tombolLepas.classList.toggle('d-none', !sudahAda);
        }

        const cari = document.getElementById('cari-sumber');

        if (cari) {
            cari.value = '';
        }

        document.querySelectorAll('#daftar-sumber .item-sumber').forEach((item) => {
            item.style.display = '';
        });

        // eslint-disable-next-line no-undef
        window.jQuery?.(modal).modal('show');
    });

    // Pencarian sederhana pada daftar sektor.
    document.getElementById('cari-sumber')?.addEventListener('input', (event) => {
        const kunci = event.target.value.toLowerCase();

        document.querySelectorAll('#daftar-sumber .item-sumber').forEach((item) => {
            const nama = (item.dataset.sektor || '').toLowerCase();
            const kode = (item.dataset.kode || '').toLowerCase();

            item.style.display = (nama.includes(kunci) || kode.includes(kunci)) ? '' : 'none';
        });
    });

    document.querySelectorAll('#daftar-sumber .btn-pilih-sumber')?.forEach((tombol) => {
        tombol.addEventListener('click', () => {
            if (!barisDipilih) {
                return;
            }

            tulisPemetaan(barisDipilih, tombol.dataset.kode, tombol.dataset.nama);
            mutasi();

            // eslint-disable-next-line no-undef
            window.jQuery?.(modal).modal('hide');
        });
    });

    document.getElementById('btn-lepas-pemetaan')?.addEventListener('click', () => {
        if (!barisDipilih) {
            return;
        }

        tulisPemetaan(barisDipilih, 'manual', null);
        mutasi();

        // eslint-disable-next-line no-undef
        window.jQuery?.(modal).modal('hide');
    });

    // -----------------------------------------------------------------------
    // Dirty-guard (FR-016, R9)
    // -----------------------------------------------------------------------
    tbody.addEventListener('input', (event) => {
        dirty = true;

        // FR-017/Q14 — mengubah `label` langsung membangkitkan ulang `kode`,
        // lalu memeriksa keunikannya per versi (FR-018/Q17). Harus jalan
        // SEBELUM pratinjau supaya pratinjau memakai kode terbaru.
        if (event.target.classList.contains('input-label')) {
            validasiKodeLokal(tbody);
        }

        renderPratinjau(wadahPratinjau, bacaDariDom(tbody));
        // FR-045 — mengetik di tabel ikut membuat penerbitan tidak boleh dipakai.
        perbaruiTombolTerbit();
    });

    tbody.addEventListener('change', (event) => {
        if (event.target.classList.contains('input-tipe')) {
            terapkanAturanTipe(tbody);
        }

        mutasi();
    });

    window.addEventListener('beforeunload', (event) => {
        if (!dirty) {
            return undefined;
        }

        event.preventDefault();
        event.returnValue = '';

        return '';
    });

    document.addEventListener('click', (event) => {
        const sektor = event.target.closest('[data-sektor-kode] a');
        const skema = event.target.closest('[data-skema-id] a');

        if (sektor || skema) {
            pasangKonfirmasi();
        }
    });

    // -----------------------------------------------------------------------
    // Inisialisasi
    // -----------------------------------------------------------------------
    pasangSeretUrutan(tbody, mutasi);
    perbaruiNomorUrutan(tbody);
    terapkanAturanTipe(tbody);
    validasiKodeLokal(tbody);
    renderPratinjau(wadahPratinjau, bacaDariDom(tbody));
}


// Dimuat sebagai `<script type="module" src="...">` (bukan bundler entry),
// jadi harus dipanggil sendiri di root modul.
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => mountSchemaEditor());
    } else {
        mountSchemaEditor();
    }
}
