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
 * Grades grafics generator (gradesgg) gradebook report
 *
 * @author     Camilo José Cruz Rivera
 * @package    gradereport_gradesgg
 * @copyright  2018 Camilo José Cruz Rivera <cruz.camilo@correounivalle.edu.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../config.php';
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/grade/lib.php';

require_once 'lib.php';

$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

$PAGE->requires->css('/grade/report/gradesgg/styles/bootstrap.min.css', true);
$PAGE->requires->css('/grade/report/gradesgg/styles/styles.css', true);
$PAGE->requires->css('/grade/report/gradesgg/styles/jquery.dataTables.min.css', true);

$PAGE->requires->jquery();

$PAGE->requires->js('/grade/report/gradesgg/js/highcharts.js');
$PAGE->requires->js('/grade/report/gradesgg/js/exporting.js');
$PAGE->requires->js('/grade/report/gradesgg/js/export-data.js');

$url = new moodle_url($CFG->wwwroot . '/grade/report/gradesgg/index.php', array('id' => $courseid));
$PAGE->set_url($url);
// Basic access checks.
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login($course);
$PAGE->set_pagelayout('report');

$context = context_course::instance($course->id);
require_capability('gradereport/gradesgg:view', $context);

print_grade_page_head($courseid, 'report', 'gradesgg', get_string('title', 'gradereport_gradesgg'), false, false, 'grades');

echo $OUTPUT->box_start('gradetreebox generalbox');

$tpldata = new stdClass();

if (isTeacher($context, $USER)) {
    $title = get_string('teacher', 'gradereport_gradesgg');

    if ($userid == 0) {

        $info_teacher = generate_teacher_info($courseid);
        $tpldata->info_teacher = $info_teacher->html_structure;
        $tableTotalsStudents = $info_teacher->tableStudents;
        $paramsTotalsStudents = new stdClass();
        $paramsTotalsStudents->table = $tableTotalsStudents;
        $PAGE->requires->js_call_amd('gradereport_gradesgg/gradesgg', 'teacher', $paramsTotalsStudents);

    } else {
        $title = get_string('student', 'gradereport_gradesgg');
        $data_obj = generate_student_info($courseid, $userid);
        $tpldata->info_student = $data_obj->html_structure;
        $amd_obj = new stdClass();
        $amd_obj->categories = $data_obj->categories;
        $PAGE->requires->js_call_amd('gradereport_gradesgg/gradesgg', 'student', $amd_obj);
        $student = $DB->get_record('user', array("id" => $userid));
        $tpldata->title = $title . " " . $student->firstname . ' ' . $student->lastname;
        $tpldata->info_teacher = "<div class='back'><a  href='$url'>Volver a listado de estudiantes</a></div>";
    }
} else {
    $title = get_string('student', 'gradereport_gradesgg');
    $data_obj = generate_student_info($courseid, $USER->id);
    $tpldata->info_student = $data_obj->html_structure;
    $amd_obj = new stdClass();
    $amd_obj->categories = $data_obj->categories;
    $PAGE->requires->js_call_amd('gradereport_gradesgg/gradesgg', 'student', $amd_obj);
    $tpldata->title = $title . " " . $USER->firstname . ' ' . $USER->lastname;
}

echo $OUTPUT->render_from_template('gradereport_gradesgg/index', $tpldata);

echo $OUTPUT->box_end();

echo $OUTPUT->footer();
die;
