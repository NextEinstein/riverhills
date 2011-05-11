<?php 
/**
 * This page lists all the instances of podcasters in a particular course
 *
 **/
    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id', PARAM_INT);   // course

    if (! $course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }

    require_login($course->id);

    add_to_log($course->id, "podcaster", "view all", "index.php?id=$course->id", "");


/// Get all required stringspodcaster

    $strpodcasters = get_string("modulenameplural", "podcaster");
    $strpodcaster  = get_string("modulename", "podcaster");


/// Print the header

    $navlinks = array();
    $navlinks[] = array('name' => $strpodcasters, 'link' => '', 'type' => 'activity');
    $navigation = $strpodcasters;

    print_header_simple("$strpodcasters", "", $navigation, "", "", true, "", navmenu($course));

/// Get all the appropriate data

    if (! $podcasters = get_all_instances_in_course("podcaster", $course)) {
        notice("There are no podcasters", "../../course/view.php?id=$course->id");
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");

    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname);
        $table->align = array ("center", "left");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname);
        $table->align = array ("center", "left", "left", "left");
    } else {
        $table->head  = array ($strname);
        $table->align = array ("left", "left", "left");
    }

    foreach ($podcasters as $podcaster) {
        if (!$podcaster->visible) {
            $link = "<a class=\"dimmed\" href=\"view.php?id=$podcaster->coursemodule\">$podcaster->name</a>";
        } else {
            $link = "<a href=\"view.php?id=$podcaster->coursemodule\">$podcaster->name</a>";
        }

        if ($course->format == "weeks" or $course->format == "topics") {
            $table->data[] = array ($podcaster->section, $link);
        } else {
            $table->data[] = array ($link);
        }
    }
    echo "<br />";

    print_table($table);
/// Finish the page
    print_footer($course);

?>
