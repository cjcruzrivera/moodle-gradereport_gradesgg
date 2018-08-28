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

require_once('../../../config.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/grade/lib.php');

$courseid = required_param('id', PARAM_INT);
$userid   = optional_param('userid', $USER->id, PARAM_INT);

$PAGE->set_url(new moodle_url($CFG->wwwroot.'/grade/report/gradesgg/index.php', array('id' => $courseid)));
// Basic access checks.
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login($course);
$PAGE->set_pagelayout('report');

$context = context_course::instance($course->id);
require_capability('gradereport/gradesgg:view', $context);


print_grade_page_head($courseid, 'report', 'grader', get_string('pluginname', 'gradereport_gradesgg'), false, false, false);
// print_grade_page_head($courseid, 'settings', 'setup', get_string('gradebooksetup', 'grades'));

echo $OUTPUT->box_start('gradetreebox generalbox');

$tpldata = new stdClass();



echo $OUTPUT->render_from_template('gradereport_gradesgg/index', $tpldata);

echo $OUTPUT->box_end();

echo $OUTPUT->footer();
die;