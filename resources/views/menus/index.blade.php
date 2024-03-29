@extends('layouts.index')

@section('content_header')
    <h1>Data Menu</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')

    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                @include('adminlte-templates::common.alerts')
                <div class="card card-outline card-primary">
                    <div class="card-body">
                        {!! Form::open(['route' => 'menus.store', 'id' => 'frmEdit']) !!}
                        <div class="row">
                            <div class="col-5">
                                <div class="card card-default">
                                    <div class="card-header">Sumber Menu URL</div>
                                    <div class="card-body">
                                        @include('menus.fields')
                                    </div>
                                    @if ($canwrite)
                                    <div class="card-footer">
                                        {!! Form::button('<i class="fas fa-save"></i> Simpan', ['type' => 'button', 'class' => 'btn btn-primary btn-sm', 'id' => 'btnUpdate'] )  !!}
                                        {!! Form::button('<i class="fas fa-plus-square"></i> Tambah', ['type' => 'button', 'class' => 'btn btn-success btn-sm', 'id' => 'btnAdd'] )  !!}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="card card-default">
                                    <div class="card-header">Struktur Menu</div>
                                    <div class="card-body">
                                        <ul id="myEditor" class="sortableLists list-group"></ul>
                                        <div class="hide">
                                            {!! Form::textarea('json_menu', null, ['hidden', 'rows' => 1]) !!}
                                        </div>
                                    </div>
                                    @if ($canwrite)
                                    <div class="card-footer">
                                        {!! Form::button('<i class="fas fa-times"></i> Batal', ['type' => 'button', 'class' => 'btn btn-danger btn-sm reload'] )  !!}
                                        {!! Form::button('<i class="fas fa-save"></i> Simpan', ['type' => 'submit', 'class' => 'btn btn-primary btn-sm'] )  !!}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script defer src="{{ asset('vendor/menu-editor/bootstrap-iconpicker.min.js') }}"></script>
<script defer src="{{ asset('vendor/menu-editor/jquery-menu-editor.js') }}"></script>
    <script nonce="{{  csp_nonce() }}">
	document.addEventListener("DOMContentLoaded", function(event) {
                var arrayjson = {!! $menus !!}
                // icon picker options
                var iconPickerOptions = {
                    searchText: "Buscar...",
                    labelHeader: "{0}/{1}"
                };
                // sortable list options
                var sortableListOptions = {
                    placeholderCss: {
                        'background-color': "#cccccc"
                    }
                };

                var editor = new MenuEditor('myEditor', {
                    listOptions: sortableListOptions,
                    iconPicker: iconPickerOptions
                });
                editor.setForm($('#frmEdit'));
                editor.setUpdateButton($('#btnUpdate'));
                editor.setData(arrayjson);

                $('#btnOutput').on('click', function () {
                    var str = editor.getString();
                    $("#out").text(str);
                });

                $("#btnUpdate").click(function () {
                    editor.update();
                });

                $('#btnAdd').click(function () {
                    editor.add();
                });

                $('#frmEdit').bind('reset', function(e){
                    $('select[name=sourcelist]').hide()
                    $('input[name=href]').show()
                })
                $('#frmEdit').submit(function(e){
                    var str = editor.getString();

                    if (str == '[]') {
                        e.preventDefault();
                        return;
                    }
                    $('#frmEdit').find('textarea[name=json_menu]').val(str)
                    return true;
                })

                $('button.reload').click(function(){
                    window.location.reload()
                })
    });
    </script>
@endsection
