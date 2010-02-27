<?php
/**
 * This page prints a particular podcaster
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 *
 *            based on moodle standard module template
 *
 * @version 1.0
 * @package podcaster
 **/
  require_once('../../config.php');
  require_once('lib.php');
  require_once('locallib.php');

  $id      = optional_param('id',      0,         PARAM_INT);
  $tab     = optional_param('tab',     'channel', PARAM_TEXT);
  $channel = optional_param('channel', 0,         PARAM_INT);  

  $tab  = ($tab == 'items' ? 'items' : ($tab == 'item' ? 'item' : 'channel'));
      
  if ($id) {
    if (!$cm = get_record('course_modules', 'id', $id)) {
      error('Course Module ID was incorrect');
    }
    if (!$course = get_record('course', 'id', $cm->course)) {
      error('Course is misconfigured');
    }
    if (!$obj = get_record('podcaster', 'id', $cm->instance)) {
      error('Course module is incorrect');
    }
  } else {
    if (!$obj = get_record('podcaster', 'id', $channel)) {
      error('Course module is incorrect');
    }
    if (!$course = get_record('course', 'id', $obj->course)) {
      error('Course is misconfigured');
    }
    if (!$cm = get_coursemodule_from_instance('podcaster', $obj->id, $course->id)) {
      error('Course Module ID was incorrect');
    }
  }

// Version 2008022501
  if ($obj->ismeta) {
    if (has_capability('moodle/site:config', get_context_instance (CONTEXT_SYSTEM)))  {
      redirect ($CFG->wwwroot.'/admin/module.php?module=podcaster&tab=metachannel');
    }
    else {
      error ('You cannot view this channel');
    }
  }
  $channel    = podcaster_channel::create_channel ($obj, $cm);
  $repository = $channel->get_repository ();
  $buildnav   = false;
  
  // allow access to public channels even if user is not logged in 
  // or has no capability to view course content
  do {
    if (!isloggedin () || isguestuser ()) {
      if ($repository && $repository->public && $channel->coursemodule->visible) {
        break;
      }
    }

    if (!($context = get_context_instance(CONTEXT_COURSE, $course->id))) {
      error ('Could not setup context');
    }
    if (has_capability ('moodle/course:view', $context)) {
      require_login($course->id);
      $buildnav = true;
    }
    elseif (!$repository || !$repository->public || !$channel->coursemodule->visible) {
      error ('You cannot view this channel');
    }
  } while (false);

  $page     = podcaster_page::create_page ($channel);

/// Print the page header
  $strpodcasters = get_string('modulenameplural', 'podcaster');
  $strpodcaster  = get_string('modulename',       'podcaster');

  $navlinks = array();
  $navlinks[] = array('name' => $strpodcasters, 'link' => 'index.php?id='.$course->id, 'type' => 'activity');
  $navlinks[] = array('name' => format_string($channel->name), 'link' => '', 'type' => 'activityinstance');
  $navigation = $channel->name;
  
  print_header_simple(format_string($obj->name), '', $navigation, '', $page->get_stylesheet(), true,
                update_module_button($cm->id, $course->id, $strpodcaster), ($buildnav ? navmenu($course, $cm) : false));

  // set up tabs
  $tabs = array (
      array (
        // channel / summary tab
            new tabobject('channel',
                            $CFG->wwwroot.'/mod/podcaster/view.php?'.
                            'id='.$cm->id,
                            get_string('channel_tabtitle', 'podcaster')),
        // items tab
            new tabobject('items',
                            $CFG->wwwroot.'/mod/podcaster/view.php?'.
                            'id='.$cm->id.'&amp;'.
                            'tab=items',
                            get_string('items_tabtitle', 'podcaster'))
        )
      ); 
  if ($tab == 'item') {
    $tabs[0][] = new tabobject ('item',
                                  $CFG->wwwroot.'/mod/podcaster/view.php?'.
                                  'id='.$cm->id.'&amp;'.
                                  'tab=item&amp;'.
                                  'item='.optional_param ('item', 0, PARAM_INT),
                                  get_string('item_tabtitle', 'podcaster'));
  }
  print_tabs ($tabs, $tab);
/// Print the main part of the page
  include_once('tabs/'.$tab.'.php');
/// Finish the page
  print_footer($course);
?>
