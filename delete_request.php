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
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_genashtim_tms'));
$PAGE->set_heading(get_string('pluginname', 'local_genashtim_tms'));

$id = required_param('rid',PARAM_INT);
$PAGE->set_url(new moodle_url('/local/genashtim_tms/delete_request.php',array('rid'=>$id)));
require_login();


use \local_genashtim_tms\request;

// require_once($CFG->dirroot . '/local/genashtim_tms/classes/functions.php');

$request = new request();
$requestDetail = $request->getRequestById($id);
if(isset($requestDetail->id)){
   if( $request->deleteRequest($id)){
    redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php',"Record deleted.");
   }
}
redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php',"The request does not exist.");
