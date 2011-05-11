<?php
/**
 * RSS 2.0 Template  
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 **/
if (!defined('MOODLE_INTERNAL')) {
  die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

class rssfull_format extends podcaster_formatimpl {
//
// Forms
//
  function define_channelform (&$mform, &$channel) {
    $mform->addElement ('header',    'channelinfo',      get_string ('channelinfo',    'podcaster'));
    $mform->addElement ('text',      'language',         get_string ('language',       'podcaster'), array('size'=>'8'));
    $mform->addElement ('text',      'managingeditor',   get_string ('managingeditor', 'podcaster'), array('size'=>'32'));
    $mform->addElement ('textarea',  'category',         get_string ('category',       'podcaster'), array('cols'=>'32', 'rows' => '5'));
    $mform->addElement ('text',      'rating',           get_string ('rating',         'podcaster'), array('size'=>'32'));

    if ($channel->repository) {
      $repository =& podcaster_repository::create_repository ($channel->repository);
      $scheme     =  $repository->prefix;
    }
    else {
      error ('Invalid channel, no repository set'); 
    }
    $mform->addElement ('chooserepositoryfile', 'image', get_string ('image', 'podcaster'), array('repository' => $scheme));
    $mform->addElement ('header',    'cloud',            get_string ('cloud',          'podcaster'));
    $mform->addElement ('text',      'clouddomain',      get_string ('clouddomain',    'podcaster'), array('size'=>'32'));
    $mform->addElement ('text',      'cloudport',        get_string ('cloudport',      'podcaster'), array('size'=>'8'));
    $mform->addElement ('text',      'cloudpath',        get_string ('cloudpath',      'podcaster'), array('size'=>'32'));
    $mform->addElement ('text',      'cloudregisterprocedure', get_string ('cloudregisterprocedure', 'podcaster'), array('size'=>'32'));
    $mform->addElement ('text',      'cloudprotocol',    get_string ('cloudprotocol',   'podcaster'), array('size'=>'8'));
    
    $mform->addElement ('header',    'textinput',            get_string ('textinput',            'podcaster'));
    $mform->addElement ('text',      'textinputtitle',       get_string ('textinputtitle',       'podcaster'), array('size'=>'32'));
    $mform->addElement ('text',      'textinputdescription', get_string ('textinputdescription', 'podcaster'), array('size'=>'32'));
    $mform->addElement ('text',      'textinputname',        get_string ('textinputname',        'podcaster'), array('size'=>'32'));
    $mform->addElement ('text',      'textinputlink',        get_string ('textinputlink',        'podcaster'), array('size'=>'32'));

    $mform->addElement ('header',    'refreshing',           get_string ('refreshing',           'podcaster'));
    $mform->addElement ('text',      'ttl',                  get_string ('ttl',                  'podcaster'), array('size'=>'4'));
    $mform->addElement ('text',      'skiphours',            get_string ('skiphours',            'podcaster'), array('size'=>'32'));
    $mform->addElement ('text',      'skipdays',             get_string ('skipdays',             'podcaster'), array('size'=>'32'));
    $mform->addElement ('checkbox',  'showpreview',          get_string ('showpreview',          'podcaster'));
  } // define_channelform ()
  
  function define_itemform (&$mform, &$item) {
    $mform->addElement ('header',    'iteminfo',        get_string ('iteminfo',       'podcaster'));
    $mform->addElement ('text',      'author',          get_string ('author',         'podcaster'), array('size'=>'32'));
    $mform->addElement ('textarea',  'category',        get_string ('category',       'podcaster'), array('cols'=>'32', 'rows' => '5'));
    $mform->addElement ('text',      'comments',        get_string ('comments',       'podcaster'), array('size'=>'32'));
    $mform->addElement ('text',      'source',          get_string ('source',         'podcaster'), array('size'=>'32'));
    $mform->addElement ('text',      'sourceurl',       get_string ('sourceurl',      'podcaster'), array('size'=>'32'));
    $mform->addElement ('header',    'enclosureinfo',   get_string ('enclosureinfo',   'podcaster'));
    if ($item->channelObj->repository) {
      $repository =& podcaster_repository::create_repository ($item->channelObj->repository);
      $scheme     =  $repository->prefix;
    }
    else {
      error ('Invalid channel, no repository set'); 
    }
    $mform->addElement ('chooserepositoryfile', 'enclosure', get_string ('enclosure', 'podcaster'), array ('repository' => $scheme));
  } // define_itemform ()

//
// HTML
//
  function get_channellinkshtml (&$channel, &$page) {
    global $CFG;
    return '<a href="'.$channel->get_rss_link ().'"><img src="'.$CFG->pixpath.'/i/rss.gif" class="channellink"></a>';
  } // get_channellinkshtml ()


  function get_channelhtml (&$channel, &$page) {
    $img = NULL;
    if (($image = $channel->get_image ()) && $image->url) {
      $img = $image->url;
    }
    return  ($img ? '<div class="image"><img src="'.$img.'" alt="" />' : '').
            '<div class="title">'.$channel->name.'</div>'.
            '<div class="description">'.$channel->intro.'</div>';
  } // get_channelhtml ()


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

//
// XML
//
  function get_schema () {
    $result = array (
    // root element
        '/@version'
            => array (XML_DATA_STATIC, XML_TYPE_STRING,     '1.0'),
        '/@encoding'
            => array (XML_DATA_STATIC, XML_TYPE_STRING,     'UTF-8'),
        '/rss'                                  
            => array(XML_DATA_ONCE,    XML_TYPE_OBJECT,     NULL),
        '/rss/@version'                         
            => array(XML_DATA_STATIC,  XML_TYPE_STRING,     '2.0'),

    // channel
        '/rss/channel'                          
            => array(XML_DATA_ONCE,     XML_TYPE_OBJECT,    'get_channel'),    

      // required channel elements
        '/rss/channel/title'                    
            => array(XML_DATA_CONTEXT,  XML_TYPE_STRING,    'name'),
        '/rss/channel/link'                     
            => array(XML_DATA_CALLBK,   XML_TYPE_STRING,    'get_link'),  
        '/rss/channel/description'              
            => array(XML_DATA_CONTEXT,  XML_TYPE_STRING,    'intro'),
        
      // optional channel elements
        '/rss/channel/language'                 
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'language'),

        '/rss/channel/copyright'                
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'copyright'),
        '/rss/channel/managingEditor'           
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'managingeditor', 'str_notempty'),
        '/rss/channel/webMaster'                
              => array(XML_DATA_CALLBK,  XML_TYPE_STRING,   'get_webmaster'),
        '/rss/channel/pubDate'                  
              => array(XML_DATA_CONTEXT, XML_TYPE_DATETIME, 'timecreated'),
        '/rss/channel/lastBuildDate'            
              => array(XML_DATA_STATIC,  XML_TYPE_DATETIME, time()),

        '/rss/channel/category'                 
              => array(XML_DATA_LOOP,   XML_TYPE_OBJECT,    'get_categories'),
        '/rss/channel/category/@domain'         
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'domain'),

        '/rss/channel/generator'                
              => array(XML_DATA_STATIC,  XML_TYPE_STRING,   'Moodle RSS / Podcast Activity V1.0'),
        '/rss/channel/docs'                     
              => array(XML_DATA_STATIC,  XML_TYPE_STRING,   'http://www.rssboard.org/rss-specification'),
        '/rss/channel/ttl'                      
              => array(XML_DATA_CONTEXT, XML_TYPE_INTEGER,  'ttl',          'int_notnull'),

        '/rss/channel/image'                    
              => array(XML_DATA_ONCE,    XML_TYPE_OBJECT,   'get_image',    'obj_notnull'),

        '/rss/channel/image/width'
              => array(XML_DATA_CONTEXT, XML_TYPE_INTEGER,  'width',        'int_notnull'),

        '/rss/channel/image/height'
              => array(XML_DATA_CONTEXT, XML_TYPE_INTEGER,  'height',       'int_notnull'),

        '/rss/channel/image/url'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'url',          'str_notempty'),
        '/rss/channel/image/title'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'title'),
        '/rss/channel/image/link'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'link'),

        '/rss/channel/textInput'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'rating'),

      // optional elements currently not supported
        '/rss/channel/cloud'                    
              => array(XML_DATA_ONCE,    XML_TYPE_OBJECT, NULL),
        '/rss/channel/cloud/@domain'            
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'clouddomain'),
        '/rss/channel/cloud/@port'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'cloudport'),
        '/rss/channel/cloud/@path'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'cloudpath'),
        '/rss/channel/cloud/@registerProcedure'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'cloudregisterprocedure'),
        '/rss/channel/cloud/@protocol'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'cloudprotocol'),

        '/rss/channel/textInput'
              => array(XML_DATA_ONCE,    XML_TYPE_INTEGER,   NULL),
        '/rss/channel/textInput/@title'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'textinputtitle'),
        '/rss/channel/textInput/@description'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'textinputdescription'),
        '/rss/channel/textInput/@name'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'textinputname'),
        '/rss/channel/textInput/@link'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'textinputlink'),

        '/rss/channel/skipHours'
              => array(XML_DATA_ONCE,    XML_TYPE_OBJECT,   NULL),
        '/rss/channel/skipHours/hour'
              => array(XML_DATA_LOOP,    XML_TYPE_INTEGER,  'get_skiphours'),
        '/rss/channel/skipDays'
              => array(XML_DATA_ONCE,    XML_TYPE_OBJECT,   NULL),
        '/rss/channel/skipDays/day'
              => array(XML_DATA_LOOP,    XML_TYPE_INTEGER,  'get_skipdays'),

    // item
        '/rss/channel/item'                     
              => array(XML_DATA_LOOP,    XML_TYPE_OBJECT,   'get_items'),
      // required item elements
        '/rss/channel/item/description'         
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'description'),

      // optional item elements
        '/rss/channel/item/title'               
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'title'),
        '/rss/channel/item/link'                
              => array(XML_DATA_CALLBK,  XML_TYPE_STRING,   'get_link'),
        '/rss/channel/item/author'              
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'author',        'str_notempty'),
        
        '/rss/channel/item/category'            
              => array(XML_DATA_LOOP,   XML_TYPE_OBJECT,    'get_categories'),
        '/rss/channel/item/category/@domain'    
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'domain'),

        '/rss/channel/item/comments'            
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'comments',       'str_notempty'),

        '/rss/channel/item/enclosure'           
              => array(XML_DATA_ONCE,    XML_TYPE_OBJECT,   'get_enclosure',  'obj_notnull'),
        '/rss/channel/item/enclosure/@url'      
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'url'),
        '/rss/channel/item/enclosure/@length'   
              => array(XML_DATA_CONTEXT, XML_TYPE_INTEGER,  'length'),
        '/rss/channel/item/enclosure/@type'     
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'type'),

        '/rss/channel/item/guid'                
              => array(XML_DATA_ONCE,    XML_TYPE_OBJECT,   'get_guid'),
        '/rss/channel/item/guid/@isPermaLink'   
              => array(XML_DATA_CONTEXT, XML_TYPE_BOOLEAN,  'isPermaLink'),

        '/rss/channel/item/pubDate'             
              => array(XML_DATA_CONTEXT, XML_TYPE_DATETIME, 'timecreated'),

        '/rss/channel/item/source'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'source'),
        '/rss/channel/item/source/@url'
              => array(XML_DATA_CONTEXT, XML_TYPE_STRING,   'sourceurl'),
      );
    return $result;
  } // get_schema ()

//
// data callbacks
//
  function get_channel (&$context) {
    return array ('cdata' => '', 'context' => $this->channel);
  } // get_channel ()

  
  function get_link (&$context) {
    global $CFG;
    if (is_a ($context, 'podcaster_channel')) {
      return $CFG->wwwroot.'/mod/podcaster/view.php?channel='.$context->id;
    }
    if (is_a ($context, 'podcaster_item')) {
      return $CFG->wwwroot.'/mod/podcaster/view.php?channel='.$context->channelObj->id.'&tab=item&item='.$context->id;
    }
  } // get_link ()


  function get_guid (&$context) {
    $c = new object ();
    $c->isPermaLink = 'true';
    $link = (is_a ($context, 'podcaster_channelimpl') ? $context->get_rss_link () : $this->get_link ($context));
    return array ('cdata' => $link, 'context' => $c);
  } // get_guid ()

  
  function get_webmaster (&$context) {
    global $CFG;
    if ($CFG->podcaster_webmaster != '') {
      return $CFG->podcaster_webmaster;
    }
    if (($admin = get_admin ()) != NULL) {
      return $admin->firstname.' '.$admin->lastname.' <'.$admin->email.'>';
    }
    return '';
  } // get_webmaster ()

  
  function get_categories (&$context) {
    $result = array ();
    if (isset ($context->category)) {
      $categories = explode ("\n", $context->category);
      for ($i = 0, $c = count ($categories); $i < $c; ++$i) {
        if (!$categories [$i]) continue;
        list ($name,$domain) = explode ('@', $categories[$i]);
        $cat = new object ();
        $cat->domain = trim ($domain);
        $result[] = array ('cdata' => trim($name), 'context' => $cat);
      }
    }
    return $result;
  } // get_categories ()

  
  function get_items (&$context) {
    $result = array ();
    if (method_exists($context, 'get_items')) {
      $items = $context->get_items ();
      for ($i = 0, $c = count ($items); $i < $c; ++$i) {
        if (isset ($items[$i]->scheduledtime) && $items[$i]->scheduledtime > time ()) {
          continue;
        }
        $result[] = array ('cdata' => '', 'context' => $items[$i]);
      }
    }
    return $result;
  } // get_items ()


  function get_enclosure (&$context) {
    global $CFG;
    $result = NULL;
    if (method_exists ($context, 'get_enclosure')) {
      $result = $context->get_enclosure ();
    }
    return array ('cdata' => '', 'context' => $result);
  } // get_enclosure ()


  function get_image (&$context) {
    $result = NULL;
    if (method_exists($context, 'get_image')) {
      do {
        $result = $context->get_image ();
        if (!$result) break;
        $hratio  = $result->width ?  144 / $result->width  : $result->width;
        $vratio  = $result->height ? 400 / $result->height : $result->height;

        $ratio   = min ($hratio, $vratio);

        if ($ratio < 1.0) {
          $result->width  = floor ($result->width * $ratio);
          $result->height = floor ($result->height * $ratio);
        }
        $result->title = htmlspecialchars($context->name);
        $result->link  = $this->get_link ($context);
      } while (false);
    }
    return array ('cdata' => '', 'context' => $result);
  } // get_image ()


  function get_skiphours (&$context) {
    $result = array ();
    if (isset ($context->skiphours)) {
      $hours = explode (',', $context->skiphours);
      foreach ($hours as $hour) {
        $result[] = array ('cdata' => $hour, 'context' => NULL);
      }
    }
    return $result;
  } // get_skiphours ()


  function get_skipdays (&$context) {
    $result = array ();
    if (isset ($context->skipdays)) {
      $days = explode (',', $context->skipdays);
      foreach ($days as $day) {
        $result[] = array ('cdata' => $day, 'context' => NULL);
      }
    }
    return $result;
  } // get_skipdays ()

  function str_notempty (&$value) {
    if (!isset ($value) || !$value + '') return false;
    return true;
  } // str_notempty ()

  function int_notnull (&$value) {
    return ($value * 1 != 0);
  } // int_notnull ()

  function obj_notnull (&$value) {
    return ($value != NULL);
  } // obj_notnull ()

} // class rssfull_format
?>
