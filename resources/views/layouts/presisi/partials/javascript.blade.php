
<!-- jQuery -->
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('assets/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<script src="{{ asset('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('assets/js/adminlte.js') }}"></script>

<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('assets/costume/js/admin.js') }}"></script>

<!-- Highcharts JS -->
<script src="{{ asset('assets/js/highcharts/highcharts.js') }}"></script>
<script src="{{ asset('assets/js/highcharts/highcharts-3d.js') }}"></script>
<script src="{{ asset('assets/js/highcharts/exporting.js') }}"></script>
<script src="{{ asset('assets/js/highcharts/highcharts-more.js') }}"></script>
<script src="{{ asset('assets/js/highcharts/sankey.js') }}"></script>
<script src="{{ asset('assets/js/highcharts/organization.js') }}"></script>
<script src="{{ asset('assets/js/highcharts/accessibility.js') }}"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
<script>
    var selectedMenuObj = null;

    $('.item-menu').each(function(i, obj) {
        if ($(obj).attr('href') ===window.location.pathname){
            selectedMenuObj = obj;
        }
    });
    $(selectedMenuObj).addClass("active");
    if ($(selectedMenuObj).closest('.parent-dropdown-menu').length > 0){
        if ($(selectedMenuObj).closest('.parent-dropdown-menu').find('.parent-menu').length > 0){
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