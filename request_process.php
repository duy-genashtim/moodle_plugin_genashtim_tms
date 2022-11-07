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

$id = optional_param('id',0,PARAM_INT);
$email = optional_param('email','',PARAM_EMAIL);
$step = optional_param('cstep',-1,PARAM_INT);
$status = optional_param('status',0,PARAM_INT);

$PAGE->set_url(new moodle_url('/local/genashtim_tms/request_process.php',array('id'=>$id,'email'=>$email,'cstep'=>$step,'status'=>$status)));
require_login();


use \local_genashtim_tms\request;
use \local_genashtim_tms\functions;

// require_once($CFG->dirroot . '/local/genashtim_tms/classes/functions.php');

$request = new request();
$function = new functions();

// $link = $CFG->wwwroot.'/local/genashtim_tms/request_process.php?id='.$request->id.'&email='.$request->manageremail.'&cstep=0&status=';


if($id == 0 || $email =='' || $step ==-1 || $status==0){
    redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php',"The request does not exist.");
}else{
    $requestDetail = $request->getRequestForApproval($id, $email,$step);
    if(!isset($requestDetail->id) || $step >= $status){
        redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php',"The request does not exist.");
    }else{
        // for manager
        $requestDetail->status = $status;
        $requestDetail->timeupdated = time();
        if($status < 3 && $USER->email == $email){
            $text = $status == 2? "Approved":"Disapproved";
            if($request->updateRequest($requestDetail)){
                if($status == 1){
                    $function->sendDisapprovedEmail($requestDetail);
                }else{
                    $function->sendApprovedEmail($requestDetail);
                }
                $string = "The request for ". $requestDetail->coursename ." has been ". $text;
                redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php', $string );
            }
        }else if($status == 3){
            // Site admin after added the course to site
            if($request->updateRequest($requestDetail)){
              
                redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php' );
            }
            
        }

    }
    redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php',"The request does not exist.");
}