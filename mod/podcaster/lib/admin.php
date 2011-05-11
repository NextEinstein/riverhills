<?php
/**
 * podcaster admin lib
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 **/
if (!defined('MOODLE_INTERNAL')) {
  die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
define ('PODCASTER_NOERROR',                      0);
define ('PODCASTER_NONUNIQUE_LICENSE_NAME',      11);
define ('PODCASTER_NONUNIQUE_REPOSITORY_NAME',   12);
define ('PODCASTER_NONUNIQUE_REPOSITORY_PREFIX', 13);
define ('PODCASTER_INVALID_REPOSITORY_PLUGIN',   14);
define ('PODCASTER_REPOSITORY_PLUGIN_NOTFOUND',  15);
define ('PODCASTER_NONUNIQUE_METACHANNEL_NAME',  16);
define ('PODCASTER_CREATECHANNEL_FAILED',        17);
define ('PODCASTER_INVALID_FORMATNAME',          18);

class podcaster_admin {
  var $subject;

  function & get_instance () {
    static $obj;
    if (!is_object ($obj)) {
      $obj = new podcaster_admin ();
    }
    return $obj;
  } // get_instance ()

  function podcaster_admin () {
    $this->subject = '';
  }

  function errorstr ($errno) {
    switch ($errno) {
      case PODCASTER_NONUNIQUE_LICENSE_NAME:
        return 'error_nonunique_license_name';
      case PODCASTER_NONUNIQUE_REPOSITORY_NAME:
        return 'error_nonunique_repository_name';
      case PODCASTER_NONUNIQUE_REPOSITORY_PREFIX:
        return 'error_nonunique_repository_prefix';
      case PODCASTER_REPOSITORY_PLUGIN_NOTFOUND:
        return 'error_repository_plugin_notfound';
      case PODCASTER_INVALID_REPOSITORY_PLUGIN:
        return 'error_invalid_repository_plugin';
      case PODCASTER_NONUNIQUE_METACHANNEL_NAME:
        return 'error_nonunique_metachannel_name';
      case PODCASTER_CREATECHANNEL_FAILED:
          return 'error_createchannel_failed';
      case PODCASTER_INVALID_FORMATNAME:
          return 'error_invalid_formatname';

      default:
        return '';
    }
  }

  function process_form ($tab, &$formdata) {
    switch ($tab) {
      case 'main':
        return $this->process_mainform ($formdata);
      case 'license':
        return $this->process_licenseform ($formdata);
      case 'repository':
         return $this->process_repositoryform ($formdata);
      case 'metachannel':
         return $this->process_metachannelform ($formdata);
      case 'language':
         return $this->process_languageform ($formdata);
      case 'tools':
         return $this->process_toolsform ($formdata);
      default:
        return PODCASTER_NOERROR;
    }
  }

///////////////////////////////////////////////////////////////////////////////
//
// process form functions
//
  function process_mainform (&$formdata) {
    global $CFG;
    $module = $formdata->module;
    unset ($formdata->tab);
    unset ($formdata->continue);
    unset ($formdata->finish);
    unset ($formdata->sesskey);
    unset ($formdata->module);
    
    // 
    $rssformats  = explode (',', $formdata->formats);
    $metaformats = explode (',', $formdata->metaformats);
    $insertobj   = new object ();
    $insertobj->language = 'en_utf8';
    
    foreach ($rssformats as $format) {
      if (!ereg ('^[a-z]+$', $format) || !is_dir ($CFG->dirroot.'/mod/podcaster/formats/'.$format) || !file_exists ($CFG->dirroot.'/mod/podcaster/formats/'.$format.'/lib.php')) {
        $this->subject = $format;
        return PODCASTER_INVALID_FORMATNAME;
      }
    }

    foreach ($metaformats as $format) {
      if (!ereg ('^[a-z]+$', $format) || !is_dir ($CFG->dirroot.'/mod/podcaster/formats/'.$format) || !file_exists ($CFG->dirroot.'/mod/podcaster/formats/'.$format.'/lib.php')) {
        $this->subject = $format;
        return PODCASTER_INVALID_FORMATNAME;
      }
    }

    foreach ($rssformats as $format) {
      if (($r = get_record ('podcaster_language', 'name', 'format_'.$format.'_title')) == false) {
        $insertobj->name = 'format_'.$format.'_title';
        insert_record ('podcaster_language', $insertobj);
        $insertobj->name = 'format_'.$format.'_desc';
        insert_record ('podcaster_language', $insertobj);
      }
    }
    foreach ($metaformats as $format) {
      if (($r = get_record ('podcaster_language', 'name', 'format_'.$format.'_title')) == false) {
        $insertobj->name = 'format_'.$format.'_title';
        insert_record ('podcaster_language', $insertobj);
        $insertobj->name = 'format_'.$format.'_desc';
        insert_record ('podcaster_language', $insertobj);
      }
    }
    foreach ($formdata as $name => $value) {
      set_config($module.'_'.$name, $value);
    }
    return PODCASTER_NOERROR;
  } // process_mainform ()


  function process_licenseform (&$formdata) {
    $licenses      = get_records ('podcaster_license');

    // check for updates / deletes
    if ($licenses) {
      foreach ($licenses as $license) {
        $delprop    = 'delete_'.$license->id;
        $delconfirm = 'delete_'.$license->id.'_confirm';

        // TODO! Browser compatibility?
        if (isset ($formdata->$delprop) && $formdata->$delprop == 'delete' && isset ($formdata->$delconfirm)) {
          delete_records ('podcaster_license', 'id', $license->id);
          delete_records ('podcaster_language', 'name', 'license_'.$license->name.'_title');
          delete_records ('podcaster_language', 'name', 'license_'.$license->name.'_desc');
        }
        else {
          $confirm = isset ($formdata->{'license_'.$license->id.'_confirm'}) ? '1' : '0';
          if ($license->confirm != $confirm) {
            $license->confirm = $confirm;
            update_record ('podcaster_license', $license);
          }
        }
      }
    }

    // TODO! Browser compatibility?
    if (isset ($formdata->add)) {
      $newlic = new object ();
      $newlic->name      = strtolower(clean_param($formdata->license_0_name, PARAM_ALPHANUM));
      $newlic->isdefault = (isset($formdata->license_0_isdefault) ? '1' : '0');
      $newlic->confirm   = (isset ($formdata->license_0_confirm) ? '1' : '0');
      if ($licenses) {
        foreach ($licenses as $license) {
          if ($license->name == $newlic->name) {
            $this->subject = $newlic->name;
            return PODCASTER_NONUNIQUE_LICENSE_NAME;
          }
        }
      }
      insert_record ('podcaster_license', $newlic);
      
      $langobj = new object ();
      $langobj->language = 'en_utf8';
      $langobj->name     = 'license_'.$newlic->name.'_title';
      insert_record ('podcaster_language', $langobj);
      
      $langobj->name     = 'license_'.$newlic->name.'_desc';
      insert_record ('podcaster_language', $langobj);
    }
    return PODCASTER_NOERROR;
  } // process_licenseform ()


  function process_repositoryform (&$formdata) {
    global $CFG;
    $repositories = get_records ('podcaster_repository');

    if ($repositories) {
      foreach ($repositories as $repository) {
        $delprop    = 'delete_'.$repository->id;
        $delconfirm = 'delete_'.$repository->id.'_confirm';

        // TODO! Browser compatibility?
        if (isset ($formdata->$delprop) && $formdata->$delprop == 'delete' && isset ($formdata->$delconfirm)) {
          delete_records ('podcaster_repository', 'id', $repository->id);
          delete_records ('podcaster_language', 'name', 'repository_'.$repository->name.'_title');
          delete_records ('podcaster_language', 'name', 'repository_'.$repository->name.'_desc');
        }
        else {
          $public      = isset ($formdata->{'repository_'.$repository->id.'_public'}) ? '1' : '0';
          $shared      = isset ($formdata->{'repository_'.$repository->id.'_shared'}) ? '1' : '0';
          $synchronize = isset ($formdata->{'repository_'.$repository->id.'_synchronize'}) ? '1' : '0';
          $enabled     = isset ($formdata->{'repository_'.$repository->id.'_enabled'}) ? '1' : '0';
          $isdefault   = isset ($formdata->{'repository_'.$repository->id.'_isdefault'}) ? '1' : '0';
          $license     = $formdata->{'repository_'.$repository->id.'_license'};
          $filefilter  = trim($formdata->{'repository_'.$repository->id.'_filefilter'});
          $params      = trim($formdata->{'repository_'.$repository->id.'_params'});
          // update repository
          if ($public    != $repository->public ||
              $shared    != $repository->shared ||
              $enabled   != $repository->enabled ||
              $isdefault != $repository->isdefault ||
              $license   != $repository->license ||
              $filefilter != $repository->filefilter ||
              $synchronize != $repository->synchronize ||
              $params    != $repository->params) {
            $repository->public    = $public;
            $repository->shared    = $shared;
            $repository->enabled   = $enabled;
            $repository->isdefault = $isdefault;
            $repository->license   = $license;
            $repository->filefilter= $filefilter;
            $repository->synchronize = $synchronize;
            $repository->params    = $params;
            update_record ('podcaster_repository', $repository);
          }
        }
      }
    }
    // TODO! Browser compatibility?
    if (isset ($formdata->add) || 
        ((isset ($formdata->continue) || isset ($formdata->finish)) && 
                                        ($formdata->repository_0_name   != '' ||
                                         $formdata->repository_0_prefix != '' ||
                                         $formdata->repository_0_plugin != ''))) {
      $newrep = new object ();
      $newrep->name    = strtolower(clean_param($formdata->repository_0_name, PARAM_ALPHANUM));
      $newrep->prefix  = strtolower(clean_param($formdata->repository_0_prefix, PARAM_ALPHANUM));
      $newrep->rss  = strtolower(clean_param($formdata->repository_0_rss, PARAM_INT));
      $newrep->plugin  = strtolower(clean_param($formdata->repository_0_plugin, PARAM_ALPHANUM));
      $newrep->license = clean_param($formdata->repository_0_license, PARAM_INT);
      $newrep->public  = (isset($formdata->repository_0_public) ? '1' : '0');
      $newrep->shared  = (isset($formdata->repository_0_shared) ? '1' : '0');
      $newrep->synchronize = (isset ($formdata->repository0_synchronize) ? '1' : '0');
      $newrep->enabled = (isset($formdata->repository_0_enabled) ? '1' : '0');
      $newrep->isdefault = (isset($formdata->repository_0_isdefault) ? '1' : '0');

      // name and prefix must be unique
      if ($repositories) {
        foreach ($repositories as $repository) {
          if ($repository->name == $newrep->name) {
            $this->subject = $newrep->name;
            return PODCASTER_NONUNIQUE_REPOSITORY_NAME;
          }
          if ($repository->prefix == $newrep->prefix) {
            $this->subject = $newrep->prefix;
            return PODCASTER_NONUNIQUE_REPOSITORY_PREFIX;
          }
        }
      }
      // plugin must exist
      if (file_exists ($CFG->dirroot.'/mod/podcaster/repository/'.$newrep->plugin.'/lib.php')) {
        $id = insert_record ('podcaster_repository', $newrep);
        $rep =& podcaster_repository::create_repository ($id, true);
        if (!$rep) {
          delete_records ('podcaster_repository', 'id', $id);
          $this->subject = $newrep->plugin;
          return PODCASTER_INVALID_REPOSITORY_PLUGIN;
        }
        $newrep->params = $rep->get_default_params ();
        update_record ('podcaster_repository', $newrep);
      }
      else {
        $this->subject = $newrep->plugin;
        return PODCASTER_REPOSITORY_PLUGIN_NOTFOUND;
      }

      $langobj = new object ();
      $langobj->language = 'en_utf8';
      $langobj->name     = 'repository_'.$newrep->name.'_title';
      insert_record ('podcaster_language', $langobj);
      
      $langobj->name     = 'repository_'.$newrep->name.'_desc';
      insert_record ('podcaster_language', $langobj);
    }
    return PODCASTER_NOERROR;
  } // process_repositoryform ()

  function process_metachannelform ($formdata) {
    global $CFG;
    $metachannels = get_records ('podcaster_metachannel');

    if ($metachannels) {
      foreach ($metachannels as $metachannel) {
        $delprop    = 'delete_'.$metachannel->id;
        $delconfirm = 'delete_'.$metachannel->id.'_confirm';

        // TODO! Browser compatibility?
        if (isset ($formdata->$delprop) && $formdata->$delprop == 'delete' && isset ($formdata->$delconfirm)) {
          require_once ($CFG->dirroot.'/course/lib.php');
          if ($metachannel->channel) {
            $cm = get_coursemodule_from_instance ('podcaster', $metachannel->channel, SITEID);
            if ($cm) {
              delete_course_module ($cm->id);
            }
            delete_records ('podcaster', 'id', $metachannel->channel);
          }
          delete_records ('podcaster_metachannel', 'id', $metachannel->id);
        }

        else {
          $repositories = implode(',', $formdata->{'metachannel_'.$metachannel->id.'_params'});
          $path         = $formdata->{'metachannel_'.$metachannel->id.'_path'};

          if ($repositories != $metachannel->params ||
              $path         != $metachannel->path) {
            $metachannel->path   = $path;
            $metachannel->params = $repositories;
            $metachannel->timemodified = time ();
            update_record ('podcaster_metachannel', $metachannel);
          }
        }
      }
    }

    // TODO! Browser compatibility?
    if (isset ($formdata->add) || 
        ((isset ($formdata->continue) || isset ($formdata->finish)) && 
                                        ($formdata->metachannel_0_name   != ''))) {
      $newmeta = new object ();
      $newmeta->name   = strtolower(clean_param($formdata->metachannel_0_name, PARAM_ALPHANUM));
      $newmeta->target = 'repository';
      $newmeta->params = implode(',', $formdata->{'metachannel_0_params'});
      $newmeta->path   = $formdata->{'metachannel_0_path'};

      // name must be unique
      if ($metachannels) {
        foreach ($metachannels as $metachannel) {
          if ($metachannel->name == $newmeta->name) {
            $this->subject = $newmeta->name;
            return PODCASTER_NONUNIQUE_METACHANNEL_NAME;
          }
        }
      }
      require_once ($CFG->dirroot.'/course/lib.php');

      // create meta channel
      $errorflag = PODCASTER_CREATECHANNEL_FAILED;
      do {
        // get the module id
        $mres = get_record ('modules', 'name', 'podcaster');
        if ($mres == false) break;
        $module  = $mres->id;

        // find first section of site
        $sres = get_records ('course_sections', 'course', SITEID, 'id');
        if ($sres == false) break;
        list ($section,) = each ($sres);

        $channel = new object ();
        $channel->name         = $newmeta->name;
        $channel->course       = SITEID;
        $channel->intro        = '';
        $channel->introformat  = 1;
        $channel->ismeta       = '1';
        $channel->timecreated  = time ();
        $channel->timemodified = time ();

        if (($id = insert_record ('podcaster', $channel)) == false) {
          return PODCASTER_CREATECHANNEL_FAILED;
        }
        $newmeta->channel = $id;
        $channel->id      = $id;

        $coursemod = new object ();
        $coursemod->instance = $id;
        $coursemod->course   = SITEID;
        $coursemod->section  = $section;
        $coursemod->module   = $module;
        $coursemod->added    = time ();
        $coursemod->visible  = 0;
        $coursemod->visibleold = 1;
        
        if (($cm = add_course_module ($coursemod)) == false) {
          delete_records ('podcaster', 'id', $id);
          return PODCASTER_CREATECHANNEL_FAILED;
        }
        $errorflag = PODCASTER_NOERROR;
      } while (false);

      if ($errorflag != PODCASTER_NOERROR) {
        return $errorflag;
      }
      insert_record ('podcaster_metachannel', $newmeta);
      redirect ($CFG->wwwroot.'/course/modedit.php?update='.$cm);
    }
    return PODCASTER_NOERROR;
  } // process_metachannelform ()

  function process_languageform (&$formdata) {
    $editlang  = optional_param ('editlang', current_language (), PARAM_TEXT); 

    $enKeys    = get_records ('podcaster_language', 'language', 'en_utf8', 'name DESC');
    $langKeys  = ($editlang != 'en_utf8' ? get_records ('podcaster_language', 'language', $editlang, 'name') : $enKeys);

    $editMap   = array ();

    if ($langKeys) {
      foreach ($langKeys as $key) {
        $editMap[$key->name] = $key;
      }
    }

    if ($enKeys) {
      foreach ($enKeys as $key) {
        $record = (isset ($editMap[$key->name]) ? $editMap[$key->name] : NULL);
        $value  = ($record != NULL ? $record->value : '');
        if (isset($formdata->{$key->name}) && $formdata->{$key->name} != $value) {
          if ($record != NULL) {
            $record->value = $formdata->{$key->name};
            update_record ('podcaster_language', $record);
          }

          else {
            $record = new object ();
            $record->language = $editlang;
            $record->name     = $key->name;
            $record->value    = $formdata->{$key->name};
            insert_record ('podcaster_language', $record);
          }
        }
      }
    }
    return PODCASTER_NOERROR;
  } // process_languageform ()

  function process_toolsform (&$formdata) {
    global $CFG;
    
    $error_reporting = ini_get ('error_reporting');
    $display_errors  = ini_get ('display_errors');

    ini_set ('error_reporting', E_ALL);
    ini_set ('display_errors', 'On');

    if (isset ($formdata->updatechannels)) {
      include_once ($CFG->dirroot.'/mod/podcaster/lib/public.php');
      include_once ($CFG->dirroot.'/mod/podcaster/lib/util.php');
      $start = microtime ();

      echo '<pre>Clearing cache ...</pre>';
      flush ();
      execute_sql ('UPDATE '.$CFG->prefix.'podcaster_item SET enclosureurl = \'\', enclosurelength = 0, enclosuretype = \'\'');
      execute_sql ('UPDATE '.$CFG->prefix.'podcaster SET imageurl = \'\', imagewidth = 0, imageheight = 0, imagetype = \'\', imagelength = 0 WHERE ismeta = 0');

      $channels = get_records ('podcaster', 'ismeta', '0');
      echo '<pre>Updating all channels ...</pre>';
      flush ();

      if ($channels) {
        foreach ($channels as $channel) {
          echo '<pre>&nbsp;&nbsp;Updating '.$channel->id.': '.htmlspecialchars ($channel->name).'</pre>';
          flush ();
          $channelstart = microtime ();
          $channelObj = podcaster_channel::create_channel ($channel);
          $channelObj->update_rss ();
          echo '<pre><a href="'.$channelObj->get_rss_link ().'">RSS</a> <a href="'.$CFG->wwwroot.'/mod/podcaster/view.php?channel='.$channelObj->id.'">HTML</a></pre>';
          echo '<pre>&nbsp;&nbsp;&nbsp;&nbsp;done in '.podcaster_util::time_diff ($channelstart, microtime ()).'</pre>';
          flush ();
        }
      }
      echo '<pre>done in '.podcaster_util::time_diff ($start, microtime ()).'</pre>';
      die ();
    }
    elseif (isset ($formdata->updatemetachannels)) {
      include_once ($CFG->dirroot.'/mod/podcaster/lib/public.php');
      include_once ($CFG->dirroot.'/mod/podcaster/lib/util.php');

      $start = microtime ();
      echo '<pre>Clearing cache ...</pre>';
      execute_sql ('UPDATE '.$CFG->prefix.'podcaster SET imageurl = \'\', imagewidth = 0, imageheight = 0, imagetype = \'\', imagelength = 0 WHERE ismeta = 1');

      $channels = get_records ('podcaster_metachannel');

      echo '<pre>Updating all metachannels ...</pre>';
      flush ();

      if ($channels) {
        foreach ($channels as $channel) {
          echo '<pre>&nbsp;&nbsp;Updating '.$channel->id.': '.htmlspecialchars ($channel->name).'</pre>';
          flush ();
          $channelstart = microtime ();
          $channelObj = podcaster_channel::create_metachannel ($channel);
          $channelObj->update_rss ();
          echo '<pre><a href="'.$channelObj->get_rss_link ().'">RSS</a> <a href="'.$CFG->wwwroot.'/mod/podcaster/view.php?channel='.$channelObj->id.'">HTML</a></pre>';
          echo '<pre>&nbsp;&nbsp;&nbsp;&nbsp;done in '.podcaster_util::time_diff ($channelstart, microtime ()).'</pre>';
          flush ();
        }
      }
      echo '<pre>done in '.podcaster_util::time_diff ($start, microtime ()).'</pre>';
      die ();
    }

    ini_set ('error_reporting', $error_reporting);
    ini_set ('display_errors',  $display_errors);
    return PODCASTER_NOERROR;
  } // process_toolsform ()

///////////////////////////////////////////////////////////////////////////////
//
// print form functions
//
  function print_form ($tab, $error = 0, $subject = '') {
    podcaster_admin::default_config ();
    if ($error) {
      echo '
        <tr valign="top">
          <td colspan="3"><div class="errorbox" style="border-color:red;color:red;">'.get_string ($this->errorstr ($error), 'podcaster', $subject).'</div></td>
        </tr>';
    }
    echo'
      <tr>
        <td colspan="3">'.get_string ('config_'.$tab, 'podcaster').'</td>
      </tr>';

    switch ($tab) {
      case 'main':
        $this->print_mainform ();
        break;
      case 'license':
        $this->print_licenseform ();
        break;
      case 'repository':
        $this->print_repositoryform ();
        break;
      case 'metachannel':
        $this->print_metachannelform ();
        break;
      case 'language':
        $this->print_languageform ();
        break;
      case 'tools':
        $this->print_toolsform ();
        break;
    }
  } // print_form ()

  function print_mainform () {
    global $CFG;

    echo '
      <tr valign="top">
        <td align="right">podcaster_type:</td>
        <td>';
    $choices = array (
        'activity'   => get_string ('activity'),
        'resource'   => get_string ('resource'));
    choose_from_menu ($choices, 'type', $CFG->podcaster_type, '');
    echo '
          </td>
        <td>
          '.get_string ('config_type', 'podcaster').'
        </td>
      </tr>
      <tr valign="top">
        <td align="right">podcaster_submenus:</td>
        <td>';
    $choices = array (
        'none'                      => get_string ('none'),
        'repository'                => get_string ('repository', 'podcaster'),
        'repository_format'         => get_string ('repository_format', 'podcaster'),
        'repository_license'        => get_string ('repository_license', 'podcaster'),
        'repository_format_license' => get_string ('repository_format_license', 'podcaster'),
        'format'                    => get_string ('format', 'podcaster'),
        'format_license'            => get_string ('format_license', 'podcaster'),
        'license'                   => get_string ('license', 'podcaster')
        );
    choose_from_menu ($choices, 'submenus', $CFG->podcaster_submenus, '');
    echo '
          </td>
        <td>
          '.get_string ('config_submenus', 'podcaster').'
        </td>
      </tr>
      <tr valign="top">
        <td align="right">podcaster_formats:</td>
        <td>
          <input name="formats" type="text" size="32" value="'.$CFG->podcaster_formats.'" />
        </td>
        <td>
          '.get_string('config_formats', 'podcaster').'
        </td>
      </tr>
      <tr valign="top">
        <td align="right">podcaster_metaformats:</td>
        <td>
          <input name="metaformats" type="text" size="32" value="'.$CFG->podcaster_metaformats.'" />
        </td>
        <td>
          '.get_string('config_metaformats', 'podcaster').'
        </td>
      </tr>
      <tr valign="top">
        <td align="right">podcaster_webmaster:</td>
        <td>
          <input name="webmaster" type="text" size="32" value="'.$CFG->podcaster_webmaster.'" />
        </td>
        <td>
          '.get_string('config_webmaster', 'podcaster').'
        </td>
      </tr>
      <tr valign="top">
      <td align="right">podcaster_copyright:</td>
        <td>';
    $choices = array (
        'user'   => get_string ('config_copyright_freetext',   'podcaster'),
        'menu'   => get_string ('config_copyright_menu',       'podcaster'),
        'repos'  => get_string ('config_copyright_repos',      'podcaster')
        );
    choose_from_menu ($choices, 'copyright', $CFG->podcaster_copyright, '');
    echo '
          </td>
        <td>
          '.get_string ('config_copyright', 'podcaster').'
        </td>
      </tr>';
  } // print_mainform ()

  function print_licenseform () {
    global $CFG;
    $licenses = get_records ('podcaster_license');
    if ($licenses) {
      foreach ($licenses as $license) {
        echo '<tr>
                <td align="right">'.get_string('config_licensename', 'podcaster').'</td>
                <td><b>'.$license->name.'</b></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_licenseconfirm', 'podcaster').'</td>
                <td><input name="license_'.$license->id.'_confirm" type="checkbox" '.($license->confirm ? 'checked="checked" ' : '').'" /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_licensedefault', 'podcaster').'</td>
                <td><input name="license_'.$license->id.'_isdefault" type="checkbox" '.($license->isdefault ? 'checked="checked" ' : '').'" /></td>
                <td style="white-space:nowrap;" align="right">
                  <button name="" value="" title="" border="0" style="border:none;background-color:transparent;"></button>
                  <input name="delete_'.$license->id.'_confirm" type="checkbox" />
                  <button name="delete_'.$license->id.'" value="delete" title="'.get_string ('delete').'" border="0" style="border:none;background-color:transparent;vertical-align:top;padding-left:0px;"><img src="'.$CFG->pixpath.'/t/delete.gif" class="iconsmall" alt="'.get_string ('delete').'" /></button>
                </td>
              </tr>
              <tr><td colspan="3"><hr size="1"/></td></tr>';
      }
    }
    // new license form
    echo '<tr>
              <td align="right"><b>'.get_string('config_newlicense', 'podcaster').'</b></td>
              <td><input name="license_0_name" type="text" size="32" value="" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td align="right">'.get_string('config_licenseconfirm', 'podcaster').'</td>
              <td><input name="license_0_confirm" type="checkbox" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td align="right">'.get_string('config_licensedefault', 'podcaster').'</td>
              <td><input name="license_0_isdefault" type="checkbox" /></td>
              <td align="right"><button name="add" value="add" title="'.get_string ('add').'" border="0" style="border:none;background-color:transparent;"><img src="'.$CFG->pixpath.'/t/go.gif" class="iconsmall" alt="'.get_string ('add').'" /></button></td>
            </tr>';
  } // print_licenseform ()


  function print_repositoryform () {
    global $CFG;
    $licenses     = get_records ('podcaster_license');
    
    $choices      = array ('0' => get_string ('nolicense', 'podcaster'));
    if ($licenses) {
      foreach ($licenses as $license) {
        $choices[$license->id.''] = $license->name;
      }
    }
    $rchoices     = array ('0' => get_string ('samerepository', 'podcaster'));

    $repositories = get_records ('podcaster_repository');
    if ($repositories) {
      foreach ($repositories as $repository) {
        $rchoices[$repository->id.''] = $repository->name;
      }

      foreach ($repositories as $repository) {
        if (!array_key_exists ($repository->rss.'', $rchoices)) {
          $repository->rss = 0;
        }
        echo '<tr>
                <td align="right">'.get_string('config_repositoryname', 'podcaster').'</td>
                <td><b>'.$repository->name.'</b></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositoryprefix', 'podcaster').'</td>
                <td><b>'.$repository->prefix.'</b></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositoryplugin', 'podcaster').'</td>
                <td><b>'.$repository->plugin.'</b></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositoryrss', 'podcaster').'</td>
                <td><b>'.$rchoices[$repository->rss.''].'</b></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositorylicense', 'podcaster').'</td>
                <td>';
        choose_from_menu ($choices, 'repository_'.$repository->id.'_license', $repository->license, '');
        echo   '
                </td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositorypublic', 'podcaster').'</td>
                <td><input name="repository_'.$repository->id.'_public" type="checkbox" '.($repository->public ? 'checked="checked" ' : '').'" /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositoryshared', 'podcaster').'</td>
                <td><input name="repository_'.$repository->id.'_shared" type="checkbox" '.($repository->shared ? 'checked="checked" ' : '').'" /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositorysynchronize', 'podcaster').'</td>
                <td><input name="repository_'.$repository->id.'_synchronize" type="checkbox" '.($repository->synchronize ? 'checked="checked" ' : '').'" /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositoryenabled', 'podcaster').'</td>
                <td><input name="repository_'.$repository->id.'_enabled" type="checkbox" '.($repository->enabled ? 'checked="checked" ' : '').'" /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositorydefault', 'podcaster').'</td>
                <td><input name="repository_'.$repository->id.'_isdefault" type="checkbox" '.($repository->isdefault ? 'checked="checked" ' : '').'" /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right" valign="top">'.get_string('config_repositoryfilefilter', 'podcaster').'</td>
                <td>
                  <input type="text" name="repository_'.$repository->id.'_filefilter" size="32" value="'.htmlspecialchars($repository->filefilter).'" />
                </td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right" valign="top">'.get_string('config_repositoryparams', 'podcaster').'</td>
                <td>
                  <textarea name="repository_'.$repository->id.'_params" rows="10" cols="32">'.$repository->params.'</textarea>
                </td>
                <td style="white-space:nowrap;" align="right" valign="bottom">
                  <button name="" value="" title="" border="0" style="border:none;background-color:transparent;"></button>
                  <input name="delete_'.$repository->id.'_confirm" type="checkbox" />
                  <button name="delete_'.$repository->id.'" value="delete" title="'.get_string ('delete').'" border="0" style="border:none;background-color:transparent;vertical-align:top;padding-left:0px;">
                    <img src="'.$CFG->pixpath.'/t/delete.gif" class="iconsmall" alt="'.get_string ('delete').'" />
                  </button>
                </td>
              </tr>
              <tr><td colspan="3"><hr size="1"/></td></tr>';
      }
    }
    // new repository form
    echo '
              <tr>
                <td align="right"><b>'.get_string('config_newrepository', 'podcaster').'</b></td>
                <td><input name="repository_0_name" type="text" size="32" value="" /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right"><b>'.get_string('config_repositoryprefix', 'podcaster').'</b></td>
                <td><input name="repository_0_prefix" type="text" size="32" value="" /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right"><b>'.get_string('config_repositoryplugin', 'podcaster').'</b></td>
                <td><input name="repository_0_plugin" type="text" size="32" value="" /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right"><b>'.get_string('config_repositoryrss', 'podcaster').'</b></td>
                <td>';
    choose_from_menu ($rchoices, 'repository_0_rss', 0, '');
    echo   '
                </td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositorylicense', 'podcaster').'</td>
                <td>';
    choose_from_menu ($choices, 'repository_0_license', 0, '');
    echo   '
                </td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositorypublic', 'podcaster').'</td>
                <td><input name="repository_0_public" type="checkbox" />
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositoryshared', 'podcaster').'</td>
                <td><input name="repository_0_shared" type="checkbox" />
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositorysynchronize', 'podcaster').'</td>
                <td><input name="repository_0_synchronize" type="checkbox" />
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositoryenabled', 'podcaster').'</td>
                <td><input name="repository_0_enabled" type="checkbox" />
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_repositorydefault', 'podcaster').'</td>
                <td><input name="repository_0_isdefault" type="checkbox" />
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right" valign="top">'.get_string('config_repositoryfilefilter', 'podcaster').'</td>
                <td>
                  <input type="text" name="repository_0_filefilter" size="32" value="" />
                </td>
                <td align="right">
                  <button name="add" value="add" title="'.get_string ('add').'" border="0" style="border:none;background-color:transparent;">
                    <img src="'.$CFG->pixpath.'/t/go.gif" class="iconsmall" alt="'.get_string ('add').'" />
                  </button>
                </td>
              </tr>';
  } // print_repositoryform ()

  function print_metachannelform () {
    global $CFG;
    require_once ($CFG->dirroot.'/mod/podcaster/lib/util.php');
    $repositories = array ();
    $disabled     = array ();
    $repos = get_records ('podcaster_repository');

    if ($repos) {
      foreach ($repos as $rep) {
        $repositories[$rep->id] = podcaster_util::get_string ('repository_'.$rep->name.'_title');
        if (!$rep->enabled) {
          $disabled[] = $rep->id;
        }
      }
    }
    $metachannels = get_records ('podcaster_metachannel');
    if ($metachannels) {
      foreach ($metachannels as $metachannel) {
        $selected = explode (',', $metachannel->params);
        $cm       = get_coursemodule_from_instance ('podcaster', $metachannel->channel, SITEID);
        $channel  = get_record ('podcaster', 'id', $metachannel->channel);

        echo '<tr>
                <td align="right">'.get_string('config_metachannelname', 'podcaster').'</td>
                <td><b>'.$metachannel->name.'</b></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_metachannelpath', 'podcaster').'</td>
                <td><input type="text" name="metachannel_'.$metachannel->id.'_path" value="'.htmlspecialchars($metachannel->path).'" /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_metachannelchannel', 'podcaster').'</td>
                <td>'.$channel->name.'&nbsp;<a href="'.$CFG->wwwroot.'/course/modedit.php?update='.$cm->id.'" title="'.get_string ('edit').'"><img src="'.$CFG->pixpath.'/t/edit.gif" class="iconsmall" alt="'.get_string ('edit').'" /></a></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right" valign="top">'.get_string('config_metachannelparams', 'podcaster').'</td>
                <td>'.$this->choose_from_menu_multiple ($repositories, 'metachannel_'.$metachannel->id.'_params', $selected, $disabled, 5).'</td>
                <td style="white-space:nowrap;" align="right" valign="bottom">
                  <button name="" value="" title="" border="0" style="border:none;background-color:transparent;"></button>
                  <input name="delete_'.$metachannel->id.'_confirm" type="checkbox" />
                  <button name="delete_'.$metachannel->id.'" value="delete" title="'.get_string ('delete').'" border="0" style="border:none;background-color:transparent;vertical-align:top;padding-left:0px;">
                    <img src="'.$CFG->pixpath.'/t/delete.gif" class="iconsmall" alt="'.get_string ('delete').'" />
                  </button>
                </td>
              </tr>
              <tr><td colspan="3"><hr size="1"/></td></tr>';
      }
    }
    // new meta channel form
    echo '
              <tr>
                <td align="right"><b>'.get_string('config_newmetachannel', 'podcaster').'</b></td>
                <td><input name="metachannel_0_name" type="text" size="32" value="" /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right">'.get_string('config_metachannelpath', 'podcaster').'</td>
                <td><input type="text" name="metachannel_0_path" value="" /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="right" valign="top">'.get_string('config_metachannelparams', 'podcaster').'</td>
                <td>'.$this->choose_from_menu_multiple ($repositories, 'metachannel_0_params', array(), $disabled, 5).'</td>
                <td align="right" valign="bottom">
                  <button name="add" value="add" title="'.get_string ('add').'" border="0" style="border:none;background-color:transparent;">
                    <img src="'.$CFG->pixpath.'/t/go.gif" class="iconsmall" alt="'.get_string ('add').'" />
                  </button>
                </td>
              </tr>';
  } // print_metachannelform ()

  function print_languageform () {
    global $CFG;
    $editlang  = optional_param ('editlang', current_language (), PARAM_TEXT); 
    
    $enKeys    = get_records ('podcaster_language', 'language', 'en_utf8', 'name DESC');
    $langKeys  = ($editlang != 'en_utf8' ? get_records ('podcaster_language', 'language', $editlang, 'name') : $enKeys);
    $editMap   = array ();
    if ($langKeys) {
      foreach ($langKeys as $key) {
        $editMap[$key->name] = $key->value;
      }
    }
    $languages = get_list_of_languages();
    echo '<tr>
            <td>&nbsp;</td>
            <td>'.get_string ('config_currentlanguage', 'podcaster').'</td>
            <td>';
    choose_from_menu ($languages, 'editlang', $editlang, '', 'document.location.href=\''.$CFG->wwwroot .'/'.$CFG->admin.'/module.php?module=podcaster&amp;tab=language&amp;editlang=\' + this.value');
    echo '  </td>
          </tr>';
    if ($enKeys) {
      $i = 0;
      foreach ($enKeys as $key) {
        $exists = (isset ($editMap[$key->name]) ? true : false);
        $value  = ($exists ? $editMap[$key->name] : '');

        echo '<tr>
                <td valign="top"><b>'.$key->name.'</b></td>
                <td valign="top">'.$key->value.'</td>
                <td valign="top"'.($exists ? '' : ' style="border:1px solid red;"').'>';
        if (substr($key->name, -5) == 'title') {
          echo '<input type="text" size="32" name="'.$key->name.'" value="'.htmlspecialchars($value).'" />';
        } else {
          echo '<textarea name="'.$key->name.'" rows="7" cols="32">'.$value.'</textarea>';
        }
        echo '
                </td>
              </tr>';
        if (++$i % 2 == 0) {
          echo '<tr><td colspan="3"><hr size="1" /></td></tr>';
        }
      }
    }
  } // print_languageform ()

  function print_toolsform () {
    echo '<tr>
            <td align="right" valign="top">'.get_string('config_updatechannels', 'podcaster').'</td>
            <td align="right" valign="bottom">
              <button name="updatechannels" value="updatechannels" title="'.get_string ('updatechannels', 'podcaster').'" border="0">'.get_string ('updatechannels', 'podcaster').'</button>
            </td>
            <td>&nbsp;</td>
          </tr>
         <tr>
            <td align="right" valign="top">'.get_string('config_updatemetachannels', 'podcaster').'</td>
            <td align="right" valign="bottom">
              <button name="updatemetachannels" value="updatemetachannels" title="'.get_string ('updatemetachannels', 'podcaster').'" border="0">'.get_string ('updatemetachannels', 'podcaster').'</button>
            </td>
            <td>&nbsp;</td>
          </tr>';
  } // print_toolsform ()

  function choose_from_menu_multiple ($choices, $name, $selected = array (), $disabled, $size = 5) {
    $result = '<select name="'.$name.'[]" multiple="multiple" size="'.$size.'">';
    foreach ($choices as $value => $n) {
      $result .= '<option value="'.$value.'"'.
        (in_array ($value, $disabled) ? ' disabled="disabled"' : '').
        (in_array ($value, $selected) ? ' selected="selected"' : '').'>'.$n.'</option>';
    }
    $result .= '</select>';
    return $result;
  } // choose_from_menu_multiple ()

  function postinstall () {
    global $CFG;
    include_once ($CFG->dirroot.'/mod/podcaster/lib/public.php');
    // repository default parameter
    $repositories = get_records ('podcaster_repository');
    foreach ($repositories as $r) {
      if (!$r->params) {
        $rep =& podcaster_repository::create_repository ($r->prefix, true);
        if (!$rep) {
          continue;
        }
        $r->params = $rep->get_default_params ();
        update_record ('podcaster_repository', $r);
      }
    }
    podcaster_admin::default_config ();
  } // postinstall

  function default_config () {
    global $CFG;
    $admin = get_admin ();
    if (!is_object ($admin)) {
      $admin = new object ();
      $admin->firstname = '';
      $admin->lastname = '';
      $admin->email = '';
    }
    // default configuration
    $conf = array ('podcaster_formats'     => 'rss',
                   'podcaster_metaformats' => 'hu',
                   'podcaster_webmaster'   => $admin->firstname.' '.$admin->lastname.' <'.$admin->email.'>',
                   'podcaster_copyright'   => 'user',
                   'podcaster_submenus'    => 'none',
                   'podcaster_type'        => 'activity'
                   );
    foreach ($conf as $name => $value) {
      if (!isset ($CFG->$name)) {
        set_config ($name, $value);
      }
    }
  } // default_config ()

} // class podcaster_admin
?>
