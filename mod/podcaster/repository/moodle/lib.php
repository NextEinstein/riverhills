<?php
/**
 * podcaster moodle repository
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 *
 */
class moodle_repository extends podcaster_repositoryimpl {

  function get_default_params () {
    return 'usehttps=false';
  } // get_default_params ()

  function local_path ($path, $absolute = true) {
    global $CFG;
    $this->init ($path);
    return ($absolute ? $CFG->dataroot.'/' : '').str_replace ($this->prefix.'://', '', $path);
  } // local_path ()

  function get_url ($path, $course = NULL) {
    global $CFG;
    $path     = $this->local_path ($path, false);
    $usehttps = $this->get_param ('usehttps');

    $usehttps = ($usehttps && $usehttps != 'false' && $usehttps != '0');
    $wwwbase = $CFG->wwwroot;

    if ($usehttps) {
      $wwwbase = str_replace ('http://', 'https://', $wwwbase);
    }
    if ($CFG->slasharguments) {
        $ffurl = $wwwbase.str_replace('//', '/', '/mod/podcaster/repository/moodle/file.php/'.($course ? $course.'/' : '').$path);
    } else {
        $ffurl = $wwwbase.str_replace('//', '/', '/mod/podcaster/repository/moodle/file.php?file='.($course ? $course.'/' : '').$path);
    }
    return $ffurl;
  } // get_url ()
} // class moodle_repository
?>
