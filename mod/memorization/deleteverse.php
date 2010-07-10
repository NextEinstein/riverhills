<?php

include_once('../../config.php');

global $CFG;

$userid = required_param('userid', PARAM_INT);
$currenttab = optional_param('currenttab', 'day', PARAM_ALPHA);
$modid = required_param('modid', PARAM_INT);
$verseid = required_param('verseid', PARAM_INT);

$linkback = $CFG->wwwroot."/mod/memorization/view.php?currenttab={$currenttab}&id={$modid}&userid={$userid}&verseid={$verseid}";

if (!confirm_sesskey()) {
    error('couldn\'t confirm your credentials, please try again');
}

if (!delete_records('memorization_verse', 'userid', $userid, 'id', $verseid)) {
    redirect($linkback, 'problem deleting verse, try again', 5);
}

redirect($linkback, '', 0);
