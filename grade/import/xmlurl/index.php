<?php  //$Id$

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.com                                            //
//                                                                       //
// Copyright (C) 1999 onwards  Martin Dougiamas  http://moodle.com       //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once 'grade_import_form.php';
require_once '../lib.php';

$id = required_param('id', PARAM_INT); // course id

if (!$course = get_record('course', 'id', $id)) {
    print_error('nocourseid');
}

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $id);
require_capability('moodle/grade:import', $context);
require_capability('gradeimport/xml_getch:view', $context);

// print header
$strgrades = get_string('grades', 'grades');
$actionstr = get_string('modulename', 'gradeimport_xmlurl');
$navigation = grade_build_nav(__FILE__, $actionstr, array('courseid' => $course->id));

$mform = new grade_import_form();

if ($data = $mform->get_data()) {

    if (empty($data->key)) {
        redirect('import.php?id='.$id.'&url='.urlencode($data->url));

    } else {
        if ($data->key == 1) {
            $data->key = create_user_key('grade/import', $USER->id, $course->id, $data->iprestriction, $data->validuntil);
        }

        print_header($course->shortname.': '.get_string('grades'), $course->fullname, $navigation);
        print_grade_plugin_selector($id, 'import', 'xmlurl');

        echo '<div class="gradeexportlink">';
        $link = $CFG->wwwroot.'/grade/import/xmlurl/fetch.php?id='.$id.'&amp;url='.urlencode($data->url).'&amp;key='.$data->key;
        echo get_string('import', 'grades').': <a href="'.$link.'">'.$link.'</a>';
        echo '</div>';
        print_footer();
        die;
    }
}

print_header($course->shortname.': '.get_string('grades'), $course->fullname, $navigation);
print_grade_plugin_selector($id, 'import', 'xmlurl');

$mform->display();

print_footer();

?>