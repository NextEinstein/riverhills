<?php  
/**
 * Library of functions and constants for module podcaster
 */

define ('PODCASTER_UPDATESELF',       1);
define ('PODCASTER_UPDATEREPOSITORY', 2);

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will create a new instance and return the id number 
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted podcaster record
 **/
function podcaster_add_instance($podcaster) {
    podcaster_preprocess_data ($podcaster);
    $podcaster->timecreated = time();
    $podcaster->dirty       = 1;

    $podcaster->imagewidth  = 0;
    $podcaster->imageheight = 0;
    $podcaster->imageurl    = '';
    $podcaster->imagetype   = '';
    if (($result = insert_record('podcaster', $podcaster)) != false) {
      podcaster_update_rss ($podcaster);
    }
    return $result;
}

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will update an existing instance with new data.
 *
 * @param object $instance An object from the form in mod.html
 * @return boolean Success/Fail
 **/
function podcaster_update_instance($podcaster) {
    podcaster_preprocess_data ($podcaster);
    $podcaster->timemodified = time();
    $podcaster->id = $podcaster->instance;
    $podcaster->dirty = 1;

    $podcaster->imagewidth  = 0;
    $podcaster->imageheight = 0;
    $podcaster->imageurl    = '';
    $podcaster->imagetype   = '';
    
    if (($result = update_record('podcaster', $podcaster)) != false) {
      podcaster_update_rss($podcaster);
    }
    return $result;
}

/**
 * Given an ID of an instance of this module, 
 * this function will permanently delete the instance 
 * and any data that depends on it. 
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 **/
function podcaster_delete_instance($id) {
    global $CFG;
    if (! $podcaster = get_record('podcaster', 'id', "$id")) {
        return false;
    }
    require_once ('locallib.php');
    $result = true;
    delete_records ('podcaster_item', 'channel', $id);

    $channel = podcaster_channel::create_channel ($podcaster);
    $channel->delete_file ('rss/'.$id.'.xml');

    // TODO! Fix for course / category metachannels
    //  check for affected meta channels
    $metachannels = get_records('podcaster_metachannel'); 
    if ($metachannels) {
      foreach ($metachannels as $metachannel) {
        if ($metachannel->target == 'repository') {
          $params = explode (',', $metachannel->params);
          if (in_array ($channel->repository, $params)) {
            $metachannel->timemodified = time ();
            update_record ('podcaster_metachannel', $metachannel);
          }
        }
      }
    }

    if (! delete_records('podcaster', 'id', $id)) {
        $result = false;
    }
    return $result;
}

/**
 * Return a small object with summary information about what a 
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 **/
function podcaster_user_outline($course, $user, $mod, $podcaster) {
    return  null;
}

/**
 * Print a detailed representation of what a user has done with 
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function podcaster_user_complete($course, $user, $mod, $podcaster) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity 
 * that has occurred in podcaster activities and print it out. 
 * Return true if there was output, or false is there was none. 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function podcaster_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;
    return false;  //  True if anything was printed, otherwise false 
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such 
 * as sending out mail, toggling flags etc ... 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function podcaster_cron () {
    global $CFG;
    require_once ($CFG->dirroot.'/mod/podcaster/locallib.php');

    $modconf  = get_record ('modules', 'name', 'podcaster');
    $lastcron = $modconf->lastcron * 1;
    
    $channelupdate     = array ();
    $repositoryupdate  = array ();
    $metachannelupdate = array ();

    // find channels modified past lastcron
    $channels = get_records_select ('podcaster', 'timemodified > '.$lastcron.' AND ismeta = 0');
    if ($channels) {
      foreach ($channels as $channel) {
        $channelupdate[''.$channel->id] = PODCASTER_UPDATEREPOSITORY;
      }
    }

    // find items scheduled > lastcron && < now ()
    $items = get_records_select ('podcaster_item', '(scheduledtime > '.$lastcron.' AND scheduledtime < '.time ().')');
    if ($items != false) {
      foreach ($items as $item) {
        $channelupdate[''.$item->channel] = PODCASTER_UPDATESELF; // implies update repository
      }
    }
    

    // update channel rss feeds
    foreach ($channelupdate as $id => $subject) {
      $c =   get_record ('podcaster', 'id', $id);
      if (!$c)
        continue;
      $cm =  get_coursemodule_from_instance ('podcaster', $id);
      if (!$cm)
        continue;

      $channel = podcaster_channel::create_channel ($c, $cm);
      if ($subject == PODCASTER_UPDATESELF) {
        $channel->update_rss ();
      }
      $repositoryupdate[$channel->repository] = true;
    }

    //  check for modified meta channels
    $metachannels = get_records('podcaster_metachannel'); 

    if ($metachannels) {
      foreach ($metachannels as $metachannel) {
        if ($metachannel->timemodified > $lastcron) {
          $metachannelupdate[$metachannel->id.''] = $metachannel;
        }
        elseif ($metachannel->target == 'repository') {
          $params = explode (',', $metachannel->params);
          foreach ($params as $p) {
            if (array_key_exists ($p, $repositoryupdate)) {
              $metachannelupdate[$metachannel->id.''] = $metachannel;
            }
          }
        }
      }
    }
    foreach ($metachannelupdate as $metachannel) {
      $channel = podcaster_channel::create_metachannel ($metachannel);
      $channel->update_rss ();
    }
    return true;
}

/**
 * Must return an array of grades for a given instance of this module, 
 * indexed by user.  It also returns a maximum allowed grade.
 * 
 * Example:
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 *
 * @param int $podcasterid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 **/
function podcaster_grades($podcasterid) {
   return NULL;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of podcaster. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $podcasterid ID of an instance of this module
 * @return mixed boolean/array of students
 **/
function podcaster_get_participants($podcasterid) {
    return false;
}

/**
 * This function returns if a scale is being used by one podcaster
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $podcasterid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 **/
function podcaster_scale_used ($podcasterid,$scaleid) {
    $return = false;

    //$rec = get_record("podcaster","id","$podcasterid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}
   
    return $return;
}

/**
 * Checks if scale is being used by any instance of podcaster.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any podcaster
 */
function podcaster_scale_used_anywhere($scaleid) {
    // if ($scaleid and record_exists('podcaster', 'grade', -$scaleid)) {
    //    return true;
    // } else {
    //    return false;
    // }
}

/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function podcaster_install() {
     return true;
}

/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function podcaster_uninstall() {
    return true;
}

/**
 * Callback for moodle admin page. 
 *
 * @return boolean true if success, false on error
 */
function podcaster_process_options (&$config) {
  global $CFG;

  $tab = $config->tab;
  switch ($tab) {
    case 'main':
    case 'license':
    case 'repository':
    case 'metachannel':
    case 'language':
    case 'tools':
      // OK
      break;
    default:
      $tab = 'main';
  }
  $module = $config->module;
  $return = (isset ($config->finish) ? true : false);
  $next   = (isset ($config->continue) ? true : false);

  $error = 0;

  require_once ($CFG->dirroot.'/mod/podcaster/locallib.php');
  require_once ($CFG->dirroot.'/mod/podcaster/lib/admin.php');
  $admin =& podcaster_admin::get_instance ();

  if (($error = $admin->process_form ($tab, $config)) != PODCASTER_NOERROR) {
    $return  = false;
    $next    = false;
    $subject = $admin->subject;
  }
  if ($return) {
    redirect($CFG->wwwroot.'/'.$CFG->admin.'/modules.php', get_string('changessaved'), 1);
  }
  $nexttab = (!$next ? $tab :
                ($tab == 'main' ? 'license' : 
                  ($tab == 'license' ? 'repository' : 
                    ($tab == 'repository' ? 'metachannel' : 
                     ($tab == 'metachannel' ? 'language' : 
                      ($tab == 'language' ? 'tools' : 'main'))))));

  redirect($CFG->wwwroot.'/'.$CFG->admin.'/module.php?module='.$module.'&amp;tab='.$nexttab.($error ? '&amp;error='.$error.'&amp;subject='.$subject : ''));
}
//////////////////////////////////////////////////////////////////////////////////////
/// Any other podcaster functions go here.  Each of them must have a name that 
/// starts with podcaster_
/// Remember (see note in first lines) that, if this section grows, it's HIGHLY
/// recommended to move all funcions below to a new "localib.php" file.

function podcaster_preprocess_data (&$podcaster) {
  if (!isset ($podcaster->showpreview)) {
    $podcaster->showpreview = 0;
  }
  if (isset ($podcaster->format)) {
    require_once ('locallib.php');
    $format =& podcaster_format::create_format ($podcaster->format);
    $format->preprocess_channel (&$podcaster);
  }
} // podcaster_preprocess_data ()

function podcaster_update_rss ($channel) {
  global $CFG;
  require_once ('locallib.php');
  $cm = NULL;
  $record  = get_record ('podcaster', 'id', $channel->id);
  if (!$record->ismeta) {
    if (isset ($channel->visible)) {
      $record->visible = $channel->visible;
    }
    $channel =& podcaster_channel::create_channel ($record, $cm);
    $channel->update_rss ();
  }
} // podcaster_update_rss ()

function podcaster_sync_file ($repository, $path, $action) {
  global $CFG;
  $updatelist = array ();
  
  $sql = 'SELECT * FROM '.$CFG->prefix.'podcaster WHERE repository = '.$repository.' AND image = \''.$path.'\'';
  $channels = get_records_sql ($sql);
  
  if ($channels) {
    foreach ($channels as $channel) {
      $channelObj = podcaster_channel::create_channel ($channel);

      $channel->image       = $channelObj->image       = ($action == 'delete' ? '' : $path);
      $channel->imageurl    = $channelObj->imageurl    = '';
      $channel->imagetype   = $channelObj->imagetype   = '';
      $channel->imagelength = $channelObj->imagelength = 0;
      $channel->imagewidth  = $channelObj->imagewidth  = 0;
      $channel->imageheight = $channelObj->imageheight = 0;

      update_record ('podcaster', $channel);

      $updatelist[$channel->id.''] = $channelObj;
    }
  }
  $sql = 'SELECT '.$CFG->prefix.'podcaster_item.* FROM '.$CFG->prefix.'podcaster,'.$CFG->prefix.'podcaster_item 
            WHERE '.$CFG->prefix.'podcaster.repository = '.$repository.' 
            AND   '.$CFG->prefix.'podcaster.id = '.$CFG->prefix.'podcaster_item.channel
            AND   '.$CFG->prefix.'podcaster_item.enclosure = \''.$path.'\'';

  $items = get_records_sql ($sql);
  if ($items) {
    foreach ($items as $item) {
      if (!array_key_exists ($item->channel.'', $updatelist)) {
        $channel    = get_record ('podcaster', 'id', $item->channel);
        $channelObj = podcaster_channel::create_channel ($channel);
        $updatelist[$channel->id.''] = $channelObj;
      }
      $item->enclosure       = ($action == 'delete' ? '' : $path);
      $item->enclosureurl    = '';
      $item->enclosuretype   = '';
      $item->enclosurelength = 0;
      update_record ('podcaster_item', $item);
    }
  }
  foreach ($updatelist as $id => $channelObj) {
    if (!$channelObj->ismeta) {
      $channelObj->update_rss ();
    }
  }
} // podcaster_sync_file ()

if (isset ($CFG->podcaster_type)) {
  function podcaster_get_types () {
    global $CFG;

    require_once ($CFG->dirroot.'/mod/podcaster/lib/util.php');
    $usegroups = false;
    
    $types = array ();

    if (isset ($CFG->podcaster_submenus) && $CFG->podcaster_submenus != 'none') {
      // TODO: adapt for other criteria

      $subtypes = array ();

      if (strpos ($CFG->podcaster_submenus, 'repository') !== false) {
        $repositories  = get_records ('podcaster_repository', 'enabled', '1');
        foreach ($repositories as $id => $r) {
          $t = new object ();
          $t->modclass = ($CFG->podcaster_type == 'resource' ? MOD_CLASS_RESOURCE : MOD_CLASS_ACTIVITY);
          $t->type    = 'podcaster&amp;type=repository'.podcaster_util::number2az ($r->id);
          $t->typestr = podcaster_util::get_string ('repository_'.$r->name.'_title');        
          $subtypes[] = $t;
          unset ($t);
        }
      }

      if (strpos ($CFG->podcaster_submenus, 'format') !== false) {
        $formats = explode (',', $CFG->podcaster_formats);

        $merge     = count ($subtypes) > 0;
        $subtypes2 = array (); 

        foreach ($formats as $format) {
          if (!$merge) {
            $t = new object  ();
            $t->modclass = ($CFG->podcaster_type == 'resource' ? MOD_CLASS_RESOURCE : MOD_CLASS_ACTIVITY);
            $t->type     = 'podcaster&amp;type=format'.strtoupper($format);
            $t->typestr  = podcaster_util::get_string ('format_'.$format.'_title');
            $subtypes2[] = $t;
            unset ($t);
          }
          else {
            foreach ($subtypes as $subtype) {
              $t = new object ();
              $t->modclass = $subtype->modclass;
              $t->type     = $subtype->type.'format'.strtoupper($format);
              $t->typestr  = $subtype->typestr.', '.podcaster_util::get_string ('format_'.$format.'_title');

              $subtypes2[] = $t;
              unset ($t);
            }
          }
        }
        $subtypes = $subtypes2;
      }

      if (strpos ($CFG->podcaster_submenus, 'license') !== false) {
        $licenses = get_records ('podcaster_license');

        $merge     = count ($subtypes) > 0;
        $subtypes2 = array (); 

        foreach ($licenses as $id => $license) {
          if (!$merge) {
            $t = new object  ();
            $t->modclass = ($CFG->podcaster_type == 'resource' ? MOD_CLASS_RESOURCE : MOD_CLASS_ACTIVITY);
            $t->type     = 'podcaster&amp;type=license'.podcaster_util::number2az($license->id);
            $t->typestr  = podcaster_util::get_string ('license_'.$license->name.'_title');
            $subtypes2[] = $t;
            unset ($t);
          }
          else {
            foreach ($subtypes as $subtype) {
              $t = new object ();
              $t->modclass = $subtype->modclass;
              $t->type     = $subtype->type.'license'.podcaster_util::number2az($license->id);
              $t->typestr  = $subtype->typestr.', '.podcaster_util::get_string ('license_'.$license->name.'_title');

              $subtypes2[] = $t;
              unset ($t);
            }
          }
        }
        $subtypes = $subtypes2;
      }
      if (count ($subtypes) > 1) {
        $usegroups = true;
        $start = new object ();
        $start->modclass = ($CFG->podcaster_type == 'resource' ? MOD_CLASS_RESOURCE : MOD_CLASS_ACTIVITY);
        $start->type     = 'podcaster_group_start';
        $start->typestr  = '--Podcasts'; 
        $types[] = $start;

        foreach ($subtypes as $subtype) {
          $types[] = $subtype;
        }

        $end = new object ();
        $end->modclass = ($CFG->podcaster_type == 'resource' ? MOD_CLASS_RESOURCE : MOD_CLASS_ACTIVITY);
        $end->type = 'podcaster_group_end';
        $end->typestr = '--';
        $types[] = $end;
      }
    }

    if ($usegroups == false) {
      $type           = new object();
      $type->modclass = ($CFG->podcaster_type == 'resource' ? MOD_CLASS_RESOURCE : MOD_CLASS_ACTIVITY);
      $type->type     = 'podcaster';
      $type->typestr  = get_string('modulename', 'podcaster');
      $types[]        = $type;
    }
    return $types;
  }
}

// postinstall tasks
if (isset ($CFG->podcaster_dopostinstall)) {
  unset_config ('podcaster_dopostinstall');
  unset ($CFG->podcaster_dopostinstall);
  include_once ($CFG->dirroot.'/mod/podcaster/lib/admin.php');
  $admin =& podcaster_admin::get_instance ();
  $admin->postinstall ();
}
?>
