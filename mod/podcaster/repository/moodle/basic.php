<?php
/**
 * podcaster basic authentication 
 *
 * @author  Humboldt Universitaet zu Berlin
 *            Christoph Soergel <christoph.soergel@cms.hu-berlin.de>
 * @version 1.0
 * @package podcaster
 *
 */
function http_basic_login () {
  global $USER;

  $realm     = 'restricted';
  $userValid = false;

  if (isloggedin ()) {
    return true;
  }
  $realm = 'restricted';
  if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
    $user = authenticate_user_login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    do
    {
      if (!$user) {
        $realm = 'loginerror';
        break;
      }
      $USER = $user;
      // check whether the user should be changing password
      if (get_user_preferences('auth_forcepasswordchange', false)) {
          if ($passwordchangeurl != '') {
              $realm = 'mustchangepassword';
          } else {
              $realm = 'mustchangepassword_butnourl';
          }
          break;
      }
      // check wether user is fully setup
      if (user_not_fully_set_up($USER)) {
          $realm = 'notfullysetup';
          break;
      }

      return true;

    } while (false);

    unset($USER);
  } // no credentials
  header('WWW-Authenticate: Basic realm="'.get_string($realm, 'podcaster').'"');
  header('HTTP/1.0 401 Unauthorized');
  echo get_string($realm, 'podcaster');
  exit;
} // http_basic_login ()
?>
