@extends('layouts.index')

@section('title', ' Kategori Artikel')

@section('content_header')
    <h1>{{ ucfirst(request()->route('aksi')) }} Kategori Artikel</h1>
@stop

@section('content')
    <div class="row" x-data="kategori()" x-init="retriveData()">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <form class="form-horizontal">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Nama Kategori</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" placeholder="Nama Aplikasi" x-model="dataKategori.kategori">
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
        function kategori() {
            return {
                dataKategori: {
                    kategori: null,
                    parrent: {{ (int) request()->route('parrent') }},
                    id: {{ (int) request()->route('id') }},
                },

                retriveData() {
                    fetch(`{{ url('api/v1/kategori/tampil') }}?id=${this.dataKategori.id}`)
                        .then(res => res.json())
                        .then(response => {
                            this.dataKategori = response.data;
                        }).catch(err => {
                            Swal.fire(
                                'Error!',
                                err,
                                'error'
                            )
                        }); // Catch errors;
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
                        type: "PUT",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: "json",
                        url: `{{ url('api/v1/kategori/perbarui') }}/${this.dataKategori.id}`,
                        data: this.dataKategori,
                        success: function(response) {
                            if (response.success == true) {
                                Swal.fire({
                                    title: 'Simpan!',
                                    text: 'Data berhasil ditambahkan',
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1500,
                                })
                                window.location.href = '{{ url('master/kategori') . '/' . (int) request()->route('parrent') }}';

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
