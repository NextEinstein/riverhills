<?php

define('MEMORIZATION_METHOD_VIEWS_FOLDER_PATH', 'methodsviews');

function memorization_method_id_filename_mapping_array() {
    $mapping = array();

    $mapping[0] = 'method7week';

    return $mapping;
}

function memorization_print_method_view($methodid, $userid) {
    $methodsmapping = memorization_method_id_filename_mapping_array();

    if (empty($methodsmapping[$methodid])) {
        debug('testing1');
        return false;
    }

    if (!include_once(MEMORIZATION_METHOD_VIEWS_FOLDER_PATH.'/'.$methodsmapping[$methodid].'.php')) {
        return false;
    }
    

    $methodprintfunction = 'memorization_print_view_'.$methodsmapping[$methodid];
    if (!function_exists($methodprintfunction)) {
        return false;
    }

    return $methodprintfunction($userid);
}
