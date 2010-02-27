<?php
/**
 * edit or add a particular item to a channel 
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 *
 * objects defined in the wrapper script:
 *  - obj: module instance
 *  - course: current course
 **/
  require_once ('../../config.php');
  require_once ('lib.php');
  require_once ('locallib.php');
  require_once ('lib/itemform.php');

  $id      = optional_param('id',      0,         PARAM_INT);
  $channel = optional_param('channel', 0,         PARAM_INT);  
  $cm      = optional_param('cm',    0,           PARAM_INT);

  if ($cm) {
    if (!$cm = get_record('course_modules', 'id', $cm)) {
      error('Course Module ID was incorrect');
    }
    if (!$course = get_record('course', 'id', $cm->course)) {
      error('Course is misconfigured');
    }
    if (!$channel = get_record('podcaster', 'id', $cm->instance)) {
      error('Course module is incorrect');
    }
  } else {
    if (!$channel = get_record('podcaster', 'id', $channel)) {
      error('Course module is incorrect');
    }
    if (!$course = get_record('course', 'id', $channel->course)) {
      error('Course is misconfigured');
    }
    if (!$cm = get_coursemodule_from_instance('podcaster', $channel->id, $course->id)) {
      error('Course Module ID was incorrect');
    }
  }
  require_login($course->id, false, $cm);

  // load appropriate channel
  $channel = podcaster_channel::create_channel ($channel, $cm);
  if (!$channel->can_edit ()) {
    print_error ('nopermissions');
  }

  // get the item instance
  if ($id) {
    $item = $channel->get_item ($id);
  }
  else {
    $item = $channel->create_item ();
  }
  if (!$item) {
    error('Item ID was incorrect');
  }

  $mform =& new podcaster_itemform ($item, $course, $cm);
  $mform->set_data ($item->get_data ());

  if ($mform->is_cancelled ()) {
    redirect ('view.php?id='.$cm->id.'&amp;tab=items');
  }
  else if ($mform->is_submitted ()) {
    $formdata = $mform->get_data ();
    foreach ($formdata as $p => $v) {
      $item->$p = $v;
    }

    $item->timemodified = time ();
    // clear cached values
    $item->enclosureurl    = '';
    $item->enclosuretype   = '';
    $item->enclosurelength = 0;

    if (isset ($item->id) && $item->id) {
      update_record ('podcaster_item', $item);
    }
    else {
      $item->channel = $channel->id;
      $item->timecreated = time ();
      insert_record ('podcaster_item', $item);
    }

    // update channel record
    $podupd                = new object ();
    $podupd->id            = $channel->id;
    $podupd->timemodified  = time ();

    update_record ('podcaster', $podupd);

    
    // update rss
    $channel->timemodified = time ();
    podcaster_update_rss ($channel);
    redirect ('view.php?id='.$cm->id.'&amp;tab=items');
  }
  $strpodcaster  = get_string ('modulename',       'podcaster');
  $strpodcasters = get_string ('modulenameplural', 'podcaster');
  $stredit   = $item->id == 0 ? get_string('additem', 'podcaster') : get_string('edit');


  print_header_simple(format_string($item->title), "",
               "<a href=\"index.php?id=$course->id\">$strpodcasters</a> ->
                <a href=\"view.php?id=$cm->id\">".format_string($channel->name, true)."</a> -> $stredit", "",
                "", true, "", navmenu($course, $cm));



  print_heading(format_string($item->title));
  $mform->display();

  print_footer($course);
?>
