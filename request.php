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

$previewnode = $PAGE->navigation->add(get_string('page_request', 'local_genashtim_tms'), new moodle_url('/local/genashtim_tms/request.php'), navigation_node::TYPE_CONTAINER);
$previewnode->make_active();

use \local_genashtim_tms\request;
use \local_genashtim_tms\functions;

$mform = new request_form();
$request = new request();
$function = new functions();
if(!$function->canAccessPlugin()){
  redirect($CFG->wwwroot.'/local/genashtim_tms/disabled.php');
}
if(!$function->canSendRequest($USER->id)){
  redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php',get_string('error-no-manager-email-setting','local_genashtim_tms'));
}

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //redirect to manage page
    redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php',get_string('cancel-message','local_genashtim_tms'));
} else if ($fromform = $mform->get_data()) {
//   insert data to the database
// $user = new stdClass();
$userFiled =  $function->get_all_user_field($USER->id);

$user = $USER;
$user->manageremail = $userFiled['manager_email']->data;

$insert = $request->insertRequestDB($fromform,$user);
if($insert){
  $requestData = $request->getRequestById($insert);
  $function->sendCourseRequestEmail($user,$requestData);
  redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php',get_string('confirmation-message','local_genashtim_tms'));
}
} 
echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();

