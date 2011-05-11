<?php

global $CFG;

$handlers = array(
    'stylesheets_setup' =>
        array(
            'handlerfile'     => '/mod/moodle_optimization/eventslib.php',
            'handlerfunction' => 'moodle_optimization_stylessheets_setup_event_handler',
            'schedule'        => 'instant'
        ),
    'starting-javascript-mod' =>
        array(
            'handlerfile'     => '/mod/moodle_optimization/eventslib.php',
            'handlerfunction' => 'moodle_optimization_javascriptmod_beginning',
            'schedule'        => 'instant'
        ),
    'after-setup' =>
        array(
            'handlerfile'     => '/mod/moodle_optimization/eventslib.php',
            'handlerfunction' => 'moodle_optimization_register_scripts',
            'schedule'        => 'instant'
        ),
    'starting-javascript-mod' =>
        array(
            'handlerfile'     => '/mod/moodle_optimization/eventslib.php',
            'handlerfunction' => 'moodle_optimization_exit_for_old_javascript_mod',
            'schedule'        => 'instant'
        ),
);