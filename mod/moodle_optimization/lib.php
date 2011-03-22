<?php
global $CFG;

require_once($CFG->dirroot.'/mod/moodle_optimization/constants.php');

function moodle_optimization_cron() {
    global $CFG;

    define('MO_CRON', true);

    mtrace('Begin check for updating cached files.');

    $versiontable = get_records('mo_cache_versions', '', '', '', 'source, version');

    moodle_optimization_run_stylesheet_cron($versiontable);
    moodle_optimization_run_javascript_mod_cron($versiontable);
    

    mtrace('Finished generating cached files.');
}

function moodle_optimization_run_stylesheet_cron($versiontable) {
    global $CFG;

    $themes = get_list_of_plugins('theme');

    // make sure the file path exists
    if (!is_dir($CFG->dataroot.'/temp/'.MO_CACHED_FILES_BASE_DIR.'/')) {
        mkdir($CFG->dataroot.'/temp/'.MO_CACHED_FILES_BASE_DIR.'/', 0777, true);
    }

    foreach ($themes as $theme) {
        $source = "theme{$theme}";

        $THEME = false; // reset the theme so we can load the next one in

        include($CFG->themedir .'/'. $theme .'/config.php');  // Main config for current theme

        // Force language too if required
        if (!empty($THEME->langsheets)) {
            $params[] = 'lang='.current_language();
        }

        // Set up image paths
        if(isset($CFG->smartpix) && $CFG->smartpix==1) {
            if($CFG->slasharguments) {        // Use this method if possible for better caching
                $extra='';
            } else {
                $extra='?file=';
            }

            $CFG->pixpath = $CFG->dirroot. '/pix/smartpix.php'.$extra.'/'.$theme;
            $CFG->modpixpath = $CFG->dirroot .'/pix/smartpix.php'.$extra.'/'.$theme.'/mod';
        } else if (empty($THEME->custompix)) {    // Could be set in the above file
            $CFG->pixpath = $CFG->dirroot .'/pix';
            $CFG->modpixpath = $CFG->dirroot .'/mod';
        } else {
            $CFG->pixpath = $CFG->themedir .'/'. $theme .'/pix';
            $CFG->modpixpath = $CFG->themedir .'/'. $theme .'/pix/mod';
        }

        // Header and footer paths
        $CFG->header = $CFG->themedir .'/'. $theme .'/header.html';
        $CFG->footer = $CFG->themedir .'/'. $theme .'/footer.html';

        // Define stylesheet loading order
        $CFG->stylesheets = array();
        if ($theme != 'standard') {    /// The standard sheet is always loaded first
            $CFG->stylesheets[] = $CFG->themedir.'/standard/styles.php';
        }
        if (!empty($THEME->parent)) {  /// Parent stylesheets are loaded next
            $CFG->stylesheets[] = $CFG->themedir.'/'.$THEME->parent.'/styles.php';
        }
        $CFG->stylesheets[] = $CFG->themedir.'/'.$theme.'/styles.php';

        // RTL support - only for RTL languages, add RTL CSS
        if (get_string('thisdirection') == 'rtl') {
            $CFG->stylesheets[] = $CFG->themedir.'/standard/rtl.css';
            $CFG->stylesheets[] = $CFG->themedir.'/'.$theme.'/rtl.css';
        }

    /// now that we've calculated all the style sheets lets generate and cache it if necessary
        $generatedfile = '';
        foreach ($CFG->stylesheets as $stylesheet) {
            if (!file_exists($stylesheet)) {
                mtrace('One of the style sheets could not be found.');
                continue;
            }

            ob_start();
            echo "\n\n\n/* Including filesheet {$stylesheet} */\n\n";
            include($stylesheet);
            echo "\n /*done including stylesheet {$stylesheet} */\n\n";
            $generatedfile .= ob_get_contents();
            ob_end_clean();
        }

        $generatedfilehash = md5($generatedfile);

        if (!empty($versiontable[$source]) && $versiontable[$source]->version == $generatedfilehash && file_exists($CFG->dataroot .'/temp/'. MO_CACHED_FILES_BASE_DIR.'/'.$generatedfilehash.'.css')) {
            continue;   // we already have this one stored in cache
        }

        // we need to make sure that we save the file before we save the version in the db so
        // that user's don't get the version and it be not there
        $result = file_put_contents($CFG->dataroot .'/temp/'. MO_CACHED_FILES_BASE_DIR.'/'.$generatedfilehash.'.css', $generatedfile);

        if (!empty($versiontable[$source])) {
            $result = set_field('mo_cache_versions', 'version', $generatedfilehash, 'source', $source);
        } else {
            $result = insert_record('mo_cache_versions', (object) array('source' => $source, 'version' => $generatedfilehash));
        }

        if ($result === false) {
            mtrace('A problem was encountered while trying to save the version number for the following theme '.$theme);
        }
    }
}

function moodle_optimization_run_javascript_mod_cron($versiontable, $justecho=false) {
    global $CFG;

    $source = 'javascript-mod';

    $extrascripts = get_directory_list($CFG->dirroot.'/mod/moodle_optimization/javascript/');

    foreach ($extrascripts as $idx => $script) {
        $extrascripts[$idx] = $CFG->dirroot.'/mod/moodle_optimization/javascript/'.$script;
    }

    $extrascripts[] = $CFG->libdir.'/javascript-static.js';

    // add the flash checking scripts if applicable
    if (!empty($CFG->excludeoldflashclients)) {
        array_push($extrascripts, array($CFG->libdir.'/yui/yahoo/yahoo-min.js',
                                        $CFG->libdir.'/yui/event/event-min.js',
                                        $CFG->libdir.'/yui/connection/connection-min.js',
                                        $CFG->libdir.'/swfobject/swfobject.js'));

    }

/// build one file now
    ob_start();
    foreach ($extrascripts as $script) {
        if (!is_readable($script)) {
            echo "\n /* There was a problem finding the script {$script} */ \n\n";
            continue;
        }
        echo "\n /* printing script {$script} */\n\n";
        @readfile($script);
        echo "\n /* Finished printing script {$script} */\n\n";
    }

    if (!empty($CFG->excludeoldflashclients)) {
        // this is the last dynamic bit of the script checker
        echo
           "<script type=\"text/javascript\">\n".
           "  var flashversion = swfobject.getFlashPlayerVersion();\n".
           "  YAHOO.util.Connect.asyncRequest('GET','".$CFG->wwwroot."/login/environment.php?sesskey=".sesskey()."&amp;flashversion='+flashversion.major+'.'+flashversion.minor+'.'+flashversion.release);\n".
           "</script>";
    }

    // finally ask all the modules now that we've added all the things that they may depend on
    include($CFG->libdir.'/javascript-mod.php');

    $generatedfile = ob_get_contents();
    ob_end_clean();

    if ($justecho) {
        echo $generatedfile;
    }

/// Generate a version (hash) and store it
    $generatedfilehash = md5($generatedfile);

    if (!empty($versiontable[$source]) && $versiontable[$source] == $generatedfilehash && file_exists($CFG->dataroot .'/temp/'. MO_CACHED_FILES_BASE_DIR.'/'.$generatedfilehash.'.css')) {
        continue;   // we already have it stored in the cache
    }

    // we need to make sure that we save the file before we save the version in the db so
    // that user's don't get the version and it be not there

    $result = file_put_contents($CFG->dataroot .'/temp/'. MO_CACHED_FILES_BASE_DIR.'/'.$generatedfilehash.'.css', $generatedfile);

    if (!empty($versiontable[$source])) {
        $result = set_field('mo_cache_versions', 'version', $generatedfilehash, 'source', $source);
    } else {
        $result = insert_record('mo_cache_versions', (object) array('source' => $source, 'version' => $generatedfilehash));
    }

    if ($result === false) {
        mtrace('A problem was encountered while trying to save the version number for the following theme '.$theme);
    }
}