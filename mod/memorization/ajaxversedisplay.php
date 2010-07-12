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
    echo '<html><body><div id="page"><div id="content">'.$verse.'</div></div></body></html>';
} else {
    echo '<html><body><div id="page"><div id="content">'.get_string('oopsnoverse', 'memorization').'</div></div></body></html>';
}
