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
$PAGE->set_url(new moodle_url('/local/genashtim_tms/tms_report.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_genashtim_tms'));
$PAGE->set_heading(get_string('page_tms_report', 'local_genashtim_tms'));
$PAGE->set_pagelayout('admin');

$previewnode = $PAGE->navigation->add(get_string('page_tms_report', 'local_genashtim_tms'), new moodle_url('/local/genashtim_tms/tms_report.php'), navigation_node::TYPE_CONTAINER);
$previewnode->make_active();
require_login();
use \local_genashtim_tms\request;
use \local_genashtim_tms\functions;
use \local_genashtim_tms\display;
$request = new request();
$function = new functions();
$display = new display();


// redirect to disabled page if dont have permission or plugin disabled
if(!$function->canAccessPlugin()){
    redirect($CFG->wwwroot.'/local/genashtim_tms/disabled.php');
}
if(!$function->isAdmin()){
    // redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php');
    redirect($CFG->wwwroot.'/local/genashtim_tms/tms_report_detail.php?uid='.$USER->id);

}


$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/local/genashtim_tms/datatable/datatables.min.css'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/genashtim_tms/datatable/datatables.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/genashtim_tms/javascript/script.js'),true);
// $users = $function->getAllUserRequest();
// $settings = $function->getSetting();
// $setting_array = json_decode(json_encode($settings), true);
$usersReport = $function->getUsersReport();
$years = $function->get3Years();
// $function->getCourseCustomField(2)
// echo "<div style='background-color: #ffffff; color: #000000;'>";
//     echo "  <pre>";
//     print_r( $usersReport);
//     echo "  </pre>";
//     echo "</div>";
//     exit();

// if($function->isManager($USER->id)){
//     $templateData->staff_request = array_values($request->getRequest4Manager($USER->email));
//     $templateData->has_staff_request = true;
// }


// $allRequests = $request->getRequestAll();
echo $OUTPUT->header();


?>
<div class="row pb-4">
  <div class="col-sm-12">
    <div class="pull-right">
      <span class="pl-2 pr-2">
        <a class="back_link" href="export_tms_report.php">Export To Excel</a>
      </span>
    </div>
  </div>
</div>
 <table id="staff_request"  class="generaltable gtms">
  <thead>
    <tr>
      <th rowspan="2">First Name</th>
      <th rowspan="2">Last Name</th>
      <th rowspan="2">Email</th>
      <th rowspan="2">First Login</th>
      <th rowspan="2">Last Login</th>
      <th rowspan="2">Num Of Courses</th>
      <th rowspan="2">Completed Course</th>
      <th rowspan="2">Total Course Amount</th>
      <th colspan="4">Training Hours</th>
    </tr>
    <tr>
    <?php foreach($years as $year){
      echo " <th>{$year}</th>";
    }?>
     <th>Total</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($usersReport as  $user) {
       echo '<tr>';
       echo '<td>'.$display->LinkTMSReportDetail($user->firstname , $user->id).'</td>';
       echo '<td>'.$display->LinkTMSReportDetail($user->lastname , $user->id).'</td>';
       echo '<td>'.$display->LinkTMSReportDetail($user->email , $user->id).'</td>';
       echo '<td>'.$display->showDate($user->firstaccess).'</td>';
       echo '<td>'.$display->showDate($user->lastaccess).'</td>';
       echo '<td>'.$user->totalCourseEnrolled.'</td>';
       echo '<td>'.$user->totalCompleted.'</td>';
       echo '<td>$'.$user->totalAmount.'</td>';
       foreach ($years as  $year) {
        if(array_key_exists($year,$user->yearData) && array_key_exists('value',$user->yearData[$year])){
          echo '<td>'.$user->yearData[$year]['value'].'</td>';
        }else{
          echo '<td>0</td>';
        }
       }
       echo '<td>'.$user->totalTrainingHours.'</td>';
       echo '</tr>';
    }?>
  </tbody>
  </table>

<?php
echo $OUTPUT->footer();

