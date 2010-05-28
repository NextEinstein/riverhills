<?php
/**
 * Page Item Definition
 *
 * @author Mark Nielsen
 * @version $Id: forum.php,v 1.1 2009/12/21 01:00:31 michaelpenne Exp $
 * @package format_page
 **/

require_once($CFG->dirroot.'/course/format/page/plugin/pageitem.php');

class format_page_pageitem_forum extends format_page_pageitem {
    /**
     * Add content to a block instance. This
     * method should fail gracefully.  Do not
     * call something like error()
     *
     * @param object $block Passed by refernce: this is the block instance object
     *                      Course Module Record is $block->cm
     *                      Module Record is $block->module
     *                      Module Instance Record is $block->moduleinstance
     *                      Course Record is $block->course
     *
     * @return boolean If an error occures, just return false and 
     *                 optionally set error message to $block->content->text
     *                 Otherwise keep $block->content->text empty on errors
     **/
    function set_instance(&$block) {
        global $CFG;

        require_once($CFG->dirroot.'/mod/forum/lib.php');

        ob_start();
        forum_print_latest_discussions($block->course, $block->moduleinstance, 10);
        $block->content->text = ob_get_contents();

        $block->content->text .= 
        '<script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery("table.forumpost tr td.topic").toggle(
                    function () {
                        jQuery(this).css({"background-image" : "url('.$CFG->pixpath.'/t/arrow_up_large.png)"});
                        jQuery(this).parent().siblings().show();
                    },
                    function () {
                        jQuery(this).css({"background-image" : "url('.$CFG->pixpath.'/t/arrow_down_large.png)"});
                        jQuery(this).parent().siblings().hide();
                    }
                );
            });
        </script>';

        ob_end_clean();

        return true;
    }
}
?>