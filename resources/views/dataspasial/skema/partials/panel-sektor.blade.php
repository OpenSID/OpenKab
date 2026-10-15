{{--
    Panel kiri: daftar sektor (FR-007) + tombol tambah sektor (FR-007c).

    Kode sektor ditampilkan sebagai BACA-SAJA (FR-007d-3) karena kode dibangkitkan
    oleh API dan dijamin unik global (research.md R4) — admin tidak mengeditnya.
--}}
<div class="card card-outline card-primary mb-3">
    <div class="card-header py-2 px-3">
        <h3 class="card-title text-sm mb-0"><i class="fas fa-layer-group mr-1"></i>Sektor</h3>
    </div>

    <div class="card-body p-0">
        @if (count($daftarSektor) === 0)
            <p class="text-muted text-center my-3 px-2 mb-0">
                Belum ada sektor. Tambahkan sektor pertama untuk mulai menyusun skema.
            </p>
        @else
            <ul class="list-group list-group-flush" data-testid="daftar-sektor">
                @foreach ($daftarSektor as $sektor)
                    @php $terpilih = $sektorTerpilih !== null && $sektorTerpilih['id'] === $sektor['id']; @endphp
                    <li class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-start {{ $terpilih ? 'active' : '' }}"
                        data-testid="item-sektor"
                        data-sektor-id="{{ $sektor['id'] }}"
                        data-sektor-kode="{{ $sektor['kode'] }}"
                        data-sektor-nama="{{ $sektor['nama'] }}"
                        data-sektor-ikon="{{ $sektor['ikon'] ?? '' }}"
                        data-ada-draf="{{ $sektor['adaDraf'] ? '1' : '0' }}">
                        <a href="{{ route('dataspasial.skema', array_filter(['sektor' => $sektor['kode']])) }}"
                           class="flex-grow-1 {{ $terpilih ? 'text-white' : 'text-dark' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="font-weight-bold">{{ $sektor['nama'] }}</div>
                                    <small class="{{ $terpilih ? 'text-white-50' : 'text-muted' }}">
                                        Kode sektor: <code>{{ $sektor['kode'] }}</code>
                                    </small>
                                </div>
                                <span class="badge badge-light text-uppercase ml-1">
                                    {{ $sektor['status'] }}
                                </span>
                            </div>

                            {{-- Label versi atau draf (FR-007). --}}
                            <div class="mt-1">
                                @if ($sektor['adaDraf'])
                                    <span class="badge badge-warning">Draf berjalan</span>
                                @elseif ($sektor['jumlahSkema'] > 0)
                                    <span class="badge badge-success">Terbit</span>
                                @else
                                    <span class="badge badge-secondary">Belum ada skema</span>
                                @endif

                                @if ($sektor['jumlahSkema'] > 0)
                                    <small class="ml-1 {{ $terpilih ? 'text-white-50' : 'text-muted' }}">
                                        {{ $sektor['jumlahSkema'] }} versi
                                    </small>
                                @endif
                            </div>
                        </a>

                        {{-- US7 (FR-007d-4): ubah nama & ikon sektor yang sudah ada. --}}
                        <button type="button"
                                class="btn btn-tool {{ $terpilih ? 'btn-light' : 'btn-outline-secondary' }} btn-ubah-sektor ml-1"
                                data-testid="btn-ubah-sektor"
                                title="Ubah sektor ini"
                                aria-label="Ubah sektor {{ $sektor['nama'] }}">
                            <i class="fas fa-pen"></i>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Tombol tambah sektor: selalu terlihat (FR-007c). --}}
    <div class="card-footer p-2">
        <button type="button" class="btn btn-outline-primary btn-block btn-sm"
                id="btn-tambah-sektor" data-testid="btn-tambah-sektor">
            <i class="fas fa-plus mr-1"></i>Tambah Sektor Baru
        </button>
    </div>
</div>

{{--
    Formulir penambahan sektor.

    ⚠️ HANYA memuat kolom NAMA (spec Q5). Tidak ada kolom kode: kode dibangkitkan
    API. ⚠️ Keunikan nama diperiksa API (spec Q13), bukan di OpenKab.
--}}
<form id="form-tambah-sektor" method="POST" action="{{ route('dataspasial.skema.sektor.store') }}"
      class="d-none" data-testid="form-tambah-sektor">
    @csrf

    <div class="card card-outline card-secondary">
        <div class="card-header py-2 px-3">
            <h3 class="card-title text-sm mb-0">Sektor Baru</h3>
        </div>
        <div class="card-body p-3">
            <div class="form-group mb-2">
                <label for="input-nama-sektor">Nama sektor <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm {{ $errors->has('nama') ? 'is-invalid' : '' }}"
                       id="input-nama-sektor" name="nama" maxlength="100"
                       value="{{ old('nama') }}" required>
                @error('nama')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="input-ikon-sektor">Ikon <span class="text-muted">(opsional)</span></label>
                <input type="text" class="form-control form-control-sm"
                       id="input-ikon-sektor" name="ikon" maxlength="100"
                       value="{{ old('ikon') }}" placeholder="mis. school, heart-pulse">
            </div>

            <div class="d-flex">
                <button type="submit" class="btn btn-primary btn-sm flex-fill mr-1">
                    <i class="fas fa-save mr-1"></i>Simpan Sektor
                </button>
                <button type="button" class="btn btn-secondary btn-sm" id="btn-batal-sektor">Batal</button>
            </div>
        </div>
    </div>
</form>

{{--
    Formulir ubah sektor (US7, FR-007d-4 … FR-007d-6).

    ⚠️ **KODE SEKTOR TIDAK ADA DI SINI** — kode dibangkitkan server dan
    bersifat baca-saja (FR-007d-5). Kode hanya ditampilkan sebagai informasi.
    Badannya memuat `nama` + `ikon` sesuai `PUT /dataspasial/sektor/{id}`.

    ⚠️ Tidak ada `nama_sektor_ada` lagi — keunikan nama adalah tanggung jawab API
    (spec Q13), sehingga OpenKab tidak perlu mengirim daftar sektor.
--}}
<form id="form-ubah-sektor" method="POST" class="d-none" data-testid="form-ubah-sektor"
      data-url-template="{{ route('dataspasial.skema.sektor.update', ['sektor' => '__ID__']) }}">
    @csrf
    @method('PUT')

    <div class="card card-outline card-secondary">
        <div class="card-header py-2 px-3">
            <h3 class="card-title text-sm mb-0">Ubah Sektor</h3>
        </div>
        <div class="card-body p-3">
            <div class="form-group mb-2">
                <label class="mb-0">Kode sektor</label>
                <p class="mb-2">
                    <code id="ubah-sektor-kode" data-testid="ubah-sektor-kode"></code>
                    <span class="text-muted small d-block">Kode tidak dapat diubah.</span>
                </p>
            </div>

            <div class="form-group mb-2">
                <label for="input-ubah-nama-sektor">Nama sektor <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="input-ubah-nama-sektor"
                       name="nama" maxlength="100" value="{{ old('nama') }}" required>
                @error('nama')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="input-ubah-ikon-sektor">Ikon <span class="text-muted">(opsional)</span></label>
                <input type="text" class="form-control form-control-sm" id="input-ubah-ikon-sektor"
                       name="ikon" maxlength="100" value="{{ old('ikon') }}"
                       placeholder="mis. school, heart-pulse">
            </div>

            <div class="d-flex">
                <button type="submit" class="btn btn-primary btn-sm flex-fill mr-1">
                    <i class="fas fa-save mr-1"></i>Simpan Perubahan
                </button>
                <button type="button" class="btn btn-secondary btn-sm" id="btn-batal-ubah-sektor">Batal</button>
            </div>
        </div>
    </div>
</form>