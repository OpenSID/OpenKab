<div class="col-4 card-chart">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Komposisi {{ $chart['text'] }}</h5>
        </div>
        <div class="card-body">
            <div id="pie-{{ $chart['key'] }}" data-key="{{ $chart['key'] }}" class="chart_content"
                data-url="{{ $statistikUrl }}?filter[id]={{ $chart['key'] }}" class="chart-container">
                <canvas id="donutChart-{{ $chart['key'] }}"></canvas>
            </div>
        </div>
        <div class="card-footer text-center">
            <a href="{{ $chart['url_detail'] }}?item_selected={{ $chart['key'] }}"
                target="_blank" rel="noopener noreferrer">Selengkapnya</a>
        </div>
    </div>
</div>
