<?php

function memorization_install() {
    global $CFG;

    $result = true;

    require_once($CFG->libdir.'/biblelib.php');

    $versions = bible_gateway_available_versions();

    if (empty($versions)) {
        return false;
    }

    // add the versions from biblegateway
    require_once($CFG->libdir.'/biblelib.php');

    $versions = bible_gateway_available_versions();

    if (empty($versions)) {
        $result = false;
    }

    if (!empty($versions)) {
        foreach ($versions as $name => $value) {
            $versionrecord = (object) array('name' => addslashes($name), 'value' => addslashes($value));

            $result = $result && insert_record('memorization_version', $versionrecord);
        }
    }

    return $result;
}

/** 
 * code to add a new instance of widget
 */
function memorization_add_instance($memorization) {
    if (get_record('memorization', 'course', $memorization->course)) {
        $modnum = get_field('modules', 'id', 'name', 'memorization');
        if (!get_record('course_modules', 'course', $memorization->course, 'module', $modnum)) {
            delete_records('memorization', 'course', $memorization->course);
            $memorization->id = insert_record('memorization', $memorization);
        } else {
            return false;
        }
    } else {
        $memorization->id = insert_record('memorization', $memorization);
    }

    return $memorization->id;
}

/**
 * code to update an existing instance
 */
function memorization_update_instance($memorizationinstance) {
    $memorizationinstance->id = $memorizationinstance->instance;
    return update_record('memorization', $memorizationinstance);
}

/**
 * code to delete an instance
 */
function memorization_delete_instance($id) {
    return delete_records('memorization', 'id', $id);
}

/**
 * given an instance, return a summary of a user's contribution
 */
function memorization_user_outline($instance) {

}

/**
 * given an instance, print details of a user's contribution
 */
function widget_user_complete() {

}