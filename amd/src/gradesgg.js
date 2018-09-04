/** 
 * Index gradesgg javascript management
 * @module amd/src/gradesgg
 * @author Camilo José Cruz rivera
 * @copyright 2018 Camilo José Cruz Rivera <cruz.camilo@correounivalle.edu.co>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'gradereport_gradesgg/Chart', 'gradereport_gradesgg/jquery.dataTables'], function ($) {

    return {
        /**
         *
         */

        student: function (data) {
            $(document).ready(function () {
                console.log(data);
                var i;
                for (i = 0; i< data.length; i++) {
                    var category = data[i];
                    console.log('cat:');
                    console.log(category);
                    var id = 'graf_' + category.id;
                    console.log(id);
                    var items = category.items;
                    var grades = category.grades;
                    ChartInfo(id, items, grades);
                }
                // data.forEach(category => {
                // console.log('cat:');
                // console.log(category);
                // var id = 'graf_' + category.id;
                // console.log(id);
                // var items = category.items;
                // var grades = category.grades
                // ChartInfo(id, items, grades)
                // });
            });

            function ChartInfo(element, items, grades) {
                Chart.defaults.global.defaultFontFamily = "Lato";
                Chart.defaults.global.defaultFontSize = 18;
                Chart.defaults.global.defaultFontColor = "#333";
                var gradesData = {
                    label: 'Grades(notas)',
                    data: grades,
                    backgroundColor:
                        '#dcf8c6',

                    borderColor:
                        '#969696',

                    borderWidth: 2,
                    hoverBorderWidth: 0
                };

                var chartOptions = {
                    scales: {
                        yAxes: [{
                            barPercentage: 0.4
                        }],
                        xAxes: [{
                            ticks: {
                                beginAtZero: true,   // minimum value will be 0.
                                max: 5
                            }
                        }]
                    },
                    elements: {
                        rectangle: {
                            borderSkipped: 'left',
                        }
                    }
                };

                var barChart = new Chart(element, {
                    type: 'horizontalBar',
                    data: {
                        labels: items,
                        datasets: [gradesData],
                    },
                    options: chartOptions
                });
            }
        },
        teacher: function (data) {
            $("#div_curso").html('');
            $("#div_curso").fadeIn(1000).append('<table id="tableTotalUsers" class="table"' +
                ' cellspacing="0" width="100%"><thead> </thead></table>');

            $("#tableTotalUsers").DataTable(data);

            $(document).on('click', '#tableTotalUsers tbody tr td', function () {
                var pagina = "index.php";
                var table = $("#tableTotalUsers").DataTable();
                var colIndex = table.cell(this).index().column;

                if (colIndex <= 3) {
                    location.href = pagina + location.search + "&userid=" + table.cell(table.row(this).index(), 0).data();
                }
            });
        }
    };

});