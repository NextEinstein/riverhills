<?php

include_once('../../config.php');

require_login();

$test = get_field('modules', 'visible', 'name', 'moodle_optimization');

var_dump((bool)$test );

require_capability('moodle/site:config', get_system_context());

print_header_simple();

print_heading('instructionsheader', 'moodle_optimization');

