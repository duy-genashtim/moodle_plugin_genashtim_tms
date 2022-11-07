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
$PAGE->set_heading(get_string('page_request_staff', 'local_genashtim_tms'));
$PAGE->set_pagelayout('admin');

$uid = required_param('uid',PARAM_INT);

$previewnode = $PAGE->navigation->add(get_string('page_request_staff', 'local_genashtim_tms'), new moodle_url('/local/genashtim_tms/track_manage.php'), navigation_node::TYPE_CONTAINER);
$subnode = $previewnode->add(get_string('page_request_staff', 'local_genashtim_tms'),new moodle_url('/local/genashtim_tms/request_staff.php',array('uid'=>$uid)));
$subnode->make_active();

use \local_genashtim_tms\request;
use \local_genashtim_tms\functions;
use \local_genashtim_tms\display;

// require_once($CFG->dirroot . '/local/genashtim_tms/classes/functions.php');

require_login();
$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/local/genashtim_tms/datatable/datatables.min.css'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/genashtim_tms/datatable/datatables.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/genashtim_tms/javascript/script.js'),true);

$request = new request();
$function = new functions();
$display = new display();
$user = $function->get_user($uid);
// redirect to disabled page if dont have permission or plugin disabled
if(!$function->canAccessPlugin()){
    redirect($CFG->wwwroot.'/local/genashtim_tms/disabled.php');
}
if(!$user){
    redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php',"The user does not exist.");
}
$manager = $function->get_all_user_field($uid);
$templateData = (object) [];

$templateData->my_request = array_values($request->getRequestByUserId($uid));
$templateData->has_my_request = true;

// $allRequests = $request->getRequestAll();
echo $OUTPUT->header();

// $template_context = (object) [
//     'requests' =>array_values($templateData)
// ];
// echo "<div style='background-color: #ffffff; color: #000000;'>";
//     echo "  <pre>1";
//     print_r( $templateData);
//     echo "  </pre>";
//     echo "</div>";
// echo $OUTPUT->render_from_template('local_genashtim_tms/track_manage',$templateData);

?>
<div class="row">
    <div class="col-sm-12">
    <p class="pull-right mb-4"> <a class="back_link" href="<?php echo $CFG->wwwroot.'/local/genashtim_tms/track_manage.php' ?>"><< Back to Request Manage Page</a></p>
    </div>
</div>
<div class="row mb-2">
  <div class="col-sm-12">
    <p><b>User:</b> <?php echo $user->firstname. ' ' .$user->lastname;?></p>
    <p><b>Email:</b> <?php echo $user->email;?></p>
    <p><b>Department:</b> <?php echo $manager['department']->data;?></p>
    <p><b>Manager Email:</b> <?php echo $manager['manager_email']->data?></p>
  </div>
</div>
 <table id="my_request"  class="generaltable gtms">
  <thead>
    <tr>
      <th>Type</th>
      <th>Course Name</th>
      <th>Duration</th>
      <th>Price</th>
      <th>Status</th>
      <th>Request Date</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($templateData->my_request as  $request) {
       echo '<tr>';
       echo '<td>'.$request->requesttype.'</td>';
       echo '<td>'.$display->LinkDetail($request->coursename,$request->id).'</td>';
       echo '<td>'.$request->courseduration.'</td>';
       echo '<td>'.$display->showPrice($request).'</td>';
       echo '<td data-sort="'.$request->status.'">'.$display->showStatus($request->status).'</td>';
       echo '<td data-sort="'.$request->timecreated.'">'.$display->showDate($request->timecreated).'</td>';
       echo '</tr>';
    }?>
  </tbody>
  </table>


<?php
echo $OUTPUT->footer();

