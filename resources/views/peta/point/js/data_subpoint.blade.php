var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/subpoint/'.$point->id }}");
var point = $('#point').DataTable({
    processing: true,
    serverSide: true,
    autoWidth: false,
    ordering: true,
    searchPanes: {
        viewTotal: false,
        columns: [0]
    },
    ajax: {
        url: url,
        headers: header,
        method: 'get',
        data: function(row) {
            return {
                "page[size]": row.length,
                "page[number]": (row.start / row.length) + 1,
                "filter[search]": row.search.value,
                "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]
                    ?.name,
                "filter[status]": $("#status").val(),
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
        {
            targets: [0, 1, 2, 3],
            orderable: false,
            searchable: false,
        },
    ],
    columns: [{
            data: null,
        },
        {
            data: null,
            render: function(data, type, row) {
                return `<input type="checkbox" class="select-checkbox" data-id="${row.id}">`;
            },
        },
        {
            data: 'aksi',
            name: 'aksi',
            searchable: true,
            orderable: true
        },
        {
            data: 'nama',
            name: 'nama',
            searchable: true,
            orderable: true
        },
        {
            data: 'enabled',
            name: 'enabled',
            searchable: false,
            orderable: false,
            class: 'padat'
        },
        {
            data: 'path_simbol',
            name: 'path_simbol',
            searchable: false,
            orderable: false,
            class: 'padat'
        },
    ],
    order: [
        [3, 'asc']
    ]
})

point.on('draw.dt', function() {
    var PageInfo = $('#point').DataTable().page.info();
    point.column(0, {
        page: 'current'
    }).nodes().each(function(cell, i) {
        cell.innerHTML = i + 1 + PageInfo.start;
    });
});