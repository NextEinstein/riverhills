<?php
global $CFG;
require_once($CFG->dirroot.'/lib/form/choosecoursefile.php');

class podcaster_chooserepositoryfile extends MoodleQuickForm_choosecoursefile {
  var $repository;

  function podcaster_chooserepositoryfile ($elementName = null, $elementLabel = null, $options = array(), $attributes = null) {
    $this->MoodleQuickForm_choosecoursefile ($elementName, $elementLabel, $options, $attributes);
    $this->repository = isset ($options['repository']) ? $options['repository'] : '';
  }

  function _createElements() {
    global $CFG, $COURSE;
    $this->_elements = array();

    $this->_elements[0] =& MoodleQuickForm::createElement('text',   'value', '', array('size'=>'24') );
    $this->_elements[1] =& MoodleQuickForm::createElement('button', 'popup', get_string('chooseafile', 'resource') .' ...');

    $button =& $this->_elements[1];
    if ($this->_options['courseid']!==null){
        $courseid=$this->_options['courseid'];
    } else {
        $courseid=$COURSE->id;
    }
    // first find out the text field id - this is a bit hacky, is there a better way?
    $choose = 'id_'.str_replace(array('[', ']'), array('_', ''), $this->getElementName(0));
    $url    = '/mod/podcaster/files/index.php?id='.$courseid.'&choose='.$choose.'&repository='.$this->repository;

    if ($this->_options['options'] == 'none') {
        $options = 'menubar=0,location=0,scrollbars,resizable,width='. $this->_options['width'] .',height='. $this->_options['height'];
    }else{
        $options = $this->_options['options'];
    }
    $fullscreen = 0;

    $buttonattributes = array('title'=>get_string("chooseafile", "resource"),
              'onclick'=>"return openpopup('$url', '".$button->getName()."', '$options', $fullscreen);");

    $button->updateAttributes($buttonattributes);
  } // _createElements ()

} // class podcaster_chooserepositoryfile
?>
