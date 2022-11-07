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
$PAGE->set_url(new moodle_url('/local/genashtim_tms/track_manage.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_genashtim_tms'));
$PAGE->set_heading(get_string('pluginname', 'local_genashtim_tms'));
use \local_genashtim_tms\request;
use \local_genashtim_tms\functions;

// require_once($CFG->dirroot . '/local/genashtim_tms/classes/functions.php');

require_login();
$request = new request();
$function = new functions();
$allRequests = $request->getRequestAll();
if($function->canAccessPlugin()){
 redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php');
}
echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_genashtim_tms/disabled',array());

echo $OUTPUT->footer();