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
use dml_exception;
use stdClass;
class request{
     /** Insert the data into our database table.
     * @param object $formData
     * @param object $userData
     * @return int id if successful
     */
  public function insertRequestDB($formData,$userData):int
  {
        global $DB;
        $isfree = !isset( $formData->isFree)?0:1;
        $price = isset( $formData->courseFee)?$formData->courseFee:"Free";
        
        $record = new stdClass();
        $record->userid = $userData->id;
        $record->manageremail = $userData->manageremail;
        $record->requesttype = $formData->courseType;
        $record->coursename = $formData->courseName;
        $record->coursedes = $formData->courseDesc;
        $record->courseduration = $formData->courseDuration;
        $record->courselink = $formData->courseLink;
    
        $record->isfree =  $isfree;
        $record->courseprice = $price;
        $record->reason = $formData->courseReason;
        $record->status = 0;
        $record->timecreated = time();
        $record->timeupdated = time();
    
       return  $DB->insert_record('local_genashtim_tms',$record,true);
    
     }
     /** Gets all record that user sent
     * @param int $userid the user 
     * @return array of request
     */
    public function getRequestByUserId($userid):array{
        global $DB;
        return $DB->get_records('local_genashtim_tms', array('userid'=>$userid),'id  DESC');
     }
 /** Gets all record by Id
     * @param int $requestID
     * @return object of request
     */
    public function getRequestById($id):object{
      global $DB;
      return $DB->get_record('local_genashtim_tms', array('id'=>$id));
   }

    /** Gets all record by Id with User
     * @param int $requestID
     * @return object of request
     */
    function getRequestAndUserById($id):object{
      global $DB;
      $sql = "SELECT a.*, u.firstname, u.lastname,u.email, u.department FROM {local_genashtim_tms} a JOIN {user} u ON a.userid = u.id WHERE a.id = ? ORDER BY  a.id DESC";
      return $DB->get_record_sql($sql, array($id));
     }
    /** Gets all record by Id
     * @param int $id id of the request
     * @param string $email email of the request
     * @param int $status current status of the request
     * @return object of request
     */
    public function getRequestForApproval($id, $email,$status):object{
      global $DB;
      $sql = "SELECT * FROM {local_genashtim_tms} a WHERE a.id = {$id} AND a.status = {$status} AND a.manageremail LIKE '{$email}' "; 
      $request = $DB->get_record_sql($sql,array());
      if($request){
         return $request;
      }
      return new stdClass();
      // return $DB->get_record('local_genashtim_tms', array('id'=>$id,$DB->sql_compare_text('manageremail')=>$DB->sql_compare_text(':'.$email), 'status'=>$status));
   }

     /** Gets all record for site admin to check
     * @return array of request
     */
    public function getRequestAll():array{
        global $DB;
        return $DB->get_records('local_genashtim_tms', array(),'id  DESC');
    
     }

      /** Gets all record for site admin to check
     * @return array of request
     */
    public function getRequestAllWithName():array{
      global $DB;
      $sql = "SELECT a.*, u.firstname, u.lastname,u.email, u.department FROM {local_genashtim_tms} a JOIN {user} u ON a.userid = u.id ORDER BY  a.id DESC";
      // return $DB->get_records('local_genashtim_tms', array(),'id  DESC');
      return $DB->get_records_sql($sql,array());
  
   }
      /** Gets all record that user manage
     * @param string $email the user 
     * @return array of request
     */
     function getRequest4Manager($email):array{
      global $DB;
      $sql = "SELECT a.*, u.firstname, u.lastname,u.email, u.department FROM {local_genashtim_tms} a JOIN {user} u ON a.userid = u.id WHERE a.manageremail = ? ORDER BY  a.id DESC";
        return $DB->get_records_sql($sql, array($email));
     }

       /** Gets all record by Id
     * @param object $request object of the new request
     * @return bool of request update
     */
    public function updateRequest($request):bool{
      global $DB;
      return $DB->update_record('local_genashtim_tms', $request);
   }

     /** Delete record by Id
     * @param int $id of the request
     * @return bool of request update
     */
    public function deleteRequest($id):bool{
      global $DB;
      return $DB->delete_records('local_genashtim_tms', array('id'=>$id));
   }
}