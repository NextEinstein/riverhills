<?php
require_once ('../../../config.php');
// check if version is supported
$filesversion = NULL;
if ($CFG->version >= 2007021500 && $CFG->version < 2007021600) {
  // currently the only supported version
  $filesversion = '1.8';
}
else if ($CFG->version >= 2007101500 && $CFG->version < 2007101600) {
  $filesversion = '1.9';
}
if ($filesversion == NULL) {
  error ('Incompatible Moodle version');
}
require_once($CFG->dirroot.'/mod/podcaster/locallib.php');
$repository = podcaster_repository::create_repository (optional_param ('repository', '', PARAM_RAW));
if (!$repository) {
  error ('Repository could not be setup');
}
include_once ($CFG->dirroot.'/mod/podcaster/files/versions/'.$filesversion.'/index.php');
?>
