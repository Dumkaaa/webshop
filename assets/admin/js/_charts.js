import ApexCharts from 'apexcharts';

(function () {
    /**
     * User log charts.
     */
    const userLogsChart = document.getElementById('chart-user-logs');
    if (userLogsChart) {
        new ApexCharts(userLogsChart, {
            chart: {
                type: 'bar',
                fontFamily: 'inherit',
                height: 100,
                sparkline: {
                    enabled: true
                },
            },
            series: [{
                name: 'Purchases',
                data: [3, 5, 4, 6, 7, 5, 6, 8, 24, 7, 12, 5, 6, 3, 8, 4, 14, 30, 17, 19, 15, 14, 25, 32, 25, 30, 20, 18, 32, 5]
            }],
            xaxis: {
                type: 'datetime',
            },
            labels: [
                '2020-06-20', '2020-06-21', '2020-06-22', '2020-06-23', '2020-06-24', '2020-06-25', '2020-06-26', '2020-06-27', '2020-06-28', '2020-06-29', '2020-06-30', '2020-07-01', '2020-07-02', '2020-07-03', '2020-07-04', '2020-07-05', '2020-07-06', '2020-07-07', '2020-07-08', '2020-07-09', '2020-07-10', '2020-07-11', '2020-07-12', '2020-07-13', '2020-07-14', '2020-07-15', '2020-07-16', '2020-07-17', '2020-07-18', '2020-07-19'
            ],
            colors: ['#206bc4'],
        }).render();
    }
})();