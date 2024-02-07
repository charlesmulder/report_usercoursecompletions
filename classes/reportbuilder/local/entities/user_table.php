<?php

namespace report_usercoursecompletions\reportbuilder\local\entities;

use lang_string, context_system, context_helper, stdClass, html_writer, moodle_url;
use core_user\fields;

use core_reportbuilder\local\entities\user;    
use core_reportbuilder\local\helpers\format;    
use core_reportbuilder\local\report\column;    
use core_reportbuilder\local\report\filter;    
use core_reportbuilder\local\filters\date;    
use core_reportbuilder\local\filters\text;    
    
class user_table extends user {

	/**
	 * @todo Remove unused columns.
	 */
	protected function get_all_columns(): array {
        global $DB;

        $usertablealias = $this->get_table_alias('user');
        $contexttablealias = $this->get_table_alias('context');

        $fullnameselect = self::get_name_fields_select($usertablealias);
        $fullnamesort = explode(', ', $fullnameselect);

        $viewfullnames = has_capability('moodle/site:viewfullnames', context_system::instance());

        // Fullname column.
        $columns[] = (new column(
            'fullname',
            new lang_string('fullname'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields($fullnameselect)
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable($this->is_sortable('fullname'), $fullnamesort)
            ->add_callback(static function(?string $value, stdClass $row) use ($viewfullnames): string {
                if ($value === null) {
                    return '';
                }

                // Ensure we populate all required name properties.
                $namefields = fields::get_name_fields();
                foreach ($namefields as $namefield) {
                    $row->{$namefield} = $row->{$namefield} ?? '';
                }

                return fullname($row, $viewfullnames);
            });

        // Formatted fullname columns (with link, picture or both).
        $fullnamefields = [
            'fullnamewithlink' => new lang_string('userfullnamewithlink', 'core_reportbuilder'),
        ];

        foreach ($fullnamefields as $fullnamefield => $fullnamelang) {
            $column = (new column(
                $fullnamefield,
                $fullnamelang,
                $this->get_entity_name()
            ))
                ->add_joins($this->get_joins())
                ->add_fields($fullnameselect)
                ->add_field("{$usertablealias}.id")
                ->set_type(column::TYPE_TEXT)
                ->set_is_sortable($this->is_sortable($fullnamefield), $fullnamesort)
                ->add_callback(static function(?string $value, stdClass $row) use ($fullnamefield, $viewfullnames): string {
                    global $OUTPUT;

                    if ($value === null) {
                        return '';
                    }

                    // Ensure we populate all required name properties.
                    $namefields = fields::get_name_fields();
                    foreach ($namefields as $namefield) {
                        $row->{$namefield} = $row->{$namefield} ?? '';
                    }

                    if ($fullnamefield === 'fullnamewithlink') {
                        return html_writer::link(new moodle_url('/report/usercoursecompletions/courses.php', ['id' => $row->id]),
                            fullname($row, $viewfullnames));
                    }

                    return $value;
                });

            $columns[] = $column;
        }

        // Add all other user fields.
        $userfields = $this->get_user_fields();
        foreach ($userfields as $userfield => $userfieldlang) {
            $columntype = $this->get_user_field_type($userfield);

            $columnfieldsql = "{$usertablealias}.{$userfield}";
            if ($columntype === column::TYPE_LONGTEXT && $DB->get_dbfamily() === 'oracle') {
                $columnfieldsql = $DB->sql_order_by_text($columnfieldsql, 1024);
            }

            $column = (new column(
                $userfield,
                $userfieldlang,
                $this->get_entity_name()
            ))
                ->add_joins($this->get_joins())
                ->set_type($columntype)
                ->add_field($columnfieldsql, $userfield)
                ->set_is_sortable($this->is_sortable($userfield))
                ->add_callback([$this, 'format'], $userfield);

            // Some columns also have specific format callbacks.
            if ($userfield === 'country') {
                $column->add_callback(static function(string $country): string {
                    $countries = get_string_manager()->get_list_of_countries(true);
                    return $countries[$country] ?? '';
                });
            } else if ($userfield === 'description') {
                // Select enough fields in order to format the column.
                $column
                    ->add_join("LEFT JOIN {context} {$contexttablealias}
                           ON {$contexttablealias}.contextlevel = " . CONTEXT_USER . "
                          AND {$contexttablealias}.instanceid = {$usertablealias}.id")
                    ->add_fields("{$usertablealias}.descriptionformat, {$usertablealias}.id")
                    ->add_fields(context_helper::get_preload_record_columns_sql($contexttablealias));
            }

            $columns[] = $column;
        }

        return $columns;
    }

}
