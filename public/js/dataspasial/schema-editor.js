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
// ---------------------------------------------------------------------------
// FR-008g / Q27 — pembungkus SweetAlert2.
//
// ⚠️ SELURUH pesan pada halaman editor skema WAJIB lewat `window.Swal`
// (sweetalert2 v11, dimuat pada `index.blade.php`). `window.alert`,
// `window.confirm`, dan `window.prompt` TIDAK BOLEH dipakai.
//
// Fallback ke `window.confirm`/`window.alert` hanya berlaku bila SweetAlert2
// gagal dimuat (mis. CDN terblokir) agar halaman tetap berfungsi.
// ---------------------------------------------------------------------------

const swalTersedia = () => typeof window.Swal !== 'undefined';

/** Notifikasi hasil aksi (sukses / gagal). */
const swalPesan = (pesan, ikon = 'info') => {
    if (!swalTersedia()) {
        window.alert(pesan);

        return Promise.resolve();
    }

    return window.Swal.fire({
        title: pesan,
        icon: ikon,
        confirmButtonText: 'Mengerti',
        customClass: { popup: 'rounded-lg' },
    });
};

const swalSukses = (pesan) => swalPesan(pesan, 'success');
const swalGalat = (pesan) => swalPesan(pesan, 'error');
const swalPeringatan = (pesan) => swalPesan(pesan, 'warning');

/** Konfirmasi ya/tidak. Mengembalikan Promise<boolean>. */
const swalKonfirmasi = async (pesan, judul = 'Konfirmasi') => {
    if (!swalTersedia()) {
        return window.confirm(pesan);
    }

    const hasil = await window.Swal.fire({
        title: judul,
        text: pesan,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, lanjutkan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: { popup: 'rounded-lg' },
    });

    return hasil.isConfirmed === true;
};

/** Input teks tunggal (mis. catatan perubahan penerbitan). */
const swalInput = async (pesan, judul = 'Masukkan nilai', nilaiAwal = '') => {
    if (!swalTersedia()) {
        return window.prompt(pesan, nilaiAwal) || '';
    }

    const hasil = await window.Swal.fire({
        title: judul,
        input: 'text',
        inputLabel: pesan,
        inputValue: nilaiAwal,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        inputValidator: (nilai) => (nilai && nilai.length > 500 ? 'Maksimal 500 karakter' : undefined),
        customClass: { popup: 'rounded-lg' },
    });

    return hasil.isConfirmed === true ? (hasil.value || '') : null;
};

/** Escape nilai sebelum disisipkan ke markup SweetAlert2. */
function escapeHtml(nilai) {
    return String(nilai ?? '').replace(/[&<>"']/g, (karakter) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[karakter]));
}

/**
 * Susun rujukan sumber satu baris: "tabel.kolom".
 *
 * ⚠️ Bentuk API hanya punya SATU kolom `rujukan_sumber` (§6a.4). Dua input
 * tabel & kolom pada tabel editor karena itu digabung di sini menjadi satu
 * nilai `rujukan`; penyambungan di sisi server ada pada
 * `SkemaEditorService::composePemetaan()`.
 *
 * @param {HTMLElement} tr
 * @param {HTMLElement|null} sel Elemen `.sumber-nilai` (pemegang dataset).
 * @returns {string}
 */
function rujukanDari(tr, sel) {
    const tabel = (tr.querySelector('.input-tabel-sumber')?.value
        || sel?.dataset?.tabelSumber || '').trim();
    const kolom = (tr.querySelector('.input-kolom-sumber')?.value
        || sel?.dataset?.kolomSumber || '').trim();

    return [tabel, kolom].filter(Boolean).join('.');
}

/**
 * Baca opsi satu baris isian.
 *
 * ⚠️ Kolom "Pilihan" tidak lagi ada di tabel editor (tidak dipakai), sehingga
 * nilai opsi diambil dari atribut `data-opsi`. Bila atribut tidak tersedia,
 * kembalikan array kosong.
 *
 * @param {HTMLElement} tr
 * @returns {Array<string>}
 */
function bacaOpsiBaris(tr) {
    const mentah = tr?.dataset?.opsi;

    if (!mentah || mentah === '[]' || mentah === 'null') {
        return [];
    }

    try {
        const hasil = JSON.parse(mentah);

        return Array.isArray(hasil) ? hasil.filter((v) => typeof v === 'string') : [];
    } catch (e) {
        return [];
    }
}

function bacaDariDom(tbody) {
    return Array.from(tbody.querySelectorAll('tr[data-testid="baris-isian"], tr.baris-baru'))
        .map((tr, index) => {
            const nilai = (selector) => tr.querySelector(selector);
            const sel = nilai('.sumber-nilai');

            return {
                // ⚠️ FR-017/Q14 — `kode` SELALU diturunkan ulang dari `label`,
                // tidak pernah dipercaya apa adanya dari input baca-saja.
                kode: slugKodeDariLabel(nilai('.input-label')?.value ?? ''),
                label: nilai('.input-label')?.value?.trim() ?? '',
                tipe: nilai('.input-tipe')?.value ?? 'teks',
                wajib: Boolean(nilai('.input-wajib')?.checked),
                satuan: nilai('.input-satuan')?.value?.trim() ?? '',
                // ⚠️ Kolom Pilihan (opsi) TIDAK lagi ditampilkan pada tabel
                // editor. Nilainya dibaca dari atribut `data-opsi` pada baris
                // supaya opsi yang tersimpan di API tidak hilang saat Simpan draf.
                opsi: bacaOpsiBaris(tr),
                urutan: index + 1,
                popupPeta: Boolean(nilai('.input-popup-peta')?.checked),
                sebagaiFilter: Boolean(nilai('.input-filter')?.checked),
                sebagaiIndikator: Boolean(nilai('.input-indikator')?.checked),
                sumber: sel?.dataset?.sumber ?? 'manual',
                // ⚠️ `rujukan` = "tabel.kolom" adalah nilai yang benar-benar
                // disimpan sebagai `rujukan_sumber` (§6a.4). Field tabel/kolom
                // di bawah hanya dipakai untuk memulihkan snapshot (Batal).
                rujukan: rujukanDari(tr, sel) || null,
                tabelSumber: sel?.dataset?.tabelSumber ?? null,
                kolomSumber: sel?.dataset?.kolomSumber ?? null,
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
        const satuan = tr.querySelector('.input-satuan');
        const bolehUbah = tbody.dataset.bolehUbah === '1';

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
            // ⚠️ Satu nilai "tabel.kolom" — inilah yang disimpan sebagai
            // `rujukan_sumber` (§6a.4). Tabel & kolom tidak dikirim terpisah.
            tambah('rujukan', baris.rujukan || '');
        }
    });
}

/** Kosongkan pratinjau (dipakai mode "sektor baru", FR-008f). */
function kosongkanPenandaPratinjau(wadah) {
    if (wadah) {
        wadah.innerHTML = '<p class="text-muted text-center small mb-0">'
            + 'Belum ada isian. Pratinjau akan muncul setelah isian pertama ditambahkan.</p>';
    }
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

export function mountSchemaEditor() {
    const akar = document.querySelector('[data-status-editor]');
    const tbody = document.getElementById('isi-tabel-isian');

    if (!akar || !tbody) {
        return;
    }

    const wadahPratinjau = document.getElementById('wadah-pratinjau');
    const template = document.getElementById('template-baris-isian');
    // FR-008c/Q24 — `bolehUbah` mutable supaya tombol "Ubah" dapat membuka
    // kunci penyuntingan pada skema `terbit` tanpa memuat ulang halaman.
    let bolehUbah = tbody.dataset.bolehUbah === '1';
    const sektorKode = akar.dataset.sektor || '';
    const skemaAktif = akar.dataset.skema || '';

    let dirty = false;

    // Q25 — snapshot isian saat halaman siap, dipakai untuk "Batal" sehingga
    // perubahan belum tersimpan dibuang tanpa memuat ulang halaman (nol request).
    const snapshotAwal = bacaDariDom(tbody);

    const tombolUbah = document.getElementById('btn-ubah');
    const tombolBatalUbah = document.getElementById('btn-batal-ubah');
    const tombolSimpan = document.getElementById('btn-simpan-draf');
    const inputNamaSektor = document.getElementById('input-sektor-nama-editor');
    const wadahNamaSektor = document.getElementById('wadah-input-sektor-nama');
    let modeUbahAktif = false;
    let modeSektorBaru = false;

    /** Aktifkan/nonaktifkan seluruh kontrol isian pada tabel (FR-008c/FR-008e). */
    const kunciKontrolIsian = (aktif) => {
        tbody.querySelectorAll(
            '.input-label, .input-tipe, .input-wajib, .input-satuan, '
            + '.input-popup-peta, .input-filter, .input-indikator, '
            + '.input-tabel-sumber, .input-kolom-sumber, .btn-pilih-sumber-baris, .btn-hapus-isian',
        ).forEach((el) => {
            if (el.tagName === 'INPUT') {
                el.disabled = !aktif;
            }
        });

        // Tombol tambah/seret isian mengikuti kunci yang sama (FR-012).
        const tombolTambah = document.getElementById('btn-tambah-isian');

        if (tombolTambah) {
            tombolTambah.disabled = !aktif;
        }

        if (inputNamaSektor) {
            inputNamaSektor.disabled = !aktif;
        }

        tbody.dataset.bolehUbah = aktif ? '1' : '0';
        terapkanAturanTipe(tbody);

        // ⚠️ Baris kosong yang ditambahkan lewat template juga ikut terkunci
        // pada keadaan baca-saja (FR-008a). Tanpa ini, baris baru akan tetap
        // aktif walau skema sedang `terbit`.
        if (!aktif) {
            template?.content.querySelectorAll(
                '.input-label, .input-tipe, .input-wajib, .input-satuan, '
                + '.input-popup-peta, .input-filter, .input-indikator',
            ).forEach((el) => {
                if (el.tagName === 'INPUT') {
                    el.disabled = true;
                }
            });
        }
    };

    /** Pulihkan nilai kontrol isian dari snapshot (Q25). */
    const pulihkanDariSnapshot = () => {
        const baris = Array.from(tbody.querySelectorAll('tr[data-testid="baris-isian"], tr.baris-baru'));

        baris.forEach((tr, index) => {
            const sumber = snapshotAwal[index];

            if (!sumber) {
                return;
            }

            const label = tr.querySelector('.input-label');
            const tipe = tr.querySelector('.input-tipe');
            const wajib = tr.querySelector('.input-wajib');
            const satuan = tr.querySelector('.input-satuan');
            const sumberNilai = tr.querySelector('.sumber-nilai');

            if (label) {
                label.value = sumber.label ?? '';
            }

            if (tipe) {
                tipe.value = sumber.tipe ?? 'teks';
            }

            if (wajib) {
                wajib.checked = Boolean(sumber.wajib);
            }

            if (satuan) {
                satuan.value = sumber.satuan ?? '';
            }

            // ⚠️ Kolom Pilihan tidak dirender; opsi dipulihkan lewat `data-opsi`
            // yang dipakai `bacaOpsiBaris()` saat serialize.
            tr.dataset.opsi = JSON.stringify(sumber.opsi ?? []);

            if (sumberNilai) {
                sumberNilai.dataset.sumber = sumber.sumber ?? 'manual';
                sumberNilai.dataset.tabelSumber = sumber.tabelSumber ?? '';
                sumberNilai.dataset.kolomSumber = sumber.kolomSumber ?? '';
                sumberNilai.dataset.rujukan = sumber.rujukan || '';

                const tabel = tr.querySelector('.input-tabel-sumber');
                const kolom = tr.querySelector('.input-kolom-sumber');

                if (tabel) {
                    tabel.value = sumber.tabelSumber ?? '';
                }

                if (kolom) {
                    kolom.value = sumber.kolomSumber ?? '';
                }
            }
        });

        renderPratinjau(wadahPratinjau, bacaDariDom(tbody));
    };

    /**
     * FR-008c (Q24) — tombol "Ubah": aksi klien semata, TANPA permintaan ke
     * sumber data. Membuka kunci isian, mengaktifkan Simpan draf, menyembunyikan
     * tombol Ubah, dan menampilkan tombol Batal.
     */
    tombolUbah?.addEventListener('click', () => {
        modeUbahAktif = true;
        bolehUbah = true;
        kunciKontrolIsian(true);

        if (tombolSimpan) {
            tombolSimpan.disabled = false;
        }

        tombolUbah.classList.add('d-none');
        tombolBatalUbah?.classList.remove('d-none');
        perbaruiTombolTerbit();
    });

    /**
     * FR-008e (Q25) — tombol "Batal": mengembalikan editor ke baca-saja
     * `terbit` tanpa permintaan ke sumber data; perubahan belum tersimpan dibuang.
     */
    tombolBatalUbah?.addEventListener('click', () => {
        modeUbahAktif = false;

        // FR-008f — Batal pada mode "sektor baru" menutup mode itu tanpa
        // menyimpan apa pun dan tanpa permintaan ke sumber data.
        if (modeSektorBaru) {
            modeSektorBaru = false;
            kosongkanTabelIsian();
            kosongkanPenandaPratinjau(wadahPratinjau);

            if (inputNamaSektor) {
                inputNamaSektor.value = akar.dataset.sektorNama || '';
            }

            kunciKontrolIsian(false);

            // Bila memang tidak ada sektor yang dipilih, kembalikan blok nama
            // sektor ke kondisi tersembunyi seperti semula.
            if (sektorKode === '') {
                wadahNamaSektor?.classList.add('d-none');
            }

            document.getElementById('isian-belum-pilih-sektor')?.classList.remove('d-none');

            if (tombolSimpan) {
                tombolSimpan.disabled = true;
            }

            tombolBatalUbah.classList.add('d-none');
            tombolUbah?.classList.remove('d-none');
            perbaruiTombolTerbit();

            return;
        }

        pulihkanDariSnapshot();
        bolehUbah = false;
        kunciKontrolIsian(false);

        if (tombolSimpan) {
            tombolSimpan.disabled = true;
        }

        tombolBatalUbah.classList.add('d-none');
        tombolUbah?.classList.remove('d-none');
        dirty = false;
        perbaruiNomorUrutan(tbody);
        perbaruiTombolTerbit();
    });

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

        return swalKonfirmasi(pesan, 'Perubahan belum disimpan');
    };

    // -----------------------------------------------------------------------
    // Tambah isian (US1#13, US2)
    // -----------------------------------------------------------------------
    document.getElementById('btn-tambah-isian')?.addEventListener('click', async () => {
        if (!template) {
            // ⚠️ Guard: template WAJIB selalu ada di DOM (lihat
            // tabel-isian.blade.php). Bila hilang, beri umpan balik alih-alih
            // diam-diam tidak terjadi apa pun (FR-008g).
            await swalGalat('Fungsi tambah isian belum siap. Muat ulang halaman.');

            return;
        }

        if (!bolehUbah) {
            await swalPeringatan('Skema ini sedang dibaca. Tekan Ubah untuk menyuntingnya.');

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
    tbody.addEventListener('click', async (event) => {
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

        if (!(await swalKonfirmasi(pesan, 'Hapus isian'))) {
            return;
        }

        tr?.remove();
        perbaruiNomorUrutan(tbody);
        mutasi();
    });

    // -----------------------------------------------------------------------
    // Tambah sektor (US1#8)
    // -----------------------------------------------------------------------
    // -----------------------------------------------------------------------
    // FR-008f / Q26 — mode "sektor baru".
    //
    // ⚠️ Tidak ada lagi formulir terpisah: tombol ini membuka mode penyuntingan
    // yang SAMA dengan tombol "Ubah". Sektor + isian pertama tersimpan
    // bersamaan pada satu POST /dataspasial/skema, dan **tidak ada** permintaan
    // ke sumber data selama mode ini aktif (Q19/Q23/Q26).
    // -----------------------------------------------------------------------
    const kosongkanTabelIsian = () => {
        tbody.querySelectorAll('tr[data-testid="baris-isian"], tr.baris-baru').forEach((tr) => {
            if (!tr.classList.contains('baris-baru')) {
                tr.remove();
            }
        });
    };

    document.getElementById('btn-tambah-sektor')?.addEventListener('click', () => {
        modeSektorBaru = true;
        modeUbahAktif = false;
        dirty = false;

        // ⚠️ Mode "sektor baru" adalah keadaan MENYUNTING — `bolehUbah` harus
        // true supaya guard tombol Tambah Isian Baru tidak salah memberi tahu
        // "Skema ini sedang dibaca" (FR-008f).
        bolehUbah = true;

        if (inputNamaSektor) {
            inputNamaSektor.value = '';
        }

        kosongkanTabelIsian();
        kosongkanPenandaPratinjau(wadahPratinjau);
        kunciKontrolIsian(true);

        // ⚠️ Blok nama sektor disembunyikan Blade saat belum ada sektor terpilih;
        // pada mode "sektor baru" blok ini WAJIB dibuka supaya admin punya
        // kolom untuk diisi nama (FR-008f).
        wadahNamaSektor?.classList.remove('d-none');

        // Pesan "pilih salah satu sektor" tidak relevan selama mode ini aktif.
        document.getElementById('isian-belum-pilih-sektor')?.classList.add('d-none');

        if (tombolSimpan) {
            tombolSimpan.disabled = false;
        }

        if (tombolTerbit) {
            tombolTerbit.disabled = true;
            tombolTerbit.title = 'Terbitkan setelah draf tersimpan.';
        }

        tombolUbah?.classList.add('d-none');
        tombolBatalUbah?.classList.remove('d-none');
        inputNamaSektor?.focus();
    });

    // -----------------------------------------------------------------------
    // Simpan draf (US3) — pemicu konfirmasi yang sama (FR-007h)
    // -----------------------------------------------------------------------
    document.getElementById('btn-simpan-draf')?.addEventListener('click', async () => {
        if (!bolehUbah) {
            return;
        }

        // FR-008f — mode "sektor baru": nama sektor wajib diisi sebelum simpan.
        if (modeSektorBaru && ((inputNamaSektor?.value ?? '').trim() === '')) {
            await swalPeringatan('Nama sektor wajib diisi.');

            return;
        }

        // ⚠️ Q22 — editor hanya menampilkan SATU skema (versi terakhir), jadi
        // tidak ada lagi daftar versi yang perlu dicek untuk "draf sudah ada".
        const isian = bacaDariDom(tbody);

        if (isian.length === 0) {
            await swalPeringatan('Sektor harus memiliki minimal satu isian.');

            return;
        }

        // ⚠️ FR-018/Q14/Q17 — yang dibandingkan adalah `label`; kode turunan
        // otomatis unik kalau label-nya unik. Cakupannya satu versi ini saja.
        const labelBentrok = isian
            .map((b) => b.label)
            .filter((label, i, arr) => label !== '' && arr.indexOf(label) !== i);

        if (labelBentrok.length > 0) {
            await swalPeringatan(`Label "${labelBentrok[0]}" dipakai lebih dari satu isian pada versi ini.`);

            return;
        }

        const kodeKosong = isian.find((b) => b.kode === '');

        if (kodeKosong) {
            await swalPeringatan(`Label "${kodeKosong.label}" tidak menghasilkan kode yang sah. Perbaiki labelnya.`);

            return;
        }

        serialisasiKeForm(isian);
        dirty = false;
        perbaruiTombolTerbit();

        // US7/Q19 — nama sektor SELALU diambil dari input yang dapat disunting;
        // pada mode "sektor baru" (Q26) input itu sengaja dikosongkan oleh sistem.
        const inputNama = document.getElementById('input-sektor-nama-editor');
        const sektorNama = (inputNama?.value ?? '').trim()
            || akar.dataset.sektorNama
            || '';

        // ⚠️ FR-008f — pada mode "sektor baru" kode sektor BELUM ada; backend
        // membangkitkannya dari nama (Q5/FR-007d-1), jadi klien mengirim kosong.
        const sektorKodeKirim = modeSektorBaru ? '' : (sektorKode || '');
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
                body: JSON.stringify({
                    sektor: sektorKodeKirim,
                    sektor_nama: sektorNama,
                    keterangan: '',
                    isian,
                }),
            });

            if (respons.status === 422) {
                const data = await respons.json().catch(() => ({}));
                await swalPeringatan((data && data.message) || 'Validasi gagal. Periksa isian dan coba lagi.');
                dirty = true;
                perbaruiTombolTerbit();
                return;
            }

            if (!respons.ok) {
                await swalGalat('Data sementara tidak tersedia. Silakan coba lagi nanti.');
                dirty = true;
                perbaruiTombolTerbit();
                return;
            }

            await swalSukses(modeSektorBaru ? 'Sektor berhasil dibuat.' : 'Draf skema berhasil disimpan.');
        } catch (error) {
            await swalGalat('Data sementara tidak tersedia. Silakan coba lagi nanti.');
            dirty = true;
            perbaruiTombolTerbit();
        }
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

        // FR-008d/Q24 — pada mode "Ubah" (draf baru dari versi terbit), tombol
        // Terbitan tetap nonaktif sampai draf benar-benar tersimpan (FR-045).
        if (modeUbahAktif) {
            tombolTerbit.disabled = true;
            tombolTerbit.title = 'Simpan draf dulu — ada perubahan yang belum disimpan.';

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

    // KAB-03 — terbitan dipindah ke LAYAR TERBITAN (dialog) yang memuat
    // ringkasan perubahan, peringatan dampak/bentrok, riwayat versi, perbandingan,
    // dan tabel adopsi desa (clarify 2026-10-16 Q1 -> A, research.md R16).
    //
    // Berkas ini **tidak** lagi mengirim permintaan terbitan; seluruh interaksi
    // ditangani `public/js/dataspasial/terbit-dialog.js`.
    //
    // FR-045 (KAB-02) tetap berlaku: tombol Terbitan tidak dapat dipakai selama
    // ada perubahan yang belum disimpan.
    tombolTerbit?.addEventListener('click', async () => {
        if (!sektorKode) {
            return;
        }

        if (dirty) {
            await swalPeringatan('Simpan draf dulu. Ada perubahan yang belum disimpan.');

            return;
        }

        window.dispatchEvent(new CustomEvent('dataspasial:terbit-dibuka', {
            detail: { sektor: sektorKode },
        }));
    });

    // -----------------------------------------------------------------------
    // Modal pemilihan sumber nilai (US4, FR-022 … FR-025, Q20)
    //
    // ⚠️ Q20 — pemetaan memakai `{sumber, rujukan_sumber}` dan TIDAK memakai
    // katalog/daftar sektor. Pilihan yang disediakan dua mode:
    // **Manual** (isian diisi operator desa) atau **Otomatis** dari sumber data.
    // Modal **terisi dengan nilai saat ini** sehingga admin dapat melanjutkan
    // atau memperbaiki pemetaan yang sudah ada tanpa mengetik ulang.
    // -----------------------------------------------------------------------
    const SUMBER_ENUM = ['openSID', 'dataPresisi', 'impor'];

    /** Terapkan pilihan sumber nilai ke satu baris isian. */
    const tulisPemetaan = (tr, { sumber, tabel, kolom }) => {
        const sel = tr.querySelector('.sumber-nilai');

        if (!sel) {
            return;
        }

        sel.dataset.sumber = sumber || 'manual';
        sel.dataset.tabelSumber = tabel || '';
        sel.dataset.kolomSumber = kolom || '';
        // ⚠️ `rujukan` harus selalu konsisten dengan tabel.kolom karena inilah
        // nilai yang dikirim ke API sebagai `rujukan_sumber` (§6a.4).
        sel.dataset.rujukan = [tabel || '', kolom || ''].filter(Boolean).join('.');

        const tombol = sel.querySelector('.btn-pilih-sumber-baris');
        const badge = tombol?.querySelector('.badge') ?? sel.querySelector('.badge');

        if (!badge) {
            return;
        }

        if (sumber && sumber !== 'manual') {
            badge.className = 'badge badge-info';
            badge.innerHTML = '<i class="fas fa-lock mr-1"></i>' + sumber;
        } else {
            badge.className = 'badge badge-secondary';
            badge.textContent = 'Manual';
        }

        // Input tabel/kolom ikut diperbarui agar `bacaDariDom()` membaca
        // nilai yang sama dengan yang tampil.
        const inputTabel = sel.querySelector('.input-tabel-sumber');
        const inputKolom = sel.querySelector('.input-kolom-sumber');

        if (inputTabel) {
            inputTabel.value = tabel || '';
        }

        if (inputKolom) {
            inputKolom.value = kolom || '';
        }
    };

    /** Tampilkan modal pemilihan sumber nilai via SweetAlert2 (FR-008g). */
    const bukaModalSumberNilai = async (tr) => {
        if (!swalTersedia()) {
            await swalGalat('Pemilih sumber nilai belum siap. Muat ulang halaman.');

            return;
        }

        const label = tr.querySelector('.input-label')?.value || '(tanpa label)';

        // ⚠️ Modal TERISI dengan nilai saat ini agar admin dapat melanjutkan /
        // memperbaiki pemetaan yang sudah ada — bukan dimulai dari kosong.
        const sel = tr.querySelector('.sumber-nilai');
        const sumberSekarang = sel?.dataset?.sumber || 'manual';
        const otomatisSekarang = sumberSekarang !== '' && sumberSekarang !== 'manual';
        const tabelSekarang = sel?.dataset?.tabelSumber || '';
        const kolomSekarang = sel?.dataset?.kolomSumber || '';

        const hasil = await window.Swal.fire({
            title: 'Sumber nilai isian',
            html: `
                <p class="mb-3 text-left small text-muted">Isian: <strong>${label}</strong></p>
                <div class="form-check mb-2 text-left">
                    <input class="form-check-input" type="radio" name="mode-sumber" id="sumber-mode-manual" value="manual" ${otomatisSekarang ? '' : 'checked'}>
                    <label class="form-check-label" for="sumber-mode-manual">Manual — diisi operator desa</label>
                </div>
                <div class="form-check mb-3 text-left">
                    <input class="form-check-input" type="radio" name="mode-sumber" id="sumber-mode-otomatis" value="otomatis" ${otomatisSekarang ? 'checked' : ''}>
                    <label class="form-check-label" for="sumber-mode-otomatis">Otomatis — diambil dari sumber data</label>
                </div>
                <div id="bagian-otomatis" style="display:none">
                    <div class="form-group text-left mb-2">
                        <label for="sumber-pilihan">Sumber data</label>
                        <select id="sumber-pilihan" class="form-control form-control-sm">
                            ${SUMBER_ENUM.map((n) => `<option value="${n}" ${n === sumberSekarang ? 'selected' : ''}>${n}</option>`).join('')}
                        </select>
                    </div>
                    <div class="form-group text-left mb-2">
                        <label for="sumber-tabel">Tabel sumber</label>
                        <input id="sumber-tabel" class="form-control form-control-sm" maxlength="100" placeholder="mis. posyandu" value="${escapeHtml(tabelSekarang)}">
                    </div>
                    <div class="form-group text-left">
                        <label for="sumber-kolom">Kolom sumber</label>
                        <input id="sumber-kolom" class="form-control form-control-sm" maxlength="100" placeholder="mis. jenis" value="${escapeHtml(kolomSekarang)}">
                    </div>
                </div>`,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            focusConfirm: false,
            preConfirm: () => {
                const otomatis = document.getElementById('sumber-mode-otomatis')?.checked;

                if (!otomatis) {
                    return { sumber: 'manual', tabel: '', kolom: '' };
                }

                const sumber = document.getElementById('sumber-pilihan')?.value || '';
                const tabel = (document.getElementById('sumber-tabel')?.value || '').trim();
                const kolom = (document.getElementById('sumber-kolom')?.value || '').trim();

                // FR-022/Q20 — bila otomatis, `sumber`, `tabel_sumber`, dan
                // `kolom_sumber` seluruhnya wajib terisi.
                if (!sumber || tabel === '' || kolom === '') {
                    window.Swal.showValidationMessage('Sumber data, tabel, dan kolom wajib diisi.');

                    return false;
                }

                return { sumber, tabel, kolom };
            },
            didOpen: (popup) => {
                const manual = popup.querySelector('#sumber-mode-manual');
                const otomatis = popup.querySelector('#sumber-mode-otomatis');
                const bagian = popup.querySelector('#bagian-otomatis');

                const sinkron = () => {
                    bagian.style.display = otomatis.checked ? 'block' : 'none';
                };

                manual.addEventListener('change', sinkron);
                otomatis.addEventListener('change', sinkron);
                sinkron();
            },
            customClass: { popup: 'rounded-lg' },
        });

        if (!hasil.isConfirmed || !hasil.value) {
            return;
        }

        tulisPemetaan(tr, hasil.value);
        mutasi();
    };

    tbody.addEventListener('click', (event) => {
        const tombol = event.target.closest('.btn-pilih-sumber-baris');

        if (!tombol) {
            return;
        }

        const tr = tombol.closest('tr');

        if (!tr) {
            return;
        }

        if (!bolehUbah) {
            swalPeringatan('Skema ini sedang dibaca. Tekan Ubah untuk menyuntingnya.');

            return;
        }

        bukaModalSumberNilai(tr);
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


// KAB-03 — setelah penerbitan berhasil, `terbit-dialog.js` menyegarkan isi
// halaman lewat pertukaran DOM (bukan muat ulang penuh, FR-022/SC-013). Editor
// perlu dipasang ulang karena elemennya baru.
window.addEventListener('dataspasial:refresh-editor', () => {
    mountSchemaEditor();
});

// Dimuat sebagai `<script type="module" src="...">` (bukan bundler entry),
// jadi harus dipanggil sendiri di root modul.
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => mountSchemaEditor());
    } else {
        mountSchemaEditor();
    }
}
