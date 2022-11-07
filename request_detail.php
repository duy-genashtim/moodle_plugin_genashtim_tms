<?php
use mod_forum\local\data_mappers\legacy\discussion;
/**
 * Version information for Genashtim TMS.
 *
 * @package    local_genashtim_tms
 * @copyright  2022 genashtim - www.genashtim.com
 * @author     Duy Hoang
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__. '/../../config.php');
// require_once($CFG->dirroot . '/local/genashtim_tms/classes/functions.php');

$PAGE->set_url(new moodle_url('/local/genashtim_tms/request_detail.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_genashtim_tms'));
$PAGE->set_heading(get_string('page_request_detail', 'local_genashtim_tms'));

use \local_genashtim_tms\request;
use \local_genashtim_tms\functions;
use \local_genashtim_tms\display;

$rid = required_param('rid',PARAM_INT);

$previewnode = $PAGE->navigation->add(get_string('page_request_status', 'local_genashtim_tms'), new moodle_url('/local/genashtim_tms/track_manage.php'), navigation_node::TYPE_CONTAINER);
$subnode = $previewnode->add(get_string('page_request_detail', 'local_genashtim_tms'),new moodle_url('/local/genashtim_tms/request_detail.php',array('rid'=>$rid)));
$subnode->make_active();

$request = new request();
$function = new functions();
$display = new display();
if(!$function->canAccessPlugin()){
  redirect($CFG->wwwroot.'/local/genashtim_tms/disabled.php');
}
if(!$function->canSendRequest($USER->id)){
  redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php',get_string('error-no-manager-email-setting','local_genashtim_tms'));
}

$requestDetail = $request->getRequestAndUserById($rid);

echo $OUTPUT->header(); ?>
<div class="row">
    <div class="col-sm-12">
    <p class="pull-right mb-4"> <a class="back_link" href="<?php echo $CFG->wwwroot.'/local/genashtim_tms/track_manage.php' ?>"><< Back to Request Manage Page</a></p>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">


<h3>Request Detail</h3>
<p><b>Request Name:</b> <?php echo $requestDetail->firstname ." ".$requestDetail->lastname; ?></p>
<p><b>Department:</b> <?php echo $requestDetail->department;?></p>
<p><b>Manager Email:</b> <?php echo $requestDetail->manageremail;?></p>
<p> Request to take the course/training below:</p>
<p><b>Course Name:</b> <?php echo $requestDetail->coursename;?></p>
<p><b>Course Description:</b> <?php echo $requestDetail->coursedes;?></p>
<p><b>Course Duration:</b> <?php echo $requestDetail->courseduration;?></p>
<p><b>Course Link:</b>  <a href="<?php echo $requestDetail->courselink;?>" target="_blank"><?php echo $requestDetail->courselink;?></a></p>
<p><b>Course Price:</b> <?php echo $display->showPrice($requestDetail);?></p>
<p><b>Reason:</b> <?php echo $requestDetail->reason;?></p>
<p><b>Request Date:</b> <?php echo $display->showDate($requestDetail->timecreated,'strftimedatetimeshort');?></p>
<p><b>Current Status:</b> <?php echo $display->showStatus($requestDetail->status,'span'); ?></p>

<?php
    $link = $CFG->wwwroot.'/local/genashtim_tms/request_process.php?id='.$requestDetail->id.'&email='.$requestDetail->manageremail.'&cstep='.$requestDetail->status.'&status=';

if(($USER->email == $requestDetail->manageremail || is_siteadmin()) && $requestDetail->status == 0){
    echo ' <p style="text-align: center ;">
    <table  style="max-width:340px">
        <tr>
        <td bgcolor="#82B541" class="btn-link-1" height="42" align="center" style="border-radius:50px;font-family: \'Open Sans\', Arial, sans-serif; color:#FFFFFF; font-size:14px;font-weight: bold;letter-spacing: 1px;padding-left: 25px;padding-right: 25px; text-align: center;"><a href="'.$link.'2" style="text-decoration:none; color:#ffffff !important;"  onclick="return confirm(\'Approve the request. Are you sure?\')">Approved</a></td>
            <td></td>
        <td bgcolor="#b71010" class="btn-link-1" height="42" align="center" style="border-radius:50px;font-family: \'Open Sans\', Arial, sans-serif; color:#FFFFFF; font-size:14px;font-weight: bold;letter-spacing: 1px;padding-left: 25px;padding-right: 25px; text-align: center;"><a href="'.$link.'1" style="text-decoration:none; color:#ffffff !important;"  onclick="return confirm(\'Disapprove the request. Are you sure?\')">Disapproved</a></td>
        </tr>
    </table>
    </p>';
}
if($function->isAdmin() && $requestDetail->status == 2){
    echo ' <p style="text-align: center ;">
    <table style="max-width:340px">
        <tr>
        <td bgcolor="#82B541" class="btn-link-1" height="42" align="center" style="border-radius:50px;font-family: \'Open Sans\', Arial, sans-serif; color:#FFFFFF; font-size:14px;font-weight: bold;letter-spacing: 1px;padding-left: 25px;padding-right: 25px; text-align: center;"><a href="'.$link.'3" style="text-decoration:none; color:#ffffff  !important;"  onclick="return confirm(\'Was the course added to the system? mark the request published?\')">Publish</a></td>
        </tr>
    </table>
    </p>';
}
echo '    </div>
</div>';
if($function->isAdmin()){
    echo '<div class="row">
    <div class="col-sm-12">
    <p class="pull-right mb-4"> <a class="back_link btn btn-danger" style="color:#fff !important"  onclick="return confirm(\'Delete the request completely?\')" href="delete_request.php?rid='.$rid.'">Delete Request</a></p>
    </div>
</div>';
}
echo $OUTPUT->footer();

