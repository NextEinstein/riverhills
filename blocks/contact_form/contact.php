<?php // $Id: contact.php,v 1.1.2.7 2009/12/10 17:00:21 kordan Exp $
    global $CFG, $USER;

    require_once('../../config.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once('contact_form.php');
    require_once($CFG->dirroot.'/blocks/contact_form/lib.php');

    $cid = optional_param('cid', 0, PARAM_INT); // course ID
    $bid = optional_param('bid', 0, PARAM_INT); // block  ID
    $rcp = optional_param('rcp', 0, PARAM_INT); // was receipt requested?
    
    // if you are reloading the page without resending right parameters,
    // stop here your work and redirect to the home page.
    if ($cid == 0)
        redirect($CFG->wwwroot.'/index.php');

    $debug = false;
    if ($debug) {
        echo '****** Written from line '.__LINE__.' of '.__FILE__.' ********<br />';
        echo 'course ID: $cid = '.$cid.'<br />';
        echo 'block  ID: $bid = '.$bid.'<br />';
        echo 'was receipt requested? $rcp = '.$rcp.'<br />';
    }
    $strcontact_form = get_string('contactform_name','block_contact_form');

    $navlinks = array();
    $navlinks[] = array('name' => $strcontact_form, 'link' => "", 'type' => 'block');

    // $COURSE is useless here but is needed if category is shown in the navigation menu. See MDL-
    // $COURSE is used by build_navigation.
    $COURSE = get_record('course', 'id', $cid);
    $navigation = build_navigation($navlinks);
    print_header_simple($strcontact_form, "", $navigation, "", "", true, "", "");

    //----------------------------------------------------------------------------

    $allhiddenrecipients = getallhiddenrecipients($cid,$bid);
    $allstandardrecipients = getallstandardrecipients($bid);

    $mform =& new block_contact_form($CFG->wwwroot.'/blocks/contact_form/contact.php');
    if ($mform->is_cancelled()) {
        // submission was canceled.
        if ($cid == 1) {
            redirect($CFG->wwwroot.'/index.php',get_string('usercanceled','block_contact_form'));
        } else {
            redirect($CFG->wwwroot.'/course/view.php?id='.$cid,get_string('usercanceled','block_contact_form'));
        }
    } else if ($fromform = $mform->get_data()) {
        // form was successfully submitted. Now send and redirect.
        include_once('sendmessage.php');

        if ($cid == 1) {
            redirect($CFG->wwwroot.'/index.php',get_string('messagesent','block_contact_form'));
        } else {
            redirect($CFG->wwwroot.'/course/view.php?id='.$cid,get_string('messagesent','block_contact_form'));
        }
    } else {
        //this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
        //or on the first display of the form.
        //put data you want to fill out in the form into array $toform here then :
        echo '<br /><div class="box generalbox boxaligncenter boxwidthnormal">';
        print_string('welcome_info', 'block_contact_form');
        echo '</div>';
        $mform->display();
    }
/// Finish the page
    print_footer();
?>