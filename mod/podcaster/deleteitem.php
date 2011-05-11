<?php
  require_once ('../../config.php');
  require_once ('lib.php');
  require_once ('locallib.php');
  require_once ('lib/itemform.php');

  $id      = required_param('id',      PARAM_INT);
  $channel = required_param('channel', PARAM_INT);  
  $confirm = optional_param('delete',  0, PARAM_INT);

  if (!$obj = get_record('podcaster', 'id', $channel)) {
    error('Course module is incorrect');
  }
  if (!$course = get_record('course', 'id', $obj->course)) {
    error('Course is misconfigured');
  }
  if (!$cm = get_coursemodule_from_instance('podcaster', $obj->id, $course->id)) {
    error('Course Module ID was incorrect');
  }
  require_login($course->id, false, $cm);

  $channel  = podcaster_channel::create_channel ($obj, $cm);
  if (!$channel) {
    error ('Channel could not be found');
  }
  if (!$channel->can_edit ()) {
    error ('You are not allowed to do so');
  }
  $item = $channel->get_item ($id);
  if (!$item) {
    error ('This item does not exist');
  }
if ($confirm == 0) {
  $strdeletecheck     = get_string ('deletecheck',     '', $item->title);
  $strdeletecheckfull = get_string ('deletecheckfull', '', "$channel->name '$item->title'");
  $strdeletefilehint  = get_string ('deletefilehint',  'podcaster', $item->enclosure);
  $strdeletefilelabel = get_string ('deletefilelabel', 'podcaster', $item->enclosure);


  $candeletefile      = false;

  print_header_simple($strdeletecheck, '', $strdeletecheck);
  print_simple_box_start('center', '60%', '#FFAAAA', 20, 'noticebox');
  print_heading($strdeletecheckfull);

  if ($item->enclosure != '' && count(podcaster_util::get_filerefs ($course, $channel, $item->enclosure)) == 1) {
    $candeletefile = true;
  }
?>
<form id="form" method="post" action="./deleteitem.php">
  <input type="hidden" name="channel" value="<?php p($channel->id) ?>" />
  <input type="hidden" name="id"      value="<?php p($id) ?>" />
  <input type="hidden" name="delete"  value="<?php p('1') ?>" />
<?php
  if ($candeletefile) {
?>
  <div style="text-align:left;">
<?php p ($strdeletefilehint); ?><br/>
  <label for="deletefile"><?php p($strdeletefilelabel); ?></label>&nbsp;<input type="checkbox" name="deletefile" id="deletefile" value="1" checked="checked" />&nbsp;
  <br/><br/>
  </div>
<?php
  }
?>
  <input type="submit" value=" <?php print_string("yes")?> " /> 
  <input type="button" value=" <?php print_string("no")?> " onclick="javascript:history.go(-1);" />
</form>
<?php
  print_simple_box_end();
  print_footer($course);
}
else {
  $deletefile = optional_param ('deletefile', 0, PARAM_INT);
  if ($deletefile) {
    $channel->delete_file ($item->enclosure);
  }
  delete_records ('podcaster_item', 'id', $id);

  // update channel record
  $podupd                = new object ();
  $podupd->id            = $channel->id;
  $podupd->timemodified  = time ();

  update_record ('podcaster', $podupd);
  
  // update rss
  $channel->timemodified = time ();
  podcaster_update_rss ($channel);
  redirect ($CFG->wwwroot.'/mod/podcaster/view.php?id='.$cm->id.'&amp;tab=items', get_string('item_deleted', 'podcaster'), 2);
}
?>
