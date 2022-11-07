<?php
/**
 * Version information for Genashtim TMS.
 *
 * @package    local_genashtim_tms
 * @copyright  2022 genashtim - www.genashtim.com
 * @author     Duy Hoang
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");

class request_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
       
        $mform = $this->_form; // Don't forget the underscore! 
        $courseType = array(
            '' => 'Select course type',
            'Formal' => 'Formal',
            'Informal' => 'Informal'
        );
        $select =  $mform->addElement('select', 'courseType', get_string('field-course-type', 'local_genashtim_tms'), $courseType);
        $select->setSelected('');

        $mform->addElement('text', 'courseName', get_string('field-course-name','local_genashtim_tms')); // Add elements to your form.
        $mform->setType('courseName', PARAM_NOTAGS);                   // Set type of element.
        $mform->setDefault('courseName',  get_string('desc-course-name','local_genashtim_tms'));        // Default value.
        $mform->addElement('textarea', 'courseDesc', get_string("field-course-des", "local_genashtim_tms"), 'wrap="virtual" rows="2" cols="4"');
        $mform->addElement('text', 'courseDuration', get_string('field-course-duration','local_genashtim_tms')); // Add elements to your form.
        $mform->setType('courseName', PARAM_NOTAGS);
        $mform->addElement('text', 'courseLink', get_string('field-course-link','local_genashtim_tms')); // Add elements to your form.
        $mform->addElement('checkbox', 'isFree', get_string('field-course-is-free', 'local_genashtim_tms'));
        $mform->setDefault('isFree', 1);
        $mform->addElement('text', 'courseFee', get_string('field-course-fee','local_genashtim_tms')); // Add elements to your form.
        // Disable courseFee if a checkbox is checked.
        $mform->disabledIf('courseFee', 'isFree', 'checked');
        $mform->addElement('textarea', 'courseReason', get_string("field-course-reason", "local_genashtim_tms"), 'wrap="virtual" rows="2" cols="4"');
        $mform->addRule('courseType',  get_string('required_type', 'local_genashtim_tms'), 'required');
        $mform->addRule('courseName',  get_string('required_name', 'local_genashtim_tms'), 'required');
        $mform->addRule('courseDesc',  get_string('required_desc', 'local_genashtim_tms'), 'required');
        $mform->addRule('courseDuration',  get_string('required_duration', 'local_genashtim_tms'), 'required');
        $mform->addRule('courseLink',  get_string('required_link', 'local_genashtim_tms'), 'required');
        $mform->addRule('courseReason',  get_string('required_reason', 'local_genashtim_tms'), 'required');

       
        $this->add_action_buttons(true,get_string('field-course-submit','local_genashtim_tms'));
    }   
    //Custom validation should be added here
    function validation($data, $files) {
        $errors= array();
        if(!isset($data['isFree']) && trim($data['courseFee']) == ''){
            $errors['courseFee'] =  get_string('required_fee', 'local_genashtim_tms');
        }
        return $errors;
    }
}