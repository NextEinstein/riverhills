<?PHP // $Id: index.php,v 1.4.2.1 2009/02/23 19:22:40 dlnsk Exp $

/// This page lists all the instances of memorization in a particular course

    require_once('../../config.php');

    $id = required_param('id', PARAM_INT);                 // Course id

    if (! $course = get_record('course', 'id', $id)) {
        error('Course ID is incorrect');
    }

    if ($mem = array_pop(get_all_instances_in_course('memorization', $course, NULL, true))) {
        redirect("view.php?id=$mem->coursemodule", 0);
    } else {
        print_error('notfound', 'memorization');
    }