_loading();
$(function () {


    setTimeout(function () {
        $.getJSON('/belum-cetak-pkwt', function (res) {
            const baseConfig = {
                chart: {
                    height: 150,
                    type: 'radialBar',
                    sparkline: { enabled: true },
                    parentHeightOffset: 0
                },
                series: [res.presentasi],
                plotOptions: {
                    radialBar: {
                        startAngle: -90,
                        endAngle: 90,
                        hollow: { size: '65%' },
                        track: {
                            strokeWidth: '45%',
                            background: config.colors.borderColor
                        },
                        dataLabels: {
                            name: {
                                show: false
                            },
                            value: {
                                fontSize: '20px',
                                color: config.colors.headingColor,
                                fontWeight: 500,
                                offsetY: -5
                            }
                        }
                    }
                },

                labels: ['Progress']
            };

            // Round
            new ApexCharts(document.querySelector("#chartRound"), {
                ...baseConfig,
                stroke: { lineCap: 'round' },
                colors: [config.colors.warning]
            }).render();

        })
        $.unblockUI();
    }, 300);

});
