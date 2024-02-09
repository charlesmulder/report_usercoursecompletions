<?php
namespace report_usercoursecompletions;

use advanced_testcase;

use report_usercoursecompletions\reportbuilder\local\systemreports\user_report;
use core_reportbuilder\system_report_factory;
use core_reportbuilder\output\system_report;
use core_reportbuilder\external\system_report_exporter;
use context_system, moodle_url;

class user_report_test extends advanced_testcase {

    public function setUp(): void {
        self::setAdminUser();
    }

    public function test_table_contains_linked_user() {
        global $PAGE;
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course(
            (object)[
                'enablecompletion' => true,
            ]
        );
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        // Prevent debug warnings from flexible_table.
        $PAGE->set_url(new moodle_url('/'));

        $report = system_report_factory::create(user_report::class, context_system::instance());
        $exporter = new system_report_exporter($report->get_report_persistent(), [
            'source' => $report,
            'parameters' => json_encode($report->get_parameters()),
        ]);
        $data = $exporter->export($PAGE->get_renderer('core_reportbuilder'));
        $this->assertStringContainsString(
            sprintf(
                '<a href="https://www.example.com/moodle/report/usercoursecompletions/course_completions.php?userid=%d">%s %s</a>',
                $user->id,
                $user->firstname,
                $user->lastname

            ),
            $data->table
        );
        $this->assertStringContainsString(
            $user->email,
            $data->table
        );
    }

}
