<?php
namespace report_usercoursecompletions;

use DOMDocument, DOMXPath;

use advanced_testcase;

use report_usercoursecompletions\reportbuilder\local\systemreports\user_report;
use core_reportbuilder\system_report_factory;
use core_reportbuilder\output\system_report;
use core_reportbuilder\external\system_report_exporter;
use context_system, moodle_url;

class user_report_test extends advanced_testcase {

    private $user, $course;

    public function setUp(): void {
        self::setAdminUser();
        $this->course = $this->getDataGenerator()->create_course(
            (object)[
                'enablecompletion' => true,
            ]
        );
        $this->user = $this->getDataGenerator()->create_user();
    }

    public function test_table_contains_linked_user() {
        global $PAGE;
        $this->resetAfterTest(true);

        $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id);

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
                $this->user->id,
                $this->user->firstname,
                $this->user->lastname

            ),
            $data->table
        );
        $this->assertStringContainsString(
            $this->user->email,
            $data->table
        );

        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;

        $doc->loadHTML('<meta charset="utf8">'.$data->table);
        $xpath = new DOMXPath($doc);

        $query = sprintf('//tbody/tr[@class="%s"]', utils::get_plugin_name() ); // Find by css class.

        $rows = $xpath->query($query);
        $this->assertSame(3, $rows->count());

        // Guest user.
        $row1 = $rows->item(0);
        $row1links = $row1->getElementsByTagName('a');
        $this->assertSame($row1links->count(), 1);
        $this->assertSame(
            $row1links->item(0)->getAttribute('href'),
            'https://www.example.com/moodle/report/usercoursecompletions/course_completions.php?userid=1'
        );
        $this->assertSame(trim($row1links->item(0)->nodeValue), 'Guest user');

        // Admin User.
        $row2 = $rows->item(1);
        $row2links = $row2->getElementsByTagName('a');
        $this->assertSame($row2links->count(), 1);
        $this->assertSame(
            $row2links->item(0)->getAttribute('href'),
            'https://www.example.com/moodle/report/usercoursecompletions/course_completions.php?userid=2'
        );
        $this->assertSame($row2links->item(0)->nodeValue, 'Admin User');

        // Test user.
        $row3 = $rows->item(2);
        $row3links = $row3->getElementsByTagName('a');
        $this->assertSame($row3links->count(), 1);
        $this->assertSame(
            $row3links->item(0)->getAttribute('href'),
            sprintf(
                'https://www.example.com/moodle/report/usercoursecompletions/course_completions.php?userid=%d',
                $this->user->id
            )
        );
        $this->assertEquals(
            $row3links->item(0)->nodeValue,
            sprintf('%s %s', $this->user->firstname, $this->user->lastname)
        );

    }

}
