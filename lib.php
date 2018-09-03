<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Grafics Grades Generator Lib
 *
 * @author     Camilo José Cruz Rivera
 * @package    custom_grader
 * @copyright  2018 Camilo José Cruz Rivera <cruz.camilo@correounivalle.edu.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once $CFG->libdir . '/gradelib.php';

/**
 * This function validate if a user is teacher or not in a course
 * @see isTeacher($context, $USER)
 * @param stdClass $context course context
 * @param stdClass $user
 * @return boolean True if user is teacher false if not
 */

function isTeacher($context, $USER)
{
    $roles = get_user_roles($context, $USER->id);
    foreach ($roles as $rol) {
        if ($rol->shortname == 'editingteacher' || $rol->shortname == 'teacher' || $rol->shortname == 'manager') {
            return true;
        }
    }
    return false;
}

/**
 * This function generate student report
 * @see generate_student_info($courseid, $userid)
 * @param integer $courseid
 * @param integer $userid
 * @return stdClass course info report
 */
function generate_student_info($courseid, $userid)
{
    $info = new stdClass();
    $info->html_structure = '';

    $categories = grade_category::fetch_all(array('courseid' => $courseid));

    $categories_to_graf = array();

    foreach ($categories as $category) {
        $info_category = generate_category_student_info($category, $userid);
        $info->html_structure .= "$info_category->html_structure <br>";
        array_push($categories_to_graf, $info_category->category);
    }

    $info->categories = $categories_to_graf;
    return $info;
}

/**
 * This function generate grade category info student report
 * @see generate_category_student_info($category, $userid)
 * @param stdClass $category
 * @param integer $userid
 * @return stdClass grade category info report
 */

function generate_category_student_info($category, $userid)
{
    $name_category = $category->get_name();
    $id_category = $category->id;
    $info = new stdClass();

    $info->category = new stdClass();
    $info->category->id = $id_category;
    $info->category->items = array();
    $info->category->grades = array();

    $items = grade_item::fetch_all(array('categoryid' => $id_category));

    if (is_array($items) || is_object($items)) {
        foreach ($items as $item) {
            array_push($info->category->items, $item->get_name());
            $grade = $item->get_grade($userid);
            if ($grade->finalgrade != '') {
                $finalgrade = $grade->finalgrade;
            } else {
                $finalgrade = get_string('ungraded', 'gradereport_gradesgg');
            }

            array_push($info->category->grades, $finalgrade);
        }
    }

    $item_category_grade = $category->load_grade_item()->get_grade($userid);

    if ($item_category_grade->finalgrade == '') {
        $item_category_grade->finalgrade = get_string('ungraded', 'gradereport_gradesgg');
    }

    if ($category->is_course_category()) {
        $info->html_structure = "<div class = 'container grafics'>
                    <h4><b>" . get_string('course', 'gradereport_gradesgg') . "  </h4><h2>$name_category</h2><hr></b>
                    <h4 id='grade'>" . get_string('grade', 'gradereport_gradesgg') . " $item_category_grade->finalgrade</h4>

                    <div class='grafica' id = 'graf_$id_category' > </div>
                </div> ";
    } else {
        $info->html_structure = "<div class = 'container grafics'>
                   <div class = 'header'> <h4><b>" . get_string('category', 'gradereport_gradesgg') . "  </h4><h2>$name_category</h2></b>
                    <h4 id='grade'>" . get_string('grade', 'gradereport_gradesgg') . " $item_category_grade->finalgrade</h4></div><hr>
                    <canvas id='graf_$id_category' width='600' height='400'></canvas>
                </div> ";
    }

    return $info;
}

/**
 * This function generate teacher report
 * @see generate_teacher_info($courseid)
 * @param integer $courseid
 * @return stdClass course info report
 */
function generate_teacher_info($courseid)
{
    global $CFG;
    $url = new moodle_url($CFG->wwwroot . '/user/index.php', array('id' => $courseid));

    $info = new stdClass();

    $info->html_structure = "<div class= 'well well-medium'>
                                <h3 style='margin-top:10px;'>Se muestran los estudiantes matriculados en el curso.
                                Para consultar las graficas de cada uno,
                                hacer click en el nombre del estudiante. <a href='$url' target='_blank'>Para gestionar los usuarios matriculados en el curso hacer click aqui </a></h3>
                            </div>
                            <div id='div_curso'>
                                <img id='loading' src='static/loading.gif'>
                            </div>";
    $info->tableStudents = get_datatable_students_by_course($courseid);

    return $info;
}

/**
 * This function generate teacher datatable report
 * @see get_datatable_students_by_course($courseid)
 * @param integer $courseid
 * @return stdClass course datatable info report
 */
function get_datatable_students_by_course($courseid)
{

    $default_students = $columns = array();
    array_push($columns, array("title" => "Id", "name" => "id", "data" => "id", "class" => "hidden"));
    array_push($columns, array("title" => "Nombre", "name" => "firstname", "data" => "firstname"));
    array_push($columns, array("title" => "Apellido", "name" => "lastname", "data" => "lastname"));
    array_push($columns, array("title" => "Email", "name" => "email", "data" => "email"));

    $default_students = get_all_students($courseid);

    $data_to_table = array(
        "bsort" => false,
        "data" => $default_students,
        "columns" => $columns,
        "select" => "false",
        "fixedHeader" => array(
            "header" => true,
            "footer" => true,
        ),
        "language" => array(
            "search" => "Buscar:",
            "oPaginate" => array(
                "sFirst" => "Primero",
                "sLast" => "Último",
                "sNext" => "Siguiente",
                "sPrevious" => "Anterior",
            ),
            "sProcessing" => "Procesando...",
            "sLengthMenu" => "Mostrar _MENU_ registros",
            "sZeroRecords" => "No se encontraron resultados",
            "sEmptyTable" => "Ningún dato disponible en esta tabla",
            "sInfo" => "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty" => "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered" => "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix" => "",
            "sSearch" => "Buscar:",
            "sUrl" => "",
            "sInfoThousands" => ",",
            "sLoadingRecords" => "Cargando...",
            "oAria" => array(
                "sSortAscending" => ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending" => ": Activar para ordenar la columna de manera descendente",
            ),
        ),
        "autoFill" => "true",
        "dom" => "lfrtBip",
        "buttons" => array(
            array(
                "extend" => 'print',
                "filename" => 'Export pdf',
                "text" => 'Imprimir',
            ),
            array(
                "extend" => 'csvHtml5',
                "filename" => 'Export csv',
                "text" => 'CSV',
            ),
            array(
                "extend" => "excel",
                "text" => 'Excel',
                "className" => 'buttons-excel',
                "filename" => 'Export excel',
                "extension" => '.xls',
            ),
        ),
    );

    return $data_to_table;

}

/**
 * This function generate teacher datatable report
 * @see get_all_students($courseid)
 * @param integer $courseid
 * @return stdClass course users info report
 */
function get_all_students($courseid)
{

    $array_students = array();

    $context = context_course::instance($courseid);

    $users = get_enrolled_users($context);

    foreach ($users as $student) {
        array_push($array_students, $student);
    }

    return $array_students;

}
