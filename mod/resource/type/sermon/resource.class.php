<?php // $Id: resource.class.php,v 1.40.2.3 2008/07/01 22:25:26 skodak Exp $
require_once($CFG->dirroot.'/mod/resource/type/file/resource.class.php');

class resource_sermon extends resource_file {


function resource_sermon($cmid=0) {
    parent::resource_base($cmid);

    if (!empty($cmid)) {
        //get the sermon portion
        $sermonresource = get_record('resource_sermon', 'resourceid', $this->resource->id);

        //prep it to be stored in the resource by changing the id field to sermonresourceid to avoid competing ids
        $sermonresource->sermonresourceid = $sermonresource->id;
        unset($sermonresource->id);
        $this->resource = (object)array_merge((array)$this->resource, (array)$sermonresource);

    }
}

function add_instance($resource) {

    //set up the resourcesermon object
    $resourcesermon = $resource;
    $resourcesermon->resourceid = parent::add_instance($resource);

    // only save guest or userid
    if (empty($resourcesermon->guestspeaker)) {
        $resourcesermon->guestspeakername = '';
        $resourcesermon->guestspeaker = 0;
    } else {
        $resourcesermon->speakerid = 0;
    }

    // if new series was chosen then place that on the save variable
    if (!empty($resourcesermon->newseries)) {
        $resourcesermon->seriesname = $resourcesermon->newseriesname;
    }

    return insert_record('resource_sermon', $resourcesermon);    //save all the missed fields to the resource_sermon table
}


function update_instance($resource) {
    parent::update_instance($resource);

    //save the sermon resource stuff
    $resourcesermon = $resource;
    $resourcesermon->resourceid = $resource->id;
    $resourcesermon->id  = get_field('resource_sermon', 'id', 'resourceid', $resource->id);

    // only save guest or userid
    if (empty($resourcesermon->guestspeaker)) {
        $resourcesermon->guestspeakername = '';
        $resourcesermon->guestspeaker = 0;
    } else {
        $resourcesermon->speakerid = 0;
    }

    // if new series was chosen then place that on the save variable
    if (!empty($resourcesermon->newseries)) {
        $resourcesermon->seriesname = $resourcesermon->newseriesname;
    }

    return update_record('resource_sermon', $resource);
}

function _postprocess(&$resource) {
    if (empty($resource->blockdisplay)) {
        $resource->options = '';
    } else {
        $resource->options = 'showblocks';
    }
    unset($resource->blockdisplay);
    $resource->popup = '';
}

/**
 * This function will get sermon html then let the parent class output the file
 * 
 */
function display() {
    global $CFG, $COURSE;

    require_once($CFG->libdir.'/biblelib.php');
	require_once($CFG->libdir.'/filelib.php');

    $resource = $this->resource;

    resource_sermon_accessed($resource->id);

    $extrahtml = array();

/// build the sermon header
    $extrahtml[] = '<div class="sermon-header ui-corner-all">';
    $extrahtml[] =     '<span class="series-name nowrap">'.$resource->seriesname.'</span>';
    $extrahtml[] = '</div>';

/// sermon information
    $extrahtml[] = '<div class="sermon-info">';

    // get the correct user information
    $speakername = $userpicture = false; 
    if (!empty($resource->speakerid)) {
        $user = get_record('user', 'id', $resource->speakerid);
        $userpicture = print_user_picture($user, SITEID, $user->picture, true, true);
        $speakername = fullname($user);
    } else {
        $guestuser = guest_user();
        $userpicture = print_user_picture($guestuser, SITEID, $guestuser->picture, true, true, false);
        $speakername = $resource->guestspeakername;
    }

    //we need the book name
    $biblebooks = biblebooks_array();
    $resource->bookname = $biblebooks[$resource->book];

    // build speaker information
    $extrahtml[] = '<div class="speaker">';
    $extrahtml[] =     '<span class="speaker-pic nowrap">'.$userpicture.'</span>';
    $extrahtml[] =     '<span class="speaker-name nowrap">'.$speakername.'</span>';
    $extrahtml[] = '</div>';

    // build sermon information
    $extrahtml[] = '<div class="sermon-text">';
    $extrahtml[] =     '<span class="sermon-title">'.$resource->name.'</span>';
    $extrahtml[] =     '<span class="sermon-place nowrap">'.get_string('sermonplace', 'resource', $resource).'</span>';
    $extrahtml[] =     '<span class="sermon-download-links nowrap">'.get_string('downloadlinks', 'resource').'</span>';
    $extrahtml[] =     '<span class="links">';
    $extrahtml[] =          '<span class="link nowrap">
                                <span class="picture">'.print_file_picture($CFG->pixpath.'/cust/doc.png', 0, $height='22px', $width='22px', $CFG->wwwroot.'/file.php/'.$resource->course.'/'.$resource->reference, true).'</span>
                                <span class="link-name"><a href="'.$CFG->wwwroot.'/file.php/'.$resource->course.'/'.$resource->reference.'">'.get_string('sermonmp3link', 'resource', $resource).'</a></span>
                            </span>';
    if (!empty($resource->referencesermontext)) {
        $extrahtml[] =      '<span class="link nowrap">
                                <span class="picture">'.print_file_picture($CFG->pixpath.'/cust/mp3.png', 0, $height='22px', $width='22px', $CFG->wwwroot.'/file.php/'.$resource->course.'/'.$resource->referencesermontext, true).'</span>
                                <span class="link-name"><a href="'.$CFG->wwwroot.'/file.php/'.$resource->course.'/'.$resource->referencesermontext.'">'.get_string('sermontextlink', 'resource', $resource).'</a></span>
                            </span>';
    }

    if (!empty($resource->referencelesson)) {
        $extrahtml[] =      '<span class="link nowrap">
                                <span class="picture">'.print_file_picture($CFG->pixpath.'/cust/doc.png', 0, $height='22', $width='22', $CFG->wwwroot.'/file.php/'.$resource->course.'/'.$resource->referencesermonlesson, true).'</span>
                                <span class="link-name"><a href="'.$CFG->wwwroot.'/file.php/'.$resource->course.'/'.$resource->referencesermonlesson.'">'.get_string('sermonlessonlink', 'resource', $resource).'</a></span>
                            </span>';
    }
    $extrahtml[] = '</span>';
    $extrahtml[] = '</div>';

    $extrahtml[] = '<div id="datedelivered-picker"></div>';
    $extrahtml[] = '<script type="text/javascript">jQuery("#datedelivered-picker").datepicker({dateFormat: "@", onSelect : function (dateText, inst) {jQuery("#datedelivered-picker").datepicker("setDate", 0)}})</script>';

    echo '<div id="extra-html" class="ui-corner-all">';
    echo implode(' ', $extrahtml);

    $fullurl = get_file_url($COURSE->id.'/'.$resource->reference);
    $cleanurl = addslashes_js($fullurl);

	$artist = '';
	if (!empty($resource->guestspeakername)) {
		$artist = "artists: \"{$resource->guestspeakername}\"";
	} else if (!empty($resource->speakerid)) {
		$user = get_record('user', 'id', $resource->speakerid);
		if (!empty($user)) {
			$artist = "artists: \"{$user->firstname} {$user->lastname}\"";
		}
	}

    $titles = '';
	if (!empty($resource->seriesname)) {
		$titles = $resource->seriesname. ': ';
	}

	$titles .= $resource->name;

	echo '<div class="newresourcecontent">';
	echo '<script type="text/javascript" src="'.$CFG->httpswwwroot.'/lib/audio-player.js"></script>';
	echo '<script type="text/javascript">
			AudioPlayer.setup("'.$CFG->httpswwwroot.'/lib/player.swf", {  
				width: 600
			});  
		  </script>';
	echo '<p id="audioplayer_1">Alternative content</p>  
			<script type="text/javascript">  
				AudioPlayer.embed("audioplayer_1", {
					soundFile: "'.$fullurl.'",
					transparentpagebg: "yes",
					titles: "'.$titles.'",
					'.$artist.'
				});
			</script>';
	echo '</div>';

}

function setup_preprocessing(&$defaults){

    //add in our sermon stuff
    $sermonresource = false;
    if (!empty($defaults['id'])) {
        //get the sermon portion
        $sermonresource = get_record('resource_sermon', 'resourceid', $defaults['id']);

        //prep it to be stored in the resource by changing the id field to sermonresourceid to avoid competing ids
        $sermonresource->sermonresourceid = $sermonresource->id;
        unset($sermonresource->id);
        $defaults = array_merge($defaults, (array)$sermonresource);

    }

    if (!isset($defaults['popup'])) {
        // use form defaults

    } else if (!empty($defaults['popup'])) {
        $defaults['windowpopup'] = 1;
        if (array_key_exists('popup', $defaults)) {
            $rawoptions = explode(',', $defaults['popup']);
            foreach ($rawoptions as $rawoption) {
                $option = explode('=', trim($rawoption));
                $defaults[$option[0]] = $option[1];
            }
        }
    } else {
        $defaults['windowpopup'] = 0;
        if (array_key_exists('options', $defaults)) {
            $defaults['blockdisplay'] = ($defaults['options']=='showblocks');
        }
    }
}

    function setup_elements(&$mform) {
        global $CFG;
        require_once($CFG->libdir.'/biblelib.php');

        //remove these so we have ui control of where name goes later
        $mform->removeElement('general');
        $mform->removeElement('name');

      /// sermon date
        $mform->addElement('html', '<table id="sermondetails-table" border="0" width="100%">
                                        <tr><td>');
        $mform->addElement('static', null, null, '<span class="sermon-delivery-title">'.get_string('sermonddeliverydate', 'resource').'</span>');
        $mform->addElement('html',
                        '<div id="datedelivered-picker"></div>'.
                            '<script type="text/javascript">
                                jQuery(document).ready(function () {
                                    jQuery("#datedelivered-picker").datepicker({ dateFormat: "@", 
                                                                                 onSelect: function(dateText, inst){jQuery(".datedelivered").val(dateText)},
                                                                                 defaultDate: jQuery(".datedelivered").val()
                                    });

                                    
                                })
                            </script>'.print_spacer(0, 250, false, true).'
                        </td><td>');

        $mform->addElement('hidden','datedelivered', 'testing', array('class' => 'datedelivered'));

    /// sermon info 
        mform_partition_start($mform);
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $previousseries = get_recordset_sql("SELECT DISTINCT seriesname FROM {$CFG->prefix}resource_sermon ORDER BY seriesname DESC");
        $seriesoptions = array(0 => get_string('selectpreviousseries', 'resource'));
        while (($series = rs_fetch_next_record($previousseries)) !== false) {
            $seriesoptions[$series->seriesname] = $series->seriesname;
        }
        $mform->addElement('select', 'seriesname', get_string('series', 'resource'), $seriesoptions);
        mform_spacer($mform, null, get_string('or', 'resource'));

        $newseries = array();
        $newseries[] = &MoodleQuickForm::createelement('text', 'newseriesname', get_string('newseriesname', 'resource'));
        $newseries[] = &MoodleQuickForm::createelement('checkbox', 'newseries', get_string('newseries', 'resource'));
        $mform->addGroup($newseries, null, get_string('newseriesname', 'resource'));
        $mform->disabledIf('newseriesname', 'newseries', 'notchecked');
        $mform->disabledIf('seriesname', 'newseries', 'checked');
        mform_partition_end($mform);


        mform_partition_start($mform);
    /// link to sermon mp3
        $mform->addElement('html', '<span class="nowrap">');
        $mform->addElement('choosecoursefile', 'reference', get_string('sermonmp3', 'resource'), null, array('maxlength' => 255, 'size' => 18));
        $mform->addGroupRule('reference', array('value' => array(array(get_string('maximumchars', '', 255), 'maxlength', 255, 'server'))));
        $mform->addRule('reference', null, 'required', null, 'client');
        $mform->addElement('html', '</span>');

    /// link to sermon pdf
        $mform->addElement('html', '<span class="nowrap">');
        $mform->addElement('choosecoursefile', 'referencesermontext', get_string('sermontext', 'resource'), null, array('maxlength' => 255, 'size' => 18));
        $mform->addGroupRule('referencesermontext', array('value' => array(array(get_string('maximumchars', '', 255), 'maxlength', 255, 'client'))));
        $mform->addElement('html', '</span>');

    /// link to reference lesson
        $mform->addElement('html', '<span class="nowrap">');
        $mform->addElement('choosecoursefile', 'referencelesson', get_string('sermonlesson', 'resource'), null, array('maxlength' => 255, 'size' => 18));
        $mform->addGroupRule('referencelesson', array('value' => array(array(get_string('maximumchars', '', 255), 'maxlength', 255, 'client'))));
        $mform->addElement('html', '</span>');
        mform_partition_end($mform);

    /// add the bible place fields
        mform_partition_start($mform);
        $mform->addElement('html', '<span class="nowrap">');
        $biblebooks = array_merge(array('' => get_string('choosebook', 'resource')), biblebooks_array());
        $bibleplace = array();
        $bibleplace[] = &MoodleQuickForm::createElement('select', 'book', get_string('biblebook', 'resource'), $biblebooks);
        $bibleplace[] = &MoodleQuickForm::createElement('text', 'beginchapter', get_string('beginchapter', 'resource'), 'size="5"');
        $mform->addGroup($bibleplace, null, get_string('biblebook', 'resource'), get_string('biblechapter', 'resource'));
        $mform->addElement('html', '</span>');
        mform_partition_end($mform);

    /// speaker fields
        mform_partition_start($mform);
        //these are a list of members with the ones who have given sermons in the past at the top of the list
        $potentialspeakers = get_recordset_sql("SELECT DISTINCT u.* FROM {$CFG->prefix}user u 
                                                    LEFT JOIN {$CFG->prefix}resource_sermon rs ON u.id = rs.speakerid
                                                    LEFT JOIN {$CFG->prefix}user u2 ON u2.id = rs.speakerid
                                                WHERE u.username != 'guest'
                                                ORDER BY u2.lastname ASC, u.lastname ASC");

        $speakeroptions = array(0 => get_string('selectfromexistinguser', 'resource'));
        while(($potentialspeaker = rs_fetch_next_record($potentialspeakers)) !== false) {
            $speakeroptions[$potentialspeaker->id] = $potentialspeaker->lastname.', '.$potentialspeaker->firstname;
        }

        $mform->addElement('select', 'speakerid', get_string('speakerbyid', 'resource'), $speakeroptions);
        mform_spacer($mform, null, get_string('or', 'resource'));
        $guestspeakergroup = array();
        $guestspeakergroup[] = &MoodleQuickForm::createElement('text', 'guestspeakername', get_string('guestspeakername', 'resource'));
        $guestspeakergroup[] = &MoodleQuickForm::createElement('checkbox', 'guestspeaker', null);

        $mform->addGroup($guestspeakergroup, null, get_string('guestspeakername', 'resource'));
        $mform->disabledIf('guestspeakername', 'guestspeaker', 'notchecked');
        $mform->disabledIf('speakerid', 'guestspeaker', 'checked');
        mform_partition_end($mform);

    /// searchable sermon text
        $mform->addElement('static', 'label', '<span class="searchsermontxt">'.get_string('searchablesermontext', 'resource').'</span>');
        $mform->addElement('textarea', 'searchablesermontext', null, array('rows'=>10, 'cols'=>70));
        $mform->setType('searchablesermontext', PARAM_TEXT);

        $mform->addElement('html', '</td></tr></table>');

        // no need for description
        $mform->removeElement('summary');

    }

}

function mform_spacer($mform, $leftspacertext=null, $rightspacertext=null) {
    $mform->addElement('static', null, $leftspacertext, $rightspacertext);
}

function mform_partition_start($mform) {
    $mform->addElement('html', '<div id="partitioner">');
}

function mform_partition_end($mform) {
    $mform->addElement('html', '</div>');
    mform_spacer($mform);
}

/**
 * increment the hit counter for a particular sermon
 * @param INT $resourceid
 */
function resource_sermon_accessed($resourceid) {
    if (empty($resourceid)) {
        return;
    }

    $sermoncounter = get_field('resource_sermon', 'hitcounter', 'resourceid', $resourceid);

    $sermoncounter++;

    $sermonresource->lastaccess = time();

    set_field('resource_sermon', 'hitcounter', $sermoncounter, 'resourceid', $resourceid);
    set_field('resource_sermon', 'lastaccess', time(), 'resourceid', $resourceid);


}
?>
