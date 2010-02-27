<?php
/**
 * ITunes Template  
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 *
 **/
require_once ($CFG->dirroot.'/mod/podcaster/formats/rss/lib.php');

class itunes_format extends rss_format {
//
// FORM
//
  // Hinzufuegen, Loeschen oder Aendern von Form-Elementen 
  // fuer Channel Bearbeiten Formular, Beispiele: 
  // siehe formats/rss/lib.php oder formats/hu/lib.php
  function define_channelform (&$mform, &$obj) {
    parent::define_channelform (&$mform, $obj);
  } // define_channelform ()

  // Hinzufuegen, Loeschen oder Aendern von Form-Elementen 
  // fuer Item Bearbeiten Formular, Beispiele: 
  // siehe formats/rss/lib.php oder formats/hu/lib.php
  function define_itemform (&$mform, &$obj) {
    parent::define_itemform ($mform, $obj);
  } // define_itemform ()
  
//
// HTML
//
// Die Folgenden Funktionen liefern HTML-Schnippsel fuer verschiedene Ansichten
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
    global $CFG;
    $url = NULL;
    $enclosure = $item->get_enclosure ();
    if ($enclosure) {
      $url = $enclosure->url;
    }
    return    '<div class="date">'.userdate($item->scheduledtime).'</div>'.
              '<div class="title"><a href="'.$CFG->wwwroot.'/mod/podcaster/view.php?channel='.$item->channel.'&amp;tab=item&amp;item='.$item->id.'">'.$item->title.'</a></div>'.
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
// XML / RSS
//
  function get_schema () {
    // Format der Angaben: 
    //    'XMLPath' => array (Quelltyp, Zielformat, Datenquelle)
    //        Quelltyp:
    //            - XML_DATA_CONTEXT: Inhalt wird als Eigenschaft des Context erwartet
    //              Beispiel:          
    //                '/rss/channel/title'                    
    //                    => array(XML_DATA_CONTEXT,  XML_TYPE_STRING,    'name')
    //
    //            - XML_DATA_STATIC:  Inhalt ist ein statischer Wert
    //              Beispiel:
    //                '/rss/@version'                         
    //                    => array(XML_DATA_STATIC,  XML_TYPE_STRING,     '2.0'),
    //
    //            - XML_DATA_CALLBK:  Inhalt wird durch Aufruf einer Callback-Methode
    //                                ermittelt, diese muss einen String zurueckliefern
    //              Beispiel:
    //                '/rss/channel/link'                     
    //                    => array(XML_DATA_CALLBK,   XML_TYPE_STRING,    'get_link'),  
    //
    //            - XML_DATA_ONCE:    Inhalt wird einmal ausgegeben. Ist eine Datenquelle
    //                                (= Callbackmethode) angegeben, wird diese aufgerufen
    //                                und fuer untergeordnete Elemente der von dieser
    //                                Methode zurueckgegebene Context gesetzt. Ansonsten
    //                                bleibt es beim aktuellen Context
    //              Beispiele:
    //                '/rss'                                  
    //                    => array(XML_DATA_ONCE,    XML_TYPE_OBJECT,     NULL),
    //                '/rss/channel'                          
    //                    => array(XML_DATA_ONCE,     XML_TYPE_OBJECT,    'get_channel'),    
    //
    //            - XML_DATA_LOOP:    wie XML_DATA_ONCE, es muss eine Callback-Methode
    //                                angegeben werden, die einen array von Context's
    //                                fuer untergeordnete Elemente zurueckgibt
    //              Beispiele:
    //                '/rss/channel/category'                 
    //                    => array(XML_DATA_LOOP,   XML_TYPE_OBJECT,    'get_categories'),
    //
    //            - XML_DATA_IGNORE:  Knoten wird ignoriert
    //              Beispiele:
    //                '/rss/channel/image/width'
    //                    => array(XML_DATA_IGNORE),
    //
    $schema    = parent::get_schema ();
    $override = 
      array (
       '/rss/@xmlns:itunes'
              => array(XML_DATA_STATIC, XML_TYPE_STRING,   'http://www.apple.com/itunes/store/podcaststechspecs.html'),

       '/rss/channel/itunes:author'
              => array(XML_DATA_CALLBK, XML_TYPE_STRING,   'get_itunesauthor'),
      );

    return array_merge ($schema, $override);
  } // get_schema ()

//
// data callbacks
//

  function get_itunesauthor (&$context) {
    $author = $context->managingeditor;
    return ereg_replace (' *<[^>]*>', '', $author);
  } // get_itunesauthor ()

} // class itunes_format

?>
