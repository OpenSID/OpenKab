<div class="row mt-4">
    <div class="col-md-4 col-sm-12 mb-4">
        <div class="bg-white p-3 rounded shadow-sm">
            <canvas id="chart_0_5"></canvas>
        </div>
    </div>
    <div class="col-md-4 col-sm-12 mb-4">
        <div class="bg-white p-3 rounded shadow-sm">
            <canvas id="chart_6_11"></canvas>
        </div>
    </div>
    <div class="col-md-4 col-sm-12 mb-4">
        <div class="bg-white p-3 rounded shadow-sm">
            <canvas id="chart_12_23"></canvas>
        </div>
    </div>
</div>

@push('js')
    <script nonce="{{ csp_nonce() }}">
        $(document).ready(function() {            
            @foreach ($data['chartStuntingUmurData'] as $item)
                (function() {
                    const ctx = document.getElementById('{{ $item['id'] }}').getContext('2d');
                    
                    // Process the data for Chart.js
                    const chartData = {!! json_encode($item['data']) !!};
                    
                    // Extract labels and values
                    let labels = [];
                    let values = [];
                    let backgroundColors = [];
                    
                    // Check if chartData is not empty
                    if (chartData && chartData.length > 0) {
                        chartData.forEach(point => {
                            if (Array.isArray(point)) {
                                labels.push(point[0]);
                                values.push(point[1]);
                            } else if (typeof point === 'object' && point.name && point.y) {
                                labels.push(point.name);
                                values.push(point.y);
                            }
                        });
                    } 
                    if(!labels.length){
                        // Set default values for empty data
                        labels = ['Tidak Ada Data'];
                    }
                    if(!values.length){
                        values = [1];
                    }
                    
                    // Set colors for pie chart
                    const colors = [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(25, 99, 132, 0.8)',
                        'rgba(25, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ];
                    const borderColors = [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ];
                    
                    // Use gray color for empty data
                    let borderColorsArray = [];
                    if (values.length === 1 && values[0] === 1 && labels[0] === 'Tidak Ada Data') {
                        backgroundColors = ['rgba(200, 200, 200, 0.8)'];
                        borderColorsArray = ['rgba(200, 200, 200, 1)'];
                    } else {
                        backgroundColors = values.map((_, index) => colors[index % colors.length]);
                        borderColorsArray = values.map((_, index) => borderColors[index % borderColors.length]);
                    }
                    
                    const chart = new Chart(ctx, {
                        plugins: [ChartDataLabels],
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: values,
                                backgroundColor: backgroundColors,
                                borderColor: borderColorsArray,
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: true,
                                    text: '{{ $item['title'] }}',
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    },
                                    padding: {
                                        top: 10,
                                        bottom: 15
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleFont: {
                                        size: 14
                                    },
                                    bodyFont: {
                                        size: 13
                                    },
                                    padding: 10,
                                    usePointStyle: true,
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.parsed;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = Math.round((value / total) * 100);
                                            return label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                },
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        usePointStyle: true,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                datalabels: {
                                    display: true,
                                    formatter: (value, ctx) => {
                                        // Check if this is an empty data chart
                                        const labels = ctx.chart.data.labels;
                                        if (labels.length === 1 && labels[0] === 'Tidak Ada Data') {
                                            return 'Tidak Ada Data'; // Show "Tidak Ada Data" for empty charts
                                        }
                                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return percentage + '%'; // Show percentage on the chart
                                    },
                                    color: '#fff',
                                    font: {
                                        weight: 'bold',
                                        size: 12
                                    },
                                    anchor: 'top',
                                    align: 'end'
                                }
                            },                            
                            animation: {
                                animateRotate: true,
                                animateScale: false,
                                duration: 800,
                                easing: 'easeOutQuart'
                            }
                        }
                    });                                        
                })();
            @endforeach
        })
    </script>
@endpush
