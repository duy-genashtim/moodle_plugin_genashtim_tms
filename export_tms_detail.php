<?php 
set_time_limit ( 0 );
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
$uid = required_param('uid',PARAM_INT);
$PAGE->set_url(new moodle_url('/local/genashtim_tms/export_tms_detail.php'),array('uid'=>$uid));

use \local_genashtim_tms\request;
use \local_genashtim_tms\functions;
use \local_genashtim_tms\display;

$request = new request();
$function = new functions();
$display = new display();

$user = $function->getUserRequest($uid);
if(!isset($user->id)){
  redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php');
}
$userFields = $function->get_all_user_field($uid);
$courses = $function->getCourseReportTMS($uid);
$fullName = $user->firstname.' '.$user->lastname;
$slugName = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $fullName)));
require_once( 'vendor/autoload.php' );

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Set document properties
$objPHPExcel->getProperties()->setCreator("Genashtim TMS")
  ->setLastModifiedBy("Genashtim TMS")
  ->setTitle("Genashtim TMS - User Report Detail")
  ->setSubject("")
  ->setDescription("")
  ->setKeywords("")
  ->setCategory("");

$objPHPExcel->setActiveSheetIndex(0)
  ->setCellValue('A1', $fullName );
$objPHPExcel->getActiveSheet()
  ->setCellValue('A2', 'Email: '. $user->email);
$objPHPExcel->getActiveSheet()
  ->setCellValue('A3', 'Department: '. $userFields['department']->data );
$objPHPExcel->getActiveSheet()
  ->setCellValue('A4', 'Manager Email: '.$userFields['manager_email']->data );

$objPHPExcel->getActiveSheet()
    ->getStyle('A1:A4')
    ->applyFromArray(
        array(
            'font' => array(
                'bold' => true
            )
        )
    );

$objPHPExcel->getActiveSheet()
  ->setCellValue('A6', 'Name of Course')
  ->setCellValue('B6', 'Course Type')
  ->setCellValue('C6', 'Course Progress')
  ->setCellValue('D6', 'Training Hours')
  ->setCellValue('E6', 'Enrolment Date')
  ->setCellValue('F6', 'Start Date')
  ->setCellValue('G6', 'Last Access')
  ->setCellValue('H6', 'Completion')
  ->setCellValue('I6', 'Course Amount(USD)');

$objPHPExcel->getActiveSheet()->getStyle('A6:I6')->getAlignment()->setWrapText(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(22);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(22);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(22);

$objPHPExcel->getActiveSheet()
    ->getStyle('A6:I6')
    ->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '3a5e88')
            ),
            'font' => array(
                'bold' => true,
                'color' => array('rgb' => 'FFFFFF'),
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => true
            )
        )
    );

$row = 7;

$totalHours = 0;
$totalAmount =0;

foreach ($courses as $course) {
  $courseAmount =$display->DisplayCourseField($course , 'course_amount',true);
  $courseHours = $display->DisplayCourseField($course , 'training_hours');

  if($course->courseProgress['completed']){
    $totalAmount += $courseAmount;
    $totalHours += $courseHours;
 }
  
  $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$row, $course->fullname );
  $objPHPExcel->getActiveSheet()->setCellValue('B'.$row, $display->DisplayCourseField($course , 'course_type'));
  $objPHPExcel->getActiveSheet()->setCellValue('C'.$row, ( $course->courseProgress['completed'] ? 'Completed' : $course->courseProgress['percentText'] ) );
  $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$row, $courseHours, PHPExcel_Cell_DataType::TYPE_NUMERIC );
  $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$row, $display->showDate($course->usertimecreated,'strftimedatetime') );
  $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$row, $display->showDate($course->usertimestart,'strftimedatetime') );
  $objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$row, $course->userLastAccess );
  $objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$row, $display->showDate($course->completionDate) );
  $objPHPExcel->getActiveSheet()->setCellValueExplicit('I'.$row, $courseAmount, PHPExcel_Cell_DataType::TYPE_NUMERIC );

  $objPHPExcel->getActiveSheet()->getStyle('A'.$row)->getAlignment()->setWrapText(true);

//   $objPHPExcel->getActiveSheet()
//       ->getStyle('I'.$row)
//       ->getNumberFormat()
//       ->setFormatCode('$###,##0.00');

  if( $course->courseProgress['completed'] ) {
    $objPHPExcel->getActiveSheet()
        ->getStyle('C'.$row)
        ->applyFromArray(
            array(
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '5CB85C')
                )
            )
        );
  }
  
  ++$row;
}

$objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$row, 'Completed Training Hours:' );
$objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$row, $totalHours );
$objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$row, 'Completed Training Amount:' );
$objPHPExcel->getActiveSheet()->setCellValue('I'.$row, $totalAmount );

$objPHPExcel->getActiveSheet()
    ->getStyle('A'.$row.':I'.$row)
    ->applyFromArray(
        array(
            'font' => array(
                'bold' => true,
            ),
        )
    );

$objPHPExcel->getActiveSheet()
    ->getStyle('C'.$row)
    ->applyFromArray(
        array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        )
    );
$objPHPExcel->getActiveSheet()->getStyle('A'.$row.':I'.$row)->getAlignment()->setWrapText(true);

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('User Detail Report');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

  // Redirect output to a client's web browser (Excel5)
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment;filename="'.$slugName.'-tms-detail.xls"');
  header('Cache-Control: max-age=0');
  
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');


$objWriter->save('php://output');

exit;


?>