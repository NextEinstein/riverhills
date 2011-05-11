<?php
/**
 * example format template
 * 
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 **/
if (!defined('MOODLE_INTERNAL')) {
  die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
//////////////////////////////////////////////////////////////////////////////////
//
// extend podcaster_formatimpl or any of the existing formats
//
// class example_format extends podcaster_formatimpl {
class example_format extends rss_format {

//////////////////////////////////////////////////////////////////////////////////
//
// Edit forms
//

  /**
    *
    */
  function define_channelform (&$mform, &$channel) {
    // call the superclass method (will define the basic set of form elements)
    parent::define_channelform ($mform, $obj);

    $mform->addElement ('header',    'mycustomheader',  get_string ('My custom header', 'podcaster'));
    $mform->addElement ('text',      'mycustominput',   get_string ('My custom input',  'podcaster'), array('size'=>'8'));

    $mform->removeElement ('copyright');
    // etc.
  } // define_channelform ()


  /**
    *
    */
  function define_itemform (&$mform, &$item) {
    // call the superclass method (will define the basic set of form elements)
    parent::define_itemform ($mform, $item);
    // 
    // add or remove elements
    //
    $mform->addElement ('header',    'mycustomheader',  $item->title);
    // etc.
  } // define_itemform ()

//////////////////////////////////////////////////////////////////////////////////
//
// HTML Snippets
//
  
  /**
    * @return HTML fragment containing a link to the channels RSS feed. 
    *         This will show up in the upper right corner on all tabs
    */
  function get_channellinkshtml (&$channel, &$page) {
    global $CFG;
    return '<a href="'.$channel->get_rss_link ().'"><img src="'.$CFG->pixpath.'/i/rss.gif" class="channellink"></a>';
  } // get_channellinkshtml ()

  /**
    * @return some nicely formatted channel info.
    *         This will be displayed in the channel tab.
    */
  function get_channelhtml (&$channel, &$page) {
    $img = NULL;
    if (($image = $channel->get_image ()) && $image->url) {
      $img = $image->url;
    }
    return  ($img ? '<div class="image"><img src="'.$img.'" alt="" />' : '').
            '<div class="title">'.$channel->name.'</div>'.
            '<div class="description">'.$channel->intro.'</div>';
  } // get_channelhtml ()


  /**
    * @return a compact HTML representation for items
    *
    */
  function get_itemhtml (&$item, &$page) {
    $url = NULL;
    $enclosure = $item->get_enclosure ();
    if ($enclosure) {
      $url = $enclosure->url;
    }
    return    '<div class="date">'.userdate($item->scheduledtime).'</div>'.
              '<div class="title">'.$item->title.'</div>'.
              '<div class="description">'.$item->description.'</div>'.
              ($url ?  '<div class="enclosure">'.$page->embedd_html ($url, $item->title).'</div>' : '').
              '<div class="separator"><hr size="1" /></div>';
  } // get_itemhtml ()

  /**
    * @return a more detailed HTML representation for items. 
    *         This will show up in the items detail tab.
    */
  function get_itemdetailhtml (&$item, &$page) {
    $url = NULL;
    $enclosure = $item->get_enclosure ();
    if ($enclosure) {
      $url = $enclosure->url;
    }
    return    '<div class="date">'.userdate($item->scheduledtime).'</div>'.
              '<div class="title">'.$item->title.'</div>'.
              '<div class="description">'.$item->description.'</div>'.
              ($url ?  '<div class="enclosure">'.$page->embedd_html ($url, $item->title).'</div>' : '').
              '<div class="separator"><hr size="1" /></div>';
  } // get_itemdetailhtml ()


//////////////////////////////////////////////////////////////////////////////////
//
// XML
//
  /**
    * This function defines the RSS Schema. We use a kind of reversed XPath notation
    * to define nodes and assign content or callback handlers to nodes.
    *
    * Although we can define virtually any XML format here you should keep in mind
    * that the result should be more or less RSS compliant.
    *
    * 
    */
  function get_schema () {
    // start with the superclass schema ...
    $schema = parent::get_schema ();
     
    // first introduce a namespace (the RSS standard requires this)
    $schema['/rss/@xmlns:ns'] => array (
        XML_DATA_STATIC,                        // content is static data
        XML_TYPE_STRING,                        // content is of type string
        'http://my.server.com/podcasts/Schema'  // data
        ),

    // will add a <customelement_static> node to channel
    $schema['/rss/channel/ns:customelement'] => array (
        XML_DATA_ONCE,                          // will be generated once 
        XML_TYPE_OBJECT,                        // is a complex type
        NULL                                    // there is no specific data associated to
                                                // this node. We just keep the current scope
        );

    $schema['/rss/channel/ns:customelement@ns:info'] => array (
        XML_DATA_CALLBK,                        // data (if there is) will be provided by a callback function
        XML_TYPE_STRING,                        // content is of type a string
        'get_infostring',                       // callback, must be defined as a class method
        'str_notempty'                          // optional: another callback to check constraints
        );
    
    $schema['/rss/channel/ns:customelement/ns:title'] => array (
        XML_DATA_CONTEXT,                       // use data of the current context (at toplevel the channel object)
        XML_TYPE_STRING,                        // content is of type a string
        'name'                                  // name of the object property
        );
    
    // more examples: see rssfull/lib.php or any of the other format classes 
    return $schema;
  } // get_schema ()

//////////////////////////////////////////////////////////////////////////////////
//
// data callbacks
//
  
  function get_infostr (&$context) {
    // context is the object currently being processed
    // you can just pass it to subelements or supply another object
    // $c = new object ();
    $c = $context;
    $cdata = 'this will be inserted text (cdata or attribute value)';
    return array ('cdata' => $cdata, 'context' => $c);
  } // get_guid ()

//////////////////////////////////////////////////////////////////////////////////
//
// constraint callbacks
//
  function str_notempty (&$value) {
    if (!isset ($value) || !$value + '') return false;
    return true;
  } // str_notempty ()
} // class example_format
?>
