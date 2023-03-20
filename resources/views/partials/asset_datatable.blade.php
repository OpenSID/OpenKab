@push('page_css')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/datatable/css/dataTables.bootstrap4.min.css') }}">
@endpush

@push('page_scripts')
    <!-- DataTables JS-->
    <script type="module" src="{{ asset('vendor/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script type="module" src="{{ asset('vendor/datatable/js/dataTables.bootstrap4.min.js') }}"></script>
    <script type="module">
        $(function () {
            $('#example1').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": true,
                "responsive": true,
            });
        });
        // $.extend($.fn.dataTable.defaults, {
        //     responsive: true,
        //     processing: true,
        //     serverSide: false,
        //     autoWidth: false,
        //     lengthMenu: [
        //         [10, 25, 50, 100, -1],
        //         [10, 25, 50, 100, "Semua"]
        //     ],
        //     pageLength: 10,
        //     language: {
        //         url: "{{ asset('vendor/datatable/dataTables.indonesian.lang') }}",
        //     }
        // });
    </script>
@endpush
