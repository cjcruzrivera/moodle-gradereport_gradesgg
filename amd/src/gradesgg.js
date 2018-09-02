/**
 * Index javascript management
 * @module amd/src/gradesgg
 * @author Camilo José Cruz rivera
 * @copyright 2018 Camilo José Cruz Rivera <cruz.camilo@correounivalle.edu.co>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function () {

    return {
        /**
         *
         */

        student: function (data) {
            $(document).ready(function () {
                console.log(data);

                data.forEach(category => {
                    console.log('cat:');
                    console.log(category);
                });

                var id = 'graf_177';
                var items = ['Item 1', 'Item 2', 'Item 3', 'Item 4', 'Item n'];
                var grades = [2, 3.1, 'No calificado', 2.3, 2];

                Highcharts.chart(id, {
                    chart: {
                        type: 'bar'
                    },
                    title: {
                        text: ''
                    },
                    xAxis: {
                        categories: items,
                        title: {
                            text: 'items'
                        }
                    },
                    yAxis: {
                        min: 0,
                        max: 5,
                        title: {
                            text: 'grade (scale)',
                            align: 'medium'
                        },
                        labels: {
                            overflow: 'justify'
                        }
                    },
                    tooltip: {
                        valueSuffix: ''
                    },
                    plotOptions: {
                        bar: {
                            dataLabels: {
                                enabled: false
                            }
                        }
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'top',
                        x: -40,
                        y: 80,
                        floating: true,
                        borderWidth: 1,
                        backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                        shadow: true
                    },
                    credits: {
                        enabled: false
                    },
                    series: [{
                        name: 'Nota',
                        data: grades,
                    }]
                });
            });
        },
    };

});