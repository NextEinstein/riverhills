<?php
/**
 * podcaster specific functions
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 **/
if (!defined('MOODLE_INTERNAL')) {
  die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
if (!defined('DATE_RSS')) {
  define ('DATE_RSS', 'D, d M Y H:i:s O');
}

// locallib seperated into smaller files:
require_once ($CFG->dirroot.'/mod/podcaster/lib/util.php');
require_once ($CFG->dirroot.'/mod/podcaster/lib/public.php');

?>
