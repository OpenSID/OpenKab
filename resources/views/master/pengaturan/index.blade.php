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
                            <div x-data="warna()" x-init="retriveData()" x-init="retrievePosts">
                                <template x-for="pengaturan in data">
                                    <div>
                                        <template x-if="pengaturan.attributes.jenis == 'select'">
                                            <div class="mb-1">
                                                <label x-text="pengaturan.attributes.judul"></label>
                                                <select class="form-control" x-model="pengaturan.attributes.value" x-bind:name="pengaturan.attributes.key">
                                                    <template x-for="(option, index) in JSON.parse(pengaturan.attributes.option)">
                                                        <option :value="index" x-text="option" :selected="index == pengaturan.attributes.value"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </template>

                                        <template x-if="pengaturan.attributes.jenis == 'color'">
                                            <div class="mb-1">
                                                <label x-text="pengaturan.attributes.judul"></label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input class="form-control" x-on:change.debounce="setAddonColor($event)" x-bind:cek="cek($el, pengaturan.attributes.value)" x-bind:name="pengaturan.attributes.key" x-bind:data-color="pengaturan.attributes.value" x-model="pengaturan.attributes.value">
                                                        <div class="input-group-append">
                                                            <div class="input-group-text">
                                                                <i class="fas fa-lg fa-square"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="button" id="reset" class="btn btn-danger btn-sm"><i class="fas fa-times"></i>&nbsp; Batal</button>
                        <button id="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i>&nbsp;
                            Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function warna() {
            return {
                data: {},

                retriveData() {
                    fetch(`{{ route('api.pengaturan_aplikasi', ['filter[key][]' => 'warna_tema']) }}`)
                        .then(res => res.json())
                        .then(response => {
                            if (response.data.length != 0) {
                                this.data = response.data
                            }
                        }).catch(err => {
                            Swal.fire(
                                'Error!',
                                err,
                                'error'
                            )
                        }); // Catch errors;
                },

                cek($el, value) {
                    $($el).colorpicker([]).on('change', this.setAddonColor);
                    $($el).closest('.input-group')
                        .find('.input-group-text > i')
                        .css('color', value);
                },

                setAddonColor(vr) {
                    $(vr.target).closest('.input-group')
                        .find('.input-group-text > i')
                        .css('color', vr.target.value);
                },

                color() {
                    $('#color').colorpicker([])
                        .on('change', this.setAddonColor);
                },
            }
        }
    </script>
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
