<?php
    require_once('../../config.php');

    global $CFG, $USER;

    require_once($CFG->libdir.'/biblelib.php');

    $userid         = optional_param('userid', $USER->id, PARAM_INT);

    $startbookid        = required_param('startbookid', PARAM_INT);
    $startchapter       = required_param('startchapter', PARAM_INT);
    $startverse         = required_param('startverse', PARAM_INT);
    $endbookid          = required_param('endbookid', PARAM_INT);
    $endchapter         = required_param('endchapter', PARAM_INT);
    $endverse           = required_param('endverse', PARAM_INT);
    $versionid          = required_param('versionid', PARAM_INT);

    require_login();

    if (!confirm_sesskey()) {
        error('Your session key could not be verified. Please try again');
    }

    $currenturl = new moodle_url(qualified_me());

    $currenturl->params(array('startbookid' => $startbookid, 'startchapter' => $startchapter, 'startverse' => $startverse, 'endbookid' => $endbookid, 'endchapter' => $endchapter, 'endverse' => $endverse, 'versionid' => $versionid, 'sesskey' => sesskey()));

    $versetext = lookup_bible_verse($versionid, $startbookid, $startchapter, $startverse, $endbookid, $endchapter, $endverse);

    if (!optional_param('confirm', 0, PARAM_BOOL)) {
        $linkyes = $currenturl->out(false, array('confirm' => '1'));

        $linkno = $CFG->wwwroot.'/mod/memorization/view.php?'.$currenturl->get_query_string();

        $message = '<div class="confirm-verse">'.get_string('confirmverse', 'memorization').'</div>'.'<div class="verse">'.$versetext.'</div>';

        print_header_simple(get_string('notice'));

        notice_yesno($message, $linkyes, $linkno);

        print_footer();
        exit;
    }

    $versionname = get_field('memorization_version', 'name', 'id', $versionid);


    $verse = (object) array('userid'        => $userid,
                            'startbookid'   => $startbookid,
                            'startchapter'  => $startchapter,
                            'startverse'    => $startverse,
                            'endbookid'     => $endbookid,
                            'endchapter'    => $endchapter,
                            'endverse'      => $endverse,
                            'text'          => $versetext,
                            'versionid'     => $versionid,
                            'repetitions'   => 0);

    $verse = addslashes_recursive($verse);

    if (!insert_record('memorization_verse', $verse)) {
        print_header();
        error('There was an error adding your verse. Please try again');
    }

    redirect('view.php?'.$currenturl->get_query_string());