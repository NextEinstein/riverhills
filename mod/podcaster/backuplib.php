<?php
    //
    // Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
    // 
    // mainly copy & paste from mod/book/backuplib.php
    //
    // Backup requires further adaption! Only very basic backup
    // features are supported.
    // 
    // Media and RSS Feeds will only be included for local
    // (i.e. moodle) repositories and also content encoding and 
    // decoding will only work with these podcasts.
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

    //This function executes all the backup procedure about this mod
    function podcaster_backup_mods($bf,$preferences) {
        global $CFG;

        $status = true;

        ////Iterate over podcaster table
        if ($podcasters = get_records ('podcaster', 'course', $preferences->backup_course, 'id')) {
            foreach ($podcasters as $podcaster) {
                if (backup_mod_selected($preferences,'podcaster',$podcaster->id)) {
                    $status = podcaster_backup_one_mod($bf,$preferences,$podcaster);
                }
            }
        }
        return $status;
    }

    function podcaster_backup_one_mod($bf,$preferences,$podcaster) {

        global $CFG;

        if (is_numeric($podcaster)) {
            $podcaster = get_record('podcaster','id',$podcaster);
        }

        $status = true;

        //Start mod
        fwrite ($bf,start_tag('MOD', 3, true));
        //Print podcaster data
        fwrite ($bf,full_tag('ID', 4, false,      $podcaster->id));
        fwrite ($bf,full_tag('MODTYPE', 4, false, 'podcaster'));
        fwrite ($bf,full_tag('NAME', 4, false,    $podcaster->name));

        fwrite ($bf,full_tag('ISMETA',         4, false,    $podcaster->ismeta));
        fwrite ($bf,full_tag('DIRTY',          4, false,    $podcaster->dirty));
        fwrite ($bf,full_tag('INTRO',          4, false,    $podcaster->intro));
        fwrite ($bf,full_tag('INTROFORMAT',    4, false,    $podcaster->introformat));
        fwrite ($bf,full_tag('COPYRIGHT',      4, false,    $podcaster->copyright));
        fwrite ($bf,full_tag('MANAGINGEDITOR', 4, false,    $podcaster->managingeditor));
        fwrite ($bf,full_tag('WEBMASTER',      4, false,    $podcaster->webmaster));
        fwrite ($bf,full_tag('CATEGORY',       4, false,    $podcaster->category));
        fwrite ($bf,full_tag('LANGUAGE',       4, false,    $podcaster->language));
        fwrite ($bf,full_tag('CLOUDDOMAIN',    4, false,    $podcaster->clouddomain));
        fwrite ($bf,full_tag('CLOUDPORT',      4, false,    $podcaster->cloudport));
        fwrite ($bf,full_tag('CLOUDPATH',      4, false,    $podcaster->cloudpath));
        fwrite ($bf,full_tag('CLOUDREGISTERPROCEDURE', 4, false,    $podcaster->cloudregisterprocedure));
        fwrite ($bf,full_tag('CLOUDPROTOCOL', 4, false,    $podcaster->cloudprotocol));
        fwrite ($bf,full_tag('TTL', 4, false,    $podcaster->ttl));
        fwrite ($bf,full_tag('IMAGE', 4, false,    $podcaster->image));
        fwrite ($bf,full_tag('IMAGEURL', 4, false,    $podcaster->imageurl));
        fwrite ($bf,full_tag('IMAGETYPE', 4, false,    $podcaster->imagetype));
        fwrite ($bf,full_tag('IMAGELENGTH', 4, false,    $podcaster->imagelength));
        fwrite ($bf,full_tag('IMAGEWIDTH', 4, false,    $podcaster->imagewidth));
        fwrite ($bf,full_tag('IMAGEHEIGHT', 4, false,    $podcaster->imageheight));
        fwrite ($bf,full_tag('RATING', 4, false,    $podcaster->rating));
        fwrite ($bf,full_tag('TEXTINPUTTITLE', 4, false,    $podcaster->textinputtitle));
        fwrite ($bf,full_tag('TEXTINPUTLINK', 4, false,    $podcaster->textinputlink));
        fwrite ($bf,full_tag('TEXTINPUTNAME', 4, false,    $podcaster->textinputname));
        fwrite ($bf,full_tag('TEXTINPUTDESCRIPTION', 4, false,    $podcaster->textinputdescription));

        fwrite ($bf,full_tag('SKIPHOURS', 4, false,    $podcaster->skiphours));
        fwrite ($bf,full_tag('SKIPDAYS', 4, false,    $podcaster->skipdays));
        fwrite ($bf,full_tag('REPOSITORY', 4, false,    $podcaster->repository));
        fwrite ($bf,full_tag('FORMAT', 4, false,    $podcaster->format));
        fwrite ($bf,full_tag('LICENSE', 4, false,    $podcaster->license));
        fwrite ($bf,full_tag('TIMECREATED', 4, false,    $podcaster->timecreated));
        fwrite ($bf,full_tag('TIMEMODIFIED', 4, false,    $podcaster->timemodified));
        fwrite ($bf,full_tag('SHOWPREVIEW', 4, false,    $podcaster->showpreview));
        //back up the items
        $status = backup_podcaster_items ($bf,$preferences,$podcaster);
        $status = fwrite($bf,end_tag('MOD', 3, true));
        return $status;
    }

    //Backup podcaster_items contents (executed from podcaster_backup_mods)
       function backup_podcaster_items ($bf,$preferences,$podcaster) {

        global $CFG;

        $status = true;
        //Print podcaster's items
        if ($items = get_records('podcaster_item', 'channel', $podcaster->id, 'id')) {
            //Write start tag
            $status =fwrite ($bf,start_tag('ITEMS',4,true));
            foreach ($items as $item) {
                //Start chapter
                fwrite ($bf, start_tag('ITEM',5,true));
                //Print chapter data
                fwrite ($bf, full_tag('ID', 6, false, $item->id));
                fwrite ($bf, full_tag('CHANNEL', 6, false, $item->channel));
                fwrite ($bf, full_tag('SCHEDULEDTIME', 6, false, $item->scheduledtime));
                fwrite ($bf, full_tag('TITLE', 6, false, $item->title));
                fwrite ($bf, full_tag('DESCRIPTION', 6, false, $item->description));
                fwrite ($bf, full_tag('COPYRIGHT', 6, false, $item->copyright));
                fwrite ($bf, full_tag('AUTHOR', 6, false, $item->author));
                fwrite ($bf, full_tag('CATEGORY', 6, false, $item->category));
                fwrite ($bf, full_tag('COMMENTS', 6, false, $item->comments));
                fwrite ($bf, full_tag('ENCLOSURE', 6, false, $item->enclosure));
                fwrite ($bf, full_tag('ENCLOSUREURL', 6, false, $item->enclosureurl));
                fwrite ($bf, full_tag('ENCLOSURETYPE', 6, false, $item->enclosuretype));
                fwrite ($bf, full_tag('ENCLOSURELENGTH', 6, false, $item->enclosurelength));
                fwrite ($bf, full_tag('SOURCE', 6, false, $item->source));
                fwrite ($bf, full_tag('SOURCEURL', 6, false, $item->sourceurl));
                fwrite ($bf, full_tag('TIMECREATED', 6, false, $item->timecreated));
                fwrite ($bf, full_tag('TIMEMODIFIED', 6, false, $item->timemodified));
                //End chapter
                $status = fwrite ($bf, end_tag ('ITEM', 5, true));
            }
            //Write end tag
            $status = fwrite ($bf,end_tag ('ITEMS',4,true));
        }
        return $status;
    } 

    //Return a content encoded to support interactivities linking. Every module
    //should have its own. They are called automatically from the backup procedure.
    function podcaster_encode_content_links ($content,$preferences) {

        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        $result = $content;

        // Link to RSS feeds
        $buscar="/(".$base."\/mod\/podcaster\/repository\/moodle\/file.php\/)([0-9]+)\/(.*)/";
        $result= preg_replace ($buscar,'$@PODCASTERFILE*$2@$$3', $result);

        return $result;
    }


    ////Return an array of info (name,value)
    function podcaster_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {

        if (!empty($instances) && is_array($instances) && count($instances)) {
            $info = array();
            foreach ($instances as $id => $instance) {
                $info += podcaster_check_backup_mods_instances($instance,$backup_unique_code);
            }
            return $info;
        }

         //First the course data
         $info[0][0] = get_string('modulenameplural','podcaster');
         $info[0][1] = count_records('podcaster', 'course', $course);

         //No user data for podcasters ;-)

         return $info;
    }

    ////Return an array of info (name,value)
    function podcaster_check_backup_mods_instances($instance,$backup_unique_code) {
         $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
         $info[$instance->id.'0'][1] = '';

         return $info;
    }

?>
