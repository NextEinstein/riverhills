<?php //$Id

/**
 * This file defines de main podcaster configuration form
 * It uses the standard core Moodle (>1.8) formslib. For
 * more info about them, please visit:
 * 
 * http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * The form must provide support for, at least these fields:
 *   - name: text element of 64cc max
 *
 * Also, it's usual to use these fields:
 *   - intro: one htmlarea element to describe the activity
 *            (will be showed in the list of activities of
 *             podcaster type (index.php) and in the header 
 *             of the podcaster main page (view.php).
 *   - introformat: The format used to write the contents
 *             of the intro field. It automatically defaults 
 *             to HTML when the htmleditor is used and can be
 *             manually selected if the htmleditor is not used
 *             (standard formats are: MOODLE, HTML, PLAIN, MARKDOWN)
 *             See lib/weblib.php Constants and the format_text()
 *             function for more info
 */
require_once ('moodleform_mod.php');
require_once ('locallib.php');

require_once ($CFG->dirroot.'/lib/formslib.php');
MoodleQuickForm::registerElementType('chooserepositoryfile', $CFG->dirroot.'/mod/podcaster/lib/chooserepositoryfile.php', 'podcaster_chooserepositoryfile');

class mod_podcaster_mod_form extends moodleform_mod {
  var $initial_data;

  function mod_podcaster_mod_form ($instance, $section, $cm) {
    if ($instance != '' && (!isset ($GLOBALS['form']) ||
                            !isset($GLOBALS['form']->id) ||
                            $GLOBALS['form']->id != $instance ))
    {
      $this->initial_data = get_record ('podcaster', 'id', $instance);
    }
    else if ($instance != '') {
      $this->initial_data = $GLOBALS['form'];
    }
    $this->moodleform_mod ($instance, $section, $cm);
  }

  function & parseTypeOptions () {
    static $typeOptions;

    if (!isset ($typeOptions)) {
      $info = optional_param ('type', '', PARAM_ALPHANUM);
      $typeOptions = array ();

      if ($info != '') {
        // TODO!
        if (($s = strpos ($info, 'repository')) !== false) {
          $v = '';
          $p = substr ($info, $s + 10);
          $i = -1;
          while (($c = substr ($p, (++$i), 1)) >= 'A' && $c <= 'Z') {
            $v .= $c;
          }
          if ($v != '') {
            $typeOptions['repository'] = podcaster_util::az2number ($v);
          }
        }

        if (($s = strpos ($info, 'format')) !== false) {
          $v = '';
          $p = substr ($info, $s + 6);
          $i = -1;
          while (($c = substr ($p, (++$i), 1)) >= 'A' && $c <= 'Z') {
            $v .= $c;
          }
          if ($v != '') {
            $typeOptions['format'] = strtolower ($v);
          }
        }

        if (($s = strpos ($info, 'license')) !== false) {
          $v = '';
          $p = substr ($info, $s + 7);
          $i = -1;
          while (($c = substr ($p, (++$i), 1)) >= 'A' && $c <= 'Z') {
            $v .= $c;
          }
          if ($v != '') {
            $typeOptions['license'] = podcaster_util::az2number ($v);
          }
        }
      }
    }
    return $typeOptions;
  }

	function definition() {
		global $CFG;
    $mform    =& $this->_form;
    
// do some tests to determine if we have to choose a
// podcast format, repository and / or license first
    if (isset($this->initial_data) && $this->initial_data->ismeta) {
      $formats       = explode(',', $CFG->podcaster_metaformats);
    }
    else {
      $formats       = explode(',', $CFG->podcaster_formats);
    }
    $repositories  = get_records ('podcaster_repository', 'enabled', '1');
    $licenses      = get_records ('podcaster_license');
    
    $format     = (isset ($this->initial_data) ? $this->initial_data->format : '');
    $repository = (isset ($this->initial_data) ? $this->initial_data->repository : 0);
    $license    = (isset ($this->initial_data) ? $this->initial_data->license : 0);
     
    if ($format == '') {
      $format = optional_param ('format',    '',   PARAM_TEXT);
      if ($format == '') {
        $typeOptions =& $this->parseTypeOptions ();
        if (array_key_exists ('format', $typeOptions)) {
          $format = $typeOptions['format'];
        }
      }
      if (count ($formats) == 1) {
        $format = $formats[0];
      }
    }


    if ($repository == 0) {
      $repository = optional_param ('repository', 0,   PARAM_INT);

      if ($repository == 0) {
        $typeOptions =& $this->parseTypeOptions ();
        if (array_key_exists ('repository', $typeOptions)) {
          $repository = $typeOptions['repository'];
        }
      }
      // there should be at least one
      if (count ($repositories) == 1) {
        list($repository,) = each ($repositories);
      }
    }


    if ($license == 0) {
      $license = optional_param ('license', 0, PARAM_INT);
      if ($license == 0) {
        $typeOptions =& $this->parseTypeOptions ();
        if (array_key_exists ('license', $typeOptions)) {
          $license = $typeOptions['license'];
        }
      }
      if (count ($licenses) == 1) {
        list ($license,) = each ($licenses);
      }
    }

    if (!isset ($this->initial_data)) {
      $this->initial_data = new object ();
      $this->initial_data->format     = $format;
      $this->initial_data->repository = $repository;
      $this->initial_data->license    = $license;
    }
    else {
      $updobj = new object ();
      $updobj->id = $this->initial_data->id;

      $dofix = false;

      if ($repository != 0 && (!isset ($this->initial_data->repository) || $this->initial_data->repository == 0)) {
        $this->initial_data->repository = $repository;
        $updobj->repository = $repository;
        $dofix = true;
      }
      if (($format != '' && $format != '') && (!isset ($this->initial_data->format) || $this->initial_data->format == '')) {
        $this->initial_data->format = $format;
        $updobj->format = $format;
        $dofix = true;
      }
      if ($license && (!isset ($this->initial_data->license) || $this->initial_data->license == 0)) {
        $this->initial_data->license = $license;
        $updobj->license = $license;
        $dofix = true;
      }
      update_record ('podcaster', $updobj);
    }

// 1st step: repository, format or license cannot be determined automatically
    if (!$repository || !$format || (!$license && $CFG->podcaster_copyright == 'menu')) {
      $mform->addElement('header', 'firststep',   get_string('create_step1',  'podcaster'));

      if (is_array($formats) && count ($formats) > 1) {
        $options = array ();
        $mform->addElement ('static', 'repository_hint', get_string ('format_hint', 'podcaster'));
        for ($i = 0, $c = count ($formats); $i < $c; ++$i) {
          $options[$formats[$i]] = podcaster_util::get_string ('format_'.$formats[$i].'_title');
        }
        $mform->addElement('select', 'format', get_string('format', 'podcaster'), $options);
      }
      elseif ($format) {
        $mform->addElement ('hidden', 'format', $format);
      }
      else {
        error ('Module is misconfigured: no formats available. Please check module configuration.');
      }
      if (is_array($repositories) && count ($repositories) > 1) {
        $mform->addElement ('static', 'repository_hint', get_string ('repository_hint', 'podcaster'));
        $options = array ();
        foreach ($repositories as $r) {
          $options[$r->id] = podcaster_util::get_string ('repository_'.$r->name.'_title');
        }
        $mform->addElement('select', 'repository', get_string('repository', 'podcaster'), $options);
      }
      elseif ($repository) {
        $mform->addElement ('hidden', 'repository', $repository);
      }
      else {
        error ('Module is misconfigured: no repositories available. Please check module configuration.');
      }

      if (is_array($licenses) && count ($licenses) > 1 && $CFG->podcaster_copyright == 'menu') {
        $mform->addElement ('static', 'license_hint', get_string ('license_hint', 'podcaster'));
        $options = array ();
        foreach ($licenses as $l) {
          $options[$l->id] = podcaster_util::get_string ('license_'.$l->name.'_title');
        }
        $mform->addElement('select', 'license', get_string('license', 'podcaster'), $options);
      }
      elseif ($license && $CFG->podcaster_copyright == 'menu') {
        $mform->addElement ('hidden', 'license', $licenses);
      }
      elseif ($CFG->podcaster_copyright == 'menu') {
        error ('Module is misconfigured: no licenses available. Please check module configuration.');
      }
      // add standard elements, common to all modules
      $this->standard_hidden_coursemodule_elements();
      $this->add_action_buttons (true);
      $mform->removeElement ('submitbutton2');
      $mform->addElement('hidden', 'reloadform', 'true');
    }
    else {
      // just submitted 1st step?
      if (optional_param ('reloadform', '', PARAM_TEXT) == 'true') {
        $querystr = '';
        $add      = optional_param ('add',    0, PARAM_TEXT);
        $update   = optional_param ('update', 0, PARAM_INT);

        if ($update != 0) {
          $querystr = '?update='.$update;
        }
        else {
          $querystr = '?add='.$add;
        }
        redirect ($CFG->wwwroot.'/course/modedit.php'.
                    $querystr.
                    '&course='.required_param('course', PARAM_INT).
                    '&section='.required_param('section', PARAM_INT).
                    '&format='.$format.
                    '&repository='.$repository.
                    '&license='.$license.
                    '&sesskey='.required_param('sesskey', PARAM_TEXT));
      }

      // show standard edit form
      $mform->addElement('header', 'general', get_string('general', 'form'));

      $mform->addElement('text', 'name', get_string('channelname', 'podcaster'), array('size'=>'48'));
      $mform->setType('name', PARAM_TEXT);
      $mform->addRule('name', null, 'required', null, 'client');

      $mform->addElement('htmleditor', 'intro', get_string('channeldescription', 'podcaster'));
      $mform->setType('intro', PARAM_RAW);
      $mform->addRule('intro', get_string('required'), 'required', null, 'client');
      $mform->setHelpButton('intro', array('writing',  'richtext'), false, 'editorhelpbutton');
      $mform->addElement('format', 'introformat', get_string('format'));

      // include format specific elements
      $xmlformat =& podcaster_format::create_format ($format);
      $xmlformat->define_channelform ($mform, $this->initial_data);

      $mform->addElement('hidden', 'format',     $format);
      $mform->setType('format', PARAM_TEXT);

      $mform->addElement('hidden', 'repository', $repository);
      $mform->setType('repository', PARAM_INT);

      $mform->addElement('hidden', 'license', $license);
      $mform->setType('license', PARAM_INT);

      // add standard elements, common to all modules
      $this->standard_coursemodule_elements();

      if ($CFG->podcaster_copyright == 'user') {
        $mform->addElement ('header',  'license_header', get_string ('license',  'podcaster'));
        $mform->addElement('textarea', 'copyright',     get_string('copyright', 'podcaster'), array('rows'=> '5', 'cols' => '40'));
      }
      else {
        $license = podcaster_license::create_license ($license, $repository);
        if ($license) {
          $mform->addElement ('header', 'license_header',  podcaster_util::get_string ('license_'.$license->name.'_title'));
          // 
          if (!isset ($this->initial_data->copyright)) {
            $copyright = podcaster_util::get_string ('license_'.$license->name.'_desc');
          }
          else {
            $copyright = $this->initial_data->copyright;
          }
          $mform->addElement ('static', 'license_desc',   '', $copyright);
          $mform->addElement ('hidden', 'copyright',  $copyright);

          if ($license->confirm) {
            $mform->addElement ('checkbox', 'confirm_license', get_string ('confirm_license', 'podcaster'));
            $mform->addRule ('confirm_license', get_string('must_confirm', 'podcaster'), 'required', null, 'client');
          }
        }
      }
      // add standard buttons, common to all modules
      $this->add_action_buttons(false);
    }
	} // definition ()

  function set_data ($data) {
    global $USER;
    // insert some default values
    if (!isset ($data->instance) || !$data->instance || !isset ($data->managingeditor)) {
      $data->showpreview    = 1;
      $data->managingeditor = $USER->email.' ('.$USER->firstname.' '.$USER->lastname.')';
    }
    return parent::set_data ($data);
  } // set_data ()

  function validation ($data, $files = null) {
    return parent::validation ($data, $files);
  } // validation ()
    
  function add_action_buttons($firstPage = false) {
    if (!$firstPage) {
      return parent::add_action_buttons ();
    }
    $submitlabel = get_string('continue');
    $mform =& $this->_form;
    
    //when two elements we need a group
    $buttonarray=array();
    $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
    $buttonarray[] = &$mform->createElement('cancel');

    $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    $mform->setType('buttonar', PARAM_RAW);
    $mform->closeHeaderBefore('buttonar');
  }

}
?>
