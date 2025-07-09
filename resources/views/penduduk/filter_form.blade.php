<div class="col-md-12">
    <div id="collapse-filter" class="collapse">
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label>Status Penduduk</label>
                    <select class="select2 form-control-sm width-100" id="status" name="status"
                        data-placeholder="Semua Status">
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Status Dasar</label>
                    <select class="select2 form-control-sm width-100" id="status-dasar" name="status-dasar"
                        data-placeholder="Semua Status Dasar">
                        <option value="1" selected>Hidup</option>
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select class="select2 form-control-sm width-100" id="sex" name="sex"
                        data-placeholder="Semua Jenis Kelamin">
                        @if ($filters['sex'] ?? false)
                            <option value="{{ $filters['sex'] }}" selected>
                                {{ App\Models\Enums\JenisKelaminEnum::getLabel($filters['sex']) }}
                            </option>
                        @endif
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label>Kabupaten</label>
                    <select name="Filter Kabupaten" id="filter_kabupaten" class="form-control-sm"
                        placeholder="Pilih Kabupaten">
                        @if ($filters['kode_kabupaten'] ?? false)
                            <option value="{{ $filters['kode_kabupaten'] }}" selected>
                                {{ $filters['nama_kabupaten'] }}</option>
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Kecamatan</label>
                    <select name="Filter Kecamatan" id="filter_kecamatan" class="form-control"
                        placeholder="Pilih Kecamatan">
                        @if ($filters['kode_kecamatan'] ?? false)
                            <option value="{{ $filters['kode_kecamatan'] }}" selected>
                                {{ $filters['nama_kecamatan'] }}</option>
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Desa</label>
                    <select name="Filter Desa" id="filter_desa" class="form-control" placeholder="Pilih Desa">
                        @if ($filters['kode_desa'] ?? false)
                            <option value="{{ $filters['kode_desa'] }}" selected>
                                {{ $filters['nama_desa'] }}</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label>Umur</label>
                    <div class="row pr-2 pl-2">
                        <input id="filter_umur_dari" class="form-control col-5" placeholder="Umur Dari" type="number"
                            value="{{ $filters['umur_dari'] ?? '' }}" min="0" max="150">
                        <span class="col-2 pt-2 text-center">sd</span>
                        <input id="filter_umur_sampai" class="form-control col-5" placeholder="Umur Sampai"
                            type="number" value="{{ $filters['umur_sampai'] ?? '' }}" min="0" max="150">
                    </div>

                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Pekerjaan</label>
                    <select id="pekerjaan_id" class="form-control select2-filter" data-option='{!! json_encode(App\Models\Enums\PekerjaanEnum::select2()) !!}'
                        placeholder="Pilih Pekerjaan">

                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Status Perkawinan</label>
                    <select id="status_kawin" class="form-control select2-filter" data-option='{!! json_encode(App\Models\Enums\StatusKawinEnum::select2()) !!}'
                        placeholder="Pilih Status Kawin">
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label>Cacat</label>
                    <select id="cacat_id" class="form-control select2-filter"data-option='{!! json_encode(App\Models\Enums\CacatEnum::select2()) !!}'
                        placeholder="Pilih Cacat">
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Cara KB</label>
                    <select id="cara_kb_id" class="form-control select2-filter" data-option='{!! json_encode(App\Models\Enums\CaraKBEnum::select2()) !!}'
                        placeholder="Pilih Cara KB">

                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Status KTP</label>
                    <select id="status_rekam" class="form-control select2-filter" data-option='{!! json_encode(App\Models\Enums\StatusKTPEnum::select2()) !!}'
                        placeholder="Pilih Status KTP">
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label>Agama</label>
                    <select id="agama_id" class="form-control select2-filter"data-option='{!! json_encode(App\Models\Enums\AgamaEnum::select2()) !!}'
                        placeholder="Pilih Agama">
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Pendidikan Sedang Ditempuh</label>
                    <select id="pendidikan_sedang_id" class="form-control select2-filter"
                        data-option='{!! json_encode(App\Models\Enums\PendidikanSedangEnum::select2()) !!}' placeholder="Pilih Pendidikan Sedang Ditempuh">

                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Pendidikan Dalam KK</label>
                    <select id="pendidikan_kk_id" class="form-control select2-filter"
                        data-option='{!! json_encode(App\Models\Enums\PendidikanKKEnum::select2()) !!}' placeholder="Pilih Pendidikan Dalam KK">
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label>Asuransi</label>
                    <select id="id_asuransi" class="form-control select2-filter"data-option='{!! json_encode(App\Models\Enums\AsuransiEnum::select2()) !!}'
                        placeholder="Pilih Asuransi">
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Warga Negara</label>
                    <select id="warganegara_id" class="form-control select2-filter"
                        data-option='{!! json_encode(App\Models\Enums\WargaNegaraEnum::select2()) !!}' placeholder="Pilih Warga Negara">

                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Golongan Darah</label>
                    <select id="golongan_darah_id" class="form-control select2-filter"
                        data-option='{!! json_encode(App\Models\Enums\GolonganDarahEnum::select2()) !!}' placeholder="Pilih Golongan Darah">
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label>Sakit Menahun</label>
                    <select id="sakit_menahun_id"
                        class="form-control select2-filter"data-option='{!! json_encode(App\Models\Enums\SakitMenahunEnum::select2()) !!}'
                        placeholder="Pilih Sakit Menahun">
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Kepemilikan Tag ID Card</label>
                    <select id="tag_id_card" class="form-control select2-filter"
                        data-option='{!! json_encode(App\Models\Enums\StatusEnum::select2()) !!}' placeholder="Pilih Kepemilikan Tag ID Card">

                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Kepemilikan Kartu Keluarga</label>
                    <select id="id_kk" class="form-control select2-filter" data-option='{!! json_encode(App\Models\Enums\StatusEnum::select2()) !!}'
                        placeholder="Pilih Kepemilikan Kartu Keluarga">
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label>Hubungan Dalam KK</label>
                    <select id="kk_level" class="form-control select2-filter"data-option='{!! json_encode(App\Models\Enums\SHDKEnum::select2()) !!}'
                        placeholder="Pilih Hubungan Dalam KK">
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Status Kehamilan</label>
                    <select id="hamil" class="form-control select2-filter" data-option='{!! json_encode(App\Models\Enums\HamilEnum::select2()) !!}'
                        placeholder="Pilih Status Kehamilan">
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Suku</label>
                    <input id="suku" class="form-control" placeholder="Suku" type="text"
                        value="{{ $filters['suku'] ?? '' }}">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label>Status Covid</label>
                    <select id="status_covid" class="form-control select2-filter"
                        data-option='{!! json_encode(App\Models\Enums\CovidEnum::select2()) !!}' placeholder="Pilih Status Covid">
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Wajib KTP</label>
                    <select id="ktp" class="form-control select2-filter" data-option='{!! json_encode(App\Models\Enums\StatusEnum::select2()) !!}'
                        placeholder="Pilih Wajib KTP">

                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Bantuan</label>
                    <select id="bantuan-penduduk" class="form-control select2-filter"
                        data-option='{!! json_encode(App\Models\Enums\StatusEnum::select2()) !!}' placeholder="Pilih Bantuan">
                    </select>
                </div>
            </div>
        </div>
        <div class="row fade">
            <input id="program_id" class="form-control" placeholder="program id" type="hidden"
                value="{{ $filters['program_id'] ?? '' }}">
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label>Kepemilikan BPJS Ketenagakerjaan</label>
                    <select id="bpjs_ketenagakerjaan" class="form-control select2-filter"
                        data-option='{!! json_encode(App\Models\Enums\StatusEnum::select2()) !!}' placeholder="Pilih Kepemilikan BPJS Ketenagakerjaan">
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <div class="input-group">
                        <div class="btn-group btn-group-sm btn-block">
                            <button type="button" id="reset" class="btn btn-secondary"><span
                                    class="fas fa-ban"></span></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <div class="input-group">
                        <div class="btn-group btn-group-sm btn-block">
                            <button type="button" id="filter" class="btn btn-primary"><span
                                    class="fas fa-search"></span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr class="mt-0">
    </div>
</div>
