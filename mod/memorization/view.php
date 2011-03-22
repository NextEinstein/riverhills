<?PHP  // $Id: view.php,v 1.3.2.2 2009/02/23 19:22:41 dlnsk Exp $

require_once("../../config.php");
require_once('locallib.php');

global $USER, $COURSE;

$userid             = optional_param('userid', $USER->id, PARAM_INT);
$printing           = optional_param('printing');
$cmid               = optional_param('id', false, PARAM_INT);

if (! $user = get_record('user', 'id', $userid)) {
    error('Could not find the correct user');
}

$ctx = !empty($cmid) ? get_context_instance(CONTEXT_MODULE, $cmid) : false;

if (empty($ctx)) {
    error('There was a problem while trying to initiate this module. Please contact us to let us know about it so we can fix it.');
}

if ($USER->id != $userid && !has_capability('mod/memorization:viewothersverses', $ctx)) {
    error('You do not have access to view someone elses memorization verses.');
}

require_login($COURSE);

// Print headers
$navlinks[] = array('name' => get_string('memorizationtitle', 'memorization'), 'link' => "view.php?", 'type' => 'activityinstance');
//$navlinks[] = array('name' => get_string('attendancereport', 'attforblock'), 'link' => null, 'type' => 'title');
$navigation = build_navigation($navlinks);
print_header("$COURSE->shortname: ".get_string('memorizationtitle', 'memorization').' - ' .get_string('export', 'quiz'), $COURSE->fullname,
                 $navigation, "", "", true, "&nbsp;", navmenu($COURSE));

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

if (!memorization_print_method_view($userpref->methodid, $userid, $cmid)) {
    error('There was an error while generating the view for the scripture memorization module. Please Contact the system administrator');
}

print_footer($COURSE);
