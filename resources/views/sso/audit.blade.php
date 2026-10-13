@extends('layouts.index')

@section('title', 'Audit Akses SSO')

@section('content_header')
    <h1>Audit Akses SSO</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-3">
                            <a class="btn btn-sm btn-secondary" data-toggle="collapse" href="#collapse-filter" role="button"
                                aria-expanded="false" aria-controls="collapse-filter">
                                <i class="fas fa-filter"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @include('sso.partials.filter')
                    <div class="table-responsive">
                        <table class="table table-striped" id="sso-audit" data-testid="datatable-sso-audit">
                            <thead>
                                <tr>
                                    <th class="padat">No</th>
                                    <th>Waktu</th>
                                    <th>Administrator</th>
                                    <th>Desa</th>
                                    <th>Status</th>
                                    <th>Alasan</th>
                                    <th>IP</th>
                                    <th>Perangkat</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('partials.asset_datepicker')

@section('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            let awalBulan = '{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}'
            let akhirBulan = '{{ \Carbon\Carbon::now()->endOfMonth()->format('d-m-Y') }}'

            var ssoAudit = $('#sso-audit').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ordering: true,
                ajax: {
                    url: "{{ route('sso.audit') }}",
                    method: 'get',
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[created_at]": [$('input[name=start]').data('daterangepicker').startDate.format('YYYY-MM-DD'), $('input[name=start]').data('daterangepicker').endDate.format('YYYY-MM-DD')],
                            "filter[status]": $('select[name=status]').val(),
                            "filter[admin_id]": $('select[name=admin_id]').val(),
                            "filter[desa_id]": $('input[name=desa_id]').val(),
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
                columns: [{
                        data: null,
                        searchable: false,
                        orderable: false,
                    },
                    {
                        data: "attributes.attempt_time",
                        name: 'attempt_time'
                    },
                    {
                        data: "attributes.admin_name",
                        name: 'admin_name',
                        defaultContent: '-',
                    },
                    {
                        data: "attributes.desa_id",
                        name: 'desa_id',
                    },
                    {
                        data: "attributes.status",
                        name: 'status',
                        render: function(data, type, row) {
                            if (data === 'success') {
                                return '<span class="badge badge-success">Berhasil</span>';
                            }
                            return '<span class="badge badge-danger">Gagal</span>';
                        },
                    },
                    {
                        data: "attributes.reason_if_failed",
                        name: 'reason_if_failed',
                        defaultContent: '-',
                    },
                    {
                        data: "attributes.ip_address",
                        name: 'ip_address',
                    },
                    {
                        data: "attributes.user_agent",
                        name: 'user_agent',
                        defaultContent: '-',
                    },
                ],
                columnDefs: [{
                    targets: [0],
                    orderable: false,
                    searchable: false,
                }],
            });

            ssoAudit.on('draw.dt', function() {
                var PageInfo = $('#sso-audit').DataTable().page.info();
                ssoAudit.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + PageInfo.start;
                });
            });

            $('#filter').on('click', function(e) {
                ssoAudit.draw();
            });

            $(document).on('click', '#reset', function(e) {
                e.preventDefault();
                $('input[name=start]').data('daterangepicker').setStartDate(awalBulan);
                $('input[name=start]').data('daterangepicker').setEndDate(akhirBulan);
                $('select[name=status]').val('').change();
                $('select[name=admin_id]').val('').change();
                $('input[name=desa_id]').val('');
                ssoAudit.draw();
            });
        });
    </script>
@endsection
