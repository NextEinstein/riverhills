<?php
/**
 * podcaster local repository
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 *
 */
include_once ($CFG->dirroot.'/mod/podcaster/lib/repository.php');

class ftp_repository extends podcaster_repositoryimpl {

  var $status,
      $tmpn,    // reserved file name for local buffering
      $tpmf,    // tmp file pointer
      $ftpc,    // ftp connection

      $cwls,    // current wd list
      $cwpt,    // current wd pointer

      $remote,  // remote path

      $rdin,    // initialized for reading?
      $wrin,    // initialized for writing?
      $wrdn;    // upload done?

  function get_default_params () {
    return 'http_host=moodle.host.org
http_basepath=/public_podcasts
http_protocol=http://
basepath=/var/www/public_podcasts
ftp_host=ftp.host.org
ftp_user=username
ftp_pass=password
mode=pasv
htuser=
htpasswd=';
  } // get_default_params ()

  function local_path ($path, $directory = false) {
    global $CFG;
    if ($directory && $path {strlen ($path) - 1} != '/')
      $path .= '/';
    return str_replace ($this->prefix.'://', '',  $path);
  } // local_path ()


  function get_url ($path, $course = NULL) {
    global $CFG;
    if ($course == NULL) {
      error ('Invalid course ID');
    }
    $path = $this->local_path ($path, false);
    $result = $this->get_param('http_protocol').
              $this->get_param('http_host').
              $this->get_param('http_basepath').'/'.$course.'/'.$path;
    return $result;
  } // get_url ()

  function is_folder ($path) {
    $result = false;
    do {
      if (!$this->init_self ($path))
        break;
      $destpath = $this->local_path ($path);

      $result = $this->ftpc->is_folder ($destpath);
    } while (false);
    return $result;
  } // is_folder ()

  // (runtime) persistent connection
  function & connect (&$c) {
    static $connection;
    do {
      if (!is_object ($connection) && $c == NULL) {
        $connection = false;
        break;
      }
      if (is_object ($c)) {
        $connection = $c;
      }
    } while (false);
    return $connection;
  } // connect ()

  function init_self ($path = null) {
    do {
      if (!isset ($this->prefix) || !$this->prefix || $path != NULL) {
        if ($path == null)
          return null;
        $this->prefix = repository_get_scheme ($path);
      }
      if (!isset($this->status)) {
        $this->status = 0;
        $this->tmpn = '';   // reserved file name for local buffering?
        $this->tmpf = NULL; // tmp file 

        $this->ftpc = NULL; // ftp connection

        $this->cwls = NULL; // current wd list
        $this->cwpt = NULL; // current wd pointer

        $this->rdin = false; // initialized for reading?
        $this->wrin = false; // initialized for writing?
        $this->wrdn = false; // upload done?
      }
      // $path = $this->parse_url ($path);
      $this->ftpc = ftp_repository::connect($this->ftpc);
      if ($this->ftpc != NULL) break;

      $host = $this->get_param ('ftp_host');
      if (!$host) 
        break;

      // paramname / default value
      $port = $this->get_param ('ftp_port', 21);
      $user = $this->get_param ('ftp_user', 'anonymous');
      $pass = $this->get_param ('ftp_pass', '');
      $fmde = $this->get_param ('mode', 'active');
      $this->ftpc = new ftp_connection ($host, $port);
      if (!$this->ftpc->connect()) 
        break;

      if (!$this->ftpc->login ($user, $pass)) 
        break;

      if (($fmde == 'pasv' || $fmde == 'passive'))
        $this->ftpc->setPassive();

      ftp_repository::connect ($this->ftpc);
    } while (false);
    return $this->ftpc;
  } // init_self ()

  function parse_url ($path) {
    return $path;
  }

/**
  *  Moodle Repository interface
  */
  /*
  function local_path ($path) {
    global $CFG;
    $path = str_replace($CFG->dataroot, '', $path);
    if ($path{0} == '/') 
      $path = substr($path, 1);
    return $path;
  } // local_path ()
  */

  /* function get_url ($path, $course = NULL) {
    $result = $this->get_param('http_protocol').
              $this->get_param('http_host').
              $this->get_param('http_basepath').'/'.$this->local_path ($this->parse_url ($path));
    return $result;
  } // get_url () */

/** 
  * PHP Stream API
  */
  function stream_open ($path, $mode, $options, &$opened_path) {
    global $CFG;
    $result = false;
    $this->init_self ($path);
    do {
      // can we handle the requested mode?
      if ($mode == 'w' || $mode == 'w+' || $mode == 'wb') $this->status += FTP_Wrapper_WR;
      if ($mode == 'r' || $mode == 'r+' || $mode == 'rb') $this->status += FTP_Wrapper_RD;
      if (!$this->status) break;

      if (($this->tmpn = tempnam ($CFG->dataroot.'/temp/FTP/', 'FTP')) == false)
        break;

      $this->remote = $this->local_path ($path);
      $opened_path = $this->local_path ($path);

      $result = true;
    } while (false);
    return $result;
  } // stream_open ()

  function stream_read ($cnt) {
    $result = '';
    do {
        if (!$this->init ($this->protocol.'://'.$this->path)) {
          break;
        }
      // opened for reading?
      if (!($this->status & FTP_Wrapper_RD)) break;
      // buffer remote file on demand
      if (!$this->rdin) {
        $this->rdin = true;
        if (!$this->ftpc->get($this->remote, $this->tmpn, true, FTP_BINARY))  {
          break;
        }
        if (!($this->tmpf = fopen ($this->tmpn, 'rb'))) break;
        fseek ($this->tmpf, 0);
      }
      $result = fread ($this->tmpf, $cnt);
    } while (false);
    return $result;
  }

  function stream_write ($data) {
    $result = 0;
    do {
      if (!($this->status & FTP_Wrapper_WR)) break;
      if (!$this->tmpn) break;

      // create local buffer file on demand
      if (!$this->wrin) {
        $this->wrin = true;
        $this->tmpf = fopen ($this->tmpn, 'wb');
        if (!$this->tmpf) break;
      }
      $result = fwrite ($this->tmpf, $data);
    } while (false);
    return $result;
  }

  function stream_close () {
    // opened for writing? Upload local buffer file to FTP server
    if (isset($this->tmpf) && ($this->status & FTP_Wrapper_WR) && !$this->wrdn) {
      fseek ($this->tmpf, 0);
      $result = $this->ftpc->put($this->tmpn, $this->remote, true, FTP_BINARY);
      $this->wrdn = true;
    }
    if ($this->tmpn) {
      // unlink local buffer files
      if (isset($this->tmpf)) {
        fclose ($this->tmpf);
        $this->tmpf = NULL;
      }
      unlink ($this->tmpn);
      $this->tmpn = '';
    }
    return true;
  }

  function stream_eof () {
    $result = true;
    do {
      if (!$this->init_self ()) {
        break;
      }
      // opened for reading? feof only 
      if (!($this->status & FTP_Wrapper_RD)) break;

      // buffer remote file on demand
      if (!$this->rdin) {
        $this->rdin = true;
        if (!$this->ftpc->get($this->remote, $this->tmpn, true, FTP_BINARY))  {
          break;
        }
        if (!($this->tmpf = fopen ($this->tmpn, 'rb'))) break;
        fseek ($this->tmpf, 0);
      }

      $result = feof ($this->tmpf);
    } while (false);
    return $result;
  }

  function stream_tell () {
    if ($this->tmpf) {
      return ftell ($this->tmpf);
    }
  }

  function stream_seek ($offset, $whence) {
    if ($this->tmpf) {
      return fseek ($this->tmpf, $offset, $whence); 
    }
    return false;
  }

  function stream_flush () {
    if (isset($this->tmpf)) {
      return fflush ($this->tmpf);
    }
    return true;
  }

  function stream_stat () {
    return false;
  }
    
  function unlink ($path) {
    $result = false;
    do {
      if (!$this->init_self ($path)) break;
      if ($this->ftpc->is_folder ($this->local_path ($path))) {
        $result = $this->ftpc->rm($this->local_path ($path).'/', true);
      }
      else {
        $result = $this->ftpc->rm($this->local_path ($path), false);
      }
      ftp_statcache::clearcache ();
    } while (false);
    return PEAR::isError($result) ? false : $result;
  }

  function rename ($path_from, $path_to) {
    $result = false;
    do {
      if (!$this->init_self ($path_from)) break;
      $result = $this->ftpc->rename($this->local_path ($path_from), $this->local_path ($path_to));
      if (!$result || PEAR::isError($result) ) break;
      ftp_statcache::clearcache ();
    } while (false);
    
    return PEAR::isError($result) ? false : $result;
  }

  function mkdir ($path, $mode, $options) {
    $result = false;
    do {
      if (!$this->init_self ($path))
        break;
      $destpath = $this->local_path ($path, true);
      $result = $this->ftpc->mkdir($destpath, $options & STREAM_MKDIR_RECURSIVE);
      ftp_statcache::clearcache ();
    } while (false);
    return PEAR::isError($result) ? false : $result;
  }

    function rmdir ($path, $options) {
      $result = false;
      do {
        if (!$this->init_self ($path))
          break;
        $destpath = $this->local_path ($path, true);
        $result = $this->ftpc->rm ($destpath, true);
        ftp_statcache::clearcache ();
      } while (false);
      return PEAR::isError($result) ? false : $result;
    }

  function dir_opendir ($path, $options) {
    do {
      if (!$this->init_self ($path)) {
        break;
      }
      $destpath = $this->local_path ($path, true);
      $stats =& ftp_statcache::get($this->ftpc, $destpath);
      if (!$stats) {
        break;
      }
      $this->cwls = array_keys($stats['children']);
      $this->cwpt = 0;
      return true;
    } while (false);
    return false;
  }
  
  function url_stat ($path, $flags) {
    if (!$this->init_self ($path)) return NULL;
    do {
      $stat =& ftp_statcache::get($this->ftpc, $this->local_path($path));
    } while (false);
    return $stat;
  }

  function dir_readdir () {
    if ($this->cwpt < count($this->cwls)) {
      $name = $this->cwls[$this->cwpt++];
      return $name;
    }
    return false;
  }

  function dir_rewinddir () {
    if (is_array($this->cwls)) {
      $this->cwpt = 0;
      return true;
    }
    return false;
  }
  
  function close_dir () {
    if (is_array($this->cwls)) {
      $this->cwls = NULL;
      $this->cwpt = 0;
      return true;
    }
    return false;
  }
  
  function is_local () {
    return false;
  } // is_local ()
} // class ftp_repository

/**
  * repository/plugins/moodle/lib.php
  * 2007-06-02 Christoph Soergel christoph.soergel@cms.hu-berlin.de
  */

// modes
define('FTP_Wrapper_RD',   1); // read
define('FTP_Wrapper_WR',   2); // write
define('FTP_Wrapper_AP',   4); // append
define('FTP_Wrapper_LS',   8); // ls
// flags
define('FTP_Wrapper_RI',  16); // initialized for reading?
define('FTP_Wrapper_WI',  32); // initialized for appending?
define('FTP_Wrapper_AI',  64); // initialized for writing?
define('FTP_Wrapper_LI', 128); // initialized for directory listing?

if (!class_exists('pear')) {
  include_once ($CFG->dirroot.'/lib/pear/PEAR.php');
} 
  include_once ($CFG->dirroot.'/mod/podcaster/repository/ftp/Net/FTP.php');
  if (!class_exists ('net_ftp')) {
    error('Unable to load required plugin ftp_repository, missing PEAR library Net_FTP.');
  } 

/**
  * class ftp_connection 
  *
  * extends Net_FTP
  * provides String representation, cwd caching
  *
  */
class ftp_connection extends Net_FTP {
  var $_currentwd;

  function ftp_connection($host = NULL, $port = NULL, $timeout = 90) {
    $this->Net_FTP($host, $port, $timeout);
  }

  function pwd () {
    if (!isset ($this->_currentwd)) {
      $this->_currentwd = parent::pwd ();
    }
    return $this->_currentwd;
  }

  function cd ($dir) {
    if (!PEAR::isError(parent::cd ($dir))) {
      $this->_currentwd = $dir;
      return true;
    }
    return false;
  }

  function is_folder ($path) {
    $cwd = $this->pwd ();
    if ($this->cd ($path)) {
      $this->cd ($cwd);
      return true;
    }
    return false;
  }
  
  function __toString() {
    return $this->_username.'@'.$this->_hostname.($this->_port ? ':'.$this->_port : '');
  }
} // class ftp_connection

/**
  * class ftp_statcache 
  *
  * transform & cache ftp file stats
  */
class ftp_statcache {
//
// private
// 
  function & getInstance () {
    static $obj;
    if (!is_object ($obj)) {
      $obj = new ftp_statcache ();
    }
    return $obj;
  } // getInstance ()

  function ftp_statcache() {
    $this->cache = array();
  } // ftp_statcache()

  function stat (&$ftpc, $path, &$stat) {
    $path = str_replace ('//', '/', $path);
    $normalized_path = ''; 
    if ($path{strlen ($path) - 1} == '/') {
      $normalized_path = substr ($path, 0, -1);
    }
    else {
      $normalized_path = $path;
    }
    $key = $ftpc.'';
    if (!array_key_exists ($key, $this->cache)) {
      $this->cache[$key] = array();
    }
    if (is_array ($stat)) {
      $this->cache[$key][$normalized_path] = $this->tofsstat($stat);
    }
    if (!array_key_exists ($normalized_path, $this->cache[$key])) {
        $ftpstat = $ftpc->ls ($path);
        $stat = $this->parse_stats ($key, $normalized_path, $ftpstat, $ftpc);
    }
    if ($this->cache[$key][$normalized_path]['nlink'] >= 2 && $this->cache[$key][$normalized_path]['children'] == NULL) {
      $ftpstat = $ftpc->ls ($path);
      $stat = $this->parse_stats ($key, $normalized_path, $ftpstat, $ftpc);
    }
    return $this->cache[$key][$normalized_path];
  } // stat ()

  function parse_stats ($key, $root, &$ftpstat, &$ftpc) {
    // regular file
    if (is_array($ftpstat) && array_key_exists('name', $ftpstat)) {
      $stats = $this->tofsstat ($ftpstat);
      if ($ftpstat['is_dir']) {
        $stats['children'] = NULL;
      }
      $this->cache [$key][$root.'/'.$ftpstat['name']] = $stats;
      return $stats;
    }

    // directory
    if (is_array($ftpstat) && (count ($ftpstat) > 0 || $ftpc->is_folder ($root))) {
      $c = count ($ftpstat);
      $children = array ();
      for ($i = 0; $i < $c; ++$i) {
        if (isset ($ftpstat[$i]['name'])) {
          $children[$ftpstat[$i]['name']] = $this->parse_stats ($key, $root, $ftpstat[$i], $ftpc);
        }
      }
      $c += 2;
      $stat = $this->tofsstat($v = array ('files_inside' => $c, 'stamp' => '0', 'size' => '0'));
      $stat['children'] = $children;
      $this->cache [$key][$root] = $stat;
    }
    return $stat;
  } // parse_stats ()

  function _clearcache () {
    $this->cache = array();
    repository_stat(NULL, true);
  } // _clearcache()

  function tofsstat(&$ftpstat) {
    if (is_array($ftpstat) && array_key_exists('stamp', $ftpstat)) {
      return array(
                0         => 0,
                1         => 0,
                'ino'     => 0,
                2         => 0,
                'mode'    => 0,
                3         => $ftpstat['files_inside'],
                'nlink'   => $ftpstat['files_inside'],
                4         => 0,
                'uid'     => 0,
                5         => 0,
                'gid'     => 0,
                6         => 0,
                'rdev'    => 0,
                7         => $ftpstat['size'],
                'size'    => $ftpstat['size'],
                8         => 0,
                'atime'   => 0,
                9         => $ftpstat['stamp'],
                'mtime'   => $ftpstat['stamp'],
                10        => 0,
                'ctime'   => 0,
                11        => 0,
                'blksize' => 0,
                12        => 0,
                'blocks'  => 0
                );
    }
    return array ();
  } // tofsstat()

//
// public interface
//
  function set (&$ftpc, $path, &$stat) {
    $obj =& ftp_statcache::getInstance();
    $obj->stat ($ftpc, $path, $stat);
  } // set()

  function get (&$ftpc, $path) {
    $obj =& ftp_statcache::getInstance();
    return $obj->stat ($ftpc, $path, $stat = NULL);
  } // get()
  
  function clearcache () {
    $obj =& ftp_statcache::getInstance();
    $obj->_clearcache ();
  } // clearcache ()
    
} // class ftp_statcache
?>
