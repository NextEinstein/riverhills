<?php
/**
 * podcaster local repository
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 *
 */
class local_repository extends podcaster_repositoryimpl {

  function get_default_params () {
    return 'http_host=moodle.host.org
http_basepath=/public_podcasts
http_protocol=http://
basepath=/var/www/public_podcasts
htuser=
htpasswd=';
  } // get_default_params ()

  function local_path ($path, $absolute = true) {
    global $CFG;
    $this->init ($path);
    $basepath = $this->get_param('basepath');
    return ($absolute ? $basepath.'/' : '').str_replace ($this->prefix.'://', '', $path);
  } // local_path ()


  function get_url ($path, $course = NULL, $withcredentials = false) {
    global $CFG;
    // if ($course == NULL) {
      // error ('Invalid course ID');
    // }
    $credentials = '';
    $path        = $this->local_path ($path, false);
    if ($withcredentials) {
      if ($this->get_param ('htuser')) {
        $credentials = $this->get_param ('htuser');
        if ($this->get_param ('htpasswd')) {
          $credentials .= ':'.$this->get_param ('htpasswd');
        }
        $credentials  .= '@';
      }
    }
    $result = $this->get_param('http_protocol').$credentials.
              $this->get_param('http_host').
              $this->get_param('http_basepath').'/'.($course ? $course.'/' : '').$path;
    return $result;
  } // get_url ()

} // class local_repository
?>
