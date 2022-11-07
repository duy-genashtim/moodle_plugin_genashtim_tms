<?php

/**
 * Version information for Genashtim TMS.
 *
 * @package    local_genashtim_tms
 * @copyright  2022 genashtim - www.genashtim.com
 * @author     Duy Hoang
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_genashtim_tms;

use core\oauth2\rest;
use stdClass;
use core_completion\activity_custom_completion;


class functions
{
    public static $request_status = array(0 => 'Request Sent', 1 => 'Manager Disapproved', 2 => 'Manager Approved', 3 => 'DONE');

    /** check if string end with value.
     * @param string $haystack
     * @param string $needle
     * @return bool if its end with needle
     */
    public function endsWith($haystack, $needle): bool
    {
        $length = strlen($needle);
        if (!$length) {
            return true;
        }
        return substr($haystack, -$length) === $needle;
    }
    /** get all course custom fields .
     * @param string $courseid
     * @return array course custom fields
     */
    private function get_all_custom_field($courseid): array
    {
        global $DB;
        $query = "SELECT cd.*, cf.name,cf.shortname,cf.configdata FROM {customfield_data} as cd LEFT JOIN {customfield_field} as cf ON cd.fieldid = cf.id WHERE instanceid = ? ";
        return $DB->get_records_sql($query, array($courseid));
    }
    /** get all course custom fields in better view .
     * @param string $courseId
     * @return array course custom fields
     */
    public function getCourseCustomField($courseId): array
    {
        $settings = $this->getSetting();
        $setting_array = json_decode(json_encode($settings), true);
        $courseFields = $this->get_all_custom_field($courseId);
        $returnArray = [];
        foreach ($courseFields as  $filed) {
            if ($filed->shortname == $settings->course_type_field || $filed->shortname == $settings->course_fee_field) {
                $tempArray = json_decode($filed->configdata, true);
                $value = "";

                if (array_key_exists('options', $tempArray)) {
                    $valueArray = preg_split("/\r\n|\n|\r/", $tempArray['options']);
                    array_unshift($valueArray, "");
                    $value = $valueArray[$filed->value];
                }
                $returnArray[$filed->shortname] = ['name' => $filed->name, 'value' => $value];
            } else if (in_array($filed->shortname, $setting_array)) {
                $returnArray[$filed->shortname] = ['name' => $filed->name, 'value' => $filed->value];
            }
        }
        return $returnArray;
    }

    /** build users report for TMS .
     * @return array users
     */
    public function getUsersReport(): array
    {
        $users = $this->getAllUserRequest();
        $settings = $this->getSetting();
        $years = $this->get3Years();
        
        foreach ($users as $key => $user) {
            $totalTrainingHours = 0;
            $totalAmount = 0;
            $totalCompleted = 0;
            $yearData = [];

            $coursesEnrolled = $this->getAllCourseEnrolled($user->id);
           
            foreach ($coursesEnrolled as  $course) {
                // count completed
                $courseCompletedDate = $this->getCourseCompleteDate($user->id,$course->id);
                if($courseCompletedDate > 0){
                    $totalCompleted ++;
                }
                // count total training hours and amount
                if (isset($course->data) && $courseCompletedDate > 0) {
                    if(array_key_exists($settings->training_hours_field,$course->data)){
                        $totalTrainingHours += (float) $course->data[$settings->training_hours_field]['value'];
                    }

                    if(array_key_exists($settings->course_fee_field,$course->data) && array_key_exists($settings->amount_field,$course->data) && trim($course->data[$settings->course_fee_field]['value']) == "Paid" ){
                        $totalAmount += (float) $course->data[$settings->amount_field]['value'];
                    }
                    // count by year
                    $enrolledYear = date("Y",$course->usertimecreated);
                    if(in_array($enrolledYear,$years)){
                        $yearKey = array_search($enrolledYear,$years);
                        $yearData[$years[$yearKey]]['year'] =  $years[$yearKey];
                        // make sure training hours has value
                        if(array_key_exists($settings->training_hours_field,$course->data)){
                            $yearData[$years[$yearKey]]['value'] += (float) $course->data[$settings->training_hours_field]['value'];
                        }
                        
                    }
                }
               
            }
            $users[$key]->totalCourseEnrolled = count($coursesEnrolled);
            $users[$key]->totalTrainingHours = $totalTrainingHours;
            $users[$key]->totalAmount = $totalAmount;
            $users[$key]->totalCompleted = $totalCompleted;
            $users[$key]->yearData = $yearData;
        }
        return $users;
    }
     /** build Course report detail Data for TMS 
      * @param int $userId
      * @param int $year/ $year ==0 => show all 
     * @return array courses
     */
    public function getCourseReportTMS($userId,$year =0):array{
        $coursesEnrolled = $this->getAllCourseEnrolled($userId);
        foreach ($coursesEnrolled as $ckey => $course) {
          
            if($year > 0 && date("Y",$course->usertimestart) != $year){
                unset( $coursesEnrolled[$ckey]);
                continue;
            }
            $coursesEnrolled[$ckey]->userEnrolledYear=  date("Y",$course->usertimestart);
            $coursesEnrolled[$ckey]->courseProgress = $this->course_progress($course,$userId);
            $coursesEnrolled[$ckey]->completionDate = $this->getCourseCompleteDate($userId,$course->id);
            $coursesEnrolled[$ckey]->userLastAccess = $this->getUserLastsAccessCourse($course->id,$userId);
        }
        return $coursesEnrolled;
    }
      /** function get 3 previous years  .
     * @return array years
     */
    public function get3Years():array{
        $years = [];
        for ($i=2; $i >= 0; $i--) { 
           $years[] = date("Y",strtotime("-".$i." year"));
        }
        return $years;
    }

    /** function get all course enrolled  .
     * @param string $userId
     * @return array courses
     */
    public function getAllCourseEnrolled($userId): array
    {
        global $DB;
        $enrolled = $DB->get_records_sql('SELECT c.* , ctx.id AS ctxid, ctx.path AS ctxpath, ctx.depth AS ctxdepth, ctx.contextlevel AS ctxlevel, ctx.instanceid AS ctxinstance, en.timestart AS usertimestart, en.timecreated AS usertimecreated FROM {course} c JOIN (SELECT DISTINCT e.courseid, ue.timestart, ue.timecreated  FROM {enrol} e JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = ?) ) en ON (en.courseid = c.id) LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = 50) WHERE c.id <> 1 ORDER BY c.visible DESC,c.sortorder ASC', array($userId));
        foreach ($enrolled as  $key => $course) {
            $enrolled[$key]->data = $this->getCourseCustomField($course->id);
        }
        return $enrolled;
    }
    /** function get all course enrolled  .
     * @param string $userId
     * @param string $courseId
     * @return int courses Completion Date
     */
    public function getCourseCompleteDate($userId, $courseId): int
    {
        global $DB;
        $completion =  $DB->get_record('course_completions', ['userid' => $userId, 'course' => $courseId]);
        
        if (isset($completion->timecompleted) && strtotime("Y",$completion->timecompleted) >2000 ) {
            return $completion->timecompleted;
        }
        return 0;
    }

    function getCourseTimeCreate($userid,$courseid){
        global $DB;
        $sql = "SELECT ue.timestart,ue.timecreated FROM {user_enrolments} ue LEFT JOIN {enrol} e ON ue.enrolid = e.id WHERE ue.userid = :userid AND e.courseid = :courseid ";
        $result = $DB->get_record_sql($sql, array("userid"=>$userid,"courseid"=>$courseid));
        if(isset($result->timecreated)){
            return $result->timecreated;
        }
        return 0;        
    }

    /** get all user custom fields .
     * @param string $userid
     * @return array course custom fields
     *  [manager_email] => stdClass Object
        (
            [id] => 284
            [userid] => 2
            [fieldid] => 4
            [data] => duy@genashtim.com
            [dataformat] => 0
            [shortname] => people_manager
            [name] => people manager
        )

    [is_manager] => stdClass Object
        (
            [id] => 285
            [userid] => 2
            [fieldid] => 5
            [data] => 0
            [dataformat] => 0
            [shortname] => is_manager
            [name] => Is Manager
        )
     */
    public function get_all_user_field($userid)
    {
        global $DB;

        $settings = $this->getSetting();
        $query = "SELECT uid.*, uif.shortname, uif.name FROM {user_info_data} uid JOIN {user_info_field} uif ON uid.fieldid = uif.id WHERE uif.shortname IN('" . $settings->is_manager_field . "','" . $settings->manager_email_field . "','" . $settings->department_field . "') AND uid.userid = ?";
        $returnData = $DB->get_records_sql($query, array($userid));
        $arrayData =  array();
        foreach ($returnData as $value) {

            if ($value->shortname == $settings->is_manager_field) {
                $arrayData['is_manager'] = $value;
            }

            if ($value->shortname == $settings->manager_email_field) {
                $arrayData['manager_email'] = $value;
            }
            if ($value->shortname == $settings->department_field) {
                $arrayData['department'] = $value;
            }
        }

        return $arrayData;
    }
    public function getSetting()
    {
        $settings = get_config('local_genashtim_tms');
        if (!isset($settings->is_manager_field) || trim($settings->is_manager_field) == "") {
            $settings->is_manager_field = 'is_manager';
        }
        if (!isset($settings->manager_email_field) || trim($settings->manager_email_field) == "") {
            $settings->manager_email_field = 'people_manager';
        }
        if (!isset($settings->department_field) || trim($settings->department_field) == "") {
            $settings->department_field = 'department';
        }
        if (!isset($settings->lms_email_limit) || trim($settings->lms_email_limit) == "") {
            $settings->lms_email_limit = '@genashtim.com';
        }
        if (!isset($settings->lms_email) || trim($settings->lms_email) == "") {
            $settings->lms_email = get_string('default_lms_email', 'local_genashtim_tms');
        }
        if (!isset($settings->hr_email) || trim($settings->hr_email) == "") {
            $settings->hr_email =  get_string('default_hr_email', 'local_genashtim_tms');
        }
        if (!isset($settings->admin_emails)) {
            $settings->admin_emails =  '';
        }
        if (!isset($settings->course_type_field) || trim($settings->course_type_field) == "") {
            $settings->course_type_field =  'course_type';
        }
        if (!isset($settings->training_hours_field) || trim($settings->training_hours_field) == "") {
            $settings->training_hours_field =  'training_hours';
        }
        if (!isset($settings->course_fee_field) || trim($settings->course_fee_field) == "") {
            $settings->course_fee_field =  'course_fee';
        }
        if (!isset($settings->amount_field) || trim($settings->amount_field) == "") {
            $settings->amount_field =  'course_amount';
        }
        return $settings;
    }
    private function isPluginAdmin()
    {
        global $USER;
        $settings = $this->getSetting();
        $adminList = array_map('trim', explode(',', $settings->admin_emails));
        if (isset($USER->email) && trim($USER->email) !="" && trim($settings->admin_emails) !="" && in_array($USER->email, $adminList)) {
            return true;
        }
        return false;
    }
    public function isAdmin()
    {
        if (is_siteadmin() || $this->isPluginAdmin()) {
            return true;
        } else {
            return false;
        }
    }

    public function  canAccessPlugin()
    {
        global $USER;
        $settings = $this->getSetting();
        if ($this->isAdmin() && isset($settings->enabled) && $settings->enabled == 1) {
            return true;
        }

        $needle = $settings->lms_email_limit; //"@sbf-pcpi.com";
        // $needle = "@genashtim.com";
        if (!isset($settings->enabled) || $settings->enabled == 0 || !$this->endsWith($USER->email, $needle)) {
            return false;
        }
        return true;
    }

    public function canSendRequest($userId)
    {
        global $USER;
        $field = $this->get_all_user_field($userId);

        $settings = $this->getSetting();
        if (!$this->endsWith($USER->email, $settings->lms_email_limit)) {
            return false;
        }

        if (!isset($field['manager_email']) || !isset($field['manager_email']->data) || trim($field['manager_email']->data) == '') {
            return false;
        }
        return true;
    }

    /** Main function to check if user is manager
     * @param int $userId
     * @return bool true or false
     */
    public function isManager($userId): bool
    {
        $field = $this->get_all_user_field($userId);
        if (!isset($field['is_manager']) || !isset($field['is_manager']->data) || $field['is_manager']->data == 0) {
            return false;
        }
        return true;
    }

    /** Main function to get Department of user
     * @param int $userId
     * @return string department
     */
    public function getDepartment($userId): string
    {
        $field = $this->get_all_user_field($userId);

        if (!isset($field['department']) || !isset($field['department']->data)) {
            return '';
        } else {
            return $field['department']->data;
        }
    }
    /** Main function to send email.
     * @param string $subject
     * @param string $body
     * @param string $receiver
     * @return int email sent or not
     */
    public function sendEmail($subject, $body, $receiver)
    {
        $mail = get_mailer();
        $mail->FromName = get_string('send_name', 'local_genashtim_tms');
        $mail->From     =  $mail->Username;
        $mail->IsHTML(true);
        // $mail->SMTPDebug  = 2; 
        $mail->clearAllRecipients();
        $mail->addAddress($receiver[0], $receiver[1]);
        $mail->Subject = $subject;
        $mail->Body = $body;

        return $mail->send();
    }

    private function courseDetailBody($request)
    {
        $body = '<p> Course Name: ' . $request->coursename . '</p>
    <p> Course Description:' . $request->coursedes . '</p>
    <p> Duration:' . $request->courseduration . '</p>
    <p> Link:' . $request->courselink . '</p>';

        if ($request->isfree == 1) {
            $body .= "<p>Course Fee: Free </p>";
        } else {
            $body .= "<p>Course Fee: Paid </p>";
            $body .= "<p>Amount: " . $request->courseprice . '</p>';
        }
        $body .= "<p>Reason: " . $request->reason . "</p><br>";
        return  $body;
    }

    function sendCourseRequestEmail($user, $request)
    {
        global $CFG;
        $setting = $this->getSetting();
        $subject = "Genashtim Course/Training Request - " . $request->requesttype;
        $link = $CFG->wwwroot . '/local/genashtim_tms/request_process.php?id=' . $request->id . '&email=' . $request->manageremail . '&cstep=0&status=';

        $body = 'Hi Manager,
    <br>
    <p> Staff Name: ' . $user->firstname . ' ' . $user->lastname . ' </p>
    <p>  Department: ' . $this->getDepartment($request->userid) . ' </p>
    <p>  Would like to request to take the course/training below:</p>';
        $body .= $this->courseDetailBody($request);
        $body .= ' <p style="text-align: center ;">
<table>
    <tr>
    <td bgcolor="#82B541" class="btn-link-1" height="42" align="center" style="border-radius:50px;font-family: \'Open Sans\', Arial, sans-serif; color:#FFFFFF; font-size:14px;font-weight: bold;letter-spacing: 1px;padding-left: 25px;padding-right: 25px; text-align: center;"><a href="' . $link . '2" style="text-decoration:none; color:#ffffff;">Approved</a></td>
        <td></td>
    <td bgcolor="#b71010" class="btn-link-1" height="42" align="center" style="border-radius:50px;font-family: \'Open Sans\', Arial, sans-serif; color:#FFFFFF; font-size:14px;font-weight: bold;letter-spacing: 1px;padding-left: 25px;padding-right: 25px; text-align: center;"><a href="' . $link . '1" style="text-decoration:none; color:#ffffff;">Disapproved</a></td>
    </tr>
</table>
</p><br>MLU Admin';

        $mail = get_mailer();
        $mail->FromName = get_string('send_name', 'local_genashtim_tms');
        $mail->From     =  $mail->Username;
        $mail->IsHTML(true);
        $mail->SMTPDebug  = 0;
        $mail->clearAllRecipients();
        $mail->addAddress($request->manageremail, '');
        $mail->addCC($setting->hr_email, '');
        // $mail->addCC('duyuno9@gmail.com','Duy Hoang');// this will be HR for testing
        // $mail->addCC('hr@genashtim.com','HR Genashtim');

        $mail->Subject = $subject;
        $mail->Body = $body;

        return $mail->send();
    }

    public function get_user($id)
    {
        global $DB;
        return $DB->get_record('user', array('id' => $id));
    }

    public function sendDisapprovedEmail($request)
    {

        $user = $this->get_user($request->userid);
        $subject = "Genashtim Course/Training Request - " . $request->requesttype . " - Disapproved";
        $body = "Hi " . $user->firstname . ",<br>";
        $body .= " <p>Your request has been disapproved by manager.</p>";
        $body .= " <p>Request detail:</p>";
        $body .= $this->courseDetailBody($request);
        $body .= " <br>MLU Admin";

        $mail = get_mailer();
        $mail->FromName = get_string('send_name', 'local_genashtim_tms');
        $mail->From     =  $mail->Username;
        $mail->IsHTML(true);
        $mail->SMTPDebug  = 0;
        $mail->clearAllRecipients();
        $mail->addAddress($user->email, $user->firstname . " " . $user->lastname);
        //    $mail->addAddress('duyuno9@gmail.com','Duy Hoang');//
        // $mail->addCC('hr@genashtim.com','HR Genashtim');


        $mail->Subject = $subject;
        $mail->Body = $body;

        return $mail->send();
    }

    function sendApprovedEmail($request)
    {

        $user = $this->get_user($request->userid);
        $setting = $this->getSetting();
        $subject = "Genashtim Course/Training Request - " . $request->requesttype . " - Approved";
        $body = 'Hi LMS Team,
    <br>
    <p>This request below has been approved by the manager:</p>
    <p> Staff Name: ' . $user->firstname . ' ' . $user->lastname . ' </p>
    <p>  Department:' . $this->getDepartment($request->userid) . ' </p>';
        $body .= $this->courseDetailBody($request);

        $body .= '<br>MLU Admin';

        $mail = get_mailer();
        $mail->FromName = get_string('send_name', 'local_genashtim_tms');
        $mail->From     =  $mail->Username;
        $mail->IsHTML(true);
        $mail->SMTPDebug  = 0;
        $mail->clearAllRecipients();
        // $mail->addAddress('duyuno9@gmail.com','Duy Hoang');
        $mail->addAddress($setting->lms_email, '');
        $mail->addCC($user->email, $user->firstname . ' ' . $user->lastname);
        // $mail->addCC('duyuno9@gmail.com','Duy Hoang');//this is HR email on testing

        $mail->addCC($setting->hr_email, '');
        // $mail->addCC('hr@genashtim.com','HR Genashtim');

        $mail->Subject = $subject;
        $mail->Body = $body;

        return $mail->send();
    }

    public function getAllUserRequest()
    {
        global $DB;
        $settings = $this->getSetting();
        $email = $settings->lms_email_limit;
        $sql = "SELECT * FROM {user} WHERE email LIKE '%" . $email . "' ORDER BY firstname ASC";
        return $DB->get_records_sql($sql);
    }

    public function getUserRequest($userId)
    {
        global $DB;
        return $DB->get_record('user',array('id'=>$userId));
    }

    
public function course_progress($course, $userid){
    global $CFG;
    require_once($CFG->libdir . '/completionlib.php');
  
    $coursprogress = [];
    $completion = new \completion_info($course);
    $iscomplete = $completion->is_course_complete($userid);
    $percentage = \core_completion\progress::get_course_progress_percentage($course, $userid);
  
    if (!is_null($percentage)) {
        $percentage = floor($percentage);
    } else {
      $percentage = 0;
    }
    $percentageText = $percentage."%";
    $coursprogress['completed'] = $completion->is_course_complete($userid);
    $coursprogress['progress'] = $percentage;
  
    if ($percentage == 100 && $iscomplete) {
      $color = 'progress-bar-success';
      $text = 'Completed';
      $progress = 'Completed';
      $completed = true;
    } else {
      $color = 'progress-bar-info';
      $text = '<span>'.$percentage.'%</span>';
      $progress = '';
      $completed = false;
    }
    
    $text =  '<div class="progress">
                <div class="progress-bar '.$color.'" role="progressbar" aria-valuenow="'.$percentage.'"
                     aria-valuemin="0" aria-valuemax="100" style="width:'.$percentage.'%">
                  '.$text.'
                </div>
              </div>';
    
    return array( 'text' => $text, 'percent' => $percentage, 'progress' => $progress,'percentText'=>  $percentageText, 'completed'=>$completed );
  }

  public function getUserLastsAccessCourse($courseid, $userid){
    global $DB;
    $lastAccess =  $DB->get_record('user_lastaccess', ['userid' => $userid, 'courseid'=>$courseid]);
    //return show_date($lastAccess->timeaccess);
    $date1 = new \DateTime();
    @$date1->setTimestamp($lastAccess->timeaccess);
    $date2 = new \DateTime('NOW');
    $interval = $date1->diff($date2);
    if(isset($lastAccess->timeaccess) AND $lastAccess->timeaccess > 100){
        return   $interval->days . " days " . $interval->h." hours"; 
    }else{
        return 'Never';
    }

}
    // define('REQUEST_STATUS',array(0=>'Request Sent',1=>'Manager Disapproved',2=>'Manager Approved',3=>'DONE'));

}
