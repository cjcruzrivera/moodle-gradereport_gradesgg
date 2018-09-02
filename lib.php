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
 * @return string html course info report
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
 * @return stdClassy html grade category info report
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
        $info->html_structure = "<div class = 'container' style='background:#daddfb'>
                    <h4><b>" . get_string('course', 'gradereport_gradesgg') . "  </h4><h2>$name_category</h2><hr></b>
                    <h4>". get_string('grade', 'gradereport_gradesgg')." $item_category_grade->finalgrade</h4>

                    <div class='grafica' id = 'graf_$id_category' > </div>
                </div> ";
    } else {
        $info->html_structure = "<div class = 'container' style='background:#daddfb'>
                   <div class = 'header'> <h4><b>" . get_string('category', 'gradereport_gradesgg') . "  </h4><h2>$name_category</h2></b>
                    <h4>". get_string('grade', 'gradereport_gradesgg')." $item_category_grade->finalgrade</h4></div><hr>

                    <div class='grafica' id = 'graf_$id_category' style='min-width: 310px; max-width: 800px; height: 400px; margin: 0 auto'> </div>
                </div> ";
    }

    return $info;
}
