@extends('layouts.index')

@section('content_header')
    <h1>Data Menu</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')

    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                @include('common.alerts')
                <div class="card card-outline card-primary">
                    <div class="card-body">
                        {!! Html::form('POST', route('menus.store'))->id('frmEdit')->open() !!}
                        <div class="row">
                            <div class="col-5">
                                <div class="card card-default">
                                    <div class="card-header">Sumber Menu URL</div>
                                    <div class="card-body">
                                        @include('menus.fields')
                                    </div>
                                    @if ($canwrite)
                                        <div class="card-footer">
                                            {!! Html::button('<i class="fas fa-save"></i> Simpan')->type('button')->class('btn btn-primary btn-sm')->id('btnUpdate') !!}
                                            {!! Html::button('<i class="fas fa-plus-square"></i> Tambah')->type('button')->class('btn btn-success btn-sm')->id('btnAdd') !!}
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
                                            {!! Html::textarea('json_menu')->attribute('hidden')->attribute('rows', 1) !!}
                                        </div>
                                    </div>
                                    @if ($canwrite)
                                        <div class="card-footer">
                                            {!! Html::button('<i class="fas fa-times"></i> Batal')->type('button')->class('btn btn-danger btn-sm reload') !!}
                                            {!! Html::button('<i class="fas fa-save"></i> Simpan')->type('submit')->class('btn btn-primary btn-sm') !!}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        {!! Html::form()->close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script defer src="{{ asset('vendor/menu-editor/bootstrap-iconpicker.min.js') }}"></script>
    <script defer src="{{ asset('vendor/menu-editor/jquery-menu-editor.js') }}"></script>
    <script nonce="{{ csp_nonce() }}">
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

            $('#btnOutput').on('click', function() {
                var str = editor.getString();
                $("#out").text(str);
            });

            $("#btnUpdate").click(function() {
                editor.update();
                const queryString = window.location.search;
                const urlParams = new URLSearchParams(queryString);
                const menutype = urlParams.get('type')
                $('select[name=menu_type]').val((menutype == null ? 1 : menutype));
            });

            $('#btnAdd').click(function() {
                editor.add();
                $('select[name=penduduk]').hide()
                $('select[name=keluarga]').hide()
                $('select[name=bantuan]').hide()
                $('select[name=rtm]').hide()
                $('select[name=kesehatan]').hide()
                const queryString = window.location.search;
                const urlParams = new URLSearchParams(queryString);
                const menutype = urlParams.get('type')
                $('select[name=menu_type]').val((menutype == null ? 1 : menutype));
            });

            $('#frmEdit').bind('reset', function(e) {
                $('select[name=sourcelist]').hide()
                $('select[name=penduduk]').hide()
                $('select[name=keluarga]').hide()
                $('select[name=bantuan]').hide()
                $('select[name=rtm]').hide()
                $('select[name=kesehatan]').hide()
                $('input[name=href]').show()
            })
            $('#frmEdit').submit(function(e) {
                var str = editor.getString()
                if (str == '[]') {
                    e.preventDefault();
                    return;
                }
                $('#frmEdit').find('textarea[name=json_menu]').val(str)
                return true;
            })

            $('button.reload').click(function() {
                window.location.reload()
            })
        });
    </script>
@endsection
