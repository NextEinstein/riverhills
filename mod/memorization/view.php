<?PHP  // $Id: view.php,v 1.3.2.2 2009/02/23 19:22:41 dlnsk Exp $

require_once("../../config.php");
require_once('locallib.php');

global $USER;

$id                 = optional_param('id', -1, PARAM_INT);   // Course Module ID, or
$userid             = optional_param('userid', $USER->id, PARAM_INT);
$printing           = optional_param('printing');

if ($id) {
    if (! $cm = get_record("course_modules", "id", $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $course = get_record("course", "id", $cm->course)) {
        error("Course is misconfigured");
    }

    if (! $memorization = get_record("memorization", "id", $cm->instance)) {
        error("Course module is incorrect");
    }

    if (! $user = get_record('user', 'id', $userid)) {
        error('Could not find the correct user');
    }
} else {
    error("Module id is incorrect.");
}

require_login($course->id);

if (!$context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
    print_error('badcontext');
}

// Print headers
$navlinks[] = array('name' => $memorization->name, 'link' => "view.php?id=$id", 'type' => 'activityinstance');
//$navlinks[] = array('name' => get_string('attendancereport', 'attforblock'), 'link' => null, 'type' => 'title');
$navigation = build_navigation($navlinks);
print_header("$course->shortname: ".$memorization->name.' - ' .get_string('export', 'quiz'), $course->fullname,
                 $navigation, "", "", true, "&nbsp;", navmenu($course));

$methods = memorization_method_id_filename_mapping_array();

if (empty($methods)) {
    error('No memorization methods can be found. The admin needs to program a method in');
}

$userpref = get_record('memorization_user_pref', 'userid', $userid);

// Make sure the user has chosen a preference
if ($userpref === false) {
    // we only need the user to choose if there is more than one to choose from
    if (count($methods) > 1) {
        redirect('changeuserpref.php', 0);
    }

    $methodid = reset(array_keys($methods));

    $userpref = (object) array('userid' => $userid, 'methodid' => $methodid);

    if (insert_record('memorization_user_pref', $userpref) === false) {
        error('An error occured. If this continues please contact the administrator');
    }
}

if (!memorization_print_method_view($userpref->methodid, $userid)) {
    error('There was an error while generating the view for the scripture memorization module. Please Contact the system administrator');
}

print_footer($course);
