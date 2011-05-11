<?php
/**
 * podcaster formats for xml and form generation 
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
///////////////////////////////////////////////////////////////////////////////
//
// data request / node generation methods
//
  // static values
define ('XML_DATA_NULL',   1);
define ('XML_DATA_STATIC', 4);
  // skip element
define ('XML_DATA_IGNORE', 5);
  // get values from current context
define ('XML_DATA_CONTEXT', 2);
define ('XML_DATA_CALLBK', 3);
  // change context to object(s) provided by callback functions
  // and output once / multiple
define ('XML_DATA_ONCE',   6); 
define ('XML_DATA_LOOP',   7);

///////////////////////////////////////////////////////////////////////////////
//
// XML Node types
//
define ('XML_NODETYPE_ROOT',     10);
define ('XML_NODETYPE_ELEMENT',   8);
define ('XML_NODETYPE_ATTRIBUTE', 9);

///////////////////////////////////////////////////////////////////////////////
//
// XML Data types
define ('XML_TYPE_STRING',       20);
define ('XML_TYPE_DATETIME',     21);
define ('XML_TYPE_INTEGER',      22);
define ('XML_TYPE_BOOLEAN',      23);
define ('XML_TYPE_OBJECT',       30);


class podcaster_formatimpl extends podcaster_format {
  var $rootNode,
      $channel;

  function podcaster_formatimpl () {
    $this->rootNode = NULL;
  }
  
  function set_data (&$channel) {
    $this->channel = $channel; // podcaster_channel::create_channel ($channel);
  } // set_data ()

  function set_obj (&$channelObj) {
    $this->channel = $channelObj;
  } // set_obj ()
  
  // XML generation
  function write_xml (&$stream) {
    if (!$this->rootNode) {
      $this->rootNode =& podcaster_xmlnode::create_node ('', XML_NODETYPE_ROOT);
      $schema = $this->get_schema ();
      foreach ($schema as $path => $data) {
        $this->rootNode->addChild ($path, $data);
      }
    }
    $this->rootNode->write_xml ($this, $c = NULL, $stream, -1);
  } // write_xml ()

} // class podcaster_formatimpl


/**
  * XML / RSS generation
  */
class podcaster_xmlnode {
  var $name,
      $type,
      $data;
  
  function & create_node ($name, $type, $data = null) {
    switch ($type) {
      case XML_NODETYPE_ELEMENT:
        $result = new podcaster_xmlelement ($name, $data);
        break;
      case XML_NODETYPE_ATTRIBUTE:
        $result = new podcaster_xmlattribute ($name, $data);
        break;
      case XML_NODETYPE_ROOT:
        $result = new podcaster_xmlrootnode ('', XML_NODETYPE_ROOT, array (XML_DATA_ONCE, NULL, NULL));
        break;
      default: 
        $result = NULL;
    }
    return $result;
  } // create_node ()

  function podcaster_xmlnode ($name = '', $type = XML_NODETYPE_ELEMENT, $data = NULL) {
    $this->name = $name;
    $this->type = $type;
    $this->data = $data;
  } // podcaster_xmlnode ()

  function addChild ($path, &$data) {
    if (!is_array($path)) {
      $path = explode ('/', $path);
      while ($path[0] == '') array_shift ($path);
    }
    foreach ($this->children as $name => $node) {
      if ($name == $path[0]) {
        array_shift ($path);
        $node->addChild ($path, $data);
        return;
      }
    }
    $nodeType = (strpos ($path[0], '@') === 0 ? XML_NODETYPE_ATTRIBUTE : XML_NODETYPE_ELEMENT);
    $nodeName = ($nodeType == XML_NODETYPE_ATTRIBUTE ? substr ($path[0], 1) : $path[0]);
    $child =& podcaster_xmlnode::create_node($nodeName, $nodeType, $data);

    if ($nodeType == XML_NODETYPE_ELEMENT) {
      $this->children[$path[0]] = $child;
    }
    else {
      $this->attributes[$path[0]] = $child;
    }
  } // addChild ()

  function write_xml (&$provider, &$context, &$stream, $level, $method = NULL) {
    $use = ($method == NULL ? $this->data : $method);

    $method     = $use[0];
    $type       = count($use) > 1 ? $use[1] : NULL;
    $source     = count($use) > 2 ? $use[2] : NULL;
    $constraint = count($use) > 3 ? $use[3] : NULL;

    switch ($method) {
      case XML_DATA_IGNORE:
        return;

      case XML_DATA_NULL:
      case XML_DATA_CALLBK:
      case XML_DATA_STATIC:
      case XML_DATA_CONTEXT:
        // write CDATA
        $content = '';
        switch ($method) {
          case XML_DATA_NULL:
            break;
          case XML_DATA_STATIC:
            $content = $source;
            break;
          case XML_DATA_CONTEXT:
            $prop    = $source;
            $content = isset($context->$prop) ? $context->$prop : '';
            break;
          case XML_DATA_CALLBK:
            if (!method_exists ($provider, $source)) {
              error ('Error in XML schema definition: callback method "'.$source.'" doesn\'t exist!');
            }
            $methodname = $source;
            $content = $provider->$methodname ($context);
            break;
        }
        if ($constraint && method_exists ($provider, $constraint)) {
          if (!$provider->$constraint ($content)) 
            return;
        }
        $this->writeOpenNode ($provider, $context, $stream, $level);
        $stream->write ($this->formatXML($content, $type));
        $this->writeContent ($provider, $context, $stream, $level);
        $this->writeCloseNode ($provider, $context, $stream, $level);
        break;

      case XML_DATA_ONCE:
      case XML_DATA_LOOP:
        $spool = NULL;
        if (!$source) {
          // no callback provided
          $spool = ($method == XML_DATA_ONCE ? array(array('cdata' => '', 'context' => $context)) : array ());
        }
        elseif (method_exists ($provider, $source)) {
          $data  =& $provider->$source ($context);
          $spool = ($method == XML_DATA_ONCE ? array($data) : $data);
        }
        else {
          error ('Error in XML schema definition: callback method "'.$this->data[2].'" doesn\'t exist!');
        }
        for ($i = 0, $s = count ($spool); $i < $s; ++$i) {

          if ($constraint && method_exists ($provider, $constraint)) {
            if (!$provider->$constraint ($spool[$i]['context']))
              continue;
          }

          $this->write_xml($provider, $spool[$i]['context'], $stream, $level, array(XML_DATA_STATIC, XML_TYPE_STRING, $spool[$i]['cdata']));
        }
        return;
      default:
        return;
    }

  } // write_xml

  function formatXML ($in, $type = XML_TYPE_STRING) {
    switch ($type) {
      case XML_TYPE_INTEGER:
        return $in * 1;
      case XML_TYPE_BOOLEAN:
        return ($in ? 'true' : 'false');
      case XML_TYPE_DATETIME:
        return date (DATE_RSS, ($in * 1));
      case XML_TYPE_STRING:
      default:
        return htmlspecialchars($in, ENT_COMPAT, 'UTF-8');
    }
  } // formatXML ()
  
  function writeOpenNode (&$provider, &$context, &$stream, $level) {
    // should be implemented by subclasses
  } // writeOpenNode ()
  
  function writeCloseNode (&$provider, &$context, &$stream, $level) {
    // should be implemented by subclasses
  } // writeCloseNode ()

  function writeContent (&$provider, &$context, &$stream, $level) {
    // should be implemented by subclasses
  } // writeContent ()

} // class podcaster_xmlnode

class podcaster_xmlrootnode extends podcaster_xmlnode {
  var $children;

  function podcaster_xmlrootnode ($name = '', $type = XML_NODETYPE_ROOT, $data = NULL) {
    $this->podcaster_xmlnode ($name, $type, $data);
    $this->children = array ();
  } // podcaster_xmlrootnode ()

  function writeOpenNode (&$provider, &$context, &$stream, $level) {
    $stream->write ('<?xml');
    foreach ($this->attributes as $attr) {
      $attr->write_xml ($provider, $context, $stream, $level);
    }
    $stream->write ('?>');
  } // writeOpenNode ()

  function writeContent (&$provider, &$context, &$stream, $level) {
    foreach ($this->children as $child) {
      $child->write_xml ($provider, $context, $stream, $level + 1);
    }
  } // writeContent ()
  
} // class podcaster_xmlrootnode


class podcaster_xmlelement extends podcaster_xmlrootnode {
  var $attributes;

  function podcaster_xmlelement ($name, $data) {
    $this->podcaster_xmlrootnode ($name, XML_NODETYPE_ELEMENT, $data);
    $this->attributes = array ();
  } // podcaster_xmlelement ()

  function writeOpenNode (&$provider, &$context, &$stream, $level) {
    $stream->write("\n".str_repeat('  ', $level).'<'.$this->name);
    foreach ($this->attributes as $attr) {
      $attr->write_xml ($provider, $context, $stream, $level);
    }
    $stream->write ('>');
  } // writeOpenNode ()
  
  function writeCloseNode (&$provider, &$context, &$stream, $level) {
    $stream->write ((count($this->children) > 0 ? "\n".str_repeat ('  ', $level) : '').'</'.$this->name.'>');
  } // writeCloseNode ()

} // class podcaster_xmlelement


class podcaster_xmlattribute extends podcaster_xmlnode {

  function podcaster_xmlattribute ($name, $data) {
    $this->podcaster_xmlnode ($name, XML_NODETYPE_ATTRIBUTE, $data);
  }  // podcaster_xmlattribute ()

  function addChild ($path, $data) {
    error ('Error in XML schema definition: attributes can\'t have children!');
  } // addChild ()

  function writeOpenNode (&$provider, &$context, &$stream, $level) {
     $stream->write (' '.$this->name.'="');
  } // writeOpenNode ()

  function writeCloseNode (&$provider, &$context, &$stream, $level) {
    $stream->write ('"');
  } // writeCloseNode ()

} // class podcaster_xmlattribute
?>
