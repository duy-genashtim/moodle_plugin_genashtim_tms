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
$PAGE->set_heading(get_string('page_tms_report', 'local_genashtim_tms'));
$PAGE->set_pagelayout('admin');
$uid = required_param('uid',PARAM_INT);
$y = optional_param("y",0,PARAM_INT);

$PAGE->set_url(new moodle_url('/local/genashtim_tms/tms_report_detail.php'),array('uid'=>$uid));


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
$previewnode = $PAGE->navigation->add(get_string('page_tms_report', 'local_genashtim_tms'), new moodle_url('/local/genashtim_tms/tms_report.php'), navigation_node::TYPE_CONTAINER);
$subnode = $previewnode->add(get_string('page_tms_report_detail', 'local_genashtim_tms'),new moodle_url('/local/genashtim_tms/request_detail.php',array('uid'=>$uid)));
$isAdmin = $function->isAdmin();
if(!$isAdmin){
    // redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php');
    if($uid == $USER->id){
      $previewnode->make_active();
    }else{
      redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php');
    }
   
}else{
  $subnode->make_active();
}


$user = $function->getUserRequest($uid);
if(!isset($user->id)){
  redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php');
}
$courses = $function->getCourseReportTMS($uid,$y);

$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/local/genashtim_tms/datatable/datatables.min.css'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/genashtim_tms/datatable/datatables.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/genashtim_tms/javascript/script.js'),true);


// $allRequests = $request->getRequestAll();
echo $OUTPUT->header();

$certificate_link = $CFG->wwwroot.'/mod/customcert/my_certificates.php?userid='.$uid;
$userProfile_link = $CFG->wwwroot.'/user/profile.php?id='.$uid;
echo $OUTPUT->heading( '<a href="'.$userProfile_link.'">'. $user->firstname . ' ' . $user->lastname .'</a>' . ' | <a href="'.$certificate_link.'" target="_blank" > Certificates</a>');
?>
<div class="row pb-4">
  <div class="col-sm-12">
    <div class="pull-right">
      <span>Enrolled Year:</span>
      <select name="year_select" id="year_select"  onchange="document.location.href = 'tms_report_detail.php?uid=<?php echo $uid;?>&y=' + this.value">
        <option value="0"> -- Show All -- </option>
        <?php 
        $thisYear = date("Y");
        
        for ($i=2020; $i <= $thisYear ; $i++) { 
          $selectedText = $i ==$y?"selected":'';
         echo '<option '.$selectedText.' value="'.$i.'">'.$i.'</option>';
        }
        ?>
      </select>
      <span class="pl-2 pr-2">
        <a class="back_link" href="export_tms_detail.php?uid=<?php echo $uid;?>">Export To Excel</a>
      </span>
    </div>
  </div>
</div>
 <table id="tms_course_detail"  class="generaltable gtms">
  <thead>
    <tr>
      <th>Name of Course</th>
      <th>Course Type</th>
      <th>Course Progress</th>
      <th>Training Hours</th>
      <th >Enrolment Date</th>
      <!-- <th class="no-sort" >Enrolment Date</th> -->
      <th>Start Date</th>
      <th>Last Access</th>
      <th>Completion</th>
      <th>Course Amount(USD)</th>
    </tr>
    
  </thead>
  <tbody>
    <?php 
    $totalHours = 0;
    $totalAmount =0;
    foreach ($courses as  $course) {
      $courseAmount =$display->DisplayCourseField($course , 'course_amount',true);
      $courseHours = $display->DisplayCourseField($course , 'training_hours');
       echo '<tr>';
       echo '<td>'.$display->LinkCourseDetail($course->fullname , $course->id,$user->firstname, $isAdmin).'</td>';
       echo '<td>'. $display->DisplayCourseField($course , 'course_type').'</td>';
       echo '<td>'.$course->courseProgress['text'].'</td>';
       echo '<td>'.$courseHours.'</td>';
       echo '<td data-sort="'.$course->usertimecreated.'">'.$display->showDate($course->usertimecreated,'strftimedatetime').'</td>';
       echo '<td data-sort="'.$course->usertimestart.'">'.$display->showDate($course->usertimestart,'strftimedatetime').'</td>';
       echo '<td>'.$course->userLastAccess.'</td>';
       echo '<td>'.$display->showDate($course->completionDate).'</td>';
       echo '<td>'.$courseAmount.'</td>';
       echo '</tr>';
       if($course->courseProgress['completed']){
          $totalAmount += $courseAmount;
          $totalHours += $courseHours;
       }
    }?>
  </tbody>
  <tfoot>
      <tr>
          <th class="font-weight-bold" colspan="3" style="text-align:right">Completed Training Hours:</th>
          <th class="font-weight-bold"><?php echo $totalHours?></th>
          <th class="font-weight-bold" colspan="4" style="text-align:right">Completed Training Amount:</th>
          <th class="font-weight-bold">$<?php echo $totalAmount?></th>
      </tr>
  </tfoot>
  </table>

<?php
echo $OUTPUT->footer();

