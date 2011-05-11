<?php
    function recaptcha_enabled() {
        global $CFG;
        return !empty($CFG->recaptchapublickey) && !empty($CFG->recaptchaprivatekey);
    }

    function getallstandardrecipients($bid) {
        $debug = false;

        $blockcontext = get_context_instance(CONTEXT_BLOCK, $bid);
        $contactpersons = get_users_by_capability($blockcontext, 'block/contact_form:contactperson', 'u.id, u.firstname, u.lastname, u.email, u.mailformat', 'u.lastname ASC','','','','',false);
        if ($debug) {
            echo '****** Written from line '.__LINE__.' of '.__FILE__.' ********<br />';
            echo '<hr />';
            echo 'contactperson (block/contact_form:contactperson)<br />';
            print_object($contactpersons);
        }
    
        return $contactpersons;
    }
    
    function getallhiddenrecipients($cid,$bid) {
        global $sid;
        
        $debug = false;

        if ($cid == 1) {
            $blockcontext = get_context_instance(CONTEXT_SYSTEM, $sid);
        } else {
            $blockcontext = get_context_instance(CONTEXT_BLOCK, $bid);
        }
        $hiddenrecipients = get_users_by_capability($blockcontext, 'block/contact_form:hiddenrecipient', 'u.id, u.firstname, u.lastname, u.email, u.mailformat', 'u.lastname ASC','','','','',false);
        if ($debug) {
            echo '****** Written from line '.__LINE__.' of '.__FILE__.' ********<br />';
            echo '<hr />';
            echo 'hiddencollector (block/contact_form:hiddenrecipients)<br />';
            print_object($hiddenrecipients);
        }
    
        return $hiddenrecipients;
    }
?>