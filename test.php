<?php
/**
 * Version information for Genashtim TMS.
 *
 * @package    local_genashtim_tms
 * @copyright  2022 genashtim - www.genashtim.com
 * @author     Duy Hoang
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__. '/../../config.php');
require_once($CFG->dirroot . '/local/genashtim_tms/classes/form/request.php');
// require_once($CFG->dirroot . '/local/genashtim_tms/classes/functions.php');

$PAGE->set_url(new moodle_url('/local/genashtim_tms/request.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_genashtim_tms'));
$PAGE->set_heading(get_string('pluginname', 'local_genashtim_tms'));
use \local_genashtim_tms\request;
use \local_genashtim_tms\functions;

$mform = new request_form();
$request = new request();
$function = new functions();

$setting = $function->getSetting();
$adminList = array_map('trim', explode(',', $settings->admin_emails));
echo "<div style='background-color: #ffffff; color: #000000;'>";
  echo "  <pre>";
  print_r( $adminList );
  echo "  </pre>";
  echo "</div> <hr>";
  echo "<div style='background-color: #ffffff; color: #000000;'>";
  echo "  <pre>";
  print_r( in_array($USER->email, $adminList));
  echo "  </pre>";
  echo "</div> <hr>";
  echo "<div style='background-color: #ffffff; color: #000000;'>";
  echo "  <pre>";
  print_r( $USER );
  echo "  </pre>";
  echo "</div> <hr>";
echo "<div style='background-color: #ffffff; color: #000000;'>";
  echo "  <pre>canAccessPlugin";
  print_r( canAccessPlugin1($function) );
  echo "  </pre>";
  echo "</div>";
function  canAccessPlugin1($function)
{
    global $USER;
    $settings = $function->getSetting();
    if (is_siteadmin() ) {
      echo "@222";
    }
    if ($function->isAdmin() && isset($settings->enabled) && $settings->enabled == 1) {
      echo "123";
        return true;
    }

    $needle = $settings->lms_email_limit; //"@sbf-pcpi.com";
    // $needle = "@genashtim.com";
    if (!isset($settings->enabled) || $settings->enabled == 0 || !$function->endsWith($USER->email, $needle)) {
      echo "000"; 
      return false;
    }
    echo "3333";
    return true;
}
