@extends('layouts.index')

@push('css')
    <style>
        /* ubah semua ukuran text yang ada dalam card-body */
        .card-body {
            font-size: 14px;
        }

        /* ubah semua ukuran h3 yang ada dalam card-body */
        .card-body h3 {
            font-size: 24px;
        }

        .card-body h5 {
            font-size: 18px;
        }

        th {
            vertical-align: middle !important;
        }
    </style>
@endpush

@section('title', 'Biodata Penduduk')

@section('content_header')
    <h1>Identitas </h1>
@stop

@section('content')
    <div class="row" x-data="{
        data: {},

        async retrieveData() {
            const response = await (await fetch('{{ url('api/v1/identitas') }}')).json();
            this.data = response.data.attributes
        }

        unggahLogo (){

        }
    }" x-init="retrieveData">
        <div class="col-lg-3 col-md-4">
            <div class="card card-widget">
                <div class="widget-user-header text-center  p-4">
                    <img :src="data.logo ?? '{{ asset('assets/img/opensid_logo.png') }}'" alt="Logo" width="150pxxx">
                    <h5 class="mt-3">Logo OpenKAB</h5>
                </div>


                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-block btn-primary btn-sm" id="ganti_logo">Ganti
                                Logo</button>
                        </div>
                    </div>

                </div>
            </div>


        </div>

        <div class="col-lg-9 col-md-8">
            <div class="card">
                <div class="card-header ui-sortable-handle" style="cursor: move;">
                    <div class="bg-info">sdss</div>
                </div>

                <div class="card-body">
                    dfd
                </div>


            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function() {
            $('#ganti_logo').click(function(e) {
                e.preventDefault();
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
                            url: '{{ url('api/v1/identitas/upload') }}',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            data: formData,
                            processData: false, // tell jQuery not to process the data
                            contentType: false, // tell jQuery not to set contentType
                            success: function(response) {
                                if (response.success == true) {
                                    Swal.fire({
                                        title: 'Simpan!',
                                        text: 'Data berhasil tersimpan',
                                        icon: 'success',
                                        showConfirmButton: false,
                                        timer: 1500,
                                    })
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.message,
                                        icon: 'error',
                                        showConfirmButton: true,
                                        allowOutsideClick: false
                                    })
                                }
                            }
                        });
                    }
                })
            });

        });
    </script>
@endsection
