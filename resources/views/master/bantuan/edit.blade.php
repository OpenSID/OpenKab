@extends('layouts.index')

@section('title', 'Ubah Bantuan')

@section('content_header')
    <h1>Ubah Bantuan</h1>
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
                <form id="bantuan-form">
                    <!-- /.card-header -->
                    <div x-data="{
                        data: {},
                    
                        async retrievePosts() {
                            try {
                                // Buat URL dan tambahkan parameter query
                                const header = @include('layouts.components.header_bearer_api_gabungan');
                                let url = new URL('{{ config('app.databaseGabunganUrl') }}/api/v1/bantuan-kabupaten');
                                url.searchParams.append('filter[id]', '{{ $id }}');
                    
                                const response = await fetch(url, {
                                    headers: header
                                });
                    
                                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    
                                const result = await response.json();
                    
                                if (result.data.length > 0) {
                                    this.data = result.data[0].attributes;

                                    this.$nextTick(() => {
                                        $('#sdate').data('daterangepicker').setStartDate(moment(this.data.sdate, '{{ config('app.format.date_js') }}'));
                                        $('#edate').data('daterangepicker').setStartDate(moment(this.data.edate, '{{ config('app.format.date_js') }}'));
                                    });
                                } else {
                                    console.warn('Data tidak ditemukan');
                                }
                            } catch (error) {
                                console.error('Error saat mengambil data:', error);
                            }
                        }
                    }" x-init="retrievePosts()">
                        <div class="card-body">
                            <div class="col">
                                <div class="mb-4">
                                    <label for="sasaran">Sasaran Program<span class="text-danger">*</span></label>
                                    <select class="form-control @error('sasaran') is-invalid @enderror" x-model="data.sasaran" name="sasaran">
                                        <option selected disabled>Pilih Sasaran</option>
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

                            <div class="col">
                                <div class="mb-4">
                                    <label for="nama">Nama Program<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror" x-model="data.nama" name="nama">
                                    @error('nama')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col">
                                <div class="mb-4">
                                    <label for="ndesc">Keterangan<span class="text-danger">*</span></label>
                                    <textarea x-model="data.ndesc" name="ndesc" class="form-control" rows="3"></textarea>
                                    @error('ndesc')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col">
                                <div class="mb-4">
                                    <label for="asaldana">Asal Dana<span class="text-danger">*</span></label>
                                    <select class="form-control @error('asaldana') is-invalid @enderror" x-model="data.asaldana" name="asaldana">
                                        <option selected disabled>Pilih Asal Dana</option>
                                        <option>Pusat</option>
                                        <option>Provinsi</option>
                                        <option>Kab/Kota</option>
                                        <option>Dana Desa</option>
                                        <option>Lain-lain (Hibah)</option>
                                    </select>
                                    @error('asaldana')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="mb-4">
                                        <label for="sdate">Tanggal Mulai<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control datepicker @error('sdate') is-invalid @enderror" x-model="data.sdate" name="sdate" id="sdate">
                                        @error('sdate')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="mb-4">
                                        <label for="edate">Tanggal Berakhir<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control datepicker @error('edate') is-invalid @enderror" x-model="data.edate" name="edate" id="edate">
                                        @error('edate')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.card-body -->
                    </div>
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
    <script nonce="{{ csp_nonce() }}"  >
    document.addEventListener("DOMContentLoaded", function(event) {
        $(document).on('click', 'button#submit', function(e) {
                e.preventDefault();
                let dateParam = $.param({ sdate: $('input[name=sdate]').data('daterangepicker').startDate.format('YYYY-MM-DD'), edate: $('input[name=edate]').data('daterangepicker').startDate.format('YYYY-MM-DD')})
                let formData = $('#bantuan-form  input,textarea,select').not('.datepicker').serialize()+'&'+dateParam
                var id = "{{ $id }}";

                const header = @include('layouts.components.header_bearer_api_gabungan');
                var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/bantuan-kabupaten/perbarui' }}");

                Swal.fire({
                    title: 'Ubah',
                    text: "Apakah anda yakin mengubah data ini?",
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
                            type: "PUT",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'Authorization': 'Bearer {{ $settingAplikasi->get('database_gabungan_api_key') }}'
                            },
                            dataType: "json",
                            url: `${url}/${id}`,
                            data: formData,
                            success: function(response) {
                                if (response.success == true) {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: 'Data berhasil diubah',
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
