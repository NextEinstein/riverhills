<?php 
//include_once('../../../config.php');

function sermon_series_get_nodes() {
    global $CFG, $COURSE;

    // sql for geting seriesnames
    $sql = "SELECT DISTINCT rs.seriesname FROM {$CFG->prefix}resource r
                JOIN {$CFG->prefix}resource_sermon rs ON rs.resourceid = r.id
            WHERE r.type = 'sermon' AND r.course = {$COURSE->id} AND name != ''
            ORDER BY rs.dateDelivered DESC";

    $seriesnames = get_recordset_sql($sql);

    //loop through and make objects of the seriesnames compatible for json_encode
    $seriesobjects = array();
    while (($seriesname = rs_fetch_next_record($seriesnames)) !== false) {
        $seriesobject = new stdClass();
        $seriesobject->data = $seriesname->seriesname;
        $seriesobject->attributes = array('class' => 'series', 'id' => 'series-'.preg_replace('/\'/', '&#39;',$seriesname->seriesname));
        $seriesobject->state = 'closed';

        $seriesobjects[] = $seriesobject;
    }

    if (empty($seriesobjects)) {
        return '';
    }

    return $seriesobjects;

}

function sermon_series_get_one_node($seriesname) {
    global $CFG, $COURSE;

    if (empty($seriesname)) {
        return '';
    }

    //get the relavant sermons for this sermon series
    $sql = "SELECT r.*, rs.datedelivered, rs.seriesname, rs.book, rs.beginchapter, cm.id as `cmid`,
                rs.guestspeaker, rs.guestspeakername, rs.hitcounter, rs.lastaccess 
            FROM {$CFG->prefix}resource r 
                JOIN {$CFG->prefix}resource_sermon rs ON rs.resourceid = r.id
                JOIN {$CFG->prefix}course_modules cm ON cm.instance = r.id 
            WHERE r.type = 'sermon' AND r.course = {$COURSE->id} AND name != '' AND rs.seriesname = '$seriesname'
            ORDER BY rs.seriesname ASC, rs.datedelivered DESC";

    $sermons = get_recordset_sql($sql);

    //loop through and make them into an array of objecst compatible for a json_encode
    $series = array();
    while (($sermon = rs_fetch_next_record($sermons)) !== false)  {
        if (empty($sermon->seriesname)) {
            $sermon->seriesname = 'no name';
        }

        //clean up some variable
//        $sermon->seriesname = stripslashes($sermon->seriesname);
//        $sermon->name = stripslashes($sermon->name);
        $sermon->datedelivered = date('m-d-Y', $sermon->datedelivered);

        $sermonnode = new stdClass();

        $sermonnode->attributes = new stdClass();
        $sermonnode->attributes->id = $sermon->cmid;
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

        $series[] = $sermonnode;

    }
    $series = array_values($series);

    if (empty($series)) {
        return '';
    }

    return $series;
}


?>