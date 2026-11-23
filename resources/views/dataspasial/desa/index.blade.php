{{--
    Halaman Status Desa (KAB-09 / API-09 / SID-11).

    Pemerintah Kabupaten menonaktifkan atau mengaktifkan kembali pengisian Data Spasial
    per desa. Desa nonaktif tidak dapat menambah/mengubah fasilitas maupun mengisi isian
    dinamis di OpenSID (mode Terpusat); data lama tetap tampil di peta dan dasbor.
    Tabel dimuat lewat AJAX; ubah status memakai form POST dengan flash message.
--}}
@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')

    <div class="row">
        <div class="col-12">
            <div class="callout callout-info" data-testid="status-desa-info">
                <p class="mb-0">
                    Desa yang <strong>dinonaktifkan</strong> tidak dapat menambah fasilitas, mengubah fasilitas,
                    maupun mengisi isian dinamis Data Spasial di OpenSID. Data yang sudah ada tetap tampil di
                    Peta Gabungan dan Dasbor, dan Impor Data dari OpenKab tetap dapat dilakukan.
                </p>
            </div>
        </div>
    </div>

    {{-- Blok Filter --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3></div>
                <div class="card-body">
                    <form id="status-desa-filter" data-testid="status-desa-filter" onsubmit="return false;">
                        <div class="row">
                            <div class="col-md-4 col-sm-6 mb-2">
                                <x-dataspasial-dropdown-kecamatan id="filter_kecamatan_status" name="kode_kecamatan"
                                    data-testid="filter-kecamatan" />
                            </div>
                            <div class="col-md-4 col-sm-6 mb-2">
                                <label for="filter_status_desa">Status Pengisian</label>
                                <select id="filter_status_desa" class="form-control form-control-sm"
                                    data-testid="filter-status" title="Pilih Status Pengisian">
                                    @foreach ($pilihanStatus as $pilihan)
                                        <option value="{{ $pilihan['value'] }}">{{ $pilihan['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($bolehUbah)
                                {{-- Aksi massal: cakupan mengikuti filter kecamatan (tanpa filter = se-kabupaten).
                                     Tombol aktif setelah jumlah desa per status dimuat (ringkasan_status). --}}
                                <div class="col-md-4 col-sm-12 mb-2" data-testid="aksi-massal">
                                    <label class="d-block">Aksi Semua Desa</label>
                                    <div class="d-flex flex-wrap" style="gap: .25rem;">
                                        <button type="button" class="btn btn-sm btn-outline-danger tombol-status-massal" disabled
                                            data-aktif="0" data-testid="tombol-nonaktifkan-semua">
                                            <i class="fas fa-lock"></i> <span class="label-massal">Nonaktifkan Semua Desa</span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success tombol-status-massal" disabled
                                            data-aktif="1" data-testid="tombol-aktifkan-semua">
                                            <i class="fas fa-lock-open"></i> <span class="label-massal">Aktifkan Semua Desa</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel desa --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-table"></i> Daftar Desa</h3>
                    <div class="card-tools">
                        <span class="badge badge-danger" id="ringkasan-nonaktif" data-testid="ringkasan-nonaktif"
                            style="display:none"></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="status-desa-loading" class="text-muted m-3" style="display:none"
                        data-testid="status-desa-loading">
                        <i class="fas fa-spinner fa-spin"></i> Memuat data desa…
                    </div>
                    <div id="status-desa-empty" class="alert alert-secondary m-3" style="display:none"
                        data-testid="status-desa-empty">
                        Tidak ada desa untuk pilihan ini.
                    </div>
                    <div id="status-desa-unavailable" class="alert alert-warning m-3" style="display:none"
                        data-testid="status-desa-unavailable">
                        <h5 class="alert-heading">Data sementara tidak tersedia</h5>
                        <p class="mb-0">Silakan coba lagi nanti.</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover mb-0" data-testid="tabel-status-desa">
                            <thead class="thead-light">
                                <tr>
                                    <th>Desa</th>
                                    <th>Kecamatan</th>
                                    <th class="text-center">Status</th>
                                    <th>Alasan</th>
                                    <th>Dinonaktifkan</th>
                                    @if ($bolehUbah)
                                        <th class="text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="status-desa-tbody" data-testid="status-desa-tbody">
                                <tr class="table-loading">
                                    <td colspan="{{ $bolehUbah ? 6 : 5 }}" class="text-center text-muted">Memuat…</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <nav id="status-desa-paginasi" data-testid="status-desa-paginasi"></nav>
                </div>
            </div>
        </div>
    </div>

    @if ($bolehUbah)
        {{-- Modal konfirmasi ubah status --}}
        <div class="modal fade" id="modal-status-desa" tabindex="-1" role="dialog" aria-hidden="true"
            data-testid="modal-status-desa">
            <div class="modal-dialog" role="document">
                <form method="POST" id="form-status-desa" class="modal-content"
                    data-action="{{ route('dataspasial.desa.status', ['config_id' => '__ID__']) }}"
                    action="{{ route('dataspasial.desa.status', ['config_id' => 0]) }}">
                    @csrf
                    <input type="hidden" name="aktif" id="input-aktif" value="0">
                    <input type="hidden" name="kode_kecamatan" id="input-kode-kecamatan" value="">
                    <input type="hidden" name="status" id="input-status-filter" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="judul-modal-status">Nonaktifkan Pengisian Desa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="teks-modal-status" class="mb-3"></p>
                        <div class="form-group mb-0" id="grup-alasan">
                            <label for="input-alasan">Alasan <span class="text-danger">*</span></label>
                            <textarea name="alasan" id="input-alasan" class="form-control" rows="3"
                                maxlength="{{ \App\Http\Requests\DataSpasial\UbahStatusDesaRequest::PANJANG_ALASAN }}"
                                data-testid="input-alasan"
                                placeholder="Contoh: Data fasilitas sedang diverifikasi kabupaten"></textarea>
                            <small class="form-text text-muted">Alasan ditampilkan kepada operator desa di OpenSID.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger" id="tombol-simpan-status"
                            data-testid="tombol-simpan-status">Nonaktifkan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal konfirmasi aksi massal --}}
        <div class="modal fade" id="modal-status-massal" tabindex="-1" role="dialog" aria-hidden="true"
            data-testid="modal-status-massal">
            <div class="modal-dialog" role="document">
                <form method="POST" id="form-status-massal" class="modal-content"
                    action="{{ route('dataspasial.desa.status-massal') }}">
                    @csrf
                    <input type="hidden" name="aktif" id="massal-aktif" value="0">
                    <input type="hidden" name="kode_kecamatan" id="massal-kode-kecamatan" value="">
                    <input type="hidden" name="nama_kecamatan" id="massal-nama-kecamatan" value="">
                    <input type="hidden" name="status" id="massal-status-filter" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="judul-modal-massal">Nonaktifkan Semua Desa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning py-2" id="teks-modal-massal" data-testid="teks-modal-massal"></div>
                        <div class="form-group" id="grup-alasan-massal">
                            <label for="massal-alasan">Alasan <span class="text-danger">*</span></label>
                            <textarea name="alasan" id="massal-alasan" class="form-control" rows="3"
                                maxlength="{{ \App\Http\Requests\DataSpasial\UbahStatusDesaRequest::PANJANG_ALASAN }}"
                                data-testid="input-alasan-massal"
                                placeholder="Contoh: Verifikasi data spasial tahunan oleh kabupaten"></textarea>
                            <small class="form-text text-muted">
                                Alasan ditampilkan kepada operator desa di OpenSID. Desa yang sudah nonaktif tetap memakai alasan lamanya.
                            </small>
                        </div>
                        <p class="small text-muted mb-2" id="catatan-desa-baru">
                            Status disimpan per desa: desa yang baru terdaftar setelah aksi ini tetap aktif.
                        </p>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="massal-konfirmasi" name="konfirmasi" value="1"
                                data-testid="konfirmasi-massal">
                            <label class="custom-control-label" for="massal-konfirmasi" id="label-konfirmasi-massal">
                                Saya memahami perubahan ini berlaku untuk semua desa pada cakupan di atas.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger" id="tombol-simpan-massal" disabled
                            data-testid="tombol-simpan-massal">Nonaktifkan Semua</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@stop

@push('js')
    {{-- ?v=filemtime: browser selalu memuat versi terbaru setelah berkas berubah (tanpa ini JS lama dari cache
         tidak mengenal tombol massal sehingga tombol tetap disabled) --}}
    <script src="{{ asset('js/dataspasial/status-desa.js') }}?v={{ @filemtime(public_path('js/dataspasial/status-desa.js')) }}"></script>
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.StatusDesaDataSpasial) return;
            window.StatusDesaDataSpasial.init({
                dataUrl: @json(route('dataspasial.desa.data')),
                bolehUbah: @json($bolehUbah),
            });
        });
    </script>
@endpush
