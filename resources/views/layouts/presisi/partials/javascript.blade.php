<!-- jQuery -->
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('assets/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script nonce="{{ csp_nonce() }}">
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<script src="{{ asset('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('assets/js/adminlte.js') }}"></script>

<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script src="{{ asset('assets/plugins/chart.js/chart.umd.js') }}"></script>
<script src="{{ asset('assets/plugins/chart.js/chartjs-plugin-datalabels.js') }}"></script>
<script src="{{ asset('assets/costume/js/admin.js') }}"></script>

<!-- Highcharts JS -->
<script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
<script src="{{ asset('vendor/moment/moment.js') }}"></script>
<script src="{{ asset('vendor/moment/id.js') }}"></script>
<script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>

<script nonce="{{ csp_nonce() }}">
    var selectedMenuObj = null;

    $('.item-menu').each(function(i, obj) {
        if ($(obj).attr('href') === window.location.pathname) {
            selectedMenuObj = obj;
        }
    });
    $(selectedMenuObj).addClass("active");
    if ($(selectedMenuObj).closest('.parent-dropdown-menu').length > 0) {
        if ($(selectedMenuObj).closest('.parent-dropdown-menu').find('.parent-menu').length > 0) {
            $(selectedMenuObj).closest('.parent-dropdown-menu').find('.parent-menu').addClass("active");
        }
    }

    $(document).on('select2:open', function(e) {
        // Pastikan ini adalah elemen Select2
        let $element = $(e.target);
        if ($element.hasClass('select2-hidden-accessible')) {
            let searchBox = document.querySelector(
                '.select2-container--open .select2-search__field');
            if (searchBox) {
                searchBox.focus();
            }
        }
    });
</script>
