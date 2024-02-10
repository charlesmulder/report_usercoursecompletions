<?php
namespace report_usercoursecompletions\reportbuilder\local\systemreports;

use report_usercoursecompletions\utils;
use context_system;
use core_reportbuilder\system_report;
use core_reportbuilder\local\entities\course;
use core_reportbuilder\local\entities\user;
use report_usercoursecompletions\reportbuilder\local\entities\course_completion;
use stdClass, moodle_url, pix_icon;

/**
 * @see https://moodledev.io/docs/apis/core/reportbuilder#system-reports-1
 * @see https://github.com/moodle/moodle/blob/master/report/configlog/classes/reportbuilder/local/systemreports/config_changes.php
 * @see https://github.com/moodle/moodle/blob/master/reportbuilder/classes/system_report.php
 */
class course_completions_report extends system_report {

    protected function initialise(): void {

        $entitycoursecompletion = new course_completion();
        $entitycoursecompletionalias = $entitycoursecompletion->get_table_alias('course_completions');

        $this->set_main_table('course_completions', $entitycoursecompletionalias);
        $this->add_entity($entitycoursecompletion);

        $entitycourse = new course();
        $entitycoursealias = $entitycourse->get_table_alias('course');
        $this->add_entity(
            $entitycourse->add_join(
                "JOIN {course} AS {$entitycoursealias} ON {$entitycoursecompletionalias}.course = {$entitycoursealias}.id"
            )
        );

        $entityuser = new user();
        $entityuseralias = $entityuser->get_table_alias('user');
        $this->add_entity(
            $entityuser->add_join(
                "JOIN {user} AS {$entityuseralias} ON {$entitycoursecompletionalias}.userid = {$entityuseralias}.id"
            )
        );

        $this->add_columns();
        $this->add_filters();
        $this->set_filter_form_default(false);

        $this->set_downloadable(false);
    }

    protected function can_view(): bool {
        return has_capability('moodle/site:config', context_system::instance());
    }

    protected function add_columns(): void {
        $columns = [
            'course:fullname',
            'course_completion:iscompleted',
            'course_completion:timecompleted',
        ];

        $this->add_columns_from_entities($columns);

        $this->set_initial_sort_column('course:fullname', SORT_DESC);
    }

    protected function add_filters(): void {
        $filters = [
            'user:userselect',
        ];
        $this->add_filters_from_entities($filters);
    }

    /**
     * Add custom CSS class.
     */
    public function get_row_class(stdClass $row): string {
        return utils::get_plugin_name();
    }


}
