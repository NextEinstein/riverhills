<?php

function moodle_optimization_stylessheets_setup_event_handler($eventdata) {
    global $CFG, $HTTPSPAGEREQUIRED;

    // make sure it is on before doing stuff ( not sure if this is necssary for the cron )
    if (!moodle_optimization_is_active()) {
        return true;
    }

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

    // make sure it is on before doing stuff ( not sure if this is necssary for the cron )
    if (!moodle_optimization_is_active()) {
        return true;
    }

    // javascript-mod is a special case since we are caching that special in the cron
    $javascriptversion = get_field('mo_cache_versions', 'version', 'source', 'javascript-mod');
    require_js($CFG->wwwroot.'/mod/moodle_optimization/javascript-mod.js?version='.$javascriptversion);

    // because we have included these files into javascript-mod this will stop the header from printing them
    $CFG->excludeoldflashclients = false;

    return true;

}

function moodle_optimization_exit_for_old_javascript_mod($eventdata) {
    global $CFG;

    // make sure it is on before doing stuff ( not sure if this is necssary for the cron )
    if (!moodle_optimization_is_active()) {
        return true;
    }

    // exit when trying to read the old javascript-mod file since we have cached the results
    // but only if we are not trying to cache the results (ie running the MO cron)
    // Ideally you should just comment out the include reference in javascript.php
    if (!defined('MO_CRON') && !define('MO_FORCE_JAVASCRIPT_MOD')) {
        exit;
    }

    return true;
}

function moodle_optimization_is_active() {
    static $on = null;

    $on = is_null($on) ? get_field('modules', 'visible', 'name', 'moodle_optimization') : $on;

    return (bool) $on;
}