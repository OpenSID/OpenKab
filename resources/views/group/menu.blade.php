<div class="container-fluid" x-data="menu()" x-init="retrieveData()">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    {!! Html::form()->id('frmEdit')->open() !!}
                    <div class="row">
                        <div class="col-5">
                            <div class="card card-default">
                                <div class="card-header">Sumber Menu URL</div>
                                <div class="card-body">
                                    @include('group.fields')
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

@push('js')
    <script defer src="{{ asset('vendor/menu-editor/bootstrap-iconpicker.min.js') }}"></script>
    <script defer src="{{ asset('vendor/menu-editor/jquery-menu-editor.js') }}"></script>
    <script nonce="{{ csp_nonce() }}">
        const header = @include('layouts.components.header_bearer_api_gabungan');
        const menu = () => {
            const retrieveData = () => {
                fetch('{{ url('api/v1/pengaturan/group/listModul/' . $id) }}', {
                        headers: header
                    })
                    .then(res => res.json())
                    .then(response => {
                        buildEditor(response.data['menu'])
                        buildListModul(response.data['modul'])
                    });
            }
            const buildListModul = (modul) => {
                let listModul = document.querySelector('select[name=sourcelist]')
                listModul.innerHTML = ''
                for (let i in modul) {
                    let option = document.createElement('option')
                    option.value = i
                    option.text = modul[i]
                    listModul.appendChild(option)
                }
            }

            const buildEditor = (arrayjson) => {
                // icon picker options
                let iconPickerOptions = {
                    searchText: "Buscar...",
                    labelHeader: "{0}/{1}"
                };
                // sortable list options
                let sortableListOptions = {
                    placeholderCss: {
                        'background-color': "#cccccc"
                    }
                };

                let editor = new MenuEditor('myEditor', {
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
                });

                $('#btnAdd').click(function() {
                    editor.add();
                });

                $('#frmEdit').bind('reset', function(e) {
                    $('select[name=sourcelist]').hide()
                    $('input[name=href]').show()
                })
                $('#frmEdit').submit(function(e) {
                    e.preventDefault();
                    var str = editor.getString()
                    if (str == '[]') {
                        Swal.fire(
                            'Error!  ',
                            'Belum ada menu yang ditambahkan',
                            'error'
                        )
                        return;
                    }
                    $('#frmEdit').find('textarea[name=json_menu]').val(str)
                    $.ajax({
                        type: "PUT",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Authorization': 'Bearer {{ $settingAplikasi->get('database_gabungan_api_key') }}'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Menyimpan',
                                didOpen: () => {
                                    Swal.showLoading()
                                },
                            })
                        },
                        url: '{{ url('api/v1/pengaturan/group/updateMenu/' . $id) }}',
                        data: {
                            menu_order: str
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.success == true) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Data berhasil ditambahkan',
                                    showConfirmButton: false,
                                    timer: 1500
                                })
                                window.location.replace("{{ url('pengaturan/groups') }}");
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
                })

                $('button.reload').click(function() {
                    window.location.reload()
                })
            }
            return {
                retrieveData,
                buildEditor
            }
        }
    </script>
@endpush
