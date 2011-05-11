<?php
    require_once($CFG->dirroot.'/lib/blocklib.php');
    
    if (! $site = get_site()) {
        redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
    }
    if (! $course = get_record('course', 'id', $cid)) {
        error('Course is misconfigured at line '.__LINE__.' of '.__FILE__);
    }
    
    //$messagepreface
    $debug = false;
    if ($debug) {
        echo '****** Written from line '.__LINE__.' of '.__FILE__.' ********<br />';
        echo '$cid = '.$cid.'<br />';
        echo '$bid = '.$bid.'<br />';
    }
    
    // definition of $messagehtml
    $a = $fromform->cf_sendername;
    $messagehtml = get_string('commentreceived', 'block_contact_form', $a);
    // <strong>Name Surame</strong> sent you a comment from
    // <strong>Nome Cognome</strong> ti ha inviato una comunicazione
    unset($a);
    $a->sitename = $site->shortname;
    if ($cid == 1) {
        $messagehtml .= get_string('fromhompageof', 'block_contact_form', $a).'<br />';
        // <strong>Name Surame</strong> sent you a comment from the <strong>home page</strong> of <strong>M19</strong>
        // <strong>Nome Cognome</strong> ti ha inviato una comunicazione dalla <strong>home page</strong> di <strong>M19</strong>
    } else {
        $a->coursename = $course->shortname;
        $messagehtml .= get_string('fromcourse', 'block_contact_form', $a).'<br />';
        // Name Surame sent you a comment from course <strong>xxx</strong> of <strong>M19</strong>
        // nome cognome ti ha inviato una comunicazione dal corso <strong>xxx</strong> di <strong>M19</strong>
    }
    $messagehtml .=  '<br />'.$fromform->cf_mailbody;
    $messagehtml =  stripslashes_safe($messagehtml);
    // end of definition of $messagehtml

    // definition of $messagetext
    $messagetext =  str_replace('<br />', "\n", $messagehtml);
    $messagetext =  strip_tags($messagetext);
    // end of definition of $messagetext
    
    if ($debug) {
        echo '****** Written from line '.__LINE__.' of '.__FILE__.' ********<br />';
        echo '$CFG->block_contact_form_subject_prefix = '.$CFG->block_contact_form_subject_prefix.'<br />';
        // [M19]
        echo '<hr />';
        echo '$CFG->block_contact_form_receipt = '.$CFG->block_contact_form_receipt.'<br />';
        echo '<hr />';

        echo '<div>$messagetext = </div>';
        echo '<textarea cols="50" rows="7">';
        echo $messagetext;
        echo '</textarea>';
        
        echo '<div>$messagetext = </div>';
        echo '<textarea cols="50" rows="7">';
        echo $messagehtml;
        echo '</textarea>';
        
        echo '<hr />';
        echo '<div>$messagetext = ';
        echo $messagetext;
        echo '</div>';
        echo '<div>$messagehtml = ';
        echo $messagehtml;
        echo '</div>';
        echo '<hr />';
        die;
    }
    
    //sender infos
    $from = new object;
    $from->firstname = $fromform->cf_sendername;
    $from->lastname = '';
    $from->email = $fromform->cf_senderemail;
    $from->maildisplay = true;
    $from->mailformat  = $fromform->cf_sendermailformat;
    
    //define the subject starting from the pre-defined prefix
    if ($cid == 1) {
        // as far as I understand, the next if is useless because
        // it was defined a default for $CFG->block_contact_form_subject_prefix
        if (!isset($CFG->block_contact_form_subject_prefix)) {
            $CFG->block_contact_form_subject_prefix = '['.strip_tags($site->shortname).'] ';
        } 
        $subject = $CFG->block_contact_form_subject_prefix.$fromform->cf_mailsubject;
    } else {        
        //set the subject to start with [shortname]
        $subject = '['.$course->shortname.'] '.$fromform->cf_mailsubject;
    }
    $subject = stripslashes_safe($subject);


if (!$debug) {
    //send emails
    if ($allhiddenrecipients) {
        foreach ($allhiddenrecipients as $thisrecipient) {
            $property = 'cf_teacher'.$thisrecipient->id;
            if ( isset($fromform->{$property}) ) {
                if ( email_to_user($thisrecipient, $from, $subject, $messagetext, $messagehtml) ) {
                    add_to_log($cid, 'Contact Form Block', 'send mail', '', "To:$thisrecipient->firstname $thisrecipient->lastname; From:$from->firstname; Subject:$subject");
                } else {
                    echo 'An error was encountered sending an email in sendmessage.php. It is likely that your email settings are not configured properly. The error reported was "'. $error .'"<br />';
                    add_to_log($cid, 'Contact Form Block', 'send mail failure', '', "To:$thisrecipient->firstname $thisrecipient->lastname; From:$from->firstname; Subject:$subject");
                }
            }
        }
    }
    
    if ($allstandardrecipients) {
        foreach ($allstandardrecipients as $thisrecipient) {
            $property = 'cf_teacher'.$thisrecipient->id;
            if ( isset($fromform->{$property}) ) {
                if ( email_to_user($thisrecipient, $from, $subject, $messagetext, $messagehtml) ) {
                    add_to_log($cid, 'Contact Form Block', 'send mail', '', "To:$thisrecipient->firstname $thisrecipient->lastname; From:$from->firstname; Subject:$subject");
                } else {
                    echo 'An error was encountered sending an email in sendmessage.php. It is likely that your email settings are not configured properly. The error reported was "'. $error .'"<br />';
                    add_to_log($cid, 'Contact Form Block', 'send mail failure', '', "To:$thisrecipient->firstname $thisrecipient->lastname; From:$from->firstname; Subject:$subject");
                }
            }
        }
    }
    
    if ( $rcp == 1 ) {
        $subject = get_string('receipt', 'block_contact_form').$subject;
        if ( email_to_user($from, $from, $subject, $messagetext, $messagehtml) ) {
            add_to_log($cid, 'Contact Form Block', 'send mail', '', "To:$from->firstname; From:$from->firstname; Subject:$subject");
        } else {
            echo 'An error was encountered trying to send email in sendmessage.php. It is likely that your email settings are not configured properly. The error reported was "'. $error .'"<br />';
            add_to_log($cid, 'Contact Form Block', 'send mail failure', '', "To:$from->firstname; From:$from->firstname; Subject:$subject");
        }
        add_to_log($cid, 'Contact Form Block', 'send mail', '', "To:$from->firstname; From:$from->firstname; Subject:$subject");
    }
} // end of: if (!$debug)

?>