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

use stdClass;

class display
{
    /** show price of course.
     * @param object $request
     * @return string price
     */
    public function showPrice($request): string
    {
        if ($request->isfree) {
            return "Free";
        } else {
            return $request->courseprice;
        }
    }
    /** show date.
     * @param int $date
     * @return string date
     * 
    $string['strftimedatefullshort'] = '%d/%m/%y';
    $string['strftimedateshort'] = '%d %B';
    $string['strftimedatetime'] = '%d %B %Y, %I:%M %p';
    $string['strftimedatetimeshort'] = '%d/%m/%y, %H:%M';
    $string['strftimedaydate'] = '%A, %d %B %Y';
    $string['strftimedaydatetime'] = '%A, %d %B %Y, %I:%M %p';
    $string['strftimedayshort'] = '%A, %d %B';
    $string['strftimedaytime'] = '%a, %H:%M';
    $string['strftimemonthyear'] = '%B %Y';
    $string['strftimerecent'] = '%d %b, %H:%M';
    $string['strftimerecentfull'] = '%a, %d %b %Y, %I:%M %p';
    $string['strftimetime'] = '%I:%M %p
     */
    public function showDate($date, $string = 'strftimedatetimeshort'): string
    {
        if ($date > 0) {
            return  userdate($date, get_string($string, 'core_langconfig'));
        }
        return "";
    }

    /** cut text.
     * @param string $text
     * @param int $limit
     * @return string text
     */
    function limit_text($text, $limit = 10)
    {
        if (str_word_count($text, 0) > $limit) {
            $words = str_word_count($text, 2);
            $pos   = array_keys($words);
            $text  = substr($text, 0, $pos[$limit]) . '...';
        }
        return $text;
    }
    /** show price of course.
     * @param int $status
     * @return string price
     */
    public function showStatus($status, $tag = 'p'): string
    {
        $text = "";
        if ($status == 0) {
            $text = '<' . $tag . ' class="bg-warning text-white text-center rounded-lg">Request Sent</' . $tag . '>';
        } else if ($status == 1) {
            $text = '<' . $tag . ' class="bg-danger text-white text-center rounded-lg">Disapproved</' . $tag . '>';
        } else if ($status == 2) {
            $text = '<' . $tag . ' class="bg-info text-white text-center rounded-lg">Approved</' . $tag . '>';
        } else if ($status == 3) {
            $text = '<' . $tag . ' class="bg-success text-white text-center rounded-lg">Published</' . $tag . '>';
        }

        return $text;
    }
    /** link to detail.
     * @param string $text
     * @param int $id id of request
     * @return string url to detail page
     */
    public function LinkDetail($text, $id): string
    {
        global $CFG;
        $url =  $CFG->wwwroot . '/local/genashtim_tms/request_detail.php?rid=' . $id;

        return '<a href="' . $url . '" target="_blank">' . $text . '</a> ';
    }
    /** link to user report.
     * @param string $text
     * @param int $id id of request
     * @return string url to detail page
     */
    public function LinkUserDetail($text, $id): string
    {
        global $CFG;
        $url =  $CFG->wwwroot . '/local/genashtim_tms/request_staff.php?uid=' . $id;

        return '<a href="' . $url . '" target="_blank">' . $text . '</a> ';
    }

     /** link to tms report detail.
     * @param string $text
     * @param int $id id of request
     * @return string url to detail page
     */
    public function LinkTMSReportDetail($text, $id): string
    {
        global $CFG;
        $url =  $CFG->wwwroot . '/local/genashtim_tms/tms_report_detail.php?uid=' . $id;

        return '<a href="' . $url . '" target="_blank">' . $text . '</a> ';
    }
      /** link to course detail.
     * @param string $text
     * @param int $id id of request
     * @param string $name
     * @param bool $isAdmin 
     * @return string url to v page
     */
    public function LinkCourseDetail($text, $id,$name,$isAdmin=false): string
    {
        global $CFG;
        $char = strtoupper($name[0]);
        // $url = $CFG->wwwroot.'/report/progress/index.php?course='.$courseid.'&sifirst='.$char.'&silast';
        $url =  $CFG->wwwroot . '/report/completion/index.php?course=' . $id.'&sifirst='.$char.'&silast';
        
        return $isAdmin?'<a href="' . $url . '" target="_blank">' . $text . '</a> ': $text;
    }
    /** Display course field by name.
     */
    public function DisplayCourseField($course,$filedName,$isNum=false){
        if(isset($course->data) && array_key_exists($filedName,$course->data) && array_key_exists('value',$course->data[$filedName])){
            return $course->data[$filedName]['value'];
        }
        return $isNum?0:'';
    }
}
