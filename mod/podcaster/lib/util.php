<?php
/**
 * podcaster data wrapper and models for xml and form generation 
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
class podcaster_util {
  function get_string ($key) {
    global $USER;
    static $strings, $fallback;

    if (!isset ($strings)) {
      $s = get_records ('podcaster_language', 'language', $USER->lang);
      $strings = array ();
      foreach ($s as $str) {
        $strings[$str->name] = $str->value;
      }
      if ($USER->lang != 'en_utf8') {
        $fallback = array ();
        $f = get_records ('podcaster_language', 'language', 'en_utf8');
        foreach ($f as $str) {
          $fallback[$str->name] = $str->value;
        }
      }
      else {
        $fallback = $strings;
      }
    }
    if (!isset ($strings[$key])) {
      if (isset ($fallback[$key])) {
        return $fallback[$key];
      }
      return '[['.$key.']]';
    }
    return $strings[$key];
  } // get_string ()

  function strip_email ($str) {
    return ereg_replace (' *<[^>]*>', '', $str);
  } // strip_email ()
  
  function time_diff($start, $stop, $asArray = false) {
    $_start = explode(' ', $start);
    $_stop  = explode(' ', $stop);

    $startS  = $_start[1];
    $startMs = $_start[0] * 1000;

    $stopS  = $_stop[1];
    $stopMs = $_stop[0] * 1000;

    if ($stopMs < $startMs) {
      $stopMs += 1000;
      --$stopS;
    }
    if (!$asArray) {
      return round((($stopS - $startS) * 1000 + round($stopMs - $startMs, 0)) / 1000, 4);
    }
    return array('s' => ($stopS - $startS),
                 'ms' => ($stopMs - $startMs));
  } // time_diff()

  
  function get_filerefs ($course, $channel, $path) {
    global $CFG;
    $repository = $channel->get_repository ();
    $refs       = array ();
    // 
    $q = 'SELECT item.id, item.channel, item.enclosure as path 
           FROM '.$CFG->prefix.'podcaster_item item,
                '.$CFG->prefix.'podcaster channel
          WHERE item.enclosure     = \''.$path.'\'
            AND item.channel       = channel.id
            AND channel.repository = '.$repository->id;
    $res = get_records_sql ($q);
    if ($res) {
      foreach ($res as $r) {
        $refs[] = $r;
      }
    }
    $q = 'SELECT channel.id, channel.id as channel, image 
           FROM '.$CFG->prefix.'podcaster channel
           WHERE channel.image      = \''.$path.'\'
             AND channel.repository = '.$repository->id;

    $res = get_records_sql ($q);
    if ($res) {
      foreach ($res as $r) {
        $refs[] = $r;
      }
    }

    if ($repository->shared) {
      $q = 'SELECT resource.id, 0 as channel, resource.reference
               FROM '.$CFG->prefix.'resource resource
               WHERE resource.course       = '.$course->id.'
                 AND (resource.type = \'file\' OR resource.type = \'directory\')
                 AND resource.reference = \''.$path.'\'';
      $res = get_records_sql ($q);
      if ($res) {
        foreach ($res as $r) {
          $refs[] = $r;
        }
      }
    }
    return $refs;
  } // get_filerefs ()

  function number2az ($number) {
    // TODO!
    $uc = UC_::getInstance ();

    return $uc->dec2uc ($number);
  }

  function az2number ($az) {
    $uc = UC_::getInstance ();
    return $uc->uc2dec ($az);
  }

} // class podcaster_util

class UC_ {
  
  function & getInstance () {
    static $obj;
    if (!is_object($obj)) {
      $obj = new UC_();
    }
    return $obj;
  } // getInstance ()

  function UC_($digits = null) {
    // default to a numbering system using upper case
    // characters only:
    if ($digits == null) {
      $digits = array(
          'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
          'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U'. 'V', 'W', 'X', 'Y', 'Z' );
    }
    $this->uc2dec = array();
    $this->dec2uc = array();
    $this->f       = array();
    $this->base    = count($digits);

    for($i = 0; $i < count($digits); ++$i) {
      $this->uc2dec[$digits[$i]] = $i;
      $this->dec2uc[$i] = $digits[$i];
    }
    for($i = 0; $i < 10; ++$i) {
      $this->f[$i] = pow($this->base, $i);
    }
  } // UC_()
    
  function inc($uc) {
    $n = $this->uc2dec($uc);
    ++$n;
    return $this->dec2uc($n);
  } // inc()
  
  function dec($uc) {
    $n = $this->uc2dec($uc);
    --$n;
    return $this->dec2uc($n);
  } // dec()
  
  function dec2uc($dec) {
    $result = array();
    $diff = -1;
    while($dec > 0) {
      $diff = $dec % $this->base;
      $c = $this->dec2uc[$diff];
      $dec -= $diff;
      $dec /= $this->base;
      $result[] = $c;
    }
    $str = '';
    for($j = count($result) - 1; $j >= 0; --$j) {
      $str .= $result[$j];
    }
    return $str; 
  } // dec2uc()

  function uc2dec($uc) {
    $result = 0;
    $len = strlen($uc);
    $p   = 0;
    while($p < $len) {
      $c = substr($uc, $p, 1);
      $d = $this->uc2dec[$c];
      $result += $this->f[($len - $p) - 1] * $d;
      ++$p;
    }
    return $result;
  } // uc2dec
}
 

///////////////////////////////////////////////////////////////////////////////
//
// very simple stream api
//
class podcaster_outputstreamWriter {
  function write ($str) {
  } // write ()
} // class podcaster_outputstreamWriter

class podcaster_stdoutwriter extends podcaster_outputstreamWriter {

  function write ($str) {
    echo $str;
    flush ();
    return true;
  } // write ()

} // class podcaster_stdoutwriter 

class podcaster_filestreamwriter extends podcaster_outputstreamWriter {
  var $path, 
      $fp;

  function podcaster_filestreamwriter ($path = '') {
    if ($path != '') {
      $this->open ($path);
    }
  } // podcaster_filestreamwriter ()

  function open ($path) {
    $this->path = $path;
    $this->fp   = fopen ($this->path, 'w');
  } // open ()

  function write ($str) {
    if ($this->fp) {
      return fwrite ($this->fp, $str);
    }
    return false;
  } // write ()
  
  function close () {
    if ($this->fp)
      fclose ($this->fp);
  } // close ()

} //  class podcaster_filestreamwriter 

class podcaster_highlightxmlwriter {

  function write ($str) {
    $str = htmlspecialchars($str, ENT_COMPAT, 'UTF-8');

    $str = str_replace (
        array (' ', '&lt;', '&gt;', "\n", "\t"),
        array ('&nbsp;', '<span style="color:red">&lt;', '&gt;</span>', '<br/>', '&nbsp;&nbsp;'),
        $str);
    echo $str;
    flush ();
  }
} // class podcaster_highlightxmlwriter
?>
