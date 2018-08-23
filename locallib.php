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
 * Classes of modules.
 *
 * @package   local_clonecategory
 * @copyright 2018 tim@avide.com.au
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Description of functions of the call of events
 *
 * @copyright 2018 tim@avide.com.au
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_clonecategory_events {
    /**
     * Call the event after cloning a course.
     */
    public static function course_cloned($objectid = 0, $log = "") {
        $context = context_system::instance();

        $event = local_clonecategory\event\course_cloned::create(
            array(
                "context"  => $context,
                "objectid" => $objectid,
                "other" => array("log" => $log)
            )
        );

        $event->trigger();
    }

}