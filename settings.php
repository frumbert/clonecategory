<?php

/**
 * @package  clonecategory
 * @copyright 2018, tim@avide.com.au
 * @license MIT
 * @doc https://docs.moodle.org/dev/Admin_settings
 */

defined('MOODLE_INTERNAL') || die();

if (has_capability('moodle/site:config', context_system::instance())) {

    $ADMIN->add('localplugins', new admin_externalpage('clonecategory_action', get_string('action_link', 'local_clonecategory'), $CFG->wwwroot. '/local/clonecategory/action.php'));

}