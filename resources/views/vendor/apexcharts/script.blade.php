<script nonce="{{ csp_nonce() }}" type="text/javascript">
    var options = {!! $chart->getOptions() !!};

    var chart_{{ $chart->getId() }} = new ApexCharts(document.querySelector("#{!! $chart->getId() !!}"), options);

    chart_{{ $chart->getId() }}.render();
</script>
