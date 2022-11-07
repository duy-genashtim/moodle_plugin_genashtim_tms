<?php
/**
 * Version information for Genashtim TMS.
 *
 * @package    local_genashtim_tms
 * @copyright  2022 genashtim - www.genashtim.com
 * @author     Duy Hoang
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use \local_genashtim_tms\functions;
function local_genashtim_tms_before_footer(){
   
}

function local_genashtim_tms_extend_navigation(global_navigation $navigation) {
    global  $CFG ;
    $menuItem = "Course Request|#";
    $menuItemSub1 = "-Request Course|/local/genashtim_tms/request.php";
    $menuItemSub2 = "-Request Status|/local/genashtim_tms/track_manage.php";
    $menuItemSub3 = "-TMS Report|/local/genashtim_tms/tms_report.php";
    $data_menu_items = explode("\n",$CFG->custommenuitems);
    // Delete if its existed in the custom Menu
    if (($key = array_search($menuItem, $data_menu_items)) !== false) {
        unset($data_menu_items[$key]);
    }
    if (($key = array_search($menuItemSub1, $data_menu_items)) !== false) {
        unset($data_menu_items[$key]);
    }
    if (($key = array_search($menuItemSub2, $data_menu_items)) !== false) {
        unset($data_menu_items[$key]);
    }
    if (($key = array_search($menuItemSub3, $data_menu_items)) !== false) {
        unset($data_menu_items[$key]);
    }
    // check the setting if on and user can access the plugin.
  
    $function = new functions();
    if($function->canAccessPlugin()){
        // start to add item to the end of the nav
        $data_menu_items[] = $menuItem;
        $data_menu_items[] = $menuItemSub1;
        $data_menu_items[] = $menuItemSub2;
        if($function->isAdmin()){
            $data_menu_items[] = $menuItemSub3;
        }
    }

   $CFG->custommenuitems = implode("\n", $data_menu_items );

}