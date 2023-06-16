@extends('layouts.index')

@section('title', 'Tambah Bantuan')

@section('content_header')
    <h1>Managemen Group</h1>
@stop

@section('content')
    <div class="row" x-data="group()" x-init="retrieveData()">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ url('pengaturan/groups') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-circle-left"></i></i>&ensp;Kembali ke Daftar Bantuan</a>
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
                            Simpan {{ isset($id)}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if(isset($id))
    sdfsd
        @include('group.edit_js', ['id' => $id])
    @else
        @include('group.create_js')
    @endif


@endsection

@include('partials.reset_form')
@include('partials.asset_datepicker')
