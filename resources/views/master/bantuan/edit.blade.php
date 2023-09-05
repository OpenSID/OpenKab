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
                            const response = await (await fetch('{{ url('api/v1/bantuan-kabupaten') }}?filter[id]={{ $id }}')).json();
                            this.data = response.data[0].attributes
                        }
                    }" x-init="retrievePosts">
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
                                        <input type="text" class="form-control datepicker @error('sdate') is-invalid @enderror" x-model="data.sdate" name="sdate">
                                        @error('sdate')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="mb-4">
                                        <label for="edate">Tanggal Berakhir<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control datepicker @error('edate') is-invalid @enderror" x-model="data.edate" name="edate">
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
    <script>
        $(document).on('click', 'button#submit', function(e) {
                e.preventDefault();
                formData = $('#bantuan-form').serialize();
                var id = "{{ $id }}";

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
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType: "json",
                            url: `{{ url('api/v1/bantuan-kabupaten/perbarui') }}/` + id,
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
    </script>
@endsection
