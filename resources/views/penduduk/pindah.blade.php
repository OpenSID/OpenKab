@extends('layouts.index')

@section('title', 'Form Pindah Penduduk')

@section('content_header')
    <h1>Form Pindah Penduduk</h1>
@stop

@section('content')
    <div class="row" x-data="pindah()" x-init="$nextTick(() => { retrieveData() })">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="float-left">
                        <div class="btn-group">
                            <a href="{{ url('penduduk') }}" class="btn btn-sm btn-block btn-secondary"><i
                                    class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form class="form-horizontal">
                        <div class="form-group row">
                            <label for="inputPassword3" class="col-sm-2 col-form-label">Tujuan Pindah</label>
                            <div class="col-sm-10">
                                <select name="kab" class="form-control" x-model="dataPenduduk.ref_pindah">
                                    <option value="1">Pindah keluar Desa/Kelurahan</option>
                                    <option value="2">Pindah keluar Kecamatan</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="inputPassword3" class="col-sm-2 col-form-label">Pilih Desa/Kelurahan</label>
                            <div class="col-sm-10">
                                <select name="kab" class="form-control" x-model="dataPenduduk.config_tujuan">
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Alamat Tujuan</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" x-model="dataPenduduk.alamat_tujuan"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Tanggal Peristiwa</label>
                            <div class="col-sm-10">
                                <div class="input-group date" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input"
                                        x-ref="tgl_peristiwa" data-date-end-date="0d">

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
                                <textarea class="form-control" x-model="dataPenduduk.catatan"></textarea>
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
        function pindah() {
            return {
                dataPenduduk: {
                    id: {{ request()->route('id') }},
                    alamat_tujuan: '',
                    ref_pindah: 1,
                    config_asal: 0,
                    config_tujuan: 0,
                    status: 1,
                    tgl_lapor: moment().format("YYYY-MM-DD"),
                    tgl_peristiwa: moment().format("YYYY-MM-DD"),
                    catatan: ''
                },
                cek() {
                    console.log('sdfadsfs')
                },

                retrieveData() {

                    this.dateTglLapor();
                    this.dateTglPeristiwa();

                },

                dateTglLapor() {
                    var data = this.dataPenduduk;
                    this.tglLapor = $(this.$refs.tgl_lapor).datepicker({
                        language: "id",
                        autoclose: true,
                        format: "dd/mm/yyyy",
                    }).datepicker("setDate",'now');;
                    this.tglLapor.on('change', function(event) {
                         data.tgl_lapor = $(this).data('datepicker').getFormattedDate('yyyy-mm-dd')
                      });

                    //                     this.tglLapor = $(this.$refs.tgl_lapor).daterangepicker({
                    //                         "singleDatePicker": true,
                    //                         "autoApply": true,
                    //                     });

                    //                     this.tglLapor.on('change', function(ev, picker) {
                    //                         this.tglLapor.val(picker.startDate.format('MM/DD/YYYY') + ' - ');
                    //   });
                },

                dateTglPeristiwa() {
                    var data = this.dataPenduduk;
                    this.tglPeristiwa = $(this.$refs.tgl_peristiwa).datepicker({
                        language: "id",
                        autoclose: true,
                        format: "dd/mm/yyyy",
                    }).datepicker("setDate",'now');;
                    this.tglPeristiwa.on('change', function(event) {
                         data.tgl_peristiwa = $(this).data('datepicker').getFormattedDate('yyyy-mm-dd')
                      });


                },


                simpan() {
                    console.log(this.dataPenduduk)
                }
            }
        }
    </script>
@endsection

@section('js')

    <script>
        $(document).ready(function() {


            // $('#tgl_lapor').daterangepicker({
            //     "singleDatePicker": true,
            //     "autoApply": true,
            // });
        });
    </script>
@endsection
