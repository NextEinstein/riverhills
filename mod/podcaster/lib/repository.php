<?php
/**
 * podcaster repository lib
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 *
 */

/**
  * substitutes for functions missing in the stream API
  *
  * Required changes in files/index.php, file.php, uploadlib.php and filelib.php:
  *
  *   file_exists:           use repository_file_exists instead
  *   is_dir:                use repository_is_dir instead
  *   filemtime:             use repository_filemtime instead
  *   filesize:              use repository_filesize instead
  *   feof:                  change code to compare requested bytes to bytes returned by fread instead
  *   chmod:                 use repository_chmod instead
  *   move_uploaded_file:    use repository_move_uploaded_file instead
  *   get_directory_size:    use repository_get_directory_size instead
  *   make_upload_directory: use repository_make_upload_directory instead
  *
  * NOTE: the default podcaster_repository implementation just strips the prefix part from
  *       given path and calls local fs-functions
  */

function repository_get_scheme ($url) {
  if (($p = strpos ($url, '://')) !== false) {
    return substr ($url, 0, $p);
  }
  return false;
} // repository_get_scheme ()

function repository_is_local ($path = NULL) {
  $proxy =& podcaster_repository::create_repository (repository_get_scheme($path));

  if ($proxy) 
    return ($proxy->is_local () ? $proxy : false);

  return false;
} // repository_is_local ()

if (!function_exists('repository_bufsiz')) {
  define ('DEFAULT_BUFSIZ',    1 * 1024 * 1024);
  function repository_bufsiz ($preferred_size = DEFAULT_BUFSIZ) {
    return repository_bufsiz_cb ($preferred_size);
  } 
}

// TODO!
function repository_chmod ($path, $mode) {
  if (($r = repository_is_local ($path)) !== false) {
    return chmod ($r->local_path ($path), $mode);
  }
  return true;
} // repository_chmod ()
  
function repository_bufsiz_cb ($preferred_size) {
  if (repository_is_local()) return $preferred_size;
  return 8192;
} // repository_bufsiz_cb ()

function repository_file_exists ($path) {
  if (($r = repository_is_local ($path)) !== false) {
    return @file_exists ($r->local_path ($path));
  }
  $stats = repository_stat ($path);
  if (is_array ($stats)) {
    return true;
  }
  return false;
} // repository_file_exists ()

function repository_move_uploaded_file ($src, $dest) {
  global $CFG;
  if (($r = repository_is_local ($dest)) !== false) {
    return move_uploaded_file($src, $r->local_path ($dest));
  }
  $result = false;
  do {
    if (($tmpn = tempnam ($CFG->dataroot.'/temp/FTP/', 'FTP')) == false) break;
    if (!move_uploaded_file ($src, $tmpn)) break;
    if (!($dfp = fopen ($dest, 'wb'))) break;
    if (!($sfp = fopen ($tmpn,  'rb'))) break;
    while (($data = fread ($sfp, 8192))) {
      fwrite ($dfp, $data);
    }
    fclose ($dfp);
    fclose ($sfp);
    unlink ($tmpn);
    $result =  true;
  } while (false);
  return $result;
} // repository_move_uploaded_file ()

function repository_is_dir ($path) {
  if (repository_is_local ($path)) {
    return @is_dir ($path);
  }
  $stat = repository_stat ($path);
  if (is_array ($stat) && array_key_exists ('is_dir', $stat)) {
    return $stat['is_dir'];
  }
  else if (is_array ($stat) && array_key_exists ('nlink', $stat)) {
    return $stat['nlink'] > 1;
  }
  return false;
} // repository_is_dir ()

function repository_filemtime($path) {
  if (repository_is_local ($path)) {
    return filemtime ($path);
  }
  $stat = repository_stat ($path);
  if (is_array($stat) && array_key_exists ('mtime', $stat)) {
    return $stat['mtime'];
  }
  return -1;
} // repository_filemtime ()

function repository_filesize ($path) {
  if (repository_is_local ($path)) {
    return filesize ($path);
  }
  $stat = repository_stat ($path);
  if (is_array($stat) && array_key_exists ('size', $stat)) {
    return $stat['size'];
  }
  return -1;
} // repository_filesize ()

function repository_stat($path, $clearstatcache = false) {
  static $cache;
  if (!is_array($cache) || $clearstatcache) {
    $cache = array();
  }
  if ($path == NULL) return;
  if (!array_key_exists ($path, $cache)) {
    do {
      $stat = @stat ($path);
    } while (false);
    $cache[$path] = $stat;
  }
  return $cache[$path];
} // repository_stat ()

function repository_get_directory_size($rootdir, $excludefile='') {
    global $CFG;
    if (($r = repository_is_local ($rootdir)) !== false) {
      return get_directory_size ($r->local_path ($rootdir), $excludefile);
    }
    if (!repository_is_dir($rootdir)) {          // Must be a directory
        return 0;
    }
    if (!$dir = @opendir($rootdir)) {  // Can't open it for some reason
        return 0;
    }
    $size = 0;
    while (false !== ($file = readdir($dir))) {
        $firstchar = substr($file, 0, 1);
        if ($firstchar == '.' or $file == 'CVS' or $file == $excludefile) {
            continue;
        }
        $fullfile = $rootdir .'/'. $file;
        if (repository_is_dir($fullfile)) {
            $size += repository_get_directory_size($fullfile, $excludefile);
        } else {
            $size += repository_filesize($fullfile);
        }
    }
    closedir($dir);
    return $size;
} // repository_get_directory_size ()

function repository_make_upload_directory($directory, $shownotices=true) {
    global $CFG;
    $currdir = $directory;
    if (!repository_file_exists($currdir)) {
        if (! mkdir($currdir, $CFG->directorypermissions)) {
            if ($shownotices) {
                echo '<div class="notifyproblem" align="center">ERROR: Could not find or create a directory ('. 
                     $currdir .')</div>'."<br />\n";
            }
            return false;
        }
    }
    return $currdir;
} // repository_make_upload_directory ()

function repository_link_to_popup_window ($url, $name='popup', $linkname='click here',
                               $height=400, $width=500, $title='Popup window',
                               $options='none', $return=false) {

    global $CFG;

    if ($options == 'none') {
        $options = 'menubar=0,location=0,scrollbars,resizable,width='. $width .',height='. $height;
    }
    $fullscreen = 0;

    $link = '<a title="'. s(strip_tags($title)) .'" href="'.$url.'" '.
           "onclick=\"this.target='$name'; return repository_openpopup('$url', '$name', '$options', $fullscreen);\">$linkname</a>";
    if ($return) {
        return $link;
    } else {
        echo $link;
    }
}

/** 
  * class podcaster_repositoryimpl
  *
  * documentation copied from
  *   http://php.net/manual/de/function.stream-wrapper-register.php
  *
  */ 
class podcaster_repositoryimpl
{

  var $plugin,
      $prefix,
      $params,
      $pluginmap,
      $fp,
      $dirhandle;


  function podcaster_repositoryimpl ($plugin = NULL, $prefix = 'NULL') {
    $this->plugin    = $plugin;
    $this->prefix    = $prefix;
    $this->params    = array ();
    $this->fp        = NULL;
    $this->dirhandle = NULL;
    if (strtolower(get_class($this)) == 'podcaster_repositoryimpl') {
      $pluginmap = array();
    } 
    else {
      $pluginmap = NULL;
    }
  }

  function init ($path) {
    if ($this->plugin == NULL) {
      $scheme =  repository_get_scheme ($path);
      $obj    =& podcaster_repository::create_repository ($scheme);

      foreach ($obj as $key => $value) {
        // if (!isset ($this->$key)) {
          $this->$key = $value;
        // }
      }
    }
    else {
    }
  } // init ()

// Moodle podcaster_repository interface
  function local_path ($path) {
    return $path;
  } // local_path ()

  function get_url ($path, $course = NULL, $withcredentials = false) {
    return $path;
  } // get_url ()

  function get_scheme () {
    return $this->prefix;
  } // get_scheme ()

  function get_arg ($first = false) {
    return ($first ? '?' : '&amp;').'repository='.$this->prefix;
  }

  function get_default_params () {
    return '';
  } // get_default_params ()

  function is_local () {
    return true;
  } // is_local ()

  function is_shared () {
    return true;
  } // is_shared ()

  function is_folder ($filename) {
    return is_dir ($this->local_path ($filename));
  } // is_folder ()

  function is_allowed ($filename) {
    if (eregi('\.([a-z0-9]+)$', $filename, $ext)) {
      return array_key_exists (strtolower(substr($ext[0], 1)), $this->allowed_types);
    }
    return false;
  } // is_allowed ()

/**
  * Factory
  * 
  */
  function & singleton ($caller) {
    if ($caller == NULL) return podcaster_repositoryimpl::_singleton ($r = NULL);
    if (!isset ($caller->plugin)) {
      $caller->plugin = podcaster_repositoryimpl::get_plugin($caller->prefix);
    }
    return podcaster_repositoryimpl::_singleton ($caller);
  } // singleton ()

  function & _singleton (&$caller) {
    global $CFG;
    static $instances; 
    static $self;

    $plugin      = NULL;
    $prefix      = NULL;
    $name        = NULL;
    $params      = NULL;
    $filefilter  = NULL;
    $public      = false;
    $shared      = false;
    $synchronize = false;
    $id          = NULL;
    if ($caller != NULL) {
      $plugin      = $caller->plugin;
      $prefix      = $caller->prefix;
      $name        = $caller->name;
      $params      = $caller->params;
      $filefilter  = $caller->filefilter;
      $public      = $caller->public;
      $shared      = $caller->shared;
      $synchronize = $caller->synchronize;
      $id          = $caller->id;
    }
    else {
      $caller = new object();
    }

    if ($plugin == NULL && $prefix == NULL) {
      if (!is_object($self)) {
        $self = new podcaster_repositoryimpl('NULL', 'NULL');
      }
      return $self;
    }

    if (!is_array ($instances)) {
      $instances = array ();
    }
    if (!array_key_exists ($prefix, $instances )) {
      include_once ($CFG->dirroot.'/mod/podcaster/repository/'.$plugin.'/lib.php');
      $classname   = $plugin.'_repository';
      if (!class_exists ($classname)) {
        error('Unable to load plugin "'.$plugin.'" (required class: '.$classname.', expected in repository/'.$plugin.'/lib.php)');
      }
      $instances[$prefix] = NULL;
      $instances[$prefix] = new $classname ($plugin, $prefix);
      if (is_object ($instances[$prefix])) { 
        foreach (explode ("\r", $params) as $p) {
          if (($lp = strpos ($p, '=')) !== false) {
            $lvalue = trim (substr ($p, 0, $lp));
            $rvalue = trim (substr ($p, $lp + 1));
            $instances[$prefix]->params[$lvalue] = $rvalue;
          }
        }
        $instances[$prefix]->id          = $id;
        $instances[$prefix]->name        = $name;
        $instances[$prefix]->filefilter  = $filefilter;
        $instances[$prefix]->prefix      = $prefix;
        $instances[$prefix]->public      = $public;
        $instances[$prefix]->shared      = $shared;
        $instances[$prefix]->synchronize = $synchronize;
        $allowedTypes = explode (',', $filefilter);
        $instances[$prefix]->allowed_types = array();
        foreach ($allowedTypes as $t) {
          $instances[$prefix]->allowed_types[strtolower(trim($t))] = true;
        }
        $class = get_class ($instances[$prefix]);
        stream_wrapper_register ($prefix, $class);
        // we need the reverse mapping too
        podcaster_repositoryimpl::add_plugin ($plugin, $prefix);
      }
    }
    return $instances[$prefix];
  } // _singleton ()

/**
  * PRIVATE
  */
  function get_plugin ($prefix) {
    $inst =& podcaster_repositoryimpl::singleton (NULL);
    return $inst->_get_plugin ($prefix);
  } // get_plugin()

  function _get_plugin ($prefix) {
    if (array_key_exists ($prefix, $this->pluginmap)) 
      return $this->pluginmap[$prefix];
    return NULL;
  } // _get_plugin()

  function add_plugin ($plugin, $prefix) {
    $inst =& podcaster_repositoryimpl::singleton (NULL);
    return $inst->_add_plugin ($plugin, $prefix);
  } // add_plugin()

  function _add_plugin ($plugin, $prefix) {
    $this->pluginmap[$prefix] = $plugin;
  } // _add_plugin()

// accessors / parameters
  function set_param ($key, $value) {
    $inst =& $this->singleton ($this);
    if (!$inst) error ('???');
    $inst->params[$key] = $value;
  } // set_param ()

  function & get_param ($key, $defaultValue = NULL) {
    $inst =& $this->singleton ($this);
    if (!$inst) error ('???');
    if (array_key_exists ($key, $inst->params)) {
      return $inst->params[$key];
    }
    return $defaultValue;
  } // get_param ()

  function set_error ($errno = 0, $error = '') {
    $inst =& podcaster_repositoryimpl::singleton(NULL);
    $inst->error = $error;
    $inst->errno = $errno;
  } // set_error ()

  function error () {
    $inst =& podcaster_repositoryimpl::singleton(NULL);
    return $inst->error;
  } // error ()

  function errno () {
    $inst =& podcaster_repositoryimpl::singleton(NULL);
    return $inst->errno;
  } // errno ()

// PHP Stream API
    function stream_open ($path, $mode, $options, &$opened_path) {
      $path = $this->local_path ($path);
      if (($this->fp = fopen ($path, $mode)) != false) {
        $this->opened_path = $path;
        $opened_path = $path;
        return true;
      }
      
      return false;
  } 

  function stream_close () {
    if ($this->fp != NULL) {
      fclose ($this->fp);
      $this->fp = NULL;
    }
  }

  function stream_read ($count) {
    if ($this->fp != NULL) {
      return fread ($this->fp, $count);
    }
    return '';
  }

  function stream_write ($data) {
    if ($this->fp != NULL) {
      return fwrite ($this->fp, $data);
    }
    return 0;
  }

  function stream_eof () {
    if ($this->fp != NULL) {
      return feof ($this->fp);
    }
    return true;
  }

  function stream_tell () {
    if ($this->fp != NULL) {
      return ftell ($this->fp);
    }
    return 0;
  }

  function stream_seek ($offset, $whence) {
    if ($this->fp != NULL) {
      return fseek ($this->fp, $offset, $whence);
    }
    return false;
  }

  function stream_flush () {
    if ($this->fp != NULL) {
      return fflush ($this->fp);
    }
    return false;
  }

  function stream_stat () {
    if ($this->fp != NULL) {
      return fstat ($this->fp);
    }
    return NULL;
  }
    
  function unlink ($path) {
    $path = $this->local_path ($path);
    return unlink ($path);
  }

  function rename ($path_from, $path_to) {
    return rename ($this->local_path ($path_from), $this->local_path($path_to));
  }

  function mkdir ($path, $mode, $options) {
    $path = $this->local_path ($path);
    $result =  mkdir ($path, $mode, $options);
    if ($result) repository_stat (NULL, true);     
    return $result;
  }

  function rmdir ($path, $options) {
    $path = $this->local_path ($path);
    return rmdir ($path);
  }

  function dir_opendir ($path, $options) {
    $path = $this->local_path ($path);
    $this->dirhandle = opendir ($path);
    return $this->dirhandle != false;
  }
    
  function url_stat ($path, $options) {
    $path = $this->local_path ($path);
    return stat ($path);
  }

  function dir_readdir () {
    if ($this->dirhandle) {
      $result = readdir ($this->dirhandle);
      return $result;
    }
    return false;
  }

 function dir_rewinddir () {
    if ($this->dirhandle) {
      return rewinddir ($this->dirhandle);
    }
    return false;
 }
  
  function close_dir () {
    if ($this->dirhandle) {
      $result = closedir ($this->dirhandle);
      $this->dirhandle = NULL;
      return $result;
    }
    return false;
  }

} // class podcaster_repositoryimpl
?>
