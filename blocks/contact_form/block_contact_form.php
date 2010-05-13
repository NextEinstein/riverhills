<?php //$Id: block_contact_form.php,v 1.3.10.6 2010/02/16 21:01:41 mcampbell Exp $

class block_contact_form extends block_base {
    
    function init() {
        $this->title = get_string('contactform_name', 'block_contact_form');
        $this->content_type = BLOCK_TYPE_TEXT;
        $this->version = 2009101601;
    }

    function applicable_formats() {
        return array('course' => true, 'site' => true, 'my' => true);
    }

    function specialization() {
        global $CFG;

        // set the block title
        // if (!empty($this->config) && !empty($this->config->title) ) {
        if (empty($this->config->title) ) {
            $this->title = get_string('blockname', 'block_contact_form');
        } else {
            $this->title = $this->config->title;
        }
        
        if (empty($this->config->receipt)) {
            // Manca il settaggio locale allora comanda il settaggio globale/generale
            // Local setting is missing, global setting is leading
            if ($CFG->block_contact_form_receipt) {
                $this->receipt = $CFG->block_contact_form_receipt;
            } else {
                // qualora mancasse anche il settaggio generale, allora vale il default: '0'
                // if the global setting is missing too, then the default value is choosen
                $this->receipt = '0';
            }
        } else {
            if ($this->config->receipt == 2) {
                // il settaggio locale rimanda al settaggio globale
                // local setting states that the global setting is the correct one 
                if ($CFG->block_contact_form_receipt) {
                    $this->receipt = $CFG->block_contact_form_receipt;
                } else {
                    // qualora mancasse anche il settaggio generale, allora vale il default: '0'
                    // but the global setting was not defined so... use the default.
                    $this->receipt = '0';
                }
            } else {
                $this->receipt = $this->config->receipt;
            }
        }
    }

    function get_content() {
        global $USER, $CFG;

        require_once($CFG->dirroot.'/blocks/contact_form/lib.php');

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = ''; //empty to start, will be populated below
        $this->content->header = $this->title;
        
        if (empty($this->instance)) {
            // We're being asked for content without an associated instance
            return $this->content;
        }

        $cid = $this->instance->pageid; // course id
        $bid = $this->instance->id;     // block  id
        
        $allhiddenrecipients = getallhiddenrecipients($cid, $bid);
        $allstandardrecipients = getallstandardrecipients($bid);
        
        $debug = false;
        if ($debug) {
            echo '****** Written from line '.__LINE__.' of '.__FILE__.' ********<br />';
            echo '$cid = '.$cid.'<br />';
            echo '$bid = '.$bid.'<br />';
            echo '$rcp = '.$this->receipt.'<br />';

            echo 'count($allhiddenrecipients) = '.count($allhiddenrecipients).'<br />';
            echo 'count($allstandardrecipients) = '.count($allstandardrecipients).'<br />';
        }
        
        if ( !($allhiddenrecipients || $allstandardrecipients) ) {
            $this->content->text  = get_string('block_misconfigured', 'block_contact_form');
            $this->content->footer  = get_string('block_misconfigured_footer', 'block_contact_form');       
        } else {
            //print_object($this);
            
            //set displaytype and linktext to stored values if defined
            if (isset($CFG->block_contact_form_display_type)) {
                $displaytype = $CFG->block_contact_form_display_type;
            } else {
                $displaytype = 0;
            }

			if (empty($this->config->displaytype)) {
				$displaytype = 2;
			} else {
				$displaytype = $this->config->displaytype;
			}
			if (empty($this->config->linktext)) {
				if ($cid == 1) {
				    $linktext = get_string('contactus_site','block_contact_form');
				} else {
				    $linktext = get_string('contactus_course','block_contact_form');
				}
			} else {
				$linktext = $this->config->linktext;
			}
    
            //check our configuration setting to see what format we should display
            // 0 == display a form button
            // 1 == display a link
            if ($displaytype == 1){
                $this->content->text  = '<div style="text-align: center;">';
                $this->content->text .= '<a href="'. $CFG->wwwroot .'/blocks/contact_form/contact.php?cid='.$cid.'&amp;bid='.$bid.'&amp;rcp='.$this->receipt.'">';
                $this->content->text .=  $linktext;
                $this->content->text .= '</a>';
                $this->content->text .= '</div>';
            } else {
                $this->content->text  = '<form id="form" method="post" action="'.$CFG->wwwroot.'/blocks/contact_form/contact.php">';
                $this->content->text .= '<div style="text-align: center;">';
                $this->content->text .= '<input type="hidden" name="cid" value="'.$cid.'" />';
                $this->content->text .= '<input type="hidden" name="bid" value="'.$bid.'" />';
                $this->content->text .= '<input type="hidden" name="rcp" value="'.$this->receipt.'" />';
                $this->content->text .= '<input type="submit" name="Submit" value="'.$linktext.'" />';
                $this->content->text .= '</div>';
                $this->content->text .= '</form>';
            }
        }
        
        return $this->content;
    }

    function instance_allow_multiple() {
        return true;
    }
    
    function has_config() {
        return true;
    }
    
    function instance_allow_config() {
        return true;
    }
}
?>
