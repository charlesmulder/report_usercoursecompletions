<?php

defined('MOODLE_INTERNAL') || die;    

use report_usercoursecompletions\utils;
    
// Just a link to course report.    
$ADMIN->add('reports', new admin_externalpage('usercoursecompletions', get_string('pluginname', utils::get_plugin_name() ),    
        $CFG->wwwroot . "/report/usercoursecompletions/index.php", 'report/log:view'));    
    
// No report settings.    
$settings = null;
