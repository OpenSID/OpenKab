@@extends('layouts.index')

@@section('content')
    @@include('partials.breadcrumbs')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <a
                       href="{{ '{{' }} route('{!! $config->prefixes->getRoutePrefixWith('.') !!}{!! $config->modelNames->camelPlural !!}.create') }} ">
@if($config->options->localized)
                         @@lang('crud.add_new')
@else
        <button type="button" class="btn btn-primary btn-sm"><i class="far fa-plus-square"></i> Tambah</button>
@endif
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @@include('flash::message')

        <div class="clearfix"></div>

        <div class="card">
            {!! $table !!}
        </div>
    </div>

@@endsection

@@section('js')
    <script>
            $(function() {
                let {{ $config->modelNames->dashedPlural }} = $('#{{ $config->modelNames->dashedPlural }}-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ordering: true,
                searchPanes: {
                    viewTotal: false,
                    columns: [0]
                },
                ajax: {
                    url: `{{ '{{ ' }}route('{{ $config->prefixes->getRoutePrefixWith('.') }}{{ $config->modelNames->camelPlural }}.index'){!! ' }}' !!}`,
                    method: 'get',
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[search]": row.search.value,
                            "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]
                                ?.name,
                        };
                    },
                    dataSrc: function(json) {
                        json.recordsTotal = json.meta.pagination.total
                        json.recordsFiltered = json.meta.pagination.total

                        return json.data
                    },
                },
                columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap',
                    },
                ],
                columns: [{
                        data: null,
                    },
                    @foreach ($config->fields as $field)
                    @if ( $config->primaryName == $field->name ) @continue; @endif
                    {
                        data: "attributes.{{ $field->name }}",
                        name: "{{ $field->name }}"
                    },
                    @endforeach
                    {
                        data: function(data) {
                            return `
                                    <a href="{{ '{{ ' }}route('{{ $config->prefixes->getRoutePrefixWith('.') }}{{ $config->modelNames->camelPlural }}.index'){!! ' }}' !!}/${data.id}/edit">
                                        <button type="button" class="btn btn-warning btn-sm edit" title="Ubah">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </a>

                                    <button type="button" class="btn btn-danger btn-sm hapus" data-id="${data.id}" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    `;
                        },
                    },
                ],
                order: [
                    [0, 'asc']
                ]
            })

            {{ $config->modelNames->dashedPlural }}.on('draw.dt', function() {
                var PageInfo = $('#{{ $config->modelNames->dashedPlural }}-table').DataTable().page.info();
                {{ $config->modelNames->dashedPlural }}.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + PageInfo.start;
                });
            });

                $(document).on('click', 'button.hapus', function() {
                    var id = $(this).data('id')
                    var that = $(this);
                    Swal.fire({
                        title: 'Hapus',
                        text: "Apakah anda yakin menghapus data ini?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Hapus'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Menyimpan',
                                didOpen: () => {
                                    Swal.showLoading()
                                },
                            })
                            $.ajax({
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType: "json",
                                url: `{{ '{{ ' }}route('{{ $config->prefixes->getRoutePrefixWith('.') }}{{ $config->modelNames->camelPlural }}.index'){!! ' }}' !!}/${id}`,
                                data: {
                                    id: id
                                },
                                success: function(response) {

                                    if (response.success == true) {
                                        Swal.fire(
                                            'Hapus!',
                                            'Data berhasil dihapus',
                                            'success'
                                        )
                                        that.parent().parent().remove();
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
                                        thrownError,
                                        'error'
                                    )

                                }
                            });
                        }
                    })
                });
            });

    </script>
@@endsection
