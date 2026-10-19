/**
 * Layar Terbitan Skema - KAB-03 (OpenSID/OpenKab#1298)
 *
 * Dialog di atas halaman `dataspasial/skema` yang dipicu tombol `#btn-terbitan`
 * milik KAB-02 (event `dataspasial:terbit-dibuka`). Lima bagian dalam satu layar:
 *
 *   1. Ringkasan perubahan  - isian baru / diubah / diarsipkan (FR-010..FR-016)
 *   2. Peringatan dampak pengarsipan + bentrok lokal desa       (FR-030..FR-044)
 *   3. Riwayat versi + perbandingan dua versi                   (FR-050..FR-058)
 *   4. Tabel status adopsi desa (sektor terpilih) + ekspor     (FR-047..FR-071)
 *   5. Catatan perubahan + pengakuan bentrok + tombol Terbitkan(FR-017..FR-023)
 *
 * ATURAN:
 * - Membuka/menutup dialog TIDAK PERNAH mengirim permintaan terbitan (FR-046).
 * - Ekspor tabel adopsi tidak boleh mengirim permintaan terbitan (FR-047).
 * - Tidak ada `window.alert` / `window.confirm` / `window.prompt` (FR-008g KAB-02).
 * - Semua pesan memakai `window.Swal` bila tersedia, atau blok pesan pada dialog.
 * - Halaman tidak pernah dimuat ulang penuh saat publish; state disegarkan lewat
 *   event `dataspasial:terbit-berhasil` (FR-022/SC-013).
 */

const TERBIT = {
    urlPreview: '/dataspasial/skema/terbit/preview',
    urlTerbit: '/dataspasial/skema/terbit',
    urlRiwayat: '/dataspasial/skema/riwayat',
    urlBandingkan: '/dataspasial/skema/perbandingan',
    urlAdopsi: '/dataspasial/skema/adopsi',
    urlEkspor: '/dataspasial/skema/adopsi/export',
};

const state = {
    sektor: null,
    preview: null,
    adopsi: { status: '', q: '', halaman: 1, totalHalaman: 1 },
    versiDipilih: [],
    wymagLoad: false,
};

const el = (selektor, akar = document) => akar.querySelector(selektor);
const semua = (selektor, akar = document) => Array.from(akar.querySelectorAll(selektor));

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const swalAda = () => typeof window.Swal !== 'undefined';

/** Pesan error tanpa membuka dialog lain (FR-005). */
function pesanDi(target, teks) {
    const wadah = el(target);

    if (!wadah) {
        return;
    }

    wadah.textContent = teks;
    wadah.classList.remove('d-none');
}

/** Bersihkan blok pesan pada target. */
function bersihkanPesan(target) {
    const wadah = el(target);

    if (wadah) {
        wadah.textContent = '';
        wadah.classList.add('d-none');
    }
}

/** Tampilkan dialog SweetAlert2 di luar blok dialog (mis. penerbitan berhasil). */
function swalInfo(judul, ikon = 'info') {
    if (!swalAda()) {
        return Promise.resolve();
    }

    return window.Swal.fire({ title: judul, icon: ikon, confirmButtonText: 'Mengerti' });
}

/**
 * Panggilan JSON serbaguna.
 *
 * @param {string} url
 * @param {object} opsi
 * @returns {Promise<{ok: boolean, status: number, data: object}>}
 */
async function panggil(url, opsi = {}) {
    try {
        const respons = await fetch(url, {
            method: opsi.method ?? 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
                ...(opsi.body ? { 'Content-Type': 'application/json' } : {}),
            },
            credentials: 'same-origin',
            ...(opsi.body ? { body: JSON.stringify(opsi.body) } : {}),
        });

        let data = {};

        try {
            data = await respons.json();
        } catch (e) {
            data = {};
        }

        return { ok: respons.ok, status: respons.status, data: data || {} };
    } catch (e) {
        return { ok: false, status: 0, data: {} };
    }
}

// ---------------------------------------------------------------------------
// 1. Ringkasan perubahan
// ---------------------------------------------------------------------------

const LABEL_KELOMPOK = {
    'isian_baru': { judul: 'Isian baru', kelas: 'badge-primary' },
    'isian_diubah': { judul: 'Isian diubah', kelas: 'badge-info' },
    'isian_diarsipkan': { judul: 'Isian diarsipkan', kelas: 'badge-warning' },
};

/**
 * Render daftar kode/label; entri tanpa `kode` adalah baris
 * "… dan N isian lainnya" hasil pemotongan FR-011.
 */
function barisIsian(item) {
    if (!item.kode) {
        return `<tr class="text-muted"><td colspan="2">${escapeHtml(item.label ?? '')}</td></tr>`;
    }

    return `<tr><td><code>${escapeHtml(item.kode)}</code></td><td>${escapeHtml(item.label ?? '')}</td></tr>`;
}

function escapeHtml(nilai) {
    return String(nilai ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    })[c]);
}

function renderRingkasan(ringkasan) {
    const wadah = el('[data-testid="ringkasan-perubahan"]');

    if (!wadah || !ringkasan) {
        return;
    }

    const versi = el('[data-testid="ringkasan-versi"]');

    if (versi) {
        versi.textContent = ringkasan.terbitan_pertama
            ? 'Terbitan pertama'
            : `v${ringkasan.versi_terbit_sebelumnya ?? '-'} → v${ringkasan.versi_akan_terbit ?? '-'}`;
    }

    const potongan = [];

    Object.keys(LABEL_KELOMPOK).forEach((kunci) => {
        const meta = LABEL_KELOMPOK[kunci];
        const daftar = ringkasan[kunci] ?? [];

        if (!daftar.length) {
            return;
        }

        const isi = kunci === 'isian_diubah'
            ? daftar.map((item) => `
                <tr>
                    <td><code>${escapeHtml(item.kode)}</code></td>
                    <td>
                        ${escapeHtml(item.label ?? '')}
                        <ul class="mb-0 pl-3 small text-muted">
                            ${(item.perubahan ?? []).map((p) => `<li>${escapeHtml(p.atribut)}: ${escapeHtml(p.dari)} → ${escapeHtml(p.ke)}</li>`).join('')}
                        </ul>
                    </td>
                </tr>`).join('')
            : daftar.map(barisIsian).join('');

        potongan.push(`
            <div class="border-bottom px-3 py-2">
                <div class="mb-1">
                    <span class="badge ${meta.kelas}">${meta.judul}</span>
                    <span class="ml-1 small text-muted">${daftar.length} isian</span>
                </div>
                <table class="table table-sm mb-0">${isi}</table>
            </div>`);
    });

    wadah.innerHTML = potongan.join('')
        || '<div class="p-3 text-muted mb-0">Tidak ada isian yang berubah pada draf ini.</div>';
}

// ---------------------------------------------------------------------------
// 2. Peringatan dampak & bentrok
// ---------------------------------------------------------------------------

function renderPeringatan(peringatan) {
    const kartu = el('[data-testid="peringatan-dampak"]');

    if (!kartu || !peringatan) {
        return;
    }

    const isi = el('[data-testid="peringatan-dampak-isi"]');
    const potongan = [];

    if (peringatan.jumlah_isian_diarsipkan > 0) {
        const daftar = (peringatan.isian_diarsipkan ?? [])
            .map((item) => `<li><code>${escapeHtml(item.kode)}</code> — ${escapeHtml(item.label ?? '')}</li>`)
            .join('');

        const jumlah = peringatan.dampak_tersedia
            ? `Jumlah data terdampak: nilai ${formatAngka(peringatan.jumlah_nilai_terdampak)}, `
                + `desa ${formatAngka(peringatan.jumlah_desa_terdampak)}, `
                + `fasilitas ${formatAngka(peringatan.jumlah_fasilitas_terdampak)}.`
            : (peringatan.pesan_dampak ?? 'Jumlah data terdampak belum dapat dihitung.');

        potongan.push(`<p class="mb-1"><strong>${escapeHtml(jumlah)}</strong></p>`);
        potongan.push(`<ul class="mb-2">${daftar}</ul>`);
    }

    if (peringatan.bentrok_tersedia) {
        const daftar = (peringatan.desa_bentrok ?? [])
            .map((d) => `<li>${escapeHtml(d.nama_desa)} <span class="text-muted">(${escapeHtml(d.kode_desa)})</span></li>`)
            .join('');

        potongan.push(`<p class="mb-1">Isian lokal desa yang bentrok: <strong>${peringatan.jumlah_desa_bentrok ?? 0}</strong> desa.</p>`);
        potongan.push(`<ul class="mb-2">${daftar || '<li class="text-muted">Tidak ada desa yang bentrok.</li>'}</ul>`);
    } else if (peringatan.pesan_bentrok) {
        potongan.push(`<p class="mb-1 text-muted">${escapeHtml(peringatan.pesan_bentrok)}</p>`);
    }

    if (peringatan.penjelasan_arsip) {
        potongan.push(`<p class="mb-0 small text-muted">${escapeHtml(peringatan.penjelasan_arsip)}</p>`);
    }

    if (!potongan.length) {
        kartu.classList.add('d-none');
        isi.innerHTML = '';

        return;
    }

    isi.innerHTML = potongan.join('');
    kartu.classList.remove('d-none');

    // FR-031/FR-032 — pengakuan eksplisit, penerbitan tidak diblokir tetapi
    // tombol tetap nonaktif sampai admin centang.
    const bungkus = el('[data-testid="pengakuan-bentrok-wrap"]');

    if (peringatan.butuh_pengakuan && bungkus) {
        bungkus.classList.remove('d-none');
    }
}

function formatAngka(nilai) {
    if (nilai === null || nilai === undefined) {
        return '—';
    }

    return new Intl.NumberFormat('id-ID').format(nilai);
}

// ---------------------------------------------------------------------------
// 3. Riwayat versi & perbandingan
// ---------------------------------------------------------------------------

function renderRiwayat(versi, bisaDibandingkan) {
    const wadah = el('[data-testid="riwayat-versi"]');

    if (!wadah || !Array.isArray(versi)) {
        return;
    }

    if (!versi.length) {
        wadah.innerHTML = '<div class="p-3 text-muted mb-0">Belum ada versi pada sektor ini.</div>';

        return;
    }

    const baris = versi.map((v) => `
        <tr>
            <td>
                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input" type="checkbox" value="${v.nomor_versi}" data-testid="pilih-versi-${v.nomor_versi}">
                    <label class="custom-control-label" for="pilih-versi-${v.nomor_versi}"></label>
                </div>
            </td>
            <td>v${v.nomor_versi}</td>
            <td><span class="badge badge-secondary">${escapeHtml(v.status_label)}</span></td>
            <td>${escapeHtml(v.diterbitkan_pada ?? '—')}</td>
            <td>${escapeHtml(v.diterbitkan_oleh ?? '—')}</td>
            <td class="small">${escapeHtml(v.catatan_label ?? '')}</td>
        </tr>`).join('');

    wadah.innerHTML = `
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th></th><th>Versi</th><th>Status</th><th>Diterbitkan</th><th>Oleh</th><th>Catatan perubahan</th>
                    </tr>
                </thead>
                <tbody>${baris}</tbody>
            </table>
        </div>`;

    const tombol = el('[data-testid="btn-bandingkan-versi"]');

    if (tombol) {
        if (!bisaDibandingkan) {
            tombol.classList.add('d-none');
            tombol.title = 'Belum ada dua versi untuk dibandingkan.';
        } else {
            tombol.classList.remove('d-none');
            tombol.disabled = true;
        }
    }

    semua('[data-testid^="pilih-versi-"]', wadah).forEach((kotak) => {
        kotak.addEventListener('change', () => {
            state.versiDipilih = semua('[data-testid^="pilih-versi-"]:checked', wadah).map((c) => Number(c.value));

            if (tombol) {
                tombol.disabled = state.versiDipilih.length !== 2;
            }
        });
    });
}

async function muatPerbandingan() {
    if (state.versiDipilih.length !== 2) {
        return;
    }

    const [a, b] = state.versiDipilih;
    const [dari, ke] = a < b ? [a, b] : [b, a];
    const wadah = el('[data-testid="bandingkan-versi"]');

    const hasil = await panggil(`${TERBIT.urlBandingkan}?sektor=${encodeURIComponent(state.sektor)}&dari=${dari}&ke=${ke}`);

    if (!wadah) {
        return;
    }

    if (!hasil.ok) {
        // FR-057 — sebut versi yang gagal, jangan tampilkan diff menyesatkan.
        wadah.innerHTML = `<div class="p-3 text-danger mb-0">${escapeHtml(hasil.data.message ?? 'Perbandingan gagal dimuat.')}</div>`;
        wadah.classList.remove('d-none');

        return;
    }

    const baris = (hasil.data.baris ?? []).map((item) => `
        <tr>
            <td><code>${escapeHtml(item.kode)}</code></td>
            <td>${escapeHtml(item.label ?? '')}</td>
            <td><span class="badge badge-light">${escapeHtml(item.status)}</span></td>
            <td class="small">
                ${(item.perubahan ?? []).map((p) => `${escapeHtml(p.atribut)}: ${escapeHtml(p.dari)} → ${escapeHtml(p.ke)}`).join('<br>') || '—'}
            </td>
        </tr>`).join('');

    wadah.innerHTML = `
        <div class="px-3 pt-2 small text-muted">Perbandingan v${dari} → v${ke}</div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead>
                    <tr><th>Kode</th><th>Label</th><th>Status</th><th>Perubahan</th></tr>
                </thead>
                <tbody>${baris}</tbody>
            </table>
        </div>`;
    wadah.classList.remove('d-none');
}

// ---------------------------------------------------------------------------
// 4. Tabel adopsi desa
// ---------------------------------------------------------------------------

async function muatAdopsi(halaman = state.adopsi.halaman) {
    if (!state.sektor) {
        return;
    }

    const params = new URLSearchParams({ sektor: state.sektor, page: String(halaman) });

    if (state.adopsi.status) {
        params.set('status', state.adopsi.status);
    }

    if (state.adopsi.q) {
        params.set('q', state.adopsi.q);
    }

    const hasil = await panggil(`${TERBIT.urlAdopsi}?${params.toString()}`);
    const wadah = el('[data-testid="tabel-adopsi"]');
    const ringkasanWadah = el('[data-testid="adopsi-ringkasan"]');

    if (!wadah) {
        return;
    }

    if (!hasil.ok) {
        // FR-047 — hanya bagian tabel yang gagal; bagian lain tetap dipakai.
        wadah.innerHTML = `<div class="p-3 text-warning mb-0">${escapeHtml(hasil.data.message ?? 'Data sementara tidak tersedia.')}</div>`;

        if (ringkasanWadah) {
            ringkasanWadah.textContent = '';
        }

        return;
    }

    const data = hasil.data;
    state.adopsi.totalHalaman = data.total_halaman ?? 1;

    if (ringkasanWadah) {
        const r = data.ringkasan ?? {};
        ringkasanWadah.textContent = `Total ${formatAngka(r.total_desa)} desa · `
            + `tertinggal ${formatAngka(r.tertinggal)} · `
            + `menunggu adopsi ${formatAngka(r.menunggu_adopsi)} · `
            + 'berkonflik —';
    }

    const baris = (data.baris ?? []).map((b) => `
        <tr class="${b.tandai_tertinggal ? 'table-warning' : ''}">
            <td>${escapeHtml(b.nama_desa)}</td>
            <td class="small">${escapeHtml(b.kode_desa)}</td>
            <td>${escapeHtml(b.nomor_versi_label)}</td>
            <td><span class="badge ${escapeHtml(b.warna_lencana)}">${escapeHtml(b.status_label)}</span></td>
            <td class="small">${escapeHtml(b.terakhir_ada_data_label)}</td>
        </tr>`).join('');

    wadah.innerHTML = `
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr><th>Desa</th><th>Kode</th><th>Versi Skema</th><th>Status Adopsi</th><th>Terakhir Ada Data</th></tr>
                </thead>
                <tbody>${baris || '<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada desa yang cocok dengan saringan.</td></tr>'}</tbody>
            </table>
        </div>`;

    renderNavigasiAdopsi(data);
    isiPilihanStatus(data.pilihan_status ?? []);
}

function renderNavigasiAdopsi(data) {
    const wadah = el('[data-testid="adopsi-navigasi"]');

    if (!wadah) {
        return;
    }

    const totalHalaman = data.total_halaman ?? 1;

    if (totalHalaman <= 1) {
        wadah.classList.add('d-none');
        wadah.innerHTML = '';

        return;
    }

    wadah.classList.remove('d-none');
    wadah.innerHTML = `
        <div class="d-flex justify-content-between align-items-center small">
            <span>Halaman ${data.halaman ?? 1} dari ${totalHalaman} · ${formatAngka(data.total ?? 0)} desa</span>
            <span>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-testid="adopsi-sebelumnya" ${(data.halaman ?? 1) <= 1 ? 'disabled' : ''}>Sebelumnya</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-testid="adopsi-berikutnya" ${(data.halaman ?? 1) >= totalHalaman ? 'disabled' : ''}>Berikutnya</button>
            </span>
        </div>`;

    el('[data-testid="adopsi-sebelumnya"]', wadah)?.addEventListener('click', () => {
        muatAdopsi((data.halaman ?? 1) - 1);
    });

    el('[data-testid="adopsi-berikutnya"]', wadah)?.addEventListener('click', () => {
        muatAdopsi((data.halaman ?? 1) + 1);
    });
}

function isiPilihanStatus(pilihan) {
    const select = el('[data-testid="adopsi-status"]');

    if (!select || select.dataset.terisi === '1') {
        return;
    }

    const sekarang = select.value;
    select.innerHTML = `<option value="">Semua status</option>`
        + pilihan.map((p) => `<option value="${escapeHtml(p.value)}">${escapeHtml(p.label)}</option>`).join('');
    select.value = sekarang;
    select.dataset.terisi = '1';
}

// ---------------------------------------------------------------------------
// 5. Catatan perubahan & penerbitan
// ---------------------------------------------------------------------------

function perbaruiTombolTerbit() {
    const tombol = el('[data-testid="btn-kirim-terbit"]');

    if (!tombol) {
        return;
    }

    const catatan = el('[data-testid="catatan-perubahan"]')?.value?.trim() ?? '';
    const pengakuan = el('[data-testid="pengakuan-bentrok"]');
    const perluPengakuan = pengakuan ? !el('[data-testid="pengakuan-bentrok-wrap"]')?.classList.contains('d-none') : false;
    const sudahAck = pengakuan ? pengakuan.checked : true;

    const cukup = catatan.length >= 5 && (!perluPengakuan || sudahAck);

    tombol.disabled = !cukup;

    const galat = el('[data-testid="catatan-perubahan-error"]');

    if (galat) {
        galat.textContent = catatan.length > 0 && catatan.length < 5
            ? 'Catatan perubahan minimal 5 karakter.'
            : '';
    }
}

async function kirimTerbitan() {
    if (!state.sektor || !state.preview) {
        return;
    }

    const tombol = el('[data-testid="btn-kirim-terbit"]');

    if (tombol) {
        tombol.disabled = true;
    }

    const hasil = await panggil(TERBIT.urlTerbit, {
        method: 'POST',
        body: {
            sektor: state.sektor,
            catatan_perubahan: el('[data-testid="catatan-perubahan"]')?.value ?? '',
            pengakuan_bentrok: el('[data-testid="pengakuan-bentrok"]')?.checked ?? false,
        },
    });

    if (!hasil.ok) {
        perbaruiTombolTerbit();
        await swalInfo(hasil.data.message ?? 'Data sementara tidak tersedia. Silakan coba lagi nanti.', 'error');

        return;
    }

    // FR-022/SC-013 — tanpa muat ulang halaman penuh: dialog ditutup, isi
    // halaman disegarkan lewat pertukaran DOM, lalu kedua modul dipasang ulang.
    const modal = el('#layar-terbitan');

    if (modal && window.jQuery) {
        window.jQuery(modal).modal('hide');
    }

    await swalInfo(hasil.data.message ?? 'Skema berhasil diterbitkan.', 'success');
    window.dispatchEvent(new CustomEvent('dataspasial:terbit-berhasil', { detail: { sektor: state.sektor } }));

    await segarkanHalaman();
}

/**
 * Segarkan isi halaman tanpa `location.reload()` (FR-022).
 *
 * Mengambil HTML halaman saat ini, menukar isi wrapper konten, lalu memberi
 * tahu kedua modul untuk memasang ulang listener-nya.
 */
async function segarkanHalaman() {
    try {
        const respons = await fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
            credentials: 'same-origin',
        });

        if (!respons.ok) {
            return;
        }

        const html = await respons.text();
        const dokumenBaru = new DOMParser().parseFromString(html, 'text/html');
        const baru = dokumenBaru.querySelector('.content-wrapper');
        const lama = document.querySelector('.content-wrapper');

        if (!baru || !lama) {
            return;
        }

        lama.innerHTML = baru.innerHTML;
        window.dispatchEvent(new CustomEvent('dataspasial:refresh-editor'));
    } catch (e) {
        // Gagal menyegarkan tidak boleh menutupi keberhasilan penerbitan.
    }
}

// ---------------------------------------------------------------------------
// Pembukaan & pemasangan dialog
// ---------------------------------------------------------------------------

async function bukaDialog(sektor) {
    const modal = el('#layar-terbitan');

    if (!modal || !window.jQuery) {
        return;
    }

    state.sektor = sektor;
    state.versiDipilih = [];
    state.preview = null;

    el('[data-testid="terbitan-sektor"]').textContent = sektor;
    el('[data-testid="terbitan-memuat"]').classList.remove('d-none');
    el('[data-testid="terbitan-isi"]').classList.add('d-none');
    el('[data-testid="terbitan-footer"]').classList.add('d-none');
    el('[data-testid="peringatan-dampak"]').classList.add('d-none');
    el('[data-testid="pengakuan-bentrok-wrap"]').classList.add('d-none');
    bersihkanPesan('[data-testid="terbitan-gagal"]');

    const pengakuan = el('[data-testid="pengakuan-bentrok"]');

    if (pengakuan) {
        pengakuan.checked = false;
    }

    const catatan = el('[data-testid="catatan-perubahan"]');

    if (catatan) {
        catatan.value = '';
    }

    window.jQuery(modal).modal('show');

    const hasil = await panggil(TERBIT.urlPreview, { method: 'POST', body: { sektor: sektor } });

    el('[data-testid="terbitan-memuat"]').classList.add('d-none');

    if (!hasil.ok) {
        pesanDi('[data-testid="terbitan-gagal"]', hasil.data.message ?? 'Data sementara tidak tersedia.');
        el('[data-testid="terbitan-isi"]').classList.remove('d-none');

        return;
    }

    state.preview = hasil.data;
    renderRingkasan(hasil.data.ringkasan);
    renderPeringatan(hasil.data.peringatan);
    renderRiwayat(hasil.data.riwayat, hasil.data.bisa_dibandingkan);
    muatAdopsi(1);

    el('[data-testid="terbitan-isi"]').classList.remove('d-none');
    el('[data-testid="terbitan-footer"]').classList.remove('d-none');
    perbaruiTombolTerbit();
}

function pasang() {
    const modal = el('#layar-terbitan');

    // Idempoten: `pasang()` dipanggil lagi setelah pertukaran DOM (elemen baru).
    if (modal === null || modal.dataset.terpasang === '1') {
        return;
    }

    modal.dataset.terpasang = '1';

    window.addEventListener('dataspasial:terbit-dibuka', (acara) => {
        bukaDialog(acara?.detail?.sektor ?? '');
    });

    el('[data-testid="catatan-perubahan"]')?.addEventListener('input', perbaruiTombolTerbit);
    el('[data-testid="pengakuan-bentrok"]')?.addEventListener('change', perbaruiTombolTerbit);
    el('[data-testid="btn-kirim-terbit"]')?.addEventListener('click', kirimTerbitan);

    el('[data-testid="btn-bandingkan-versi"]')?.addEventListener('click', muatPerbandingan);

    el('[data-testid="adopsi-status"]')?.addEventListener('change', (acara) => {
        state.adopsi.status = acara.target.value;
        muatAdopsi(1);
    });

    let cariTunda = null;
    el('[data-testid="adopsi-cari"]')?.addEventListener('input', (acara) => {
        window.clearTimeout(cariTunda);
        cariTunda = window.setTimeout(() => {
            state.adopsi.q = acara.target.value.trim();
            muatAdopsi(1);
        }, 350);
    });

    // FR-067 — ekspor daftar tertinggal; TIDAK mengirim permintaan terbitan.
    el('[data-testid="btn-ekspor-tertinggal"]')?.addEventListener('click', () => {
        if (!state.sektor) {
            return;
        }

        const params = new URLSearchParams({ sektor: state.sektor, status: 'tertinggal' });

        if (state.adopsi.q) {
            params.set('q', state.adopsi.q);
        }

        window.location.assign(`${TERBIT.urlEkspor}?${params.toString()}`);
    });
}

// Dipasang ulang setelah penyegaran DOM pasca-terbitan (lihat `segarkanHalaman`).
window.addEventListener('dataspasial:refresh-editor', pasang);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', pasang);
} else {
    pasang();
}
