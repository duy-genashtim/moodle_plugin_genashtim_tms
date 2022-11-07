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

$PAGE->set_url(new moodle_url('/local/genashtim_tms/export_tms_report.php'));
$PAGE->set_context(context_system::instance());
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
    redirect($CFG->wwwroot.'/local/genashtim_tms/track_manage.php');
}
$usersReport = $function->getUsersReport();
$years = $function->get3Years();



// $rid = required_param('rid',PARAM_INT);
require_once( 'vendor/autoload.php' );

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Set document properties
$objPHPExcel->getProperties()->setCreator("Genashtim TMS")
  ->setLastModifiedBy("Genashtim TMS")
  ->setTitle("Genashtim TMS - User Report")
  ->setSubject("")
  ->setDescription("")
  ->setKeywords("")
  ->setCategory("");

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
  ->setCellValue('A1', 'First name')
  ->setCellValue('B1', "Last name")
  ->setCellValue('C1', 'Email')
  ->setCellValue('D1', 'First Login')
  ->setCellValue('E1', 'Last Login')
  ->setCellValue('F1', 'Num Of Courses')
  ->setCellValue('G1', 'Completed Course')
  ->setCellValue('H1', 'Total Course Amount')
  ->setCellValue('I1', 'Training Hours '.$years[0])
  ->setCellValue('J1', 'Training Hours '.$years[1])
  ->setCellValue('K1', 'Training Hours '.$years[2])
  ->setCellValue('L1', 'Total Training Hours');

$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getAlignment()->setWrapText(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(45);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(28);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(28);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(14);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(11);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(11);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(14);


$objPHPExcel->getActiveSheet()
    ->getStyle('A1:L1')
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

$row = 2;
foreach ($usersReport as $user) {
  $year1Data = (array_key_exists($years[0],$user->yearData) && array_key_exists('value',$user->yearData[$years[0]]))?$user->yearData[$years[0]]['value']:0;
  $year2Data = (array_key_exists($years[1],$user->yearData) && array_key_exists('value',$user->yearData[$years[1]]))?$user->yearData[$years[1]]['value']:0;
  $year3Data = (array_key_exists($years[2],$user->yearData) && array_key_exists('value',$user->yearData[$years[2]]))?$user->yearData[$years[2]]['value']:0;
  

  $objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $user->firstname );
  $objPHPExcel->getActiveSheet()->setCellValue('B'.$row, $user->lastname );
  $objPHPExcel->getActiveSheet()->setCellValue('C'.$row, $user->email );
  $objPHPExcel->getActiveSheet()->setCellValue('D'.$row, $display->showDate($user->firstaccess,'strftimedatetime') );
  $objPHPExcel->getActiveSheet()->setCellValue('E'.$row, $display->showDate($user->lastaccess,'strftimedatetime'));
  $objPHPExcel->getActiveSheet()->setCellValue('F'.$row, $user->totalCourseEnrolled );
  $objPHPExcel->getActiveSheet()->setCellValue('G'.$row, $user->totalCompleted );
  $objPHPExcel->getActiveSheet()->setCellValue('H'.$row, $user->totalAmount );
  $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,  $year1Data );
  $objPHPExcel->getActiveSheet()->setCellValue('J'.$row,  $year2Data );
  $objPHPExcel->getActiveSheet()->setCellValue('K'.$row, $year3Data );
  $objPHPExcel->getActiveSheet()->setCellValue('L'.$row, $user->totalTrainingHours );

  $objPHPExcel->getActiveSheet()->getStyle('H'.$row)->getNumberFormat()->setFormatCode('$###,##0.00');

  ++$row;
}
// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('TMS Users Report');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
 // Redirect output to a client's web browser (Excel5)
 header('Content-Type: application/vnd.ms-excel');
 header('Content-Disposition: attachment;filename="genashtim-tms-users-report.xls"');
 header('Cache-Control: max-age=0');
 
 $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
 $objWriter->save('php://output');

 exit;
