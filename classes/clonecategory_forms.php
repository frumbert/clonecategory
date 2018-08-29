<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Clone category form.
 *
 * @package local_clonecategory
 * @copyright 2018 tim@avide.com.au
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/coursecatlib.php');

/**
 * Clone category form.
 *
 * @package local_clonecategory
 * @copyright 2018 tim@avide.com.au
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_clonecategory_form extends moodleform {

    /**
     * The form definition.
     */
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form;
        $data = $this->_customdata;
        $button_label = get_string('clone_courses', 'local_clonecategory');

        // get a select-friendly list of the categories
        $options = coursecat::make_categories_list('moodle/category:manage'); // array keys are out-of-order but match the mdl_course_category table ids
        $mform->addElement('select', 'source', get_string('source_category','local_clonecategory'), $options);
        $mform->addRule('source', get_string('required'), 'required', null);

        // array_unshift($options, get_string('top')); // unshift REDEFINES the keys, which is bad for us
        // $options = array_merge([get_string('top')], $options); // array_merge REDIFINES the keys, which is bad for us
        $options = array(get_string('top')) + $options; // adding arrays doesn't touch the keys, which are out-of-order
        $mform->addElement('select', 'destination', get_string('destination_category','local_clonecategory'), $options);
        $mform->addRule('destination', get_string('required'), 'required', null);

        // add an optional name/idnumber to create a new category with; only acceptable if both this and idnumber are specifeid
        $mform->addElement('text', 'destcategoryname', get_string('destination_category_name','local_clonecategory'), array('size' => '30'));
        $mform->setType('destcategoryname',PARAM_TEXT);
        $mform->addHelpButton('destcategoryname', 'categoryname', 'local_clonecategory');
        $mform->addElement('text', 'destcategoryidnumber', get_string('destination_category_idnumber','local_clonecategory'), array('size' => '30'));
        $mform->setType('destcategoryidnumber',PARAM_TEXT);

        // timestamp of start date of all newly cloned courses
        $mform->addElement('date_selector', 'startdate', get_string('startdate'));
        $mform->setDefault('startdate', $data['startdate']);

        // timestamp of end date of all newly cloned courses
        $mform->addElement('date_selector', 'enddate', get_string('enddate'));
        $mform->setDefault('enddate', $data['enddate']);

        $this->add_action_buttons(true, $button_label);
    }

    /**
     * Validates the data submit for this form.
     *
     * @param array $data An array of key,value data pairs.
     * @param array $files Any files that may have been submit as well.
     * @return array An array of errors.
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        if (!empty($data['source'])) {
            if ($rec = $DB->get_record('course_categories', array('id' => $data['source']))) {
                if (empty($rec->idnumber)) {
                    $errors['source'] = get_string('error_missing_source_idnumber', 'local_clonecategory');
                }
            }
        }
        if (!empty($data['destination'])) {
            if ($rec = $DB->get_record('course_categories', array('id' => $data['source']))) {
                if (empty($rec->idnumber)) {
                    $errors['destination'] = get_string('error_missing_destination_idnumber', 'local_clonecategory');
                }
            }
        }

        if ((int) $data['destination']===0 && empty($data['destcategoryidnumber'])) {
            $errors['destination'] = get_string('error_destination_not_top_when_empty', 'local_clonecategory');
        }

        if (!empty($data['enddate']) && !empty($data['startdate'])) {
            if ($data['enddate'] < $data['startdate'] || $data['startdate'] === $data['enddate']) {
                $errors['enddate'] = get_string('error_date_problem', 'local_clonecategory');
            }
        }

        if (empty($data['destcategoryname']) && !empty($data['destcategoryidnumber'])) {
            $errors['destcategoryname'] = get_string('error_must_specify_both', 'local_clonecategory');
        }

        if (!empty($data['destcategoryname']) && empty($data['destcategoryidnumber'])) {
            $errors['destcategoryidnumber'] = get_string('error_must_specify_both', 'local_clonecategory');
        }
        return $errors;
    }

    public function reset() {
        $this->_form->updateSubmission(null, null);
    }
}
