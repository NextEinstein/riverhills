<?php    
/**
 * Prints a podcaster summary, included from view.php
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 *            Michael Ganzer    <michael.ganzer@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 **/
if (!defined('MOODLE_INTERNAL')) {
  die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

$page->print_channel_header ();
$page->print_channel ();
$page->print_channel_footer ();
?>
