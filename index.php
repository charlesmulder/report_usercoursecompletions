<?php
use report_usercoursecompletions\utils;
use report_usercoursecompletions\reportbuilder\local\systemreports\user_report;
use core_reportbuilder\system_report_factory;

require_once( sprintf('%s/config.php', $_SERVER['DOCUMENT_ROOT'] ) );
require_once($CFG->libdir.'/adminlib.php');
require_admin();

admin_externalpage_setup( 'usercoursecompletions', '', [], '', [ 'pagelayout' => 'report' ] );

echo $OUTPUT->header();
echo $OUTPUT->heading( get_string('userreportheading', utils::get_plugin_name() ) );

$report = system_report_factory::create(user_report::class, context_system::instance());

echo $report->output();

echo $OUTPUT->footer();
