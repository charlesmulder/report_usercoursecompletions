<?php 

namespace report_usercoursecompletions\reportbuilder\local\systemreports;

use report_usercoursecompletions\utils;
use context_system;    
use core_reportbuilder\system_report;    
//use core_reportbuilder\local\entities\user;    
use report_usercoursecompletions\reportbuilder\local\entities\user_table;
use core_reportbuilder\local\report\action;    
use stdClass, moodle_url, pix_icon;

class user_report extends system_report {

	/**
     * Initialise report, we need to set the main table, load our entities and set columns/filters
     */
    protected function initialise(): void {

		$entityuser = new user_table();
		$entityuseralias = $entityuser->get_table_alias('user');

        $this->set_main_table('user', $entityuseralias);
        $this->add_entity($entityuser);

        // Now we can call our helper methods to add the content we want to include in the report.
        $this->add_columns();
        $this->add_filters();

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
            'user_table:fullnamewithlink',
            'user_table:firstname',
			'user_table:lastname',
			'user_table:email',
        ];

        $this->add_columns_from_entities($columns);

        // Default sorting.
        $this->set_initial_sort_column('user_table:lastname', SORT_DESC);

        // Custom callback to show 'CLI or install' in fullname column when there is no user.
        if ($column = $this->get_column('user_table:fullnamewithlink')) {
            $column->add_callback(static function(string $fullname, stdClass $row): string {
                return $fullname ?: 'TODO: replace this string';
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
