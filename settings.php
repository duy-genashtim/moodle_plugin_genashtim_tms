<?php
/**
 * Version information for Genashtim TMS.
 *
 * @package    local_genashtim_tms
 * @copyright  2022 genashtim - www.genashtim.com
 * @author     Duy Hoang
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if ($hassiteconfig) { // needs this condition or there is error on login page

    $settings = new admin_settingpage('local_genashtim_tms', get_string('pluginname', 'local_genashtim_tms'));
    $settings->add(new admin_setting_configcheckbox('local_genashtim_tms/enabled', get_string('activate', 'local_genashtim_tms'),
        get_string('activate_des', 'local_genashtim_tms'), 0));
    $settings->add(new admin_setting_configtext('local_genashtim_tms/manager_email_field', get_string('manager_email', 'local_genashtim_tms'),
    get_string('manager_email_des', 'local_genashtim_tms'), 'people_manager'));
    $settings->add(new admin_setting_configtext('local_genashtim_tms/admin_emails', get_string('admin_emails', 'local_genashtim_tms'),
    get_string('admin_emails_des', 'local_genashtim_tms'), ''));
    $settings->add(new admin_setting_configtext('local_genashtim_tms/department_field', get_string('department_field', 'local_genashtim_tms'),
    get_string('department_field_des', 'local_genashtim_tms'), 'department'));
    $settings->add(new admin_setting_configtext('local_genashtim_tms/is_manager_field', get_string('is_manager', 'local_genashtim_tms'),
    get_string('is_manager_des', 'local_genashtim_tms'), 'is_manager'));
    $settings->add(new admin_setting_configtext('local_genashtim_tms/lms_email', get_string('lms_email', 'local_genashtim_tms'),
    get_string('lms_email_des', 'local_genashtim_tms'), get_string('default_lms_email', 'local_genashtim_tms')));
    $settings->add(new admin_setting_configtext('local_genashtim_tms/hr_email', get_string('lms_hr_email', 'local_genashtim_tms'),
    get_string('lms_hr_email_des', 'local_genashtim_tms'), get_string('default_hr_email', 'local_genashtim_tms')));

    $settings->add(new admin_setting_configtext('local_genashtim_tms/lms_email_limit', get_string('lms_email_limit', 'local_genashtim_tms'),
    get_string('lms_email_limit_des', 'local_genashtim_tms'), '@genashtim.com'));

    $settings->add(new admin_setting_configtext('local_genashtim_tms/course_type_field', get_string('course_type_field', 'local_genashtim_tms'),
    get_string('course_type_des', 'local_genashtim_tms'), 'course_type'));
    $settings->add(new admin_setting_configtext('local_genashtim_tms/training_hours_field', get_string('training_hours_field', 'local_genashtim_tms'),
    get_string('training_hours_des', 'local_genashtim_tms'), 'training_hours'));
    $settings->add(new admin_setting_configtext('local_genashtim_tms/course_fee_field', get_string('course_fee_field', 'local_genashtim_tms'),
    get_string('course_fee_des', 'local_genashtim_tms'), 'course_fee'));
    $settings->add(new admin_setting_configtext('local_genashtim_tms/amount_field', get_string('amount_field', 'local_genashtim_tms'),
    get_string('amount_des', 'local_genashtim_tms'), 'course_amount'));

     $ADMIN->add('localplugins', $settings);

    // $ADMIN->add('localplugins', new admin_category('local_message_category', get_string('pluginname', 'local_message')));

    // $settings = new admin_settingpage('local_message', get_string('pluginname', 'local_message'));
    // $ADMIN->add('local_message_category', $settings);

    // $settings->add(new admin_setting_configcheckbox('local_message/enabled',
    //     get_string('setting_enable', 'local_message'), get_string('setting_enable_desc', 'local_message'), '1'));

    // $ADMIN->add('local_message_category', new admin_externalpage('local_message_manage', get_string('manage', 'local_message'),
    //     $CFG->wwwroot . '/local/message/manage.php'));
}