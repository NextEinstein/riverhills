<?PHP /*  $Id: styles.php,v 1.65.14.1 2009/07/29 07:42:36 moodler Exp $ */

/// Every theme should contain a copy of this script.  It lets us 
/// set up variables and so on before we include the raw CSS files.
/// The output of this script should be a completely standard CSS file.

/// THERE SHOULD BE NO NEED TO MODIFY THIS FILE!!  USE CONFIG.PHP INSTEAD.


    $lifetime  = (48 * 3600) + 1;                       // Seconds to cache this stylesheet (2 days for standard)
    $nomoodlecookie = true;                             // Cookies prevent caching, so don't use them
    if (!defined('MO_CRON')) {
        require_once("../../config.php");                   // Load up the Moodle libraries
    }
    $themename = basename(dirname(__FILE__));           // Name of the folder we are in
    $forceconfig = optional_param('forceconfig', 'standard', PARAM_FILE);   // Get config from this theme
    $lang        = optional_param('lang', '', PARAM_FILE);          // Look for styles in this language

    style_sheet_setup(time(), $lifetime, $themename, $forceconfig, $lang);
   
?>
