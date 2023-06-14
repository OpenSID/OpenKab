@extends('layouts.index')

@section('title', 'Tambah Bantuan')

@section('content_header')
    <h1>Tambah Group</h1>
@stop

@section('content')
    <div class="row" x-data="group()" x-init="retrieveData()">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('bantuan.index') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-circle-left"></i></i>&ensp;Kembali ke Daftar Bantuan</a>
                </div>
                <form id="bantuan-form">
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="col">
                            <div class="mb-4">
                                <label for="sasaran">Nama Group </label>
                                <input type="text" class="form-control" name="name" x-model="dataGroup.name">
                            </div>
                        </div>

                        <div class="col">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <td width="30">#</td>
                                        <td width="40" colspan="2">No</td>
                                        <td>Nama Modul</td>
                                    </tr>
                                </thead>

                                <template x-for="(value, index) in menu">
                                    <tbody>


                                        <tr>
                                            <td><input type="checkbox" name="menu" x-model="value.selected" @input.debounce.100ms="selected(value)"></td>
                                            <td x-text="(index +1)" colspan="2" width=40 class="text-center"></td>
                                            <td x-text="value.text"></td>
                                            <td></td>
                                        </tr>

                                        <template x-for="(submenu, index2) in value.submenu">
                                            <tr>
                                                <td><input type="checkbox" name="menu" x-model="submenu.selected" @input.debounce.100ms="selected_sub(value)"></td>
                                                <td width=20></td>
                                                <td x-text="(index+1)+ '.' + (index2+1)"  width=20 class="text-center"></td>
                                                <td x-text="submenu.text" class="ps-5" style="padding-left: 50px;"></td>
                                                <td></td>
                                            <tr>
                                        </template>
                                    </tbody>
                                </template>
                            </table>
                        </div>


                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="button" id="reset" class="btn btn-danger btn-sm"><i class="fas fa-times"></i>&nbsp; Batal</button>
                        <button x-on:click="simpan()" type="button" class="btn btn-primary btn-sm"><i class="fas fa-save"></i>&nbsp;
                            Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function group() {
            return {
                dataGroup: {
                    'name': '',
                    'menu': new Array()
                },
                menu: {},
                retrieveData() {
                    this.retriveMenu();
                },

                retriveMenu() {
                    fetch('{{ url('api/v1/pengaturan/group/menu') }}')
                        .then(res => res.json())
                        .then(response => {
                            this.menu = response.data
                        });
                },
                simpan() {
                    let menu = _.chain(this.menu).filter(function (menu) {
                        if (menu.submenu && menu.selected) {
                            let submenu = _.chain(menu.submenu).filter(function (_submenu) {
                                if (_submenu.selected) {
                                    return _submenu;
                                }
                            }).value();
                            if (submenu.length > 0) {
                                return submenu;
                            }

                        }else if(menu.selected) {
                            return menu
                        }
                    }).value()
                    this.dataGroup.menu = menu;
                    Swal.fire({
                        title: 'Menyimpan',
                        didOpen: () => {
                            Swal.showLoading()
                        },
                    })
                    var data = this.dataGroup;
                    console.log(data)
                    $.ajax({
                        type: "Post",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ url('api/v1/pengaturan/group') }}',
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
                                window.location.replace("{{ url('penduduk') }}");
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
                },
                selected(data){
                    if (data.submenu) {
                        data.submenu = _.chain(data.submenu).map(function (value) {
                            value.selected = data.selected;
                            return value;
                        }).value();
                    }
                },
                selected_sub(data) {
                    let selected = _.chain(data.submenu).filter(function (menu) {
                        if (menu.selected) {

                            return menu
                        }
                    }).value();
                    if (selected.length == 0 ) {
                        data.selected = false;
                    }else{
                        data.selected = true;
                    }

                }
            }
        }
    </script>
@endsection

@include('partials.reset_form')
@include('partials.asset_datepicker')
