<?php 

// date nodes
define('CURRENT_YEAR', 1);
define('PREVIOUS_YEAR', 2);
define('PREVIOUS_FIVE_YEARS', 3);
define('OLDER', 4);

function sermon_date_get_nodes() {
    global $CFG, $COURSE, $alphabet;

    //make a node for current year's sermons
	$object = new stdClass();
    $object->data = get_string('currentyear', 'resource');
    $object->attributes = array('class' => 'date', 'id' => 'date~'.CURRENT_YEAR);
    $object->state = 'closed';

    $objects[] = $object;

    //make a node for previous year
	$object = new stdClass();
    $object->data = get_string('previousyear', 'resource');
    $object->attributes = array('class' => 'date', 'id' => 'date~'.PREVIOUS_YEAR);
    $object->state = 'closed';

    $objects[] = $object;

    //make a node for 5 years
  	$object = new stdClass();
    $object->data = get_string('previousfiveyears', 'resource');
    $object->attributes = array('class' => 'date', 'id' => 'date~'.PREVIOUS_FIVE_YEARS);
    $object->state = 'closed';

    $objects[] = $object;

    //make a node for all older than 5 years
  	$object = new stdClass();
    $object->data = get_string('olderthanfive', 'resource');
    $object->attributes = array('class' => 'date', 'id' => 'date~'.OLDER);
    $object->state = 'closed';

    $objects[] = $object;
    
    if (empty($objects)) {
        return '';
    }

    return $objects;

}

function sermon_date_get_one_node($datecategory) {
    global $CFG, $COURSE, $alphabet;

    if (empty($datecategory)) {
        return '';
    }

    //get the relevant sermons for these dates
    $sql = "SELECT r.*, rs.datedelivered, rs.seriesname, rs.book, rs.beginchapter, cm.id as `cmid`,
                rs.guestspeaker, rs.guestspeakername, rs.hitcounter, rs.lastaccess 
            FROM {$CFG->prefix}resource r 
                JOIN {$CFG->prefix}resource_sermon rs ON rs.resourceid = r.id
                JOIN {$CFG->prefix}course_modules cm ON cm.instance = r.id 
            WHERE r.type = 'sermon' AND r.course = {$COURSE->id} AND name != '' ";

    $startdate = 0;
    $enddate = 0;

	switch ($datecategory) {
		case CURRENT_YEAR :
			$startdate = mktime(0,0,0,1,1, date('Y'));
			$enddate = time();
			break;
		case PREVIOUS_YEAR :
			$startdate = mktime(0,0,0,1,1, date('Y') - 1);
			$enddate = mktime(0,0,0,1,1, date('Y'));
			break;
		case PREVIOUS_FIVE_YEARS :
			$startdate = mktime(0,0,0,1,1, date('Y') - 6);
			$enddate   = mktime(0,0,0,1,1, date('Y') - 1);
			break;
		case OLDER :
			$startdate = 0;
			$enddate = mktime(0,0,0,1,1, date('Y') - 6);
			break;
	}

	$sql .= " AND rs.datedelivered >= '{$startdate}' AND rs.datedelivered < '{$enddate}' ";

    $sql .= " ORDER BY rs.datedelivered DESC";

    $sermons = get_recordset_sql($sql);

    //loop through and make them into an array of objects compatible for a json_encode
    $datesermons = array();
    while (($sermon = rs_fetch_next_record($sermons)) !== false)  {
        if (empty($sermon->seriesname)) {
            $sermon->seriesname = 'no name';
        }

//        $sermon->seriesname = stripslashes($sermon->seriesname);
//        $sermon->name = stripslashes($sermon->name);
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

        $datesermons[] = $sermonnode;

    }
	$datesermons = array_values($datesermons);

    if (empty($datesermons)) {
        return '';
    }

    return $datesermons;
}


?>