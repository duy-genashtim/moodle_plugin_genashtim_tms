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
$PAGE->set_heading(get_string('page_request_status', 'local_genashtim_tms'));
$PAGE->set_pagelayout('admin');

$previewnode = $PAGE->navigation->add(get_string('page_request_status', 'local_genashtim_tms'), new moodle_url('/local/genashtim_tms/track_manage.php'), navigation_node::TYPE_CONTAINER);
$previewnode->make_active();
use \local_genashtim_tms\request;
use \local_genashtim_tms\functions;
use \local_genashtim_tms\display;

// require_once($CFG->dirroot . '/local/genashtim_tms/classes/functions.php');

require_login();
$request = new request();
$function = new functions();
$display = new display();
// redirect to disabled page if dont have permission or plugin disabled
if(!$function->canAccessPlugin()){
    redirect($CFG->wwwroot.'/local/genashtim_tms/disabled.php');
}
$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/local/genashtim_tms/datatable/datatables.min.css'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/genashtim_tms/datatable/datatables.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/genashtim_tms/javascript/script.js'),true);

$templateData = (object) [];
$templateData->my_request = array_values($request->getRequestByUserId($USER->id));
$templateData->has_my_request = true;
// if($function->isManager($USER->id)){
//     $templateData->staff_request = array_values($request->getRequest4Manager($USER->email));
//     $templateData->has_staff_request = true;
// }
if($function->isManager($USER->id)){
    $templateData->staff_request = array_values($request->getRequest4Manager('duy@genashtim.com'));
    $templateData->has_staff_request = true;
  }
if($function->isAdmin()){
    $templateData->all_request = array_values( $request->getRequestAllWithName());
    $templateData->has_all_request = true;
}

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
<div class="tab-container">
<div class="tab-menu mb-4">
      <ul>
         <li><a href="#" class="tab-a active-a" data-id="tab1">My Request</a></li>
         <?php if(isset($templateData->has_staff_request) && $templateData->has_staff_request){?>
         <li><a href="#" class="tab-a" data-id="tab2">Staff Request</a></li>
         <?php }?>
          <?php if(isset($templateData->has_all_request) && $templateData->has_all_request){?>
         <li><a href="#" class="tab-a" data-id="tab3">All Request</a></li>
         <?php }?>
      </ul>
   </div><!--end of tab-menu-->
   <div  class="tab tab-active" data-id="tab1">
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
         
   </div><!--end of tab one--> 
   <?php if(isset($templateData->has_staff_request) && $templateData->has_staff_request){?>
   <div  class="tab " data-id="tab2">
   <table id="staff_request"  class="generaltable gtms">
  <thead>
    <tr>
      <th>Full Name</th>
      <th>Department</th>
      <th>Type</th>
      <th>Course Name</th>
      <th>Duration</th>
      <th>Price</th>
      <th>Status</th>
      <th>Request Date</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($templateData->staff_request as  $request) {
       echo '<tr>';
       echo '<td>'.$display->LinkUserDetail($request->firstname . ' '.$request->lastname, $request->userid).'</td>';
       echo '<td>'.$function->getDepartment($request->userid).'</td>';
       echo '<td>'.$request->requesttype.'</td>';
       echo '<td>'.$display->LinkDetail($request->coursename,$request->id).'</td>';
       echo '<td>'.$request->courseduration.'</td>';
       echo '<td>'.$display->showPrice($request).'</td>';
       echo '<td>'.$display->showStatus($request->status).'</td>';
       echo '<td>'.$display->showDate($request->timecreated).'</td>';
       echo '</tr>';
    }?>
  </tbody>
  </table>
   </div><!--end of tab two--> 
   <?php }?>
    <?php if(isset($templateData->has_all_request) && $templateData->has_all_request){?>
      <div  class="tab " data-id="tab3">
      <table id="all_request"  class="generaltable gtms">
  <thead>
    <tr>
      <th>Full Name</th>
      <th>Department</th>
      <th>Type</th>
      <th>Course Name</th>
      <th>Duration</th>
      <th>Price</th>
      <th>Status</th>
      <th>Request Date</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($templateData->all_request as  $request) {
       echo '<tr>';
       echo '<td>'.$display->LinkUserDetail( $request->firstname . ' '.$request->lastname, $request->userid).'</td>';
       echo '<td>'.$function->getDepartment($request->userid).'</td>';
       echo '<td>'.$request->requesttype.'</td>';
       echo '<td>'.$display->LinkDetail($request->coursename,$request->id).'</td>';
       echo '<td>'.$request->courseduration.'</td>';
       echo '<td>'.$display->showPrice($request).'</td>';
       echo '<td>'.$display->showStatus($request->status).'</td>';
       echo '<td>'.$display->showDate($request->timecreated).'</td>';
       echo '</tr>';
    }?>
  </tbody>
  </table>
   </div><!--end of tab three--> 
   <?php }?>
</div><!--end of container-->

<?php
echo $OUTPUT->footer();

