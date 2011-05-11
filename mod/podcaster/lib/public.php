<?php
/**
 * All podcaster public interfaces
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 *
 * @TODO: documentation
 *
 **/
if (!defined('MOODLE_INTERNAL')) {
  die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

/**
  * interface podcaster_channel
  *
  */
class podcaster_channel {
  /**
    * factory
    */
  function & create_channel (&$record, $cm = NULL) {
    static $channels;
    global $CFG;
    require_once ($CFG->dirroot.'/mod/podcaster/lib/model.php');
    if (!is_array ($channels)) {
      $channels = array ();
    }
    if (!array_key_exists ($record->id.'', $channels)) {
      if (isset($record->ismeta) && $record->ismeta) {
        $channels[$record->id.''] = new podcaster_metachannelimpl ($record, $cm);
      }
      else {
        $channels[$record->id.''] = new podcaster_channelimpl ($record, $cm);
      }
    }
    return $channels[$record->id.''];
  } // create_channel ()

  function create_metachannel (&$record) {
    static $metachannels;
    if (!is_array ($metachannels)) {
      $metachannels = array ();
    }
    if (!array_key_exists ($record->id.'', $metachannels)) {
      $channel = get_record ('podcaster', 'id', $record->channel);
      $channel->target       = $record->target;
      $channel->params       = $record->params;
      $channel->path         = $record->path;
      $channel->timemodified = ($channel->timemodified > $record->timemodified ? $channel->timemodified : $record->timemodified);

      $metachannels[$record->id.''] = podcaster_channel::create_channel ($channel);
    }
    return $metachannels[$record->id.''];
  } // create_metachannel ()

  /**
    * constructor should not be called directly, use
    * factory instead
    */
  function podcaster_channel (&$record, $cm) {
  } // podcaster_channel ()
 
  function can_edit () {
  } // can_edit ()

  function get_items () {
  } // get_items ()

  function create_item () {
  } // create_item ()

  function get_item ($itemId) {
  } // get_item ()

  function & get_repository () {
  } // get_repository ()

  function get_rss_link () {
  } // get_rss_link ()

  function get_image () {
  } // get_image ()

  function update_rss () {
  } // update_rss ()

  function delete_file ($path) {
  } // delete_file ()
} // class podcaster_channel


/**
  * interface podcaster_item
  *
  */
class podcaster_item {
  function podcaster_item (&$record, &$channelObj) {
  } // podcaster_item ()

  function get_enclosure () {
  } // get_enclosure ()

  function get_data () {
  } // get_data ()

} // class podcaster_item

/**
  * interface podcaster_license
  *
  */
class podcaster_license {
  /*
   * factory
   */
  function create_license ($licenseId, $repositoryId = NULL) {
    global $CFG;
    require_once ($CFG->dirroot.'/mod/podcaster/lib/model.php');

    if (!$licenseId) {
      $repository = get_record ('podcaster_repository', 'id', $repositoryId);
      if ($repository) {
        $licenseId = $repository->license;
      }
    }
    if ($licenseId && ($license = get_record ('podcaster_license', 'id', $licenseId)) != NULL) {
      $result = new podcaster_licenseimpl ($license);
    }
    else {
      $result = NULL;
    }
    return $result;
  } // create_license ()

  function podcaster_license ($record) {
  } // podcaster_license ()
} // class podcaster_license


/**
  * interface podcaster_format
  *
  * default implementation: podcaster/lib/format.php class podcaster_formatimpl
  *
  */
class podcaster_format {
  function create_format ($name) {
    global $CFG;
    require_once ($CFG->dirroot.'/mod/podcaster/lib/format.php');
    include_once ($CFG->dirroot.'/mod/podcaster/formats/'.$name.'/lib.php');
    $classname = $name.'_format';
    if (!class_exists ($classname)) {
      error ('Error! Podcast format "'.$classname.'" doesn\'t exist');
    }
    $result = new $classname ();
    return $result;
  } // create_format ()

  /* public final */ 
  function set_data (&$channel) {
  } // set_data ()

  /* public final */ 
  function set_obj (&$channelObj) {
  } // set_obj ()
  
  // XML generation
  /* public final */ 
  function write_xml (&$stream) {
  } // write_xml ()

// podcaster_format interface
  function preprocess_channel (&$channel) {
  } // preprocess_channel ()

  function preprocess_item (&$item) {
  } // preprocess_item ()

  function get_schema () {
  } // get_schema ()

  function define_channelform (&$mform) {
  } // define_channelform ()

  function define_itemform (&$mform) {
  } // define_itemform ()

  function get_channelhtml (&$channel, &$page) {
  } // get_channelhtml ()

  function get_channellinkshtml (&$channel, &$page) {
  } // get_channelhtml ()

  function get_itemhtml (&$item, &$page) {
  } // get_itemhtml ()

  function get_itemdetailhtml (&$item, &$page) {
  } // get_itemdetailhtml ()

} // class podcaster_format


/**
  * interface podcaster_page
  *
  * default implementation: podcaster/lib/page.php class podcaster_pageimpl
  *
  */
class podcaster_page {

  function & create_page (&$channel) {
    global $CFG;
    require_once ($CFG->dirroot.'/mod/podcaster/lib/page.php');
    $r = new podcaster_pageimpl ($channel);
    return $r;
  } // create_page ()

  function get_stylesheet () {
  } // get_stylesheet ()
//
// channel
//
  function print_channel_header () {
  } // print_channel_header ()

  function print_channel () {
  } // print_channel ()

  function print_channel_footer () {
  } // print_channel_footer ()

//
// itemlist
//
  function print_itemlist_header () {
  } // print_itemlist_header ()

  function print_itemlist_footer () {
  } // print_itemlist_footer ()

  function print_item (&$item) {
  } // print_item ()

  function print_item_actions (&$item) {
  } // print_item_actions ()

  function print_item_enclosure (&$item) {
  } // print_item_enclosure ()

  function print_add_item_button () {
  } // print_add_item_button ()

  function print_itemdetail_header () {
  } // print_itemdetail_header ()

  function print_itemdetail (&$item) {
  } // print_itemdetail ()

  function print_itemdetail_footer () {
  } // print_itemdetail_footer ()

  function embedd_html ($url, $title) {
  } // embedd_html ()

} // class podcaster_page

define ('PODCASTER_RSS_OK',       1);
define ('PODCASTER_RSS_OUTDATED', 2);
define ('PODCASTER_RSS_ERROR',    3);
define ('PODCASTER_RSS_UNKOWN',   4);

/** 
  * interface podcaster_repository
  */
class podcaster_repository {
/**
  * Factory
  */
  function & create_repository ($data, $disabled = false, $rss = false) {
    global $CFG;
    require_once ($CFG->dirroot.'/mod/podcaster/lib/repository.php');
    if (is_object ($data)) {
      $info = $data;
    }
    else {
      // is_int the PHP Way
      if ($data === 0 || $data * 1 !== 0) {
        if (!$disabled) {
          if (($info = get_record('podcaster_repository', 'id', $data, 'enabled', '1')) == false) {
            return $info;
          }
        }
        else {
          if (($info = get_record('podcaster_repository', 'id', $data)) == false) {
            return $info;
          }
        }
      }
      elseif (!$disabled) {
        if (($info = get_record('podcaster_repository', 'prefix', $data, 'enabled', '1')) == false) {
          return $info;
        }
      } else {
        if (($info = get_record('podcaster_repository', 'prefix', $data)) == false) {
          return $info;
        }
      }
    }
    if ($rss && $info->id != $info->rss && $info->rss != 0) {
      $obj =& podcaster_repository::create_repository ($info->rss, $disabled, false);
      return $obj;
    }
    $obj =& podcaster_repositoryimpl::_singleton ($info);
    return $obj;
  } // create_repository ()

  function podcaster_repository ($plugin = NULL, $prefix = 'NULL://') {
  } // podcaster_repository ()

  function init ($path) {
  } // init ()

// Moodle podcaster_repository interface
  function local_path ($path) {
  } // local_path ()

  function get_url ($path, $course = NULL) {
  } // get_url ()

  function get_scheme () {
  } // get_scheme ()

  function get_arg ($first = false) {
  }

  function get_default_params () {
  } // get_default_params ()

  function is_local () {
  } // is_local ()

  function is_allowed ($filename) {
  } // is_allowed ()

  function get_param ($key, $defaultValue = NULL) {
  } // get_param ()

  function set_param ($key, $value) {
  } 

  function set_error ($errno = 0, $error = '') {
  } 

  function error () {
  }

  function errno () {
  }

// PHP Stream API
  /**
    *  bool Stream_open (string $path, string $mode, 
    *                    int $options, string $opened_path)
    *
    * This method is called immediately after your stream object is created. 
    *
    * path specifies the URL that was passed to fopen() and that this object 
    * is expected to retrieve. You can use parse_url() to break it apart.
    *
    * mode is the mode used to open the file, as detailed for fopen(). You 
    * are responsible for checking that mode is valid for the path requested.
    *
    * options holds additional flags set by the streams API. It can hold one or
    * more of the following values OR'd together.
    * 
    * Flag                 Description
    * STREAM_USE_PATH       If path is relative, search for the resource using 
    *                       the include_path.
    * STREAM_REPORT_ERRORS  If this flag is set, you are responsible for raising
    *                       errors using trigger_error() during opening of the
    *                       stream. If this flag is not set, you should not 
    *                       raise any errors.
    *
    * If the path is opened successfully, and STREAM_USE_PATH is set in 
    * options, you should set opened_path to the full path of the file/resource
    * that was actually opened.
    *
    * If the requested resource was opened successfully, you should return TRUE,
    * otherwise you should return FALSE 
    */
    function stream_open ($path, $mode, $options, &$opened_path) {
    } 

  /**
    * void stream_close ( void )
    *
    * This method is called when the stream is closed, using fclose().
    * You must release any resources that were locked or allocated by the 
    * stream. 
    */
  function stream_close () {
  }

  /** 
    * string stream_read ( int $count )
    *
    * This method is called in response to fread() and fgets() calls on the 
    * stream. You must return up-to count bytes of data from the current read/
    * write position as a string. If there are less than count bytes available,
    * return as many as are available. 
    * If no more data is available, return either FALSE or an empty string. 
    * You must also update the read/write position of the stream by the number 
    * of bytes that were successfully read. 
    */

  function stream_read ($count) {
  }

  /**
    * int stream_write ( string $data )
    * This method is called in response to fwrite() calls on the stream. 
    * You should store data into the underlying storage used by your stream. 
    * If there is not enough room, try to store as many bytes as possible. You 
    * should return the number of bytes that were successfully stored in the 
    * stream, or 0 if none could be stored. You must also update the read/write
    * position of the stream by the number of bytes that were successfully
    * written. 
    */
  function stream_write ($data) {
  }

  /**
    * bool stream_eof ( void )
    *
    * This method is called in response to feof() calls on the stream. 
    * You should return TRUE if the read/write position is at the end of the 
    * stream and if no more data is available to be read, or FALSE otherwise.
    */
  function stream_eof () {
  }

  /** 
    * int stream_tell ( void )
    *
    * This method is called in response to ftell() calls on the stream. You 
    * should return the current read/write position of the stream.
    */
    function stream_tell () {
    }

  /** 
    * bool stream_seek ( int $offset, int $whence )
    *
    * This method is called in response to fseek() calls on the stream. 
    * You should update the read/write position of the stream according to 
    * offset and whence. See fseek() for more information about these 
    * parameters. Return TRUE if the position was updated, FALSE otherwise.
    */

    function stream_seek ($offset, $whence) {
    }

  /**
    * bool stream_flush ( void )
    *
    * This method is called in response to fflush() calls on the stream. 
    * If you have cached data in your stream but not yet stored it into the 
    * underlying storage, you should do so now. Return TRUE if the cached data 
    * was successfully stored (or if there was no data to store), or FALSE if 
    * the data could not be stored.
    */
    function stream_flush () {
    }

  /**
    * array stream_stat ( void ) 
    *
    * This method is called in response to fstat() calls on the stream and 
    * should return an array containing the same values as appropriate for the
    * stream.
    */
    function stream_stat () {
    }
    
  /**
    * bool unlink ( string $path )
    *
    * This method is called in response to unlink() calls on URL paths 
    * associated with the wrapper and should attempt to delete the item 
    * specified by path. It should return TRUE on success or FALSE on failure.
    * In order for the appropriate error message to be returned, do not define 
    * this method if your wrapper does not support unlinking.
    *
    * Remark: Userspace wrapper unlink method is not supported prior to 
    *         PHP 5.0.0. 
    */
    function unlink ($path) {
    }

  /**
    * bool rename ( string $path_from, string $path_to )
    *
    * This method is called in response to rename() calls on URL paths 
    * associated with the wrapper and should attempt to rename the item 
    * specified by path_from to the specification given by path_to. It should 
    * return TRUE on success or FALSE on failure. In order for the appropriate 
    * error message to be returned, do not define this method if your wrapper 
    * does not support renaming.
    *
    * Remark: Userspace wrapper rename method is not supported prior to
    *         PHP 5.0.0. 
    */
    function rename ($path_from, $path_to) {
    }

  /** 
    * bool mkdir ( string $path, int $mode, int $options )
    *
    * This method is called in response to mkdir() calls on URL paths associated
    * with the wrapper and should attempt to create the directory specified by
    * path. It should return TRUE on success or FALSE on failure. In order for
    * the appropriate error message to be returned, do not define this method 
    * if your wrapper does not support creating directories. 
    *
    * Possible values for options include STREAM_REPORT_ERRORS and 
    * STREAM_MKDIR_RECURSIVE.
    *
    * Remark: Userspace wrapper mkdir method is not supported prior to 
    *         PHP 5.0.0. 
    */
    function mkdir ($path, $mode, $options) {
    }

  /** 
    * bool rmdir ( string $path, int $options )
    *
    * This method is called in response to rmdir() calls on URL paths associated
    * with the wrapper and should attempt to remove the directory specified by
    * path. It should return TRUE on success or FALSE on failure. In order for 
    * the appropriate error message to be returned, do not define this method
    * if your wrapper does not support removing directories. 
    * Possible values for options include STREAM_REPORT_ERRORS.
    *
    * Remark: Userspace wrapper rmdir method is not supported prior to
    *         PHP 5.0.0. 
    */
    function rmdir ($path, $options) {
    }

  /**
    * bool dir_opendir ( string $path, int $options )
    *
    * This method is called immediately when your stream object is created for 
    * examining directory contents with opendir(). 
    * path specifies the URL that was passed to opendir() and that this object 
    * is expected to explore. You can use parse_url() to break it apart.
    */
    function dir_opendir ($path, $options) {
    }
    
  /**
    * array url_stat ( string $path, int $flags )
    *
    * This method is called in response to stat() calls on the URL paths 
    * associated with the wrapper and should return as many elements in common 
    * with the system function as possible. Unknown or unavailable values 
    * should be set to a rational value (usually 0).
    *
    * flags holds additional flags set by the streams API. It can hold one or 
    * more of the following values OR'd together:
    * Flag                  Description
    * STREAM_URL_STAT_LINK  For resources with the ability to link to other 
    *                       resource (such as an HTTP Location: forward, or a
    *                       filesystem symlink). 
    *                       This flag specified that only information about the 
    *                       link itself should be returned, not the resource 
    *                       pointed to by the link. This flag is set in response
    *                       to calls to lstat(), is_link(), or filetype().
    * STREAM_URL_STAT_QUIET If this flag is set, your wrapper should not raise 
    *                       any errors. If this flag is not set, you are 
    *                       responsible for reporting errors using the 
    *                       trigger_error() function during stating of the path.
    */
    function url_stat ($path, $options) {
    }

  /**
    * string dir_readdir ( void )
    *
    * This method is called in response to readdir() and should return a string
    *  representing the next filename in the location opened by dir_opendir().
    */
    function dir_readdir () {
    }

  /** 
    * bool dir_rewinddir ( void )
    *
    * This method is called in response to rewinddir() and should reset the 
    * output generated by dir_readdir(). i.e.: The next call to dir_readdir()
    * should return the first entry in the location returned by dir_opendir().
    */
   function dir_rewinddir () {
   }
  
  /**
    * bool dir_closedir ( void )
    *
    * This method is called in response to closedir(). You should release any 
    * resources which were locked or allocated during the opening and use of 
    * the directory stream.
    */
    function close_dir () {
    }

} // class podcaster_repository()
?>
