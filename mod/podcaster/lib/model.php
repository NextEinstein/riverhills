<?php
/**
 * podcaster data wrapper and formats for xml and form generation 
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 *
 **/
if (!defined('MOODLE_INTERNAL')) {
  die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

/** 
  * implements podcaster_channel AND podcaster_item interface 
  */
class podcaster_channelimpl extends podcaster_channel {
  var $items,
      $canedit,
      $repositoryObj,
      $rssRepositoryObj,
      $imageObject,
      $coursemodule,
      $status;

  /**
    * constructor should not be called directly, use podcaster_channel::create  instead
    */
  function podcaster_channelimpl (&$record, $cm) {
    foreach ($record as $key => $value) {
      $this->$key = $value;
    }
    $this->items            = NULL;
    $this->repositoryObj    = NULL;
    $this->rssRepositoryObj = NULL;
    $this->imageObj         = NULL;
    if ($cm == NULL) {
      $cm = get_coursemodule_from_instance ('podcaster', $this->id, $this->course);
    }
    $this->coursemodule  = $cm;
    $this->canedit       = $this->coursemodule && has_capability ('moodle/course:manageactivities', get_context_instance (CONTEXT_MODULE, $this->coursemodule->id)); 
    $this->status        = -1;
  } // podcaster_channelimpl ()

  function can_edit () {
    return $this->canedit;
  } // can_edit ()

  function get_items () {
    if ($this->items == NULL) {
      $this->items = array ();
      $items = get_records ('podcaster_item', 'channel', $this->id, 'scheduledtime,id');
      if ($items) {
        foreach ($items as $item) {
          if ($item->scheduledtime < time () || $this->can_edit ()) {
            $this->items[] = new podcaster_itemimpl ($item, $this);
          }
        }
      }
    }
    return $this->items;
  } // get_items ()

  function create_item () {
    return new podcaster_itemimpl ($v = array ('id' => 0, 'title' => '', 'description' => ''), $this);
  } // create_item ()

  function get_item ($itemId) {
    $r = get_record ('podcaster_item', 'id', $itemId, 'channel', $this->id);
    if ($r != false && ($this->can_edit () || $r->scheduledtime < time ())) {
      return new podcaster_itemimpl ($r, $this);
    }
    return NULL;
  } // get_item ()

  function & get_repository ($rssRepos = false) {
    global $CFG;
    if ($rssRepos) {
      if ($this->rssRepositoryObj === NULL) {
        // no yet initialized
        do {
          $this->rssRepositoryObj = false;
          if (!isset ($this->repository) || !$this->repository) break;
          $this->rssRepositoryObj = podcaster_repository::create_repository ($this->repository, false, true);
        } while (false);
      }
      return $this->rssRepositoryObj;
    }
    else {
      if ($this->repositoryObj === NULL) {
        // no yet initialized
        do {
          $this->repositoryObj = false;
          if (!isset ($this->repository) || !$this->repository) break;
          $this->repositoryObj = podcaster_repository::create_repository ($this->repository);
        } while (false);
      }
      return $this->repositoryObj;
    }
  } // get_repository ()

  function get_rss_link ($withcredentials = false) {
    $r    = $this->get_repository (true);
    if (!$r) return false;
    return $r->get_url ($r->prefix.'://rss/'.$this->id.'.xml', $this->course, $withcredentials);
  } // get_rss_link ()

  function get_rss_status () {
    if ($this->status == -1) {
      do {
        // already verified?
        if (!$this->dirty) {
          $this->status = PODCASTER_RSS_OK;
          break;
        }
        $r = $this->get_repository (true);
        if (!$r->synchronize) {
          $this->status = PODCASTER_RSS_OK;
          break;
        }
        $this->status = PODCASTER_RSS_UNKOWN;

        $local  = @file_get_contents ($r->prefix.'://'.$this->course.'/rss/'.$this->id.'.xml');
        if (!$local) {
          $this->status = PODCASTER_RSS_ERROR;
          break;
        }

        $url    = $this->get_rss_link (true);
        if (ini_get ('allow_url_fopen')) {
          $remote = @file_get_contents ($url);
        }
        elseif (function_exists ('curl_init')) {
          if (!($ch = curl_init($url))) {
            $this->status = PODCASTER_RSS_UNKOWN;
            break;
          }
          curl_setopt ($ch, CURLOPT_HEADER, 0);
          curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
          $remote = curl_exec ($ch);
          curl_close ($ch);
        }
        else {
          $this->status = PODCASTER_RSS_UNKOWN;
          break;
        }
        if (!$remote) {
          $this->status = PODCASTER_RSS_UNKOWN;
          break;
        }
        $this->status = ((strcmp($remote, $local) == 0) ? PODCASTER_RSS_OK : PODCASTER_RSS_OUTDATED);
        if ($this->status == PODCASTER_RSS_OK) {
          $updobj        = new object ();
          $updobj->id    = $this->id;
          $updobj->dirty = 0;
          $this->dirty   = 0;
          update_record ('podcaster', $updobj);
        }
      } while (false);
    }

    return $this->status;
  } // get_rss_status ()

  function get_image () {
    global $CFG;
    if ($this->imageObj == NULL) {
      $this->imageObj = $this->get_cached_image ();
    }
    return $this->imageObj;
  } // get_image ()
  
  function get_cached_image () {
    global $CFG;
    // need to update mimetype and length?
    if ( ($this->image && (!$this->imageurl || !$this->imagetype || !$this->imagelength)) ||
         (!$this->image && $this->imageurl)) {

      require_once ($CFG->libdir.'/filelib.php');
      $r =& $this->get_repository ();

      $updateObj = new object ();
      $updateObj->id     = $this->id;

      $this->imageurl    = $updateObj->imageurl     = '';
      $this->imagewidth  = $updateObj->imagewidth   = 0;
      $this->imageheight = $updateObj->imageheight  = 0;
      $this->imagelength = $updateObj->imagelength  = 0;
      $this->imagetype   = $updateObj->imagetype    = '';

      if ($r && ($url = $r->get_url ($r->prefix.'://'.$this->image, $this->course)) !== false) {
        $this->imageurl    = $updateObj->imageurl    = $url;
        $this->imagetype   = $updateObj->imagetype   = mimeinfo ('type', $this->image);
        $this->imagelength = $updateObj->imagelength = repository_filesize ($r->prefix.'://'.$this->course.'/'.$this->image);

        if (function_exists ('getimagesize')) {
          if (($sizeinfo = getimagesize ($r->prefix.'://'.$this->course.'/'.$this->image)) != NULL) {
            $this->imagewidth  = $updateObj->imagewidth  = $sizeinfo[0];
            $this->imageheight = $updateObj->imageheight = $sizeinfo[1];
          }
        }
      }
      update_record ('podcaster', $updateObj);
    }
    if ($this->imageurl) {
      $result = new object ();
      $result->url    = $this->imageurl;
      $result->type   = $this->imagetype;
      $result->length = $this->imagelength;
      $result->width  = $this->imagewidth;
      $result->height = $this->imageheight;
    }
    else {
      $result = NULL;
    }
    return $result;
  } // get_cached_image ()

  function update_rss () {
    // make sure stream is registered
    $r = $this->get_repository (true);
    if (!$r) 
      return false;
    
    $destpath = $r->prefix.'://'.$this->course.'/rss';
    $destfile = $destpath.'/'.$this->id.'.xml';
    if ((isset ($this->visible) && !$this->visible) ||              // just submitted
        ($this->coursemodule && !$this->coursemodule->visible && !isset ($this->visible))) { // called from cron
      @unlink ($destfile);
      return true;
    }
    repository_make_upload_directory ($destpath);
    
    $format = podcaster_format::create_format ($this->format);
    $format->set_data ($this);
    $format->write_xml($w = new podcaster_filestreamwriter ($destfile));
    return true;
  } // update_rss ()


// podcaster_item interface
  function get_enclosure () {
    return $this->get_image ();
  } // get_enclosure ()

  function get_data () {
    $result = new object ();
    $skip   = array ('repositoryObj', 'imageObj', 'canedit', 'coursemodule', 'items');
    foreach ($this as $p => $v) {
      if (!in_array($p, $skip)) {
        $result->$p = $this->$p;
      }
    }
    return $result;
  } // get_data ()

  function delete_file ($path, $isRssFile = false) {
    $r =& $this->get_repository ($isRssFile);
    @unlink ($r->prefix.'://'.$this->course.'/'.$path);
  } // delete_file ()

} // class podcaster_channelimpl

class podcaster_metachannelimpl extends podcaster_channelimpl {
// Version 2008022501: repositories only supported
  var $params;

  function podcaster_metachannelimpl (&$record, $coursemodule = NULL) {
    $this->podcaster_channelimpl ($record, $coursemodule);
    $this->params = explode (',', $this->params);
  } // podcaster_metachannelimpl ()

  function get_items () {
    if ($this->items == NULL) {
      foreach ($this->params as $p) {
        $channels = get_records_select ('podcaster', 'repository = '.$p.' AND ismeta = 0', 'name,timemodified');
        if ($channels) {
          foreach ($channels as $channel) {
            $item = podcaster_channel::create_channel ($channel);
            if ($item->coursemodule->visible) {
              $item->title       = $item->name;
              $item->description = $item->intro;
              $this->items[] = $item;
            }
          }
        }
      }
    }
    return $this->items;
  } // get_items ()

  function update_rss () {
    if ($this->path == '')
      return;
    // make sure stream is registered
    $r = $this->get_repository (true);
    if (!$r) 
      return false;

    $dirname  = dirname ($this->path);
    $destpath = $r->prefix.'://'.$dirname;
    $destfile = $r->prefix.'://'.$this->path;

    repository_make_upload_directory ($destpath);
    $format = podcaster_format::create_format ($this->format);
    $format->set_data ($this);
    $format->write_xml($w = new podcaster_filestreamwriter ($destfile));
    return true;
  } // update_rss ()
  
  function get_rss_link ($withcredentials = false) {
    $r    = $this->get_repository (true);
    if (!$r) return false;
    return $r->get_url ($r->prefix.'://'.$this->path);
  } // get_rss_link ()


} // class podcaster_metachannelimpl 

class podcaster_itemimpl extends podcaster_item {
  var $channelObj,
      $enclosureObj;

  function podcaster_itemimpl (&$record, &$channelObj) {
    $this->channelObj = $channelObj;
    foreach ($record as $key => $value) {
      $this->$key = $value;
    }
    $this->enclosureObj = NULL;
  } // podcaster_itemimpl ()

  function get_enclosure () {
    if ($this->enclosureObj == NULL) {
      $this->enclosureObj = $this->get_cached_enclosure ();
    }
    return $this->enclosureObj;
  } // get_enclosure ()
      
  function get_cached_enclosure () {
    global $CFG;
    // need to calculate mimetype and length?
    if (($this->enclosure && (!$this->enclosureurl || !$this->enclosuretype || !$this->enclosurelength)) ||
        (!$this->enclosure && $this->enclosureurl)) {
      @include_once ($CFG->libdir.'/filelib.php');
      $r =& $this->channelObj->get_repository ();

      $updateObj = new object ();
      $updateObj->id         = $this->id;
      $this->enclosureurl    = $updateObj->enclosureurl    = '';
      $this->enclosuretype   = $updateObj->enclosuretype   = '';
      $this->enclosurelength = $updateObj->enclosurelength = 0;
      
      if ($r && ($url = $r->get_url ($r->prefix.'://'.$this->enclosure, $this->channelObj->course)) !== '') {
        $this->enclosureurl    = $updateObj->enclosureurl    = $url;
        $this->enclosuretype   = $updateObj->enclosuretype   = mimeinfo ('type', $this->enclosure);
        $this->enclosurelength = $updateObj->enclosurelength = repository_filesize ($r->prefix.'://'.$this->channelObj->course.'/'.$this->enclosure);
      }
      update_record ('podcaster_item', $updateObj);
    }
    if ($this->enclosureurl) {
      $result = new object ();
      $result->name   = $this->enclosure;
      $result->url    = $this->enclosureurl;
      $result->type   = $this->enclosuretype;
      $result->length = $this->enclosurelength;
    }
    else {
      $result = NULL;
    }
    return $result;
  } // get_cached_enclosure ()

  function get_data () {
    $result = new object ();
    $skip   = array ('channelObj', 'enclosureObj');
    foreach ($this as $p => $v) {
      if (!in_array($p, $skip)) {
        $result->$p = $this->$p;
      }
    }
    return $result;
  } // get_data ()

} // class podcaster_itemimpl

class podcaster_licenseimpl {
  function podcaster_licenseimpl ($record) {
     foreach ($record as $key => $value) {
      $this->$key = $value;
    }
  } // podcaster_licenseimpl ()

} // class podcaster_licenseimpl
?>
