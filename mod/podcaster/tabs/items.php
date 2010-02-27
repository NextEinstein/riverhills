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
$page->print_itemlist_header ();
$page->print_add_item_button ();

$items = $channel->get_items ();
for ($i = 0, $c = count ($items); $i < $c; ++$i) {
  $page->print_item ($items[$i]);
}
$page->print_itemlist_footer ();
?>
