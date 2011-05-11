<?php
      // Based on:
      // 
      // $Id: file.php,v 1.44.2.2 2007/09/25 19:09:08 skodak Exp $
      // This script fetches files from the dataroot directory
      // Syntax:      file.php/courseid/dir/dir/dir/filename.ext
      //              file.php/courseid/dir/dir/dir/filename.ext?forcedownload=1 (download instead of inline)
      //              file.php/courseid/dir (returns index.html from dir)
      // Workaround:  file.php?file=/courseid/dir/dir/dir/filename.ext
      // Test:        file.php/testslasharguments

      // added one line to support http basic authentication



    require_once('../../../../config.php');
    require_once($CFG->dirroot.'/lib/filelib.php');
    require_once($CFG->dirroot.'/mod/podcaster/repository/moodle/basic.php');

    if (!isset($CFG->filelifetime)) {
        $lifetime = 86400;     // Seconds for files to remain in caches
    } else {
        $lifetime = $CFG->filelifetime;
    }

    // disable moodle specific debug messages
    disable_debugging();

    $relativepath = get_file_argument('file.php');
    $forcedownload = optional_param('forcedownload', 0, PARAM_BOOL);
    
    // relative path must start with '/', because of backup/restore!!!
    if (!$relativepath) {
        error('No valid arguments supplied or incorrect server configuration');
    } else if ($relativepath{0} != '/') {
        error('No valid arguments supplied, path does not start with slash!');
    }

    $pathname = $CFG->dataroot.$relativepath;

    // extract relative path components
    $args = explode('/', trim($relativepath, '/'));
    if (count($args) == 0) { // always at least courseid, may search for index.html in course root
        error('No valid arguments supplied');
    }
  
    // security: limit access to existing course subdirectories
    if (($args[0]!='blog') and (!$course = get_record_sql("SELECT * FROM {$CFG->prefix}course WHERE id='".(int)$args[0]."'"))) {
        error('Invalid course ID');
    }

    // security: prevent access to "000" or "1 something" directories
    // hack for blogs, needs proper security check too
    if (($args[0] != 'blog') and ($args[0] != $course->id)) {
        error('Invalid course ID');
    }
# HU, CS Repository plugin support
    if ($course->id != SITEID)
      http_basic_login ();

    // security: login to course if necessary
    if ($args[0] == 'blog') {
        if (empty($CFG->bloglevel)) {
            error('Blogging is disabled!');
        } else if ($CFG->bloglevel < BLOG_GLOBAL_LEVEL) {
            require_login();
        } else if ($CFG->forcelogin) {
            require_login();
        }
    } else if ($course->id != SITEID) {
        require_login($course->id);
    } else if ($CFG->forcelogin) {
        if (!empty($CFG->sitepolicy)
            and ($CFG->sitepolicy == $CFG->wwwroot.'/file.php'.$relativepath
                 or $CFG->sitepolicy == $CFG->wwwroot.'/file.php?file='.$relativepath)) {
            //do not require login for policy file
        } else {
            require_login();
        }
    }

    // security: only editing teachers can access backups
    if ((count($args) >= 2) and (strtolower($args[1]) == 'backupdata')) {
        if (!has_capability('moodle/site:backup', get_context_instance(CONTEXT_COURSE, $course->id))) {
            error('Access not allowed');
        } else {
            $lifetime = 0; //disable browser caching for backups 
        }
    }

    if (is_dir($pathname)) {
        if (file_exists($pathname.'/index.html')) {
            $pathname = rtrim($pathname, '/').'/index.html';
            $args[] = 'index.html';
        } else if (file_exists($pathname.'/index.htm')) {
            $pathname = rtrim($pathname, '/').'/index.htm';
            $args[] = 'index.htm';
        } else if (file_exists($pathname.'/Default.htm')) {
            $pathname = rtrim($pathname, '/').'/Default.htm';
            $args[] = 'Default.htm';
        } else {
            // security: do not return directory node!
            not_found($course->id);
        }
    }

    // security: teachers can view all assignments, students only their own
    if ((count($args) >= 3)
        and (strtolower($args[1]) == 'moddata')
        and (strtolower($args[2]) == 'assignment')) {

        $lifetime = 0;  // do not cache assignments, students may reupload them
        if (!has_capability('mod/assignment:grade', get_context_instance(CONTEXT_COURSE, $course->id))
          and $args[4] != $USER->id) {
           error('Access not allowed');
        }
    }

    // security: force download of all attachments submitted by students
    if ((count($args) >= 3)
        and (strtolower($args[1]) == 'moddata')
        and ((strtolower($args[2]) == 'forum')
            or (strtolower($args[2]) == 'assignment')
            or (strtolower($args[2]) == 'data')
            or (strtolower($args[2]) == 'glossary')
            or (strtolower($args[2]) == 'wiki')
            or (strtolower($args[2]) == 'exercise')
            or (strtolower($args[2]) == 'workshop')
            )) {
        $forcedownload  = 1; // force download of all attachments
    }
    if ($args[0] == 'blog') {
        $forcedownload  = 1; // force download of all attachments
    }    

    // security: some protection of hidden resource files
    // warning: it may break backwards compatibility
    if ((!empty($CFG->preventaccesstohiddenfiles)) 
        and (count($args) >= 2)
        and (!(strtolower($args[1]) == 'moddata' and strtolower($args[2]) != 'resource')) // do not block files from other modules!
        and (!has_capability('moodle/course:viewhiddenactivities', get_context_instance(CONTEXT_COURSE, $course->id)))) {

        $rargs = $args;
        array_shift($rargs);
        $reference = implode('/', $rargs);

        $sql = "SELECT COUNT(r.id) " .
                 "FROM {$CFG->prefix}resource r, " .
                      "{$CFG->prefix}course_modules cm, " .
                      "{$CFG->prefix}modules m " .
                 "WHERE r.course    = '{$course->id}' " .
                   "AND m.name      = 'resource' " .
                   "AND cm.module   = m.id " .
                   "AND cm.instance = r.id " .
                   "AND cm.visible  = 0 " .
                   "AND r.type      = 'file' " .
                   "AND r.reference = '{$reference}'";
        if (count_records_sql($sql)) {
           error('Access not allowed');
        }
    }

    // check that file exists
    if (!file_exists($pathname)) {
        not_found($course->id);
    }

    // ========================================
    // finally send the file
    // ========================================
    session_write_close(); // unlock session during fileserving
    $filename = $args[count($args)-1];
    send_file($pathname, $filename, $lifetime, $CFG->filteruploadedfiles, false, $forcedownload);

    function not_found($courseid) {
        global $CFG;
        header('HTTP/1.0 404 not found');
        error(get_string('filenotfound', 'error'), $CFG->wwwroot.'/course/view.php?id='.$courseid); //this is not displayed on IIS??
    }
?>
