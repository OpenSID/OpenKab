@extends('layouts.index')

@section('title', 'Form Pindah Penduduk')

@section('content_header')
    <h1>Form Pindah Penduduk</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row" x-data="pindah()" x-init="$nextTick(() => { retrieveData() })">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="float-left">
                        <div class="btn-group">
                            <a href="{{ url('profile-kependudukan/penduduk') }}" class="btn btn-sm btn-block btn-secondary"><i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form class="form-horizontal">
                        <div class="form-group row">
                            <label for="inputPassword3" class="col-sm-2 col-form-label">Tujuan Pindah</label>
                            <div class="col-sm-10">
                                <select name="kab" class="form-control" x-model="dataPindah.ref_pindah">
                                    <option value="1">Pindah keluar Desa/Kelurahan</option>
                                    <option value="2">Pindah keluar Kecamatan</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="inputPassword3" class="col-sm-2 col-form-label">Pilih Desa/Kelurahan</label>
                            <div class="col-sm-10">
                                <select name="kab" class="form-control" x-model="dataPindah.kelurahan_tujuan" x-ref="selectDesa">
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Alamat Tujuan</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" x-model="dataPindah.alamat_tujuan"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Tanggal Peristiwa</label>
                            <div class="col-sm-10">
                                <div class="input-group date" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" x-ref="tgl_peristiwa" data-date-end-date="0d">

                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Tanggal Lapor</label>
                            <div class="col-sm-10">
                                <div class="input-group date" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" x-ref="tgl_lapor" data-date-end-date="0d">

                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Catatan Peristiwa</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" x-model="dataPindah.catatan"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-info" x-on:click="simpan()" :disabled="loading">
                        <span x-show="loading"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan</span>
                        <span x-show="!loading">Simpan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('vendor/moment/moment.js') }}"></script>
    <script src="{{ asset('vendor/moment/id.js') }}"></script>
    <script nonce="{{ csp_nonce() }}"  >
        function pindah() {
            return {
                dataPindah: {
                    id: {{ request()->route('id') }},
                    alamat_tujuan: '',
                    ref_pindah: 1,
                    config_asal: '',
                    kelurahan_tujuan: '',
                    status: 1,
                    tgl_lapor: moment().format("YYYY-MM-DD"),
                    tgl_peristiwa: moment().format("YYYY-MM-DD"),
                    catatan: undefined
                },
                dataPenduduk: {},
                loading: false,

                retrieveData() {
                    this.retrivePenduduk();
                },

                retrivePenduduk() {
                    fetch('{{ url('api/v1/penduduk?filter[id]=' . request()->route('id')) }}')
                        .then(res => res.json())
                        .then(response => {
                            this.dataPenduduk = response.data[0].attributes;
                            this.dataPindah.config_asal = this.dataPenduduk.config_id;
                            this.$nextTick();
                            this.dateTglLapor();
                            this.dateTglPeristiwa();
                            this.selectDesa();
                        });
                },

                selectDesa() {
                    var dataPenduduk = this.dataPenduduk;
                    var dataPindah = this.dataPindah;
                    this.select2 = $(this.$refs.selectDesa).select2({
                        theme: 'bootstrap4',
                        ajax: {
                            url: function() {
                                return "{{ url('api/v1/wilayah/desa/') }}";
                            },
                            dataType: 'json',
                            data: function(params) {
                                return {
                                    'page[number]': params.page || 1,
                                    'filter[search]': params.term || '',
                                    'filter[asal]': dataPenduduk.config_id,
                                    'filter[kode_kecamatan]': (dataPindah.ref_pindah == 1) ? '' : dataPenduduk.config.kode_kecamatan,
                                };
                            },
                            processResults: function(data) {
                                let results = data.data;
                                return {
                                    results: results.map(value => {
                                        return {
                                            'id': value.id,
                                            'text': value.attributes.nama_kecamatan + ' - ' + value.attributes.nama_desa,
                                        }
                                    }),
                                    pagination: {
                                        more: (data.meta.pagination.current_page >= data.meta.pagination
                                            .total_pages) ? false : true
                                    }
                                }
                            },
                            cache: true
                        },
                        placeholder: '--  Cari Nama Desa --',
                        minimumInputLength: 0,
                        allowClear: true,
                        escapeMarkup: function(markup) {
                            return markup;
                        },
                    });
                    this.select2.on("select2:select", (event) => {
                        this.dataPindah.kelurahan_tujuan = event.params.data.id;
                    });
                },

                dateTglLapor() {
                    var data = this.dataPindah;
                    this.tglLapor = $(this.$refs.tgl_lapor).daterangepicker({
                        autoApply: true,
                        format: "dd/mm/yyyy",
                        singleDatePicker: true,
                        locale: {
                            firstDay: 1
                        }
                    })
                    // .datepicker("setDate", 'now');;
                    this.tglLapor.on('apply.daterangepicker', function(event, picker) {
                        data.tgl_lapor = picker.startDate.format('yyyy-mm-dd')
                    });
                },

                dateTglPeristiwa() {
                    var data = this.dataPindah;
                    this.tglPeristiwa = $(this.$refs.tgl_peristiwa).daterangepicker({
                        autoApply: true,
                        format: "dd/mm/yyyy",
                        singleDatePicker: true,
                        locale: {
                            firstDay: 1
                        }

                    });
                    // .datepicker("setDate", 'now');;
                    this.tglPeristiwa.on('apply.daterangepicker', function(event, picker) {
                        data.tgl_peristiwa = picker.startDate.format('yyyy-mm-dd');
                    });


                },

                simpan() {
                    var data = this.dataPindah;

                    Swal.fire({
                        title: 'Menyimpan',
                        didOpen: () => {
                            Swal.showLoading()
                        },
                    })

                    $.ajax({
                        type: "Post",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ url('api/v1/penduduk/aksi/pindah') }}',
                        data: data,
                        dataType: "json",
                        success: function(response) {
                            if (response.success == true) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Data berhasil ditambahkan',
                                    showConfirmButton: false,
                                    timer: 1500
                                })
                                window.location.replace("{{ url('profile-kependudukan/penduduk') }}");
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message,
                                    'error'
                                )
                            }
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            console.log('erer')
                            Swal.fire(
                                'Error!  ' + xhr.status,
                                JSON.parse(xhr.responseText).message,
                                'error'
                            )

                        }
                    });
                }
            }
        }
    </script>
@endsection
