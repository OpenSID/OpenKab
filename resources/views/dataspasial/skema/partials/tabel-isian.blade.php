{{--
    Panel tengah: tabel isian (FR-013).

    ⚠️ Kolom **Kode tidak lagi ditampilkan** (FR-017/Q14). `kode` adalah nilai
    turunan dari `label` yang dibuat otomatis oleh aplikasi, sehingga tidak ada
    kolom yang perlu diisi admin.

    Kolom memuat: label, tipe, wajib, satuan, pilihan, popup peta, filter,
    indikator, dan sumber nilai (manual atau pemetaan).

    Kolom popup/filter/indikator & sumber nilai diisi penuh pada US4/US5;
    kolomnya sudah disiapkan sejak US1 agar tata letak tidak berubah
    saat story berikutnya tiba (FR-008).
--}}
<div class="card card-outline card-success">
    <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
        <h3 class="card-title text-sm mb-0">
            <i class="fas fa-table mr-1"></i>Isian
            @if ($sektorTerpilih)
                <span class="text-muted font-weight-normal">· {{ $sektorTerpilih['nama'] }}</span>
            @endif
        </h3>

        <div>
            {{-- Simpan draf: aktif hanya bila skema berstatus draf (FR-008b). --}}
            <button type="button" class="btn btn-success btn-sm mr-1"
                    id="btn-simpan-draf" data-testid="btn-simpan-draf"
                    {{ $bolehSimpan ? '' : 'disabled' }}>
                <i class="fas fa-save mr-1"></i>Simpan draf
            </button>

{{--
                Terbitkan (US8, FR-041): aktif bila skema berstatus `draf`.
                Frontend MENONAKAN tombol saat ada perubahan yang belum disimpan
                (FR-045) — penerbitan menghasilkan versi immutable.
            --}}
            <button type="button" class="btn btn-primary btn-sm"
                    id="btn-terbitan" data-testid="btn-terbitan"
                    {{ $bolehTerbitkan ? '' : 'disabled' }}>
                <i class="fas fa-paper-plane mr-1"></i>Terbitkan
            </button>
        </div>
    </div>

    @if ($skemaTerpilih !== null && $skemaTerpilih['status'] === 'terbit')
        {{-- Skema terbit hanya baca-saja (FR-008a). --}}
        <div class="alert alert-info border-0 rounded-0 mb-0 py-2 px-3" role="alert">
            <i class="fas fa-lock mr-1"></i>
            Skema versi terbit hanya dapat dibaca. Buat draf baru untuk mengubah isiannya.
        </div>
    @endif

    <div class="card-body p-0">
        @if ($sektorTerpilih === null)
            <p class="text-muted text-center my-4 px-2 mb-0" data-testid="isian-belum-pilih-sektor">
                Pilih salah satu sektor untuk menyusun isiannya.
            </p>
        @elseif (count($isian) === 0)
            <p class="text-muted text-center my-4 px-2 mb-0" data-testid="isian-kosong">
                Belum ada isian pada skema ini. Tekan <strong>Tambah Isian Baru</strong> untuk
                menambahkan kolom formulir pertama.
            </p>
        @endif

        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0" id="tabel-isian" data-testid="tabel-isian">
                <thead class="thead-light">
                    <tr>
                        <th style="width:2.5rem" title="Seret untuk mengurutkan">&nbsp;</th>
                        <th style="width:2.5rem">#</th>
                        <th>Label</th>
                        <th style="width:8rem">Tipe</th>
                        <th style="width:4rem" class="text-center">Wajib</th>
                        <th style="width:6rem">Satuan</th>
                        <th style="width:11rem">Pilihan</th>
                        <th style="width:11rem">Sumber nilai</th>
                        <th style="width:4rem" class="text-center" title="Tampil di popup peta">Peta</th>
                        <th style="width:4rem" class="text-center" title="Tersedia sebagai filter">Filter</th>
                        <th style="width:4rem" class="text-center" title="Dipakai sebagai indikator dasbor">Indikator</th>
                        <th style="width:3rem">&nbsp;</th>
                    </tr>
                </thead>
                <tbody id="isi-tabel-isian" data-testid="isi-tabel-isian"
                       data-boleh-ubah="{{ $bolehUbah ? '1' : '0' }}">
                    @foreach ($isian as $index => $baris)
                        @include('dataspasial.skema.partials.form-isian', [
                            'index' => $index,
                            'baris' => $baris,
                            'bolehUbah' => $bolehUbah,
                            'daftarSektorSumber' => $daftarSektorSumber ?? [],
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{--
        Template baris kosong untuk "Tambah Isian Baru" (US1#13).
        ⚠️ HANYA dirender saat skema dapat disunting. Pada skema `terbit` template
        ini tidak boleh ada karena baris kosong-nya memuat tombol interaktif
        (saklar & pemilih sumber) yang tidak boleh aktif (FR-008a).
    --}}
    @if ($bolehUbah)
    <template id="template-baris-isian">
        <tr class="baris-baru" draggable="false">
            <td class="text-center handle-seret align-middle" title="Seret untuk mengurutkan">
                <i class="fas fa-grip-vertical text-muted"></i>
            </td>
            <td class="text-center align-middle urutan text-muted"></td>
            {{-- ⚠️ Tidak ada kolom kode: dibangkitkan otomatis dari label (Q14). --}}
            <td><input type="text" class="form-control form-control-sm input-label" maxlength="255" placeholder="Jumlah Siswa"></td>
            <td>
                <select class="form-control form-control-sm input-tipe">
                    @foreach (\App\Enums\TipeIsian::getValues() as $tipe)
                        <option value="{{ $tipe }}">{{ $tipe }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center"><input type="checkbox" class="input-wajib"></td>
            <td><input type="text" class="form-control form-control-sm input-satuan" maxlength="50" placeholder="orang"></td>
            <td>
                <select class="form-control form-control-sm input-opsi" multiple size="2" disabled>
                    <option disabled>—</option>
                </select>
            </td>
            <td class="sumber-nilai">
                <button type="button" class="btn btn-outline-secondary btn-sm btn-pilih-sumber-baris"
                        title="Pilih sumber nilai">
                    <span class="badge badge-secondary">Manual</span>
                </button>
            </td>
            <td class="text-center"><input type="checkbox" class="input-popup-peta"></td>
            <td class="text-center"><input type="checkbox" class="input-filter"></td>
            <td class="text-center"><input type="checkbox" class="input-indikator"></td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-isian" title="Hapus isian">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>
    </template>
    @endif

    {{-- Pemilih sumber nilai (FR-022): sumber = daftar sektor DataSpasial. --}}
    @if ($bolehUbah)
        @include('dataspasial.skema.partials.pilih-sumber')
    @endif

    {{-- Tombol tambah isian: selalu terlihat (FR-007c). --}}
    <div class="card-footer p-2">
        <button type="button" class="btn btn-outline-success btn-block btn-sm"
                id="btn-tambah-isian" data-testid="btn-tambah-isian"
                {{ $bolehUbah ? '' : 'disabled' }}>
            <i class="fas fa-plus mr-1"></i>Tambah Isian Baru
        </button>
    </div>
</div>

{{-- Form submit draf. Isian diserialisasi oleh JS dari state yang sama. --}}
<form id="form-draf" method="POST" action="{{ route('dataspasial.skema.draf.store') }}"
      class="d-none" data-testid="form-draf">
    @csrf
    <input type="hidden" name="sektor" value="{{ $sektorTerpilih['kode'] ?? '' }}">
    <input type="hidden" name="nomor_versi" value="{{ $skemaTerpilih['nomorVersi'] ?? '' }}">
    <input type="hidden" name="catatan_perubahan" value="">
    <div id="wadah-input-isian"></div>
</form>

{{--
    Form submit terbitan (US8, FR-041).

    ⚠️ `sektor` yang dikirim adalah **KODE** sektor karena `{sektor}` pada
    path `POST /dataspasial/skema/{sektor}/terbit` adalah kode sektor.
--}}
<form id="form-terbit" method="POST" action="{{ route('dataspasial.skema.terbit') }}"
      class="d-none" data-testid="form-terbit">
    @csrf
    <input type="hidden" name="sektor" value="{{ $sektorTerpilih['kode'] ?? '' }}">
    <input type="hidden" name="skema" value="{{ $skemaTerpilih['id'] ?? '' }}">
    <input type="hidden" name="catatan_perubahan" value="">
</form>