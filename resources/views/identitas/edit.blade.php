@extends('layouts.index')

@push('css')
    <style>
        .select2-container .select2-selection--single {
            height: 34px;
        }
    </style>
@endpush

@section('title', 'Pengaturan Identitas')

@section('content_header')
    <h1>Identitas </h1>
@stop

@section('content')
    <div class="row" x-data="identitas()" x-init="retrieveData()">
        <div class="col-lg-3 col-md-4">
            <div class="card card-widget">
                <div class="widget-user-header text-center  p-4">
                    <img :src="dataIdentitas.logo ? '{{ asset('storage/img') }}/' + dataIdentitas.logo :
                        '{{ asset('assets/img/opensid_logo.png') }}'"
                        alt="Logo" width="150px">
                    <h5 class="mt-3" x-text="'Logo ' + dataIdentitas.nama_aplikasi">Logo OpenKAB</h5>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-block btn-primary btn-sm" @click="uploadGambar()">Ganti
                                Logo</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="float-left">
                        <div class="btn-group">
                            <a href="{{ url('pengaturan/identitas') }}" class="btn btn-sm btn-block btn-secondary"><i
                                    class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form class="form-horizontal">
                        <div class="form-group row">
                            <label for="inputPassword3" class="col-sm-2 col-form-label">Pilih Kab</label>
                            <div class="col-sm-10">
                                <select name="kab" class="form-control" x-ref="select"></select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Nama Aplikasi</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" placeholder="Nama Aplikasi"
                                    x-model="dataIdentitas.nama_aplikasi">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Deskripsi Aplikasi</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" x-model="dataIdentitas.deskripsi"></textarea>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-footer">
                    <button type="button" class="btn btn-info" x-on:click="simpan()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function identitas() {
            return {
                dataIdentitas: {},
                id: 1,
                retrieveData() {
                    var server_pantau = "{{ config('app.serverPantau') }}";
                    var token_pantau = "{{ config('app.tokenPantau') }}";
                    fetch('{{ url('api/v1/identitas') }}')
                        .then(res => res.json())
                        .then(response => {
                            this.dataIdentitas = response.data.attributes;
                            this.id = response.data.id;
                            this.$nextTick();
                            this.selectKab();
                        });
                },

                selectKab() {
                    this.select2 = $(this.$refs.select).select2({
                        ajax: {
                            url: function() {
                                return "{{ config('app.serverPantau') }}" +
                                    'index.php/api/wilayah/carikabupaten?&token=' +
                                    '{{ config('app.tokenPantau') }}';
                            },
                            dataType: 'json',
                            data: function(params) {
                                return {
                                    q: params.term || '',
                                    page: params.page || 1,
                                };
                            },
                            processResults: function(data) {
                                let results = data.results;
                                return {
                                    results: results.map(value => {
                                        return {
                                            'id': value.kode_kab,
                                            'text': value.nama_prov + ' - ' + value.nama_kab,
                                            'kode_kab': value.kode_kab,
                                            'kode_prov': value.kode_prov,
                                            'nama_kab': value.nama_kab,
                                            'nama_prov': value.nama_prov
                                        }
                                    }),
                                    pagination: data.pagination,
                                }
                            },
                            cache: true
                        },
                        placeholder: this.dataIdentitas.nama_provinsi ? this.dataIdentitas.nama_provinsi + ' - ' +
                            this.dataIdentitas.nama_kabupaten : '--  Cari Nama Kabupaten/Kota --',
                        minimumInputLength: 0,
                        allowClear: true,
                        escapeMarkup: function(markup) {
                            return markup;
                        },
                    });

                    this.select2.on("select2:select", (event) => {
                        this.dataIdentitas.kode_kabupaten = event.params.data.kode_kab;
                        this.dataIdentitas.kode_provinsi = event.params.data.kode_prov;
                        this.dataIdentitas.nama_kabupaten = event.params.data.nama_kab;
                        this.dataIdentitas.nama_provinsi = event.params.data.nama_prov;
                    });
                },

                uploadGambar() {
                    Swal.fire({
                        title: 'Unggah gambar',
                        input: 'file',
                        confirmButtonText: 'Unggah',
                        showLoaderOnConfirm: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Sedang Mengunggah',
                                didOpen: () => {
                                    Swal.showLoading()
                                },
                                allowOutsideClick: false
                            })
                            var formData = new FormData();
                            formData.append('file', result.value);
                            $.ajax({
                                url: '{{ url('api/v1/identitas/upload') }}/' + this.id,
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: 'POST',
                                data: formData,
                                processData: false, // tell jQuery not to process the data
                                contentType: false, // tell jQuery not to set contentType
                                success: function(response) {
                                    if (response.success == true) {
                                        this.data.logo = response.data
                                        Swal.fire({
                                            title: 'Simpan!',
                                            text: 'Data berhasil tersimpan',
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500,
                                        })
                                        location.reload();

                                    } else {
                                        Swal.fire({
                                            title: 'Error!',
                                            text: response.message,
                                            icon: 'error',
                                            showConfirmButton: true,
                                            allowOutsideClick: false
                                        })
                                    }
                                },
                                error: function(XMLHttpRequest, textStatus, errorThrown) {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: XMLHttpRequest.responseJSON['message'],
                                        icon: 'error',
                                        showConfirmButton: true,
                                        allowOutsideClick: false
                                    })
                                }
                            });
                        }
                    });
                },

                simpan() {
                    Swal.fire({
                        title: 'Sedang Menyimpan',
                        didOpen: () => {
                            Swal.showLoading()
                        },
                        allowOutsideClick: false
                    })
                    $.ajax({
                        type: "Put",
                        url: '{{ url('api/v1/identitas/perbarui') }}/' + this.id,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: this.dataIdentitas,
                        success: function(response) {
                            if (response.success == true) {
                                this.data.logo = response.data
                                Swal.fire({
                                    title: 'Simpan!',
                                    text: 'Data berhasil tersimpan',
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1500,
                                })
                                window.location.href = '{{ url('pengaturan/identitas') }}';

                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error',
                                    showConfirmButton: true,
                                    allowOutsideClick: false
                                })
                            }
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            Swal.fire({
                                title: 'Error!',
                                text: XMLHttpRequest.responseJSON['message'],
                                icon: 'error',
                                showConfirmButton: true,
                                allowOutsideClick: false
                            })
                        }
                    });
                }
            }
        }
    </script>
@endsection

@section('js')
@endsection
