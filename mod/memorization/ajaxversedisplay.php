<?php

include_once('../../config.php');

if (!confirm_sesskey()) {
    exit;
}

$verseid = optional_param('verseid', false, PARAM_INT);
$userid = optional_param('userid', false, PARAM_INT);

if (!$verseid || !$userid) {
    exit;
}

$verse = get_field('memorization_verse', 'text', 'userid', $userid, 'id', $verseid);

if (!empty($verse)) {
    echo $verse;
} else {
    echo get_string('oopsnoverse', 'memorization');
}
