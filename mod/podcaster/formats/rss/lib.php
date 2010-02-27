<?php
/**
 * RSS 2.0 Template  
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 **/
if (!defined('MOODLE_INTERNAL')) {
  die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
require_once ($CFG->dirroot.'/mod/podcaster/formats/rssfull/lib.php');
require_once ($CFG->dirroot.'/lib/filelib.php');

class rss_format extends rssfull_format {
//
// Forms
//
  function define_channelform (&$mform, &$channel) {
    parent::define_channelform ($mform, $channel);
    $mform->removeElement ('rating');
    $mform->removeElement ('cloud');
    $mform->removeElement ('clouddomain');
    $mform->removeElement ('cloudport');
    $mform->removeElement ('cloudpath');
    $mform->removeElement ('cloudregisterprocedure');
    $mform->removeElement ('cloudprotocol');
    $mform->removeElement ('textinput');
    $mform->removeElement ('textinputtitle');
    $mform->removeElement ('textinputdescription');
    $mform->removeElement ('textinputname');
    $mform->removeElement ('textinputlink');
    $mform->removeElement ('refreshing');
    $mform->removeElement ('ttl');
    $mform->removeElement ('skiphours');
    $mform->removeElement ('skipdays');
  } // define_channelform ()

  function define_itemform (&$mform, &$item) {
    parent::define_itemform ($mform, $item);
  } // define_itemform ()

//
// HTML
// 
  function get_channellinkshtml (&$channel, &$page) {
    global $CFG;
    $pixsrc = '';
    switch ($channel->get_rss_status ()) {
      case PODCASTER_RSS_OK:
        $pixsrc = $CFG->pixpath.'/i/rss.gif';
        break;
      case PODCASTER_RSS_ERROR:
        $pixsrc = $CFG->wwwroot.'/mod/podcaster/icons/rss_error.gif';
        break;
      case PODCASTER_RSS_OUTDATED:
        $pixsrc = $CFG->wwwroot.'/mod/podcaster/icons/rss_outdated.gif';
        break;
      case PODCASTER_RSS_UNKOWN:
      default:
        $pixsrc = $CFG->wwwroot.'/mod/podcaster/icons/rss_unknown.gif';
        break;
    }
    return '<a href="'.$channel->get_rss_link ().'"><img src="'.$pixsrc.'" class="channellink"></a>';
  } // get_channellinkshtml ()

  function get_channelhtml (&$channel, &$page) {
    $img = NULL;
    if (($image = $channel->get_image ()) && $image->url) {
      $img = $image->url;
    }
    return  ($img ? '<div class="image"><img src="'.$img.'" alt="" /></div>' : '').
            '<div class="timecreated">'.get_string('pubdate', 'podcaster').':&nbsp;'.userdate($channel->timecreated, get_string('strftimedatetime')).'</div>'.
            '<div class="title">'.htmlspecialchars($channel->name).'</div>'.
            '<div class="author">&nbsp;('.htmlspecialchars(podcaster_util::strip_email ($channel->managingeditor)).')</div>'.
            '<div class="description">'.$channel->intro.'</div>'.
            '<div style="clear:both;"></div>';
  } // get_channelhtml ()

  function get_itemhtml (&$item, &$page) {
    global $CFG;
    $url = NULL;
    $enclosure = $item->get_enclosure ();
    if ($enclosure) {
      $url = $enclosure->url;
    }
    $result = '';
    if ($item->channelObj->can_edit ()) {
      if ($item->scheduledtime > time ()) {
        $result = '<div class="scheduledtime">'.get_string('scheduledtime', 'podcaster').':&nbsp;'.userdate($item->scheduledtime, get_string('strftimedatetime')).'</div>';
      }
      $result .= '<div class="timecreated">'.get_string('timecreated', 'podcaster').':&nbsp;'.userdate($item->timecreated, get_string('strftimedatetime')).'</div>';
      $result .= '<div class="timemodified">'.get_string('lastmodified').':&nbsp;'.userdate($item->timemodified, get_string('strftimedatetime')).'</div>';
    }
    else {
        $result = '<div class="scheduledtime">'.get_string('pubdate', 'podcaster').':&nbsp;'.userdate($item->scheduledtime, get_string('strftimedatetime')).'</div>';
    }
    return    $result.
              '<div class="title"><a href="'.$CFG->wwwroot.'/mod/podcaster/view.php?channel='.$item->channel.'&amp;tab=item&amp;item='.$item->id.'">'.htmlspecialchars($item->title).'</a></div>'.
              '<div class="author">&nbsp;('.htmlspecialchars (podcaster_util::strip_email($item->author)).')</div>'.
              ($url ? '<div class="download"><a href="'.$url.'" title="'.($enclosure->name).'">'.($enclosure->name).' ('.$this->filesize ($enclosure->length).') <img src="'.$CFG->pixpath.'/f/'.mimeinfo ('icon', $url).'" border="0" alt="'.($enclosure->name).'" align="top" /></a></div>' : '').
              ($url && $item->channelObj->showpreview ?  '<div class="enclosure">'.$page->embedd_html ($url, $item->title).'</div>' : '').
              '<div class="description">'.$item->description.'</div>'.
              '<div class="separator"><hr size="1" /></div>';
  } // get_itemhtml ()

  function get_itemdetailhtml (&$item, &$page) {
    global $CFG;
    $url = NULL;
    $enclosure = $item->get_enclosure ();
    if ($enclosure) {
      $url = $enclosure->url;
    }
    $result = '';
    if ($item->channelObj->can_edit ()) {
      if ($item->scheduledtime > time ()) {
        $result = '<div class="scheduledtime">'.get_string('scheduledtime', 'podcaster').':&nbsp;'.userdate($item->scheduledtime, get_string('strftimedatetime')).'</div>';
      }
      $result .= '<div class="timecreated">'.get_string('timecreated', 'podcaster').':&nbsp;'.userdate($item->timecreated, get_string('strftimedatetime')).'</div>';
      $result .= '<div class="timemodified">'.get_string('lastmodified').':&nbsp;'.userdate($item->timemodified, get_string('strftimedatetime')).'</div>';
    }
    else {
        $result = '<div class="scheduledtime">'.get_string('pubdate', 'podcaster').':&nbsp;'.userdate($item->scheduledtime, get_string('strftimedatetime')).'</div>';
    }
    return    $result.
              '<div class="title">'.htmlspecialchars($item->title).'</div>'.
              '<div class="author">&nbsp;('.htmlspecialchars (podcaster_util::strip_email($item->author)).')</div>'.
              ($url ? '<div class="download"><a href="'.$url.'" title="'.($enclosure->name).'">'.($enclosure->name).' ('.$this->filesize ($enclosure->length).') <img src="'.$CFG->pixpath.'/f/'.mimeinfo ('icon', $url).'" border="0" alt="'.($enclosure->name).'" align="top" /></a></div>' : '').
              ($url ?  '<div class="enclosure">'.$page->embedd_html ($url, $item->title).'</div>' : '').
              '<div class="description">'.$item->description.'</div>'.
              '<div class="separator"><hr size="1" /></div>';
  } // get_itemdetailhtml ()
  
//
// XML
//
  function get_schema () {
    $schema = parent::get_schema ();
    $override = array (
        '/rss/channel/rating'                   => array(XML_DATA_IGNORE),
        '/rss/channel/cloud'                    => array(XML_DATA_IGNORE),
        '/rss/channel/cloud/@domain'            => array(XML_DATA_IGNORE),
        '/rss/channel/cloud/@port'              => array(XML_DATA_IGNORE),
        '/rss/channel/cloud/@path'              => array(XML_DATA_IGNORE),
        '/rss/channel/cloud/@registerProcedure' => array(XML_DATA_IGNORE),
        '/rss/channel/cloud/@protocol'          => array(XML_DATA_IGNORE),
        '/rss/channel/textInput'                => array(XML_DATA_IGNORE),
        '/rss/channel/textInput/@title'         => array(XML_DATA_IGNORE),
        '/rss/channel/textInput/@description'   => array(XML_DATA_IGNORE),
        '/rss/channel/textInput/@name'          => array(XML_DATA_IGNORE),
        '/rss/channel/textInput/@link'          => array(XML_DATA_IGNORE),
        '/rss/channel/skipHours'                => array(XML_DATA_IGNORE),
        '/rss/channel/skipHours/hour'           => array(XML_DATA_IGNORE),
        '/rss/channel/skipDays'                 => array(XML_DATA_IGNORE),
        '/rss/channel/skipDays/day'             => array(XML_DATA_IGNORE));
    return array_merge ($schema, $override);
  } // get_schema ()
// HELPERS
  function filesize ($length) {
    return ($length > 0x100000 ? sprintf ('%.1fMB', $length / (0x100000)) : sprintf ('%.1fkB', $length / 0x400));
  }

} // class rss_format
?>
