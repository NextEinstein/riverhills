<?php

define('MEMORIZATION_METHOD_VIEWS_FOLDER_PATH', 'methodsviews');

function memorization_method_id_filename_mapping_array() {
    $mapping = array();

    $mapping[0] = 'method7week';

    return $mapping;
}

function memorization_print_method_view($methodid, $userid, $cmid) {
    $methodsmapping = memorization_method_id_filename_mapping_array();

    if (empty($methodsmapping[$methodid])) {
        debug('testing1');
        return false;
    }

    if (!include_once(MEMORIZATION_METHOD_VIEWS_FOLDER_PATH.'/'.$methodsmapping[$methodid].'.php')) {
        return false;
    }
    

    $methodprintfunction = 'memorization_print_view_'.$methodsmapping[$methodid];

    if ($CFG->mobiledevice && function_exists($methodprintfunction.'_mobile')) {
        $methodprintfunction .= '_mobile';
    } else if (!function_exists($methodprintfunction)) {
        return false;
    }

    return $methodprintfunction($userid, $cmid);
}

function memorization_print_new_verse_box() {
    global $CFG, $USER;

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

        $lastversionid = get_field_sql("SELECT versionid FROM {$CFG->prefix}memorization_verse WHERE userid={$USER->id} ORDER BY id DESC");

        foreach ($versions as $versionid => $version) {
            $selected = $versionid == $lastversionid ? ' SELECTED="selected" ' : '';
            $versionselect .= '<option '.$selected.' value="'.$versionid.'">'.$version->value.'</option>';
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