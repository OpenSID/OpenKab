<div class="grid grid-cols-1 container px-3 lg:px-5">
    <div class="bg-white p-4 rounded shadow-sm overflow-auto" style="min-height: 500px;">
        <canvas id="chart_posyandu"></canvas>
    </div>
</div>
@push('styles')
<style nonce="{{ csp_nonce() }}" >
#chart_posyandu{
    height: 450px;
    min-width: 800px;
}
</style>
@endpush
@push('js')
    <script nonce="{{ csp_nonce() }}">
        $(document).ready(function() {
            const ctx = document.getElementById('chart_posyandu').getContext('2d');
            
            // Process the series data to extract labels and datasets
            const seriesData = <?= json_encode($data['chartStuntingPosyanduData']['data']) ?>;
            const categories = <?= json_encode($data['chartStuntingPosyanduData']['categories']) ?>;
            
            // Extract labels from the first series if available
            let labels = categories;
            
            // Prepare datasets for Chart.js
            let datasets = [];
            if (seriesData && seriesData.length > 0) {
                seriesData.forEach((series, index) => {
                    const colors = [
                        'rgba(2, 142, 250, 0.8)',
                        'rgba(94, 228, 151, 0.8)',
                        'rgba(253, 17, 59, 0.8)'
                    ];
                    const borderColors = [
                        'rgba(2, 142, 250, 1)',
                        'rgba(94, 228, 151, 1)',
                        'rgba(253, 177, 59, 1)'
                    ];                    
                    datasets.push({
                        label: series.name || 'Series ' + (index + 1),
                        data: series.data || series,
                        backgroundColor: colors[index % colors.length],
                        borderColor: borderColors[index % colors.length],
                        borderWidth: 2,
                        borderRadius: 4,
                        borderSkipped: false,                                                                                      
                    });
                });
            } else {
                // Create an empty dataset to show an empty chart
                labels = ['Tidak Ada Data'];
                const colors = ['rgba(200, 200, 200, 0.8)'];
                const borderColors = ['rgba(200, 200, 200, 1)'];
                datasets.push({
                    label: 'Tidak Ada Data',
                    data: [0],
                    backgroundColor: colors,
                    borderColor: borderColors,
                    borderWidth: 2,
                    borderRadius: 4,
                    borderSkipped: false,
                });
            }
            
            const chart = new Chart(ctx, {
                plugins: [ChartDataLabels],
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    // Change to vertical bar chart (remove indexAxis to use default)
                    plugins: {
                        
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
                            position: 'nearest',
                            // Improve tooltip positioning and appearance
                            caretPadding: 5,
                            caretSize: 5,
                            cornerRadius: 6,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                },
                                title: function(tooltipItems) {
                                    // Customize the title of the tooltip
                                    return tooltipItems[0].label;
                                }
                            }
                        },
                        datalabels: {
                            display: true,
                            anchor: 'end',
                            align: 'top',
                            formatter: function(value, context) {
                                // Check if this is an empty data chart
                                const labels = context.chart.data.labels;
                                if (labels && labels.length === 1 && labels[0] === 'Tidak Ada Data') {
                                    return 'Tidak Ada Data'; // Show "Tidak Ada Data" for empty charts
                                }
                                return Math.round(value);
                            },
                            font: {
                                weight: 'bold',
                                size: 12
                            },
                            color: 'black'
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 90,
                                minRotation: 0,
                                font: {
                                    size: 11
                                },
                                // Ensure all labels are displayed
                                 autoSkip: true,
                                 autoSkipPadding: 20,         // ubah dari false → true
                                 maxTicksLimit: 25,
                            },
                            // Ensure enough space for labels
                            offset: true,                            
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Angka Kasus Stunting',
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            },
                            // Remove fixed min value to allow auto-scaling based on data range
                            // This will make the chart more proportional when values are small
                        }
                    },
                    animation: {
                        duration: 10,
                        easing: 'easeOutQuart'
                    }
                }
            });                        
        })
    </script>
@endpush        
