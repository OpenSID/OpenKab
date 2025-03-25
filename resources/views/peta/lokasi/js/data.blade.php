const header = @include('layouts.components.header_bearer_api_gabungan');
var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/plan' }}");
var point = $('#plan').DataTable({
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
            "filter[point]": $("#point").val(),
            "filter[subpoint]": $("#subpoint").val(),
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
        data: 'aksi',
        name: 'aksi',
        searchable: false,
        orderable: false
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
        orderable: true
    },
    {
        data: 'jenis',
        name: 'jenis',
        searchable: false,
        orderable: false
    },
    {
        data: 'kategori',
        name: 'kategori',
        searchable: false,
        orderable: false
    },
],
order: [
    [2, 'asc']
]
})

point.on('draw.dt', function() {
var PageInfo = $('#plan').DataTable().page.info();
point.column(0, {
    page: 'current'
}).nodes().each(function(cell, i) {
    cell.innerHTML = i + 1 + PageInfo.start;
});
});