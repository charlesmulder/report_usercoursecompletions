<?php
/**
 * Entry pages for Moodle files should all start by including the config.php file found in
 * the root directory of the codebase
 */
use report_usercoursecompletions\utils;
use report_usercoursecompletions\reportbuilder\local\systemreports\course_completions_report;
use core_reportbuilder\system_report_factory;
use core_reportbuilder\local\filters\user;

require_once( sprintf('%s/config.php', $_SERVER['DOCUMENT_ROOT'] ) );
require_once($CFG->libdir.'/adminlib.php');
require_admin();

$userid = optional_param('userid', '', PARAM_INT);

admin_externalpage_setup( 'usercoursecompletions', '', ['userid' => $userid], '', [ 'pagelayout' => 'report' ] );

echo $OUTPUT->header();
echo $OUTPUT->heading( get_string('coursecompletionsreportheading', utils::get_plugin_name() ) );

// Create out report instance, setting initial filtering if required.
$report = system_report_factory::create(course_completions_report::class, context_system::instance());

if ( ! empty( $userid ) ) {
    $report->set_filter_values([
        'user:userselect_operator' => user::USER_SELECT,
        'user:userselect_value' => [ $userid ],
    ]);
}

echo $report->output();

echo $OUTPUT->footer();

