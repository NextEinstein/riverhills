<?php

include_once('../../config.php');

global $USER;

if (!confirm_sesskey()) {
    header("HTTP/1.0 401 Unauthorized");
    exit;
}

$newrepetition = optional_param('newrepetition', 0, PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);
$verseid = optional_param('verseid', 0, PARAM_INT);

if (!set_field('memorization_verse', 'repetitions', $newrepetition, 'id', $verseid, 'userid', $userid)) {
    header("HTTP/1.0 500 Internal Server Error");
    exit; 
}

header("HTTP/1.0 200 OK");