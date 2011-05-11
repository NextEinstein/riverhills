<?php
/**
 * RSS 2.0 Template  
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 *
 **/
require_once ($CFG->dirroot.'/mod/podcaster/formats/rss/lib.php');

class hu_format extends rss_format {

  function define_channelform (&$mform, &$obj) {
    parent::define_channelform ($mform, $obj);
    $mform->removeElement ('language');
    $mform->removeElement ('category');
  } // define_channelform ()

  function define_itemform (&$mform, &$obj) {
    parent::define_itemform ($mform, $obj);
    $mform->removeElement ('source');
    $mform->removeElement ('sourceurl');
    $mform->removeElement ('category');
    $mform->removeElement ('comments');
  } // define_itemform ()

  function get_schema () {
    $schema    = parent::get_schema ();
    $override = 
      array (
        '/rss/@xmlns:hu'                         
              => array(XML_DATA_STATIC,  XML_TYPE_STRING,   'http://www2.hu-berlin.de/podcast/Schema'),

      // optional channel elements
        '/rss/channel/language' 
              => array(XML_DATA_CALLBK,  XML_TYPE_STRING,   'get_language'),
        '/rss/channel/ttl'     
              => array(XML_DATA_IGNORE),
        '/rss/channel/item/category'            
              => array(XML_DATA_IGNORE),
        '/rss/channel/item/comments'     
              => array(XML_DATA_IGNORE),
        '/rss/channel/item/category/@domain'    
              => array(XML_DATA_IGNORE),

        '/rss/channel/item/source'
              => array(XML_DATA_IGNORE),
        '/rss/channel/item/source/@url' 
              => array(XML_DATA_IGNORE)
          );

    if (is_a ($this->channel, 'podcaster_metachannelimpl')) {
        $override['/rss/channel/item/hu:copyright']
              = array(XML_DATA_CONTEXT, XML_TYPE_STRING,    'copyright');
        $override['/rss/channel/item/hu:categories']
              = array(XML_DATA_ONCE,    XML_TYPE_OBJECT,    NULL);
        $override['/rss/channel/item/hu:categories/hu:category']
              = array(XML_DATA_LOOP, XML_TYPE_OBJECT,       'get_hucategories');

        $override['/rss/channel/item/hu:categories/hu:category/@id']
              = array(XML_DATA_CONTEXT, XML_TYPE_INTEGER,   'id');
        $override['/rss/channel/item/hu:lastBuildDate']
              = array(XML_DATA_CONTEXT, XML_TYPE_DATETIME,  'timemodified');
    }
    else {

        $override['/rss/channel/hu:categories']
              = array(XML_DATA_ONCE, XML_TYPE_OBJECT,       NULL);
        $override['/rss/channel/hu:categories/hu:category']
              = array(XML_DATA_LOOP, XML_TYPE_OBJECT,       'get_hucategories');
        $override['/rss/channel/hu:categories/hu:category/@id']
              = array(XML_DATA_CONTEXT, XML_TYPE_INTEGER,   'id');
    }
    return array_merge ($schema, $override);
  } // get_schema ()

//
// data callbacks
//
  function get_hucategories (&$context) {
    $result = array ();
    do {
      if (is_a ($context, 'podcaster_channel')) {
        $courseId = $context->course;
        $course   = get_record ('course', 'id', $courseId);
        if (!$course) break;
        $categoryId = $course->category;
        $categories = array ();

        while ($categoryId != 0) {
          $category = get_record ('course_categories', 'id', $categoryId);
          if ($category) {
            $categories[] = $category;
            $categoryId   = $category->parent;
          }
          else {
            $categoryId   = 0;
          }
        }
        foreach (array_reverse ($categories) as $category) {
          $co       = new object ();
          $co->id   = $category->id;
          $result[] = array('cdata' => strip_tags($category->name), 'context' => $category);
        }
      }
    } while (false);
    return $result;
  } // get_categories ()

  function get_language (&$context) {
    global $CFG;
    $lang   = $CFG->lang;
    $course = get_record ('course', 'id', $context->course);
    if ($course && $course->lang != '') {
      $lang = $course->lang;
    }
    return str_replace ('_utf8', '', $lang);
  } // get_language ()

  function get_link (&$context) {
    $link = parent::get_link ($context);
    return str_replace ('https://', 'http://', $link);
  } // get_link ()

} // class hu_format
?>
