@extends('layouts.index')

@push('css')
    <style nonce="{{ csp_nonce() }}">
       .select2-container--default .select2-selection--multiple .select2-selection__choice{
        background-color: #007bff;
        border-color: #007bff;
        color: #ffffff;
       }
       .select2-container--default .select2-selection--multiple .select2-selection__choice__remove{
        border-right: none;
        color: #FFFFFF
       }       
    </style>
@endpush

@section('title', 'Tambah Bantuan')

@section('content_header')
    <h1>Tambah Bantuan</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('bantuan.index') }}" class="btn btn-primary btn-sm"><i
                            class="fas fa-arrow-circle-left"></i></i>&ensp;Kembali ke Daftar Bantuan</a>
                </div>
                <form id="bantuan-form" x-data="{ sasaran: '' }">
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="col">
                            <div class="mb-4">
                                <label for="sasaran">Sasaran Program<span class="text-danger">*</span></label>
                                <select class="form-control @error('sasaran') is-invalid @enderror" name="sasaran" x-model="sasaran">
                                    <option selected disabled value="">Pilih Sasaran</option>
                                    <option value="1">Penduduk Perorangan</option>
                                    <option value="2">Keluarga / KK</option>
                                    <option value="3">Rumah Tangga</option>
                                    <option value="4">Kelompok / Organisasi</option>
                                </select>
                                @error('sasaran')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col" x-show="sasaran == '2'" x-cloak>
                            <div class="mb-4">
                                <label for="kk_level">Penerima (Hubungan Dalam KK)<span class="text-danger">*</span></label>
                                <select class="form-control @error('kk_level') is-invalid @enderror" name="kk_level[]" multiple="multiple" data-placeholder="Pilih Hubungan Dalam KK">
                                    @php
                                        $shdkOptions = App\Models\Enums\SHDKEnum::select2();
                                    @endphp
                                    @foreach($shdkOptions as $option)
                                        <option value="{{ $option['id'] }}">{{ $option['text'] }}</option>
                                    @endforeach
                                </select>
                                @error('kk_level')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-4">
                                <label for="nama">Nama Program<span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                    name="nama">
                                @error('nama')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-4">
                                <label for="ndesc">Keterangan<span class="text-danger">*</span></label>
                                <textarea name="ndesc" class="form-control" rows="3"></textarea>
                                @error('ndesc')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-4">
                                <label for="asaldana">Asal Dana<span class="text-danger">*</span></label>
                                <select class="form-control @error('asaldana') is-invalid @enderror" name="asaldana">
                                    <option selected disabled value="">Pilih Asal Dana</option>
                                    <option>Pusat</option>
                                    <option>Provinsi</option>
                                    <option>Kab/Kota</option>
                                    <option>Dana {{ config('app.sebutanDesa') }}
                                    </option>
                                    <option>Lain-lain (Hibah)</option>
                                </select>
                                @error('asaldana')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-4">
                                <label for="publikasi">Publikasi<span class="text-danger">*</span></label>
                                <select class="form-control @error('publikasi') is-invalid @enderror" name="publikasi">
                                    <option selected disabled value="">Pilih Publikasi</option>
                                    <option value="1">Ya</option>
                                    <option value="0">Tidak</option>
                                </select>
                                @error('publikasi')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="mb-4">
                                    <label for="sdate">Tanggal Mulai<span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control datepicker @error('sdate') is-invalid @enderror" name="sdate">
                                    @error('sdate')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-4">
                                    <label for="edate">Tanggal Berakhir<span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control datepicker @error('edate') is-invalid @enderror" name="edate">
                                    @error('edate')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="button" id="reset" class="btn btn-danger btn-sm"><i
                                class="fas fa-times"></i>&nbsp; Batal</button>
                        <button id="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i>&nbsp;
                            Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@include('partials.reset_form')
@include('partials.asset_datepicker')

@section('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            $('select[name="kk_level[]"]').select2({                
                placeholder: 'Pilih Hubungan Dalam KK',
                allowClear: true,    
                width: '100%'
            });

            $(document).on('click', 'button#submit', function(e) {
                e.preventDefault();
                let dateParam = $.param({
                    sdate: $('input[name=sdate]').data('daterangepicker').startDate.format(
                        'YYYY-MM-DD'),
                    edate: $('input[name=edate]').data('daterangepicker').startDate.format(
                        'YYYY-MM-DD')
                })
                let formData = $('#bantuan-form  input,textarea,select').not('.datepicker').serialize() +
                    '&' + dateParam

                var url = new URL(
                    "{{ config('app.databaseGabunganUrl') . '/api/v1/bantuan-kabupaten/tambah' }}");

                Swal.fire({
                    title: 'Tambah',
                    text: "Apakah anda yakin menambah data ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Simpan'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menyimpan',
                            didOpen: () => {
                                Swal.showLoading()
                            },
                        })
                        $.ajax({
                            type: "POST",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content'),
                                'Authorization': 'Bearer {{ $settingAplikasi->get('database_gabungan_api_key') }}'
                            },
                            dataType: "json",
                            url: url,
                            data: formData,
                            success: function(response) {
                                if (response.success == true) {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: 'Data berhasil ditambahkan',
                                        icon: 'success',
                                        showConfirmButton: true,
                                        timer: 1500
                                    })
                                    window.location = `{{ url('master/bantuan') }}`
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        response.message,
                                        'error'
                                    )
                                }
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                Swal.fire(
                                    'Error!',
                                    xhr.responseJSON.message,
                                    'error'
                                )
                            }
                        });
                    }
                })
            });
        })
    </script>
@endsection
