<?php
namespace report_usercoursecompletions\reportbuilder\local\entities;

use lang_string, context_system, context_helper, stdClass, html_writer, moodle_url;
use core_user\fields;

use report_usercoursecompletions\utils;
use report_usercoursecompletions\reportbuilder\local\filters\distinct;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use core_reportbuilder\local\filters\{ date, user, course_selector };

/**
 * @see https://moodledev.io/docs/apis/core/reportbuilder#entity
 * @see https://github.com/moodle/moodle/blob/master/report/configlog/classes/reportbuilder/local/entities/config_change.php
 */
class course_completion extends base {

    protected function get_default_tables(): array {
        return [
            'user',
            'course_completions',
        ];
    }

    protected function get_default_table_aliases(): array {
        return [
            'user' => 'u',
            'course_completions' => 'ccs',
        ];
    }

    protected function get_default_entity_title(): lang_string {
        return new lang_string('entitycoursecompletiontitle', utils::get_plugin_name() );
    }

    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this->add_filter($filter);
        }

        return $this;
    }


    /**
     * Returns list of all available columns
     *
     * SELECT mdl_course.fullname, mdl_course_completions.timecompleted
     * MariaDB [moodle]> describe mdl_course_completions;
     * +---------------+------------+------+-----+---------+----------------+
     * | Field         | Type       | Null | Key | Default | Extra          |
     * +---------------+------------+------+-----+---------+----------------+
     * | id            | bigint(10) | NO   | PRI | NULL    | auto_increment |
     * | userid        | bigint(10) | NO   | MUL | 0       |                |
     * | course        | bigint(10) | NO   | MUL | 0       |                |
     * | timeenrolled  | bigint(10) | NO   |     | 0       |                |
     * | timestarted   | bigint(10) | NO   |     | 0       |                |
     * | timecompleted | bigint(10) | YES  | MUL | NULL    |                |
     * | reaggregate   | bigint(10) | NO   |     | 0       |                |
     * +---------------+------------+------+-----+---------+----------------+
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $tablealias = $this->get_table_alias('course_completions');

        $columns[] = (new column(
            'userid',
            new lang_string('entitycoursecompletionuseridcolumn', utils::get_plugin_name() ),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$tablealias}.userid")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'course',
            new lang_string('entitycoursecompletioncourseidcolumn', utils::get_plugin_name() ),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$tablealias}.course")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'timecompleted',
            new lang_string('entitycoursecompletiontimecompletedcolumn', utils::get_plugin_name() ),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$tablealias}.timecompleted")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate']);

        $columns[] = (new column(
            'iscompleted',
            new lang_string('entitycoursecompletionstatuscolumn', utils::get_plugin_name() ),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN)
            ->add_fields("{$tablealias}.timecompleted")
            ->set_is_sortable(false)
            ->add_callback(static fn( $value ) => format::boolean_as_text(boolval($value)));

        return $columns;
    }

    /**
     * @return filter[]
     */
    protected function get_all_filters(): array {
        return [];
    }
}
