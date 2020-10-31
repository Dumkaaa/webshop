import ApexCharts from 'apexcharts';

(function () {
    /**
     * Action log charts.
     */
    const actionLogsChart = document.getElementById('chart-action-logs');
    if (actionLogsChart) {
        new ApexCharts(actionLogsChart, {
            chart: {
                type: 'bar',
                fontFamily: 'inherit',
                height: actionLogsChart.dataset.height,
                parentHeightOffset: 0,
                toolbar: {
                    show: false,
                },
                sparkline: {
                    enabled: true,
                },
                animations: {
                    enabled: false,
                },
                stacked: true,
            },
            grid: {
                show: true,
                padding: {
                    top: 40,
                },
                strokeDashArray: 2,
                xaxis: {
                    lines: {
                        show: true,
                    },
                },
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'right',
                markers: {
                    width: 8,
                    height: 8,
                    radius: 100,
                },
            },
            series: [{
                name: actionLogsChart.dataset.createLabel,
                data: JSON.parse(actionLogsChart.dataset.createBar),
            }, {
                name: actionLogsChart.dataset.editLabel,
                data: JSON.parse(actionLogsChart.dataset.editBar),
            }, {
                name: actionLogsChart.dataset.deleteLabel,
                data: JSON.parse(actionLogsChart.dataset.deleteBar),
            }],
            plotOptions: {
                bar: {
                    columnWidth: '50%',
                }
            },
            dataLabels: {
                enabled: false,
            },
            yaxis: {
                labels: {
                    // Fix float to integer.
                    formatter: function(val) {
                        return Math.floor(val);
                    },
                },
            },
            labels: JSON.parse(actionLogsChart.dataset.labels),
            colors: ['#bfe399', '#a6c4e7', '#eba6a5'],
        }).render();
    }
})();