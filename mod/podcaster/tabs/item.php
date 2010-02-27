<?php    
/**
 * Prints a podcaster summary, included from view.php
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 **/
if (!defined('MOODLE_INTERNAL')) {
  die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
$item     = required_param ('item', PARAM_INT);
$itemObj  = $channel->get_item ($item); 

if (!$itemObj) {
  error ('Item ID is invalid');
}
$page->print_itemdetail_header ($itemObj);

$page->print_itemdetail ($itemObj);
$page->print_itemdetail_footer ($itemObj);
?>
