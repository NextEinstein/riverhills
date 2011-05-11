<?php

function moodle_optimization_stylessheets_setup_event_handler($eventdata) {
    global $CFG, $HTTPSPAGEREQUIRED;

    if (empty($CFG->stylesheets)) {
        return true;    // nothing to do, this should never happen
    }

    // get it back to wwwroot since we are going to compile these files into one
    if (!empty($HTTPSPAGEREQUIRED)) {
        foreach ($CFG->stylesheets as $key => $stylesheet) {
            $CFG->stylesheets[$key] = str_replace('https:', 'http:', $stylesheet);
        }
    }

    // convert into a operating system directory
    foreach ($CFG->stylesheets as $key => $stylesheet) {
        $CFG->stylesheets[$key] = str_replace($CFG->wwwroot, $CFG->dirroot, $stylesheet);
    }

    // print the new link
    $version = get_field('mo_cache_versions', 'version', 'source', 'theme'.current_theme());

    $CFG->stylesheets = array();
    $CFG->stylesheets[] = $CFG->wwwroot.'/mod/moodle_optimization/styles.css?version='.$version;

    return true;
}

function moodle_optimization_register_scripts($eventdata) {
    global $CFG;

    // javascript-mod is a special case since we are caching that special in the cron
    $javascriptversion = get_field('mo_cache_versions', 'version', 'source', 'javascript-mod');
    require_js($CFG->wwwroot.'/mod/moodle_optimization/javascript-mod.js?version='.$javascriptversion);

    // because we have included these files into javascript-mod this will stop the header from printing them
    $CFG->excludeoldflashclients = false;

    return true;

}

function moodle_optimization_exit_for_old_javascript_mod($eventdata) {
    global $CFG;

    exit;

    return true;
}