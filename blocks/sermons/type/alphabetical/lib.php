<?php 

define('NUMBER_OF_PARTION_LETTERS', 4);
static $alphabet = false;
if ($alphabet === false) {
	$alphabet = array(1=>'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
}
function sermon_alphabetical_get_nodes() {
    global $CFG, $COURSE, $alphabet;

    $counter = 1;
    $numberofparts = floor(count($alphabet) / NUMBER_OF_PARTION_LETTERS);

	$letters = array();

    for ($x = 0; $x <= $numberofparts; $x++) {
    	$lettersobject = new stdClass();

    	$letterstartnumber = $x * NUMBER_OF_PARTION_LETTERS + 1;
    	
    	$startletter = $alphabet[$letterstartnumber];
    	$endletter = (($startletternumber + NUMBER_OF_PARTION_LETTERS) <= count($alphabet)) ? $alphabet[($startletternumber + NUMBER_OF_PARTION_LETTERS) - 1] : $alphabet[count($alphabet)];
    	
        $lettersobject->data = "{$startletter} - {$endletter}";
        $lettersobject->attributes = array('class' => 'alphabetical', 'id' => 'alphabetical~'.$letterstartnumber);
        $lettersobject->state = 'closed';

        $letters[] = $lettersobject;
    }

    if (empty($letters)) {
        return '';
    }

    return $letters;

}

function sermon_alphabetical_get_one_node($letterstartnumber) {
    global $CFG, $COURSE, $alphabet;

    if (empty($letterstartnumber)) {
        return '';
    }

    $extrawhere = array();

    for ($x = 0; $x < NUMBER_OF_PARTION_LETTERS; $x++) {
    	$extrawhere[] = "name LIKE '{$alphabet[$letterstartnumber + $x]}%' ";
    }

    //get the relavant sermons for this sermon series
    $sql = "SELECT r.*, rs.datedelivered, rs.seriesname, rs.book, rs.beginchapter, cm.id as `cmid`,
                rs.guestspeaker, rs.guestspeakername, rs.hitcounter, rs.lastaccess 
            FROM {$CFG->prefix}resource r 
                JOIN {$CFG->prefix}resource_sermon rs ON rs.resourceid = r.id
                JOIN {$CFG->prefix}course_modules cm ON cm.instance = r.id 
            WHERE r.type = 'sermon' AND r.course = {$COURSE->id} AND name != '' ";
    $sql .= !empty($extrawhere) ? ' AND (' .implode(' OR ', $extrawhere).')' : '';
    $sql .= " ORDER BY r.name ASC";

    $sermons = get_recordset_sql($sql);

    //loop through and make them into an array of objecst compatible for a json_encode
    $letters = array();
    while (($sermon = rs_fetch_next_record($sermons)) !== false)  {
        if (empty($sermon->seriesname)) {
            $sermon->seriesname = 'no name';
        }

        $sermon->datedelivered = date('m-d-Y', $sermon->datedelivered);

        $sermonnode = new stdClass();

        $sermonnode->attributes = new stdClass();
        $sermonnode->attributes->id = $sermon->cmid.'~'.sermon_block_make_name_safe_for_id($sermon->seriesname);
        $sermonnode->attributes->class = 'leaf sermon';

        if (!empty($sermon->reference)) {
            $sermonnode->attributes->class .= ' mp3 ';
        } else if (!empty($sermon->referencesermontext)) {
            $sermonnode->attributes->class .= ' text ';
        } else if (!empty($sermon->referencelesson)) {
            $sermonnode->attributes->class .= ' lesson ';
        }

        $sermonnode->attributes->class .= (rs_fetch_record($sermons) === false) ? ' last ' : '';

        $sermonnode->data = get_string('sermonleaf', 'resource', $sermon);

        $letters[] = $sermonnode;

    }
	$letters = array_values($letters);

    if (empty($letters)) {
        return '';
    }

    return $letters;
}


?>