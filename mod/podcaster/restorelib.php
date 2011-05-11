<?PHP 
    //
    // Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
    // 
    // mainly copy & paste from mod/book/restorelib.php
    //
    // Backup requires further adaption! Only very basic backup
    // features are supported.
    //
    // 
    //This is the 'graphical' structure of the podcaster mod:
    //
    //                       podcaster
    //                     (CL,pk->id)
    //                        |
    //                        |
    //                        |
    //                     podcaster_item
    //               (CL,pk->id, fk->channel)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

    //This function executes all the restore procedure about this mod
    function podcaster_restore_mods($mod,$restore) {

        global $CFG;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid ($restore->backup_unique_code, $mod->modtype, $mod->id);

        if ($data) {
            //Now get completed xmlized object
            $info = $data->info;

            $properties = get_podcaster_channel_properties (); 
                                  
            $podcaster = new object();

        
            $podcaster->course = $restore->course_id;
            foreach ($properties as $p) {
              $podcaster->$p = backup_todb ($info['MOD']['#'][strtoupper ($p)]['0']['#']);
            }

            //The structure is equal to the db, so insert the podcaster
            $newid = insert_record ('podcaster', $podcaster);

            //Do some output
            if (!defined('RESTORE_SILENTLY')) {
                echo '<ul><li>'.get_string('modulename','podcaster').' "'.$podcaster->name.'"<br>';
            }
            backup_flush(300);

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, $mod->modtype, $mod->id, $newid);
                //now restore items
                $status = podcaster_items_restore($mod->id, $newid, $info, $restore);

            } else {
                $status = false;
            }
            //Finalize ul
            if (!defined('RESTORE_SILENTLY')) {
                echo "</ul>";
            }

        } else {
            $status = false;
        }

        return $status;
    }

    //This function restores the podcaster_items
    function podcaster_items_restore ($old_podcaster_id, $new_podcaster_id, $info, $restore) {

        global $CFG;

        $status = true;

        //Get the items array
        $items = $info['MOD']['#']['ITEMS']['0']['#']['ITEM'];

        //Iterate over items
        for($i = 0; $i < sizeof($items); $i++) {
            $sub_info = $items[$i];

            //We'll need this later!!
            $old_id = backup_todb($sub_info['#']['ID']['0']['#']);
            
            $properties = array (
                'channel', 'scheduledtime', 'title', 'description',
                'copyright', 'author', 'category', 'comments', 'enclosure',
                'enclosureurl', 'enclosuretype', 'enclosurelength', 'source',
                'sourceurl', 'timecreated', 'timemodified');

            //Now, build the ASSIGNMENT_CHAPTERS record structure
            $item = new object();
// echo '<pre>';
// var_dump ($sub_info);
// echo '</pre>';
// die ();
            foreach ($properties as $p) {
              $item->$p = backup_todb ($sub_info['#'][strtoupper ($p)]['0']['#']);
            }
            $item->channel = $new_podcaster_id;
            //The structure is equal to the db, so insert the podcaster_chapters
            $newid = insert_record ('podcaster_item', $item);

            //Do some output
            if (($i+1) % 50 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo '.';
                    if (($i+1) % 1000 == 0) {
                        echo '<br>';
                    }
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, 'podcaster_item', $old_id, $newid);
            } else {
                $status = false;
            }
        }
        return $status;
    }

    //This function returns a log record with all the necessay transformations
    //done. It's used by restore_log_module() to restore modules log.
    function podcaster_restore_logs($restore,$log) {

        $status = false;

        //Depending of the action, we recode different things
        switch ($log->action) {
            case "update":
            case "view": //TO DO ... verify!!!
               if ($log->cmid) {
                    //Get the new_id of the chapter (to recode the url field)
                    $ch = backup_getid($restore->backup_unique_code,"podcaster_item",$log->info);
                    if ($ch) {
                        $log->url = "view.php?id=".$log->cmid."&item=".$ch->new_id;
                        $log->info = $ch->new_id;
                        $status = true;
                    }
                }
                break;
            case "view all":
                if ($log->cmid) {
                    //Get the new_id of the module (to recode the info field)
                    $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                    if ($mod) {
                        $log->url = "view.php?id=".$log->cmid;
                        $log->info = $mod->new_id;
                        $status = true;
                    }
                }
                break;
            default:
                if (!defined('RESTORE_SILENTLY')) {
                    echo "action (".$log->module."-".$log->action.") unknown. Not restored<br>";                 //Debug
                }
                break;
        }

        if ($status) {
            $status = $log;
        }
        return $status;
    }

    //Return a content decoded to support interactivities linking. Every module
    //should have its own. They are called automatically from
    //podcaster_decode_content_links_caller() function in each module
    //in the restore process
    function podcaster_decode_content_links ($content, $restore) {
        global $CFG;

        $result = $content;

        $searchstring='/\$@(PODCASTERFILE)\*([0-9]+)@\$/';
        $result = preg_replace ($searchstring, $CFG->wwwroot.'/mod/podcaster/repository/moodle/file.php/'.$restore->course_id.'/', $result);
        return $result;
    }
    
    function podcaster_decode_content_links_caller($restore) {
        $properties = get_podcaster_channel_properties ();
        $itemprops  = get_podcaster_item_properties ();
        $i = 0;
        if ($podcasters = get_records ('podcaster', 'course', $restore->course_id, 'id')) {
          foreach ($podcasters as $podcast) {
            // TODO: optimization. We dont really have to process all the fields
            foreach ($properties as $p) {
              $podcast->$p = restore_decode_content_links_worker ($podcast->$p, $restore);
            }
            $status = update_record ('podcaster', $podcast);

            if ($items = get_records ('podcaster_item', 'channel', $podcast->id, 'id')) {
              foreach ($items as $item) {
                // TODO: optimization. We dont really have to process all the fields
                foreach ($itemprops as $p) {
                  $item->$p = restore_decode_content_links_worker ($item->$p, $restore);
                }
                $status = update_record ('podcaster_item', $item);
              }
            }
          }
          //Do some output
          if (($i+1) % 5 == 0) {
              if (!defined('RESTORE_SILENTLY')) {
                  echo '.';
                  if (($i+1) % 100 == 0) {
                      echo '<br />';
                  }
              }
              backup_flush(300);
          }
        }
        return $status;
    } // 

    function get_podcaster_channel_properties () {
      return array (
                'ismeta', 'dirty', 'name', 'intro', 'introformat',
                'copyright', 'managingeditor', 'webmaster', 'category',
                'language', 'clouddomain', 'cloudport', 'cloudpath', 
                'cloudregisterprocedure', 'cloudprotocol', 'ttl', 'image',
                'imageurl', 'imagetype', 'imagelength', 'imagewidth', 'imageheight',
                'rating', 'textinputtitle', 'textinputdescription', 'textinputname',
                'textinputlink', 'skiphours', 'skipdays', 'repository', 'format',
                'license', 'timecreated', 'timemodified', 'showpreview'
                );
    }

    function get_podcaster_item_properties () {
      return array (
        'channel', 'scheduledtime', 'title', 'description',
        'copyright', 'author', 'category', 'comments', 'enclosure',
        'enclosureurl', 'enclosuretype', 'enclosurelength', 'source',
        'sourceurl', 'timecreated', 'timemodified');
    }


?>
