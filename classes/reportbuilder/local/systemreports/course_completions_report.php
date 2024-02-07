<?php
namespace report_usercoursecompletions\reportbuilder\local\systemreports;

use report_usercoursecompletions\utils;
use context_system;
use core_reportbuilder\system_report;
use core_reportbuilder\local\entities\course;
use core_reportbuilder\local\entities\user;
use report_usercoursecompletions\reportbuilder\local\entities\course_completion;
use stdClass, moodle_url, pix_icon;

class course_completions_report extends system_report {

    /**
     * Initialise report, we need to set the main table, load our entities and set columns/filters
     */
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

        // Now we can call our helper methods to add the content we want to include in the report.
        $this->add_columns();
        $this->add_filters();
        $this->set_filter_form_default(false);

        $this->set_downloadable(false);
    }


    /**
     * Validates access to view this report
     *
     * @return bool
     */
    protected function can_view(): bool {
        return has_capability('moodle/site:config', context_system::instance());
    }

    /**
     * Adds the columns we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    protected function add_columns(): void {
        $columns = [
            'course:fullname',
            'course_completion:iscompleted',
            'course_completion:timecompleted',
        ];

        $this->add_columns_from_entities($columns);

        // Default sorting.
        $this->set_initial_sort_column('course:fullname', SORT_DESC);
    }

    /**
     * Adds the filters we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    protected function add_filters(): void {
        $filters = [
            'user:userselect',
        ];
        $this->add_filters_from_entities($filters);
    }

}
