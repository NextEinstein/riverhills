<?php
/**
 * form class for rss items 
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
			  Michael Ganzer <michael.ganzer@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 *
 **/
if (!defined('MOODLE_INTERNAL')) {
  die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
require_once ($CFG->dirroot.'/lib/formslib.php');
MoodleQuickForm::registerElementType('chooserepositoryfile', $CFG->dirroot.'/mod/podcaster/lib/chooserepositoryfile.php', 'podcaster_chooserepositoryfile');

class podcaster_itemform extends moodleform {
  var $item,
      $coursemodule;

  function podcaster_itemform (&$item, $course, $coursemodule) {
    global $CFG;
    $this->coursemodule = $coursemodule;
    $this->item         = $item;

    $this->moodleform ($CFG->wwwroot.'/mod/podcaster/edititem.php?id='.$this->coursemodule->id.'&item='.$this->item->id.'');
  } // podcaster_itemform ()

	function definition() {
		global $CFG;

		$mform    =& $this->_form;

    $mform->addElement ('header', 'general', get_string('general', 'form'));
    $mform->addElement('date_time_selector', 'scheduledtime', get_string('scheduledtime', 'podcaster'));

    $mform->addElement('text', 'title', get_string('itemtitle', 'podcaster'), array('size'=>'48'));
    $mform->setType('title', PARAM_TEXT);
    $mform->addRule('title', null, 'required', null, 'client');

    $mform->addElement('htmleditor', 'description', get_string('itemdescription', 'podcaster'));
    $mform->setType('description', PARAM_RAW);
    $mform->addRule('description', get_string('required'), 'required', null, 'client');
    $mform->setHelpButton('description', array('writing',  'richtext'), false, 'editorhelpbutton');

    $mform->addElement('format', 'introformat', get_string('format'));
    
    $format   = $this->item->channelObj->format;

    $xmlformat =& podcaster_format::create_format ($format);
    $xmlformat->define_itemform ($mform, $this->item);

    if ($CFG->podcaster_copyright == 'user') {
      $mform->addElement ('header', 'license_header', get_string ('license', 'podcaster'));
      $mform->addElement('textarea', 'copyright',   get_string('copyright',      'podcaster'), array('rows'=> '5', 'cols' => '40'));
    }
    else {
      $license = podcaster_license::create_license ($this->item->channelObj->license, $this->item->channelObj->repository);
      if ($license) {
        $mform->addElement ('header', 'license_header', get_string ('license', 'podcaster'));
        $mform->addElement ('static', 'license_name', podcaster_util::get_string ('license_'.$license->name.'_title')); 
        $mform->addElement ('static', 'license_desc',  '',podcaster_util::get_string ('license_'.$license->name.'_desc'));

        if ($license->confirm) {
          $mform->addElement ('checkbox', 'confirm_license', get_string ('confirm_license', 'podcaster'));
          $mform->addRule ('confirm_license', get_string('must_confirm', 'podcaster'), 'required', null, 'client');
        }
      }
    }

    $mform->addElement ('hidden', 'cm',       $this->coursemodule->id);
    $mform->addElement ('hidden', 'channel',  $this->item->channelObj->id);
    $mform->addElement ('hidden', 'id',       $this->item->id);
    $mform->setType ('item', PARAM_TEXT);

    $this->add_action_buttons();
	} // definition ()
  
  function set_data ($data) {
    global $USER;
    // insert some default values
    if (!isset ($data->id) || !$data->id) {
      $data->author = $USER->email.' ('.$USER->firstname.' '.$USER->lastname.')';
    }
    return parent::set_data ($data);
  } // set_data ()


} // class podcaster_itemform
?>

