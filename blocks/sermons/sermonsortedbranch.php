<?php

require_once('../../config.php');
require_once('lib.php');

define('SERMONTREEBREAKER', 'd1v1der');

$sorttype = optional_param('sorttype', false, PARAM_ALPHA);
$nodename = optional_param('nodename', false, PARAM_TEXT);

$includefile = $CFG->dirroot.'/blocks/sermons/type/'.$sorttype.'/lib.php';
if (!file_exists($includefile)) {
    //TODO: add a status return
    return '';
}

include_once($includefile);

//if we aren't getting a files in a node name then get nodes
$jsonobject = false;
if (empty($nodename)) {
    $nodesfunction = "sermon_{$sorttype}_get_nodes";

    //if the function exists then use it
    $jsonobject = function_exists($nodesfunction) ? $nodesfunction() : false;

} else {
    $onenodefunction = "sermon_{$sorttype}_get_one_node";

    //if the function exists then use it
    $jsonobject = function_exists($onenodefunction) ? $onenodefunction($nodename) : false;

}

if (!empty($jsonobject)) {
    echo '[' . json_encode($jsonobject) .']';
}

/*
exit;

// build the select
$select = ' r.*, rs.datedelivered, rs.seriesname, rs.book, rs.beginchapter,
            rs.guestspeaker, rs.guestspeakername, rs.hitcounter, rs.lastaccess ';

if ($sorttype == 'speaker') {
    $select .= ' CONCAT(u.firstname, " ", u.lastname) as speakername ';
}

$select .= " FROM {$CFG->prefix}resource r ";

// Build the joins
$join = " JOIN {$CFG->prefix}resource_sermon rs ON rs.resourceid = r.id ";

if ($sorttype == 'speaker') {
    $join .= " LEFT JOIN {$CFG->prefix}user u ON u.id = rs.speakerid";
}

$where = " r.type = 'sermon' AND r.course = {$COURSE->id} AND name != '' ";

$orderby = '';
switch ($sorttype) {
    case 'series' :
        $orderby .= ' rs.seriesname ASC'; 
        break;
    case 'sermonname' :
        $orderby .= ' r.name ASC';
        break;
    case 'date' :
        $orderby .= ' rs.datedelivered ASC';
        break;
    case 'popularity' :
        $orderby .= ' rs.hitcounter ASC ';
        break;
    case 'speaker' :
        $orderby .= ' u.lastname ASC ';
        break;
    default :
        $orderby .= ' rs.seriesname ASC ';
}

$orderby .= ', rs.datedelivered ASC ';

$sermons = get_recordset_sql("SELECT {$select} {$join} WHERE {$where} ORDER BY {$orderby}");




?>*/