<?php
global $CFG;

if ($CFG->mobiledevice !== false) {?>
<link rel="stylesheet" type="text/css" href="<?php echo $CFG->httpsthemewww ?>/<?php echo current_theme(); ?>/mobile.css" />
<?php }