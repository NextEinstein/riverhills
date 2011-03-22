<?php

include_once('constants.php');

$yearsecs = 60 * 60 * 24 * 365;
$expires = $yearsecs - 1000;

$ifModifiedSince = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : 0;

if ($ifModifiedSince && strtotime($ifModifiedSince) < time()) {
    header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified');
    die; // stop processing
}

header("Cache-Control: max-age={$expires}, public");
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
header("Last-Modified: " . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
header("content-type: application/x-javascript");

// If we made it this far it is because the user doesn't have the cached file so get it

// first check the temp folder (this should always be here but use a fallback method in case
global $nosetup;

$nosetup = true;        // this will do a very simple setup so we have the datadir

include('../../config.php');

$version = $_GET['version'];

if (file_exists($CFG->dataroot.'/temp/'.MO_CACHED_FILES_BASE_DIR.'/'.$version.'.css')) {
    readfile($CFG->dataroot.'/temp/'.MO_CACHED_FILES_BASE_DIR.'/'.$version.'.css');
    exit;
}

$nosetup = false;
include('../../config.php');

// Since for some reason we didn't have the cached file we need to generate it using the same
// function that the cron uses to generate it
define('MO_FORCE_JAVASCRIPT_MOD', true);

$justecho = true;
moodle_optimization_run_javascript_mod_cron('nothing', $justecho);

