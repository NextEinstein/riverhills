<?php
/**
 * the podcaster "weblib"
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 *            Michael Ganzer <michael.ganzer@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 *
 **/
if (!defined('MOODLE_INTERNAL')) {
  die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

class podcaster_pageimpl {
  var $format,
      $channel;
  
  function podcaster_pageimpl (&$channel) {
    if ($channel) {
      $this->format  = podcaster_format::create_format ($channel->format);
    }
    $this->channel = $channel;
  } // podcaster_pageimpl ()

  function get_stylesheet () {
    global $CFG;
    return '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/mod/podcaster/css/default.css" />';
  } // podcaster_get_stylesheet ()
//
// channel
//
  function print_channel_header () {
    echo '<div id="mod-podcaster-channel">
  <div class="channellinks">'.$this->format->get_channellinkshtml ($this->channel, $this).'</div>';
  } // print_channel_header ()

  function print_channel () {
    echo '<div class="channel">';
    echo $this->format->get_channelhtml ($this->channel, $this);
    echo '</div>';
  } // print_channel ()

  function print_channel_footer () {
    echo '</div>';
  } // print_channel_footer ()

//
// itemlist
//
  function print_itemlist_header () {
    echo '<div id="mod-podcaster-itemlist">
  <div class="channellinks">'.$this->format->get_channellinkshtml ($this->channel, $this).'</div>';
  } // print_itemlist_header ()

  function print_itemlist_footer () {
    echo '</div>';
  } // print_itemlist_footer ()

  function print_item (&$item) {
    echo '<div class="item'.($item->scheduledtime > time () ? ' scheduled' : '').'">';
    
    $this->print_item_actions (&$item);
    echo $this->format->get_itemhtml ($item, $this);

    echo '</div>';
    $item->get_enclosure ();
  } // print_item ()

  function print_item_actions (&$item) {
    global $CFG;
    if ($item->channelObj->can_edit ()) {
      echo '<div class="actions">
  <a title="'.get_string ('delete').'" href="deleteitem.php?cm='.$item->channelObj->coursemodule->id.'&amp;id='.$item->id.'&amp;channel='.$item->channelObj->id.'"><img src="'.$CFG->pixpath.'/t/delete.gif" class="iconsmall" alt="'.get_string ('delete').'" /></a>
  <a title="'.get_string ('edit').'" href="edititem.php?cm='.$item->channelObj->coursemodule->id.'&amp;id='.$item->id.'&amp;channel='.$item->channelObj->id.'"><img src="'.$CFG->pixpath.'/t/edit.gif" class="iconsmall" alt="'.get_string ('edit').'" /></a>
</div>';
    }
  } // print_item_actions ()

  function print_item_enclosure (&$item) {
  } // print_item_enclosure ()

  function print_add_item_button () {
    global $CFG;
    if ($this->channel->can_edit ()) {
        $string = get_string('additem', 'podcaster');
        echo '<form method="get" action="'.$CFG->wwwroot.'/mod/podcaster/edititem.php">
    <div>
      <input type="hidden" name="cm" value="'.$this->channel->coursemodule->id.'" />
      <input type="hidden" name="channel" value="'.$this->channel->id.'" />
      <input type="hidden" name="return" value="true" />
      <input type="hidden" name="sesskey" value="'.sesskey().'" />
      <input type="submit" value="'.$string.'" />
    </div>
  </form>';
    }
  } // print_add_item_button ()
  
  function print_itemdetail_header (&$item) {
    echo '<div id="mod-podcaster-itemdetail">
  <div class="channellinks">'.$this->format->get_channellinkshtml ($this->channel, $this).'</div>
  <div class="item'.($item->scheduledtime > time () ? ' scheduled' : '').'">';
  } // print_itemdetail_header ()

  function print_itemdetail (&$item) {
    $this->print_item_actions (&$item);
    echo $this->format->get_itemdetailhtml ($item, $this);
  } // print_itemdetail ()

  function print_itemdetail_footer (&$item) {
    echo '</div></div>';
  } // print_itemdetail_footer ()


// nicked & slightly modified from resource/type/file/resource.class.php
  function embedd_html ($url, $title) {
    global $CFG, $THEME;
    include_once ($CFG->libdir.'/filelib.php');

    $mimetype = mimeinfo ('type', $url);
    $filename = basename ($url);
    $displaytype = '';
    if (in_array($mimetype, array('image/gif','image/jpeg','image/png'))) {  // It's an image
      $displaytype = 'image';
    } else if ($mimetype == 'audio/mp3') {    // It's an MP3 audio file
      $displaytype = 'mp3';
    } else if ($mimetype == 'video/x-flv') {    // It's a Flash video file
      $displaytype = 'flv';
    } else if (substr($mimetype, 0, 10) == 'video/x-ms') {   // It's a Media Player file
      $displaytype = 'mediaplayer';
    } else if ($mimetype == 'video/quicktime' || $mimetype == 'video/mp4') {   // It's a Quicktime file
      $displaytype = 'quicktime';
    } else if ($mimetype == 'application/x-shockwave-flash') {   // It's a Flash file
      $displaytype = 'flash';
    } else if ($mimetype == 'application/pdf') {   // It's a PDF file
      $displaytype = 'pdf';
   	}
    switch ($displaytype) {
//
// image
//
      case 'image':
        return 
            '<div class="mod-podcaster-image">
<img title="'.strip_tags($title, true).'" class="image" src="'.$url.'" alt="'.strip_tags($title, true).'" />
</div>';  

//
// mp3
// 
      case 'mp3':
        if (!empty($THEME->resource_mp3player_colors)) {
            $c = $THEME->resource_mp3player_colors;   // You can set this up in your theme/xxx/config.php		
		} else {
            $c = 'bgColour=000000&btnColour=ffffff&btnBorderColour=cccccc&iconColour=000000&'.
                 'iconOverColour=00cc00&trackColour=cccccc&handleColour=ffffff&loaderColour=ffffff&'.
                 'font=Arial&fontColour=FF33FF&buffer=10&waitForPlay=no&autoPlay=yes';
        }
        $c .= '&volText='.get_string('vol', 'resource').'&panText='.get_string('pan','resource');
        $c = htmlentities($c);
        $id = 'filter_mp3_'.time(); //we need something unique because it might be stored in text cache
        $cleanurl = addslashes_js($url);
        // If we have Javascript, use UFO to embed the MP3 player, otherwise depend on plugins 

// Javascript not show the items correctly
/*        return '<div class="mod-podcaster-mp3">
<span class="mediaplugin mediaplugin_mp3" id="'.$id.'"></span>
<script type="text/javascript">
   //<![CDATA[
   var FO = { movie:"'.$CFG->wwwroot.'/lib/mp3player/mp3player.swf?src='.$cleanurl.'",
                 width:"600", height:"70", majorversion:"6", build:"40", flashvars:"'.$c.'", quality: "high" };
   UFO.create(FO, "'.$id.'");
   //]]>
</script>
<noscript>
  <object type="audio/mpeg" data="'.$url.'" width="600" height="70">
    <param name="src" value="'.$url.'" />
    <param name="quality" value="high" />
    <param name="autoplay" value="false" />
    <param name="autostart" value="false" />
  </object>
  <p><a href="'. $url .'">'.$url . '</a></p>
</noscript>
</div>';
*/
// delete URL in Itemview
return '<div class="mod-podcaster-mp3">
<span class="mediaplugin mediaplugin_mp3" id="'.$id.'"></span>
  <object type="audio/mpeg" data="'.$url.'" width="600" height="70">
    <param name="src" value="'.$url.'" />
    <param name="quality" value="high" />
    <param name="autoplay" value="false" />
    <param name="autostart" value="false" />
  </object>
</div>';
//
// flv
// 
      case 'flv':
        $id = 'filter_flv_'.time(); //we need something unique because it might be stored in text cache
        $cleanurl = addslashes_js($url);
        // If we have Javascript, use UFO to embed the FLV player, otherwise depend on plugins

// Javascript not show the items correctly
/*        return '<div class="mod-podcaster-flv">
<span class="mediaplugin mediaplugin_flv" id="'.$id.'"></span>
<script type="text/javascript">
   //<![CDATA[
   var FO = { movie:"'.$CFG->wwwroot.'/filter/mediaplugin/flvplayer.swf?file='.$cleanurl.'",
              width:"600", height:"400", majorversion:"6", build:"40", allowscriptaccess:"never", quality: "high" };
   UFO.create(FO, "'.$id.'");
   //]]>
</script>
<noscript>
  <object type="video/x-flv" data="'.$url.'" width="600" height="400">
    <param name="src" value="'.$url.'" />
    <param name="quality" value="high" />
    <param name="autoplay" value="false" />
    <param name="autostart" value="false" />
  </object>
  <p><a href="'.$url.'">'.$url.'</a></p>
</noscript>
</div>';
*/
// delete URL in Itemview
        return '<div class="mod-podcaster-flv">
<span class="mediaplugin mediaplugin_flv" id="'.$id.'"></span>
  <object type="video/x-flv" data="'.$url.'" width="600" height="400">
    <param name="src" value="'.$url.'" />
    <param name="quality" value="high" />
    <param name="autoplay" value="false" />
    <param name="autostart" value="false" />
  </object>
</div>';


//
// mediaplayer
// 
      case 'mediaplayer':
        return '<div class="mod-podcaster-wmv">
<object type="video/x-ms-wmv" data="'.$url.'">
  <param name="controller" value="true" />    
  <param name="autostart" value="false" />            
  <param name="src" value="'.$url.'" />
  <param name="scale" value="noScale" />
  <a href="'.$url.'">'.$url.'</a>
</object>
</div>';

//
// mpeg
// 
      case 'mpeg':
        return '<div class="mod-podcaster-mpeg">
<object classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95"
        codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsm p2inf.cab#Version=5,1,52,701"
        type="application/x-oleobject">
  <param name="fileName" value="'.$url.'" />
  <param name="autoStart" value="false" />
  <param name="animationatStart" value="true" />
  <param name="transparentatStart" value="true" />
  <param name="showControls" value="true" />
  <param name="Volume" value="-450" />
  <!--[if !IE]>-->
  <object type="video/mpeg" data="'.$url.'">            
    <param name="controller" value="true" /> 
    <param name="autostart" value="false" />            
    <param name="src" value="'.$url.'" /> 
  <!--<![endif]-->
  <a href="'.$url.'">'.$url.'</a>
  <!--[if !IE]>-->
  </object>
  <!--<![endif]-->
  <a href="'.$url.'">'.$url.'</a>
</object>
</div>';

//
// quicktime
//
      case 'quicktime':
        return '<div class="mod-podcaster-quicktime">
<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
        codebase="http://www.apple.com/qtactivex/qtplugin.cab">
  <param name="src" value="'.$url.'" />
  <param name="autoplay" value="false" />
  <param name="loop" value="false" />
  <param name="controller" value="true" />
  <param name="scale" value="aspect" />
  <!--[if !IE]>-->
  <object type="video/quicktime" data="'.$url.'">
  <param name="controller" value="true" />
  <param name="autoplay" value="false" />
  <param name="loop" value="false" />
  <param name="scale" value="aspect" />
  <!--<![endif]-->
  <a href="'.$url.'">'.$url.'</a>
  <!--[if !IE]>-->
  </object>
  <!--<![endif]-->
</object>
</div>';

//
// flash
//
      case 'flash':
        return '<div class="mod-podcaster-swf">
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">
  <param name="movie" value="'.$url.'" />
  <param name="autoplay" value="false" />
  <param name="loop" value="false" />
  <param name="controller" value="true" />
  <param name="scale" value="aspect" />
  <!--[if !IE]>-->
  <object type="application/x-shockwave-flash" data="'.$url.'">
  <param name="controller" value="true" />
  <param name="autoplay" value="false" />
  <param name="loop" value="false" />
  <param name="scale" value="aspect" />
  <!--<![endif]-->
  <a href="'.$url.'">'.$url.'</a>
  <!--[if !IE]>-->
  </object>
  <!--<![endif]-->
</object>
</div>';

//
// zip
//
// no MIME-Type
/*      case 'zip':
        $icon = mimeinfo('icon', $url);
        return '<div class="mod-podcaster-zip">
  <a href="'.$url.'"><img src="'.$CFG->pixpath.'/f/'.$icon.'"  class="icon" alt="'.$filename.'" />&nbsp;'.format_string($filename).'</a>
</div>';
*/
//
// pdf
//
      case 'pdf':
        $icon = mimeinfo('icon', $url);
        return '<div class="mod-podcaster-pdf">
<object data="'.$url.'" type="application/pdf">
  <a href="'.$url.'"><img src="'.$CFG->pixpath.'/f/'.$icon.'"  class="icon" alt="'.$filename.'" />&nbsp;'.format_string($filename).'</a>
</object>
</div>';
      default:
        return '';
    }
  } // embedd_html ()
} // class podcaster_pageimpl

?>
