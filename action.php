<?php
/**
 * @package  clonecategory
 * @copyright 2018, tim@avide.com.au
 * @license MIT
 */

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/externallib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->libdir . '/coursecatlib.php');
require_once(dirname(__FILE__).'/classes/clonecategory_forms.php');

$context = context_system::instance();
$strtitle = get_string('action_link', 'local_clonecategory');

// checks permissions, setup up $PAGE
admin_externalpage_setup('clonecategory_action');

$action = optional_param('action', '', PARAM_ALPHA);
$config = get_config('local_clonecategory');
$returnurl = new moodle_url("/local/clonecategory/action.php");

$action = optional_param('action', false, PARAM_ALPHA);
$source = optional_param('source', 0, PARAM_INT);
$dest = optional_param('destination', 0, PARAM_INT);
$name = optional_param('name', '', PARAM_RAW);
$start = optional_param('start', time(), PARAM_INT);
$end = optional_param('end', strtotime('+3 month', time()), PARAM_INT);

$formdata = array(
    'source' => $source,
    'destination' => $dest,
    'destcategoryname' => $name,
    'startdate' => $start,
    'enddate' => $end,
);

function rutime($ru, $rus, $index) {
    return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
     -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
}

// may not require this after all
// function clean_activity_userdata($courseid) {
//     global $DB, $CFG;
//     $course = $DB->get_record("course", array("id"=>$courseid));
//     if ($allmods = $DB->get_records('modules') ) {
//         foreach ($allmods as $mod) {
//             $modname = $mod->name;
//             $modlib = $CFG->dirroot."/mod/$modname/lib.php";
//             $mod_reset__data = $modname.'_reset_course_form_defaults';
//             $mod_reset__userdata = $modname.'_reset_userdata';
//             if (file_exists($modlib)) {
//                 if (!$DB->count_records($modname, array('course'=>$course->id))) {
//                     continue; // Skip mods with no instances
//                 }
//                 include_once($modlib);
//                 if (function_exists($mod_reset__data)) {
//                     $data = (object) $mod_reset__data($course);
//                     $data->courseid = $course->id;
//                     if (function_exists($mod_reset__userdata)) {
//                         @$mod_reset__userdata($data); // ignore failures
//                     } // userdata
//                 } // defaults
//             } // exists
//         } //each mod
//     } // setup mods
// }

$mform = new local_clonecategory_form(null,$formdata);
$log = [];

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {

    $start = explode(' ', microtime())[0] + explode(' ', microtime())[1];

    ob_implicit_flush();

    // This will almost certainly take a long time.
    core_php_time_limit::raise();

    // category selection
    $src = coursecat::get($data->source);
    $dest = coursecat::get($data->destination);

    // if a destaintion category name was supplied, create it and update the $dest object
    if (!empty($data->destcategoryname) && !empty($data->destcategoryidnumber)) {
        $dest = coursecat::create([
            "name" => trim($data->destcategoryname),
            "idnumber" => trim($data->destcategoryidnumber),
            "parent" => $dest->id
        ]);
    }

    // core_course_external::duplicate_course requries
    // 'courseid' => $courseid,
    // 'fullname' => $fullname,
    // 'shortname' => $shortname,
    // 'categoryid' => $categoryid,
    // 'visible' => $visible,
    // 'options' => $options

    $allcourses = $src->get_courses(array('recursive' => false));

    // thanks for the rediculous option format, Moodle
    $options = array(
        ['name'=>'activities','value'=>1],
        ['name'=>'blocks','value'=>1],
        ['name'=>'filters','value'=>1],
        ['name'=>'users','value'=>0],
        ['name'=>'role_assignments','value'=>0],
        ['name'=>'comments','value'=>0],
        ['name'=>'userscompletion','value'=>0],
        ['name'=>'logs','value'=>0],
        ['name'=>'grade_histories','value'=>0]
    );
    foreach ($allcourses as $course) {
        $rustart = getrusage();

        $idn = explode('_', $course->shortname);
        $shortname = reset($idn) . '_' . $dest->idnumber;

        // if a course matching the shortname and destination category already exists, skip it
        if ($DB->record_exists("course", array("shortname" => $shortname, "category" => $dest->id))) {

            $log[] = "<li>A course with shortname {$shortname} already exists in the desination category (id={$dest->id}); skipping</li>";

        } else {

            $clone = core_course_external::duplicate_course($course->id, $course->fullname, $shortname, $dest->id, 0, $options); // default options seem ok

            $newid = $clone['id'];
            $newshortname = $clone['shortname'];
            $newfullname = str_replace($src->idnumber, $dest->idnumber, $course->fullname);

            $DB->set_field_select('course', 'fullname', $newfullname, "id = ?", [$newid]);
            $DB->set_field_select('course', 'startdate', $data->startdate, "id = ?", [$newid]);
            $DB->set_field_select('course', 'enddate', $data->enddate, "id = ?", [$newid]);

            $ru = getrusage();
            $rulog = "Computation time: " . rutime($ru, $rustart, "utime") . ", System call time: " . rutime($ru, $rustart, "stime");
            $entry = "Cloned {$course->id}/{$course->shortname} into {$newid}/{$newshortname}; stats: {$rulog}";
            \local_clonecategory_events::course_cloned($clone['id'], $entry);
            $log[] = "<li>{$entry}</li>";

        }

        flush();
    }

    $log[] = "<li>Memory Used: " . memory_get_usage() . ", Peak Memory: ". memory_get_peak_usage() . "</li>";
    $log[] = "<li>Time taken: ". round((explode(' ', microtime())[0] + explode(' ', microtime())[1]) - $start, 4) . " seconds</li>";
    // don't bother with idnumber on courses
    // update mdl_course set idnumber = shortname where category = $data->destination

    $log[] = "<li><b>Clone Process completed</b><br/><hr/></li>";

}
echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);

echo "<ul>", implode(PHP_EOL, $log), "</ul>";

$mform->display();

echo $OUTPUT->footer();