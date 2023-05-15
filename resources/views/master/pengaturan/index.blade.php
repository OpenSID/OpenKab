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

@section('title', 'Pengaturan Aplikasi')

@section('content_header')
    <h1>Pengaturan Aplikasi</h1>
@stop

@section('content')
    @include('partials.flash_message')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <form id="pengaturan-form">

                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="col">
                            <div x-data="{
                                data: {},
                            
                                async retrievePosts() {
                                    const response = await (await fetch('{{ route('api.pengaturan_aplikasi', ['filter[key][]' => 'warna_tema']) }}')).json();
                                    this.data = response.data[0].attributes
                                }
                            }" x-init="retrievePosts">
                                <div class="mb-4">
                                    <label x-text="data.judul"></label>
                                    <x-adminlte-input-color name="color" x-bind:name="data.key"
                                        x-bind:data-color="data.value" x-model="data.value" data-format='hex'
                                        data-horizontal=true>
                                        <x-slot name="appendSlot">
                                            <div class="input-group-text">
                                                <i class="fas fa-lg fa-square"></i>
                                            </div>
                                        </x-slot>
                                    </x-adminlte-input-color>
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

@section('js')
    <script>
        $(document).on('click', 'button#submit', function(e) {
            e.preventDefault();
            formData = $('#pengaturan-form').serialize();

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
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: "json",
                        url: `{{ url('api/v1/pengaturan/update') }}`,
                        data: formData,
                        success: function(response) {
                            console.log(response);
                            if (response.success == true) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Data berhasil diubah',
                                    icon: 'success',
                                    showConfirmButton: true,
                                    timer: 1500
                                })
                                window.location = `{{ url('master/pengaturan') }}`
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
