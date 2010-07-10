<?php
global $CFG;

require_once($CFG->libdir.'/biblelib.php');

define('MEMORIZATION_COMPLETE', 161);

define('MEMORIZATION_VERSE_DAY_LOWER', 0);
define('MEMORIZATION_VERSE_DAY_UPPER', 49);

define('MEMORIZATION_VERSE_WEEK_LOWER', MEMORIZATION_VERSE_DAY_UPPER);
define('MEMORIZATION_VERSE_WEEK_UPPER', 77);

define('MEMORIZATION_VERSE_MONTH_LOWER', MEMORIZATION_VERSE_WEEK_UPPER);
define('MEMORIZATION_VERSE_MONTH_UPPER', 161);

define('MEMORIZATION_VERSE_LIFE_LOWER', MEMORIZATION_VERSE_MONTH_UPPER);
define('MEMORIZATION_VERSE_LIFE_UPPER', 201);

function memorization_print_view_method7week($userid) {
    global $CFG;

    $currenttab = optional_param('currenttab', 'day', PARAM_ALPHA);

    // calculate the range of repititions for current tab
    $repititionsupper = $repetitionslower = 0;

    switch ($currenttab) {
        case 'day' :                            //once a day for 7 weeks ----------7 weeks * 7d/week = 49 times
            $repetitionslower = MEMORIZATION_VERSE_DAY_LOWER;
            $repetitionsupper = MEMORIZATION_VERSE_DAY_UPPER;
            break;
        case 'week' :                           // once a week for 7 months -------7 months * 4w/month = 28 more times
            $repetitionslower = MEMORIZATION_VERSE_WEEK_LOWER;
            $repetitionsupper = MEMORIZATION_VERSE_WEEK_UPPER;
            break;
        case 'month' :                          // once a month for 7 years--------7 years * 12m/year = 84 more times
            $repetitionslower = MEMORIZATION_VERSE_MONTH_LOWER;
            $repetitionsupper = MEMORIZATION_VERSE_MONTH_UPPER;
            break;
        case 'life' :                           // once a quarter for life
            $repetitionslower = MEMORIZATION_VERSE_LIFE_LOWER;
            $repetitionsupper = MEMORIZATION_VERSE_LIFE_UPPER;
    }

    $userversesrs = get_recordset_select('memorization_verse', "userid = '{$userid}' AND repetitions >= '{$repetitionslower}' AND repetitions < '{$repetitionsupper}'", 'id ASC');

    $tablecount = new stdClass();
    $tablecount->day   = get_record_select('memorization_verse', "userid = '{$userid}' AND repetitions >= '".MEMORIZATION_VERSE_DAY_LOWER."'   AND repetitions < '".MEMORIZATION_VERSE_DAY_UPPER."'",   'COUNT(*) as total');
    $tablecount->week  = get_record_select('memorization_verse', "userid = '{$userid}' AND repetitions >= '".MEMORIZATION_VERSE_WEEK_LOWER."'  AND repetitions < '".MEMORIZATION_VERSE_WEEK_UPPER."'",  'COUNT(*) as total');
    $tablecount->month = get_record_select('memorization_verse', "userid = '{$userid}' AND repetitions >= '".MEMORIZATION_VERSE_MONTH_LOWER."' AND repetitions < '".MEMORIZATION_VERSE_MONTH_UPPER."'", 'COUNT(*) as total');
    $tablecount->life  = get_record_select('memorization_verse', "userid = '{$userid}' AND repetitions >= '".MEMORIZATION_VERSE_LIFE_LOWER."'  AND repetitions < '".MEMORIZATION_VERSE_LIFE_UPPER."'",  'COUNT(*) as total');

    if ($userversesrs === false) {
        error('Problem with database setup, please contact the system administrator');
    }

    /// Prints out tabs
    $page = new moodle_url('view.php', array('id' => required_param('id', PARAM_INT), 'userid' => $userid));

    $toprow = array();
    $toprow[] = new tabobject('day', $page->out(false, array('currenttab' => 'day')),
                get_string('daytab','memorization') . " ( {$tablecount->day->total} )");

    $toprow[] = new tabobject('week', $page->out(false, array('currenttab' => 'week')),
                get_string('weektab','memorization') . " ( {$tablecount->week->total} )");

    $toprow[] = new tabobject('month', $page->out(false, array('currenttab' => 'month')),
                get_string('monthtab','memorization') . " ( {$tablecount->month->total} )");

    $toprow[] = new tabobject('life', $page->out(false, array('currenttab' => 'life')),
                get_string('lifetab','memorization') . " ( {$tablecount->life->total} )");

    print_tabs(array($toprow), $currenttab);

    // make a table for checkboxes
    $table = new stdClass();

    $table->head = array(get_string('verse', 'memorization'),
                         get_string('tablehead:/'.$currenttab, 'memorization'),
                         get_string('progress', 'memorization'),
                         get_string('moveverseto', 'memorization'));

    $table->align = array('left', 'center', 'center', 'center');
    $table->class = 'generaltable memorization memorization-'.$currenttab;

    $table->data = array();
    $table->rowclass = array();

    $biblebooks = biblebooks_array();
    while (($verse = rs_fetch_next_record($userversesrs)) !== false) {
        $row = array();

        // add the verse reference
        $versecell = '<span class="verse-cell"><a class="thickbox" href="ajaxversedisplay.php?verseid='.$verse->id.'&userid='.$userid.'&sesskey='.sesskey().'"> <img class="hint-icon" src="'.$CFG->wwwroot.'/mod/memorization/pix/bulb.png"/></a>';
        if ($verse->startbookid == $verse->endbookid && $verse->startchapter == $verse->endchapter) {
            $versecell .= "{$biblebooks[$verse->startbookid]} <span class=\"break-scripturizer\">{$verse->startchapter}:{$verse->startverse}-{$verse->endverse}</span>";
        } elseif ($verse->startbookid == $verse->endbookid) {
            $versecell .= "{$biblebooks[$verse->startbookid]} <span class=\"break-scripturizer\">{$verse->startchapter}:{$verse->startverse} - {$verse->endchapter}:{$verse->endverse}</span>";
        } else {
            $versecell .= "{$biblebooks[$verse->startbookid]} <span class=\"break-scripturizer\">{$verse->startchapter}:{$verse->startverse} - {$biblebooks[$verse->startbookid]} {$verse->endchapter}:{$verse->endverse}</span>";
        }
        $versecell .= '</span>';

        $row[] = $versecell;

        // add the checkbox progress
        $checkboxcell = '<div class="boxes">';
        $counter = 0;
        $persistentclass = '';
        for ($x = $repetitionslower+1; $x != $repetitionsupper+1; $x++) {
            $counter++;
            $class = '';

            // add classes for spacing issues
            if ($currenttab == 'day' || $currenttab == 'year') {
                $class .= $counter % 7 == 0 ? 'seventh-box' : '';
                $class .= $counter % 14 == 0 ? ' second-set ' : '';
                $class .= $counter % 7 == 0 && $counter %14 != 0 ? ' first-set ' : '';
            } elseif ($currenttab == 'week' || $currenttab == 'life') {
                $class = $counter % 4 == 0 ? ' fourth-box ' : '';
            }

            $persistentclass .= " greater-than-{$verse->id}-{$x} ";
            // we need to get each greater than box for jquery stuff so we know what it is less than
            for ($y = $repetitionsupper+1; $y != $x; $y--) {
                $class .= " less-than-{$verse->id}-{$y} ";
            }
            $class .= $persistentclass;

            $checked = $x <= $verse->repetitions ? ' checked="checked" ' : '';

            // add 7 and 14 boxes wrappers
            $checkboxcell .= $counter % 14 == 1 ? '<div class="box-14-wrapper">' : '';
            $checkboxcell .= $counter % 7 == 1 ? '<div class="box-7-wrapper">' : '';

            $checkboxcell .= '<input '.$checked.' type="checkbox" class="'.$class.' repetition-box" id="'.$verse->id.'-'.$x.'" value="'.$x.'">';

            // end 14 box wrapper
            $checkboxcell .= $counter % 7 == 0 ? '</div>' : '';
            $checkboxcell .= $counter % 14 == 0 ? '</div>' : '';
        }

        // catch any that didn't end with a end span
        $checkboxcell .= $counter % 7 != 0 ? '</div>' : '';
        $checkboxcell .= $counter  % 14 != 0 ? '</div>' : '';
        $checkboxcell .= '</div><div class="moving-verse" style="display: none;">'.get_string('moving-verse-'.$currenttab, 'memorization').'</div>';

        $row[] = $checkboxcell;

        // add the progress bar
        $progress = $verse->repetitions / MEMORIZATION_COMPLETE * 100;
        $verseidprogressmap[$verse->id] = $progress;
        $row[] = "<div id=\"progressbar-{$verse->id}\"></div>";         // don't forget this is initialized later

        // move verse links
        $movecell = '<span class="move-verse">';
        $movecell .= $currenttab != 'day'   ? '<span class="day"  ><a href="'.$page->out(false, array('currenttab' => 'day'))  .'" class="move-verse-link" id="move-verse-'.$verse->id.'-'.MEMORIZATION_VERSE_DAY_LOWER.'">'.get_string('moveday', 'memorization').'</a></span>'      : '';
        $movecell .= $currenttab != 'week'  ? '<span class="week" ><a href="'.$page->out(false, array('currenttab' => 'week')) .'" class="move-verse-link" id="move-verse-'.$verse->id.'-'.MEMORIZATION_VERSE_WEEK_LOWER.'">'.get_string('moveweek', 'memorization').'</a></span>'    : '';
        $movecell .= $currenttab != 'month' ? '<span class="month"><a href="'.$page->out(false, array('currenttab' => 'month')).'" class="move-verse-link" id="move-verse-'.$verse->id.'-'.MEMORIZATION_VERSE_MONTH_LOWER.'">'.get_string('movemonth', 'memorization').'</a></span>'  : '';
        $movecell .= $currenttab != 'year'  ? '<span class="life" ><a href="'.$page->out(false, array('currenttab' => 'life')) .'" class="move-verse-link" id="move-verse-'.$verse->id.'-'.MEMORIZATION_VERSE_LIFE_LOWER.'">'.get_string('movelife', 'memorization').'</a></span>'    : '';
        $deleteurl = new moodle_url('deleteverse.php', array('userid' => $userid, 'verseid' => $verse->id, 'currenttab' => $currenttab, 'modid' => required_param('id', PARAM_INT)));
        $movecell .= "<span class=\"delete\"><a href=\"{$deleteurl->out_action()}\"><img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" /></a></span>";
        $movecell .= '</span>';

        $row[] = $movecell;

        $table->data[] = $row;
        $table->rowclass[] = 'verse-'.$verse->id;
    }


    print_table($table);


    if ($currenttab == 'day') {
        _memorization_print_new_verse_box();
    }

    echo '<script type="text/javascript">
            jQuery(document).ready(function () {
                function parse_verse_id_from_repetion_box(elementid) {
                    return elementid.replace(/\-[0-9]*$/, "");
                }

                function parse_verse_id_from_move_to_link(elementid) {
                    firststrip = elementid.replace(/move\-verse\-/, "");
                    return firststrip.replace(/\-[0-9]*$/, "");
                }

                function parse_repetition_value_from_move_to_link(elementid) {
                    return elementid.replace(/.*\-/, "");
                }

                function update_verse_in_database_and_progressbar(verseid, newrepetition) {
                    jQuery.ajax({
                       type: "POST",
                       url: "updateverserepetition.php",
                       data: "sesskey='.sesskey().'&userid='.$userid.'&verseid="+verseid+"&newrepetition="+newrepetition,
                       success: function(msg){
                         jQuery("#progressbar-"+verseid).progressbar({ value : ((newrepetition / '.MEMORIZATION_COMPLETE.') * 100) });
                       }
                     });
                }';

                // this is for the last checkbox click
                if (!empty($verseidprogressmap)) {
                    foreach ($verseidprogressmap as $verseid => $progress) {
                        echo 'jQuery("#progressbar-'.$verseid.'").progressbar({ value:'.($progress+1).' });

                        // this will fade the verse onto the next page when last checkbox clicked
                        jQuery("#'.$verseid.'-'.$repetitionsupper.'").change(function () {
                            if (jQuery(this).is(":checked") && '.$repetitionsupper.' <= '.MEMORIZATION_COMPLETE.') {
                                if ('.$repetitionsupper.' == '.MEMORIZATION_VERSE_DAY_UPPER.') {
                                    jQuery(".tabtree .selected a").html(
                                        jQuery(".tabtree .selected a").html().replace(/([0-9])+?/, "'.($tablecount->day->total - 1).'")
                                    );
                                    jQuery(".tabtree .selected + li a").html(
                                        jQuery(".tabtree .selected + li a").html().replace(/([0-9])+?/, "'.($tablecount->week->total + 1).'")
                                    );
                                } else if ('.$repetitionsupper.' == '.MEMORIZATION_VERSE_WEEK_UPPER.') {
                                    jQuery(".tabtree .selected a").html(
                                        jQuery(".tabtree .selected a").html().replace(/([0-9])+?/, "'.($tablecount->week->total - 1).'")
                                    );
                                    jQuery(".tabtree .selected + li a").html(
                                        jQuery(".tabtree .selected + li a").html().replace(/([0-9])+?/, "'.($tablecount->month->total + 1).'")
                                    );
                                } else if ('.$repetitionsupper.' == '.MEMORIZATION_VERSE_MONTH_UPPER.') {
                                    jQuery(".tabtree .selected a").html(
                                        jQuery(".tabtree .selected a").html().replace(/([0-9])+?/, "'.($tablecount->month->total - 1).'")
                                    );
                                    jQuery(".tabtree .selected + li a").html(
                                        jQuery(".tabtree .selected + li a").html().replace(/([0-9])+?/, "'.($tablecount->life->total + 1).'")
                                    );
                                }

                                jQuery("tr.verse-'.$verseid.' td.c1 div.boxes").fadeOut(1500, function () {
                                    jQuery("tr.verse-'.$verseid.' td.c1 div.moving-verse").fadeIn(1500, function () {
                                        jQuery("tr.verse-'.$verseid.'").fadeOut(500);
                                    });
                                });
                            }
                        });';

                    }
                }

                // this will move the verse between day / week / month
    echo        'jQuery(".move-verse-link").click(function () {
                    verseid = parse_verse_id_from_move_to_link(jQuery(this).attr("id"));
                    newrepvalue = parse_repetition_value_from_move_to_link(jQuery(this).attr("id"));
                    update_verse_in_database_and_progressbar(verseid, newrepvalue);
                });';


    echo        'jQuery(".repetition-box").change(function () {
                    verseid = parse_verse_id_from_repetion_box(jQuery(this).attr("id"));

                    if (jQuery(this).is(":checked")) {
                        newrepetition = jQuery(this).val() - 0;

                        jQuery(".less-than-"+$(this).attr("id")).attr("checked", true);
                        update_verse_in_database_and_progressbar(verseid, newrepetition);

                    } else {
                        jQuery(".greater-than-"+$(this).attr("id")).removeAttr("checked");

                        newrepetition = jQuery(this).val() - 1;

                        update_verse_in_database_and_progressbar(verseid, newrepetition);
                    }
                });
            });
          </script>';
    return true;
}

function _memorization_print_new_verse_box() {
    print_box_start('add-verse-box generalbox box');
    print_heading(get_string('newverse', 'memorization'));

    $biblebooks = biblebooks_array();

    // create the book selector
    $biblebookoptions = '';
    foreach ($biblebooks as $booknumber => $bookofbible) {
        if ($booknumber == 0) {
            continue;
        }

        $biblebookoptions .= '<option value="'.$booknumber.'">'.$bookofbible.'</option>';
    }

    $startbookid = '<select name="startbookid">'.$biblebookoptions.'</select>';
    $endbookid = '<select name="endbookid">'.$biblebookoptions.'</select>';

    // create the chapter inputs
    $startchapter = '<input type="text" name="startchapter" size="5" />';
    $endchapter = '<input type="text" name="endchapter" size="5"/>';

    // create the verse inputs
    $startverse = '<input type="text" name="startverse" size="5"/>';
    $endverse = '<input type="text" name="endverse" size="5"/>';

    // create the version chooser
    $versions = get_records('memorization_version');

    if (!empty($versions)) {
        $versionselect = '<select name="versionid">';
        foreach ($versions as $versionid => $version) {
            $versionselect .= '<option value="'.$versionid.'">'.$version->value.'</option>';
        }
        $versionselect .= '</select>';
    }

    $currenturl = new moodle_url(qualified_me());

    echo '<form method="POST" action="addverse.php?'.$currenturl->get_query_string().'">
          <input type="hidden" name="sesskey" value="'.sesskey().'">
          <table>
            <tr>
              <td>'.get_string('fromverse', 'memorization').'</td>
              <td>'.$startbookid .' '. $startchapter. ':'. $startverse.'</td>
            </tr>

            <tr>
              <td>'.get_string('toverse', 'memorization').'</td>
              <td>'.$endbookid .' '. $endchapter. ':'. $endverse.'</td>
            </tr>

            <tr>
              <td>'.get_string('version', 'memorization'). '</td>
              <td>'.$versionselect.'</td>
            </tr>
          </table>
          <input type="submit">
          </form>';

    print_box_end();
}