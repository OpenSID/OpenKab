@extends('layouts.index')

@section('title', ' Kategori Artikel')

@section('content_header')
    <h1 id="judul_kategori">{{ ucfirst(request()->route('aksi')) }} Kategori Artikel</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row" x-data="kategori()" x-init="retriveData()">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="float-left">
                        <div class="btn-group">
                            <a href="{{ url('master/kategori/').'/'.request()->route('parrent') }}" class="btn btn-sm btn-block btn-secondary"><i
                                    class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form class="form-horizontal">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label" id="kategori" x-text="dataKategori.label_kategori"></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" x-bind:placeholder="dataKategori.label_kategori" x-model="dataKategori.kategori">
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
    <script nonce="{{ csp_nonce() }}"  >
        function kategori() {
            return {
                dataKategori: {
                    'kategori': null,
                    'parrent': {{ (int) request()->route('parrent') }},
                    'label_kategori' : ''
                },

                retriveData() {
                    fetch(`{{ url('api/v1/kategori/tampil') }}?id=${this.dataKategori.parrent}`)
                        .then(res => res.json())
                        .then(response => {


                            if (response.data != null) {
                                this.dataKategori.label_kategori = 'Nama Sub Kategori'
                                $('#judul_kategori').text(`Sub Kategori ${response.data.kategori}`)
                            }else{
                                this.dataKategori.label_kategori = 'Nama Kategori'
                            }
                            // console.log*

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
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: "json",
                        url: `{{ url('api/v1/kategori/buat') }}`,
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
                                window.location.href = '{{ url('master/kategori').'/'. (int) request()->route('parrent') }}';

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
