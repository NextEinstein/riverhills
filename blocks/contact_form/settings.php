<?php
$site = get_site();
$settings->add(new admin_setting_configtext('block_contact_form_subject_prefix', get_string('subject_prefix', 'block_contact_form'),
                   get_string('subject_prefix_info', 'block_contact_form'), '['. strip_tags($site->shortname) .']',PARAM_RAW));

$settings->add(new admin_setting_configcheckbox('block_contact_form_receipt', get_string('receipt', 'block_contact_form'),
                   get_string('receipt_info', 'block_contact_form'), 0));
?>