<?php
namespace report_usercoursecompletions\reportbuilder\local\systemreports;

use report_usercoursecompletions\utils;
use lang_string, context_system, context_helper, stdClass, html_writer, moodle_url, pix_icon, core_user;
use core_user\fields;
use core_reportbuilder\system_report;
use core_reportbuilder\local\entities\user;
use report_usercoursecompletions\reportbuilder\local\entities\course_completion;

/**
 * @see https://moodledev.io/docs/apis/core/reportbuilder
 */
class user_report extends system_report {

    protected function initialise(): void {

        $entityuser = new user();
        $entityuseralias = $entityuser->get_table_alias('user');
        $this->set_main_table('user', $entityuseralias);
        $this->add_entity($entityuser);

        // Now we can call our helper methods to add the content we want to include in the report.
        $this->add_columns();
        $this->add_filters();

        $this->set_downloadable(false);
    }


    protected function can_view(): bool {
        return has_capability('moodle/site:config', context_system::instance());
    }

    /**
     * SELECT mdl_user.firstname, mdl_user.lastname, mdl_user.email FROM mdl_user
     * @see See https://docs.moodle.org/dev/Additional_name_fields
     */
    protected function add_columns(): void {

        $columns = [
            'user:fullnamewithlink',
            'user:email',
        ];

        $this->add_columns_from_entities($columns);

        // Custom callback to modify href for fullname column.
        if ($columnfullnamewithlink = $this->get_column('user:fullnamewithlink')) {
            $columnfullnamewithlink->add_callback(static function(string $fullname, stdClass $row) : string {

                $namefields = fields::get_name_fields();
                foreach ($namefields as $namefield) {
                    $row->{$namefield} = $row->{$namefield} ?? '';
                }

                return html_writer::link(
                    new moodle_url(
                        '/report/usercoursecompletions/course_completions.php', ['userid' => $row->id]
                    ),
                    core_user::get_fullname($row, context_system::instance() )
                );
            });
        }
    }

    /**
     * Adds the filters we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    protected function add_filters(): void {
        $filters = [];
        $this->add_filters_from_entities($filters);
    }

}
