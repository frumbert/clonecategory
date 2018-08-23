<?php


/**
 * @package  clonecategory
 * @copyright 2018, tim@avide.com.au
 * @license MIT
 * @doc https://docs.moodle.org/dev/String_API
 */

defined('MOODLE_INTERNAL') || die();

$string['plugintitle'] = 'Clone all courses in a category';
$string['action_link'] = 'Clone Category';

$string['source_category'] = 'Source category';
$string['destination_category'] = 'Destination parent category';

$string['destination_category_name'] = 'Destination category name';
$string['destination_category_idnumber'] = 'Destination category idnumber';
$string['categoryname_help'] = 'if set, and an idnumber is also specified, this will be created underneath the desination category';
$string['categoryname'] =  'Optional - create sub-category in destination';

$string['error_missing_source_idnumber'] = 'Source Category is missing its idnumber (required)';
$string['error_missing_destination_idnumber'] = 'Destination Category is missing its idnumber (required)';
$string['error_date_problem'] = 'The end date must occur after the start date';
$string['error_destination_not_top_when_empty'] = 'Destination cannot be Top when not adding a new category';
$string['error_must_specify_both'] = 'You must enter both Name and IdNumber fields if entering either';

$string['list_courses'] = "List courses";
$string['clone_courses'] = "Clone courses";
$string['eventcoursecloned'] = 'Course cloned';