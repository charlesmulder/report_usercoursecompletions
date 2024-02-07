<?php
/**
 * Entry pages for Moodle files should all start by including the config.php file found in
 * the root directory of the codebase
 */
use report_usercoursecompletions\utils;
use report_usercoursecompletions\reportbuilder\local\systemreports\user_report;
use core_reportbuilder\system_report_factory;    
use core_reportbuilder\local\filters\text;    
    
require_once( sprintf('%s/config.php', $_SERVER['DOCUMENT_ROOT'] ) );
require_once($CFG->libdir.'/adminlib.php');    

admin_externalpage_setup( 'usercoursecompletions', '', [], '', [ 'pagelayout' => 'report' ] );
    
echo $OUTPUT->header();    
echo $OUTPUT->heading( get_string('userreportheading', utils::get_plugin_name() ) );    
    
// Create out report instance, setting initial filtering if required.    
$report = system_report_factory::create(user_report::class, context_system::instance());    

echo $report->output();    
    
echo $OUTPUT->footer();
