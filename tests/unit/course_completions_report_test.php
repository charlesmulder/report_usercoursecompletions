<?php
namespace report_usercoursecompletions;

use DOMDocument, DOMXPath;

use advanced_testcase;

use report_usercoursecompletions\reportbuilder\local\systemreports\course_completions_report;
use core_reportbuilder\system_report_factory;
use core_reportbuilder\output\system_report;
use core_reportbuilder\external\system_report_exporter;
use context_system, moodle_url, completion_info, completion_criteria, completion_completion;

class course_completions_report_test extends advanced_testcase {

    private $user, $course;

    public function setUp(): void {
        global $PAGE;
        self::setAdminUser();
        $this->user = $this->getDataGenerator()->create_user();
        $this->course = $this->getDataGenerator()->create_course(
            (object)[
                'enablecompletion' => true,
            ]
        );
        $PAGE->set_url(
            new moodle_url(
                '/report/usercoursecompletions/course_completions.php',
                [ 'userid' => $this->user->id ]
            )
        );
    }

    public function test_not_enrolled() {
        global $PAGE;
        $this->resetAfterTest(true);
        $report = system_report_factory::create(course_completions_report::class, context_system::instance());
        $exporter = new system_report_exporter($report->get_report_persistent(), [
            'source' => $report,
            'parameters' => json_encode($report->get_parameters()),
        ]);
        $data = $exporter->export($PAGE->get_renderer('core_reportbuilder'));
        $this->assertStringContainsString( '!! Nothing to display !!', $data->table );
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;

        $doc->loadHTML($data->table);
        $xpath = new DOMXPath($doc);

        $query = sprintf('//tbody/tr[@class="%s"]', utils::get_plugin_name() );

        $rows = $xpath->query($query);
        $this->assertSame(0, $rows->count());
    }

    public function test_enrolled_and_complete() {
        global $PAGE;
        $this->resetAfterTest(true);
        $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id);

        $completioncriteria = completion_criteria::factory([
            'criteriatype' => COMPLETION_CRITERIA_TYPE_SELF,
        ]);
        $completioncompletion = new completion_completion([
            'userid' => $this->user->id,
            'course' => $this->course->id,
        ]);
        $completioncompletion->mark_complete();
        $completioncriteria->complete( $completioncompletion );
        $completioninfo = new completion_info($this->course);
        $this->assertSame(COMPLETION_ENABLED, $completioninfo->is_enabled());
        $this->assertTrue($completioninfo->is_course_complete($this->user->id));

        $report = system_report_factory::create(course_completions_report::class, context_system::instance());
        $exporter = new system_report_exporter($report->get_report_persistent(), [
            'source' => $report,
            'parameters' => json_encode($report->get_parameters()),
        ]);
        $data = $exporter->export($PAGE->get_renderer('core_reportbuilder'));
        $this->assertStringContainsString($this->course->fullname, $data->table );
        $this->assertStringContainsString('Yes', $data->table );
        $this->assertStringNotContainsString('No', $data->table );
        $this->assertStringContainsString(userdate($completioncompletion->timecompleted), $data->table);

        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;

        $doc->loadHTML($data->table);
        $xpath = new DOMXPath($doc);

        $query = sprintf('//tbody/tr[@class="%s"]', utils::get_plugin_name() );

        $rows = $xpath->query($query);
        $this->assertSame(1, $rows->count());
        $cells = $doc->getElementsByTagName('td');
        $this->assertSame($cells->item(0)->nodeValue, $this->course->fullname); // Course fullname.
        $this->assertSame($cells->item(1)->nodeValue, 'Yes'); // Is completed.
        $this->assertSame($cells->item(2)->nodeValue, userdate($completioncompletion->timecompleted)); // Time completed.
    }

    public function test_enrolled_and_incomplete() {
        global $PAGE;
        $this->resetAfterTest(true);
        $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id);

        $completioncriteria = completion_criteria::factory([
            'criteriatype' => COMPLETION_CRITERIA_TYPE_SELF,
        ]);
        $completioncompletion = new completion_completion([
            'userid' => $this->user->id,
            'course' => $this->course->id,
        ]);
        $completioncompletion->mark_enrolled();
        $completioninfo = new completion_info($this->course);
        $this->assertSame(COMPLETION_ENABLED, $completioninfo->is_enabled());
        $this->assertFalse($completioninfo->is_course_complete($this->user->id));

        $report = system_report_factory::create(course_completions_report::class, context_system::instance());
        $exporter = new system_report_exporter($report->get_report_persistent(), [
            'source' => $report,
            'parameters' => json_encode($report->get_parameters()),
        ]);
        $data = $exporter->export($PAGE->get_renderer('core_reportbuilder'));
        $this->assertStringContainsString($this->course->fullname, $data->table );
        $this->assertStringContainsString('No', $data->table );
        $this->assertStringNotContainsString('Yes', $data->table);

        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;

        $doc->loadHTML($data->table);
        $xpath = new DOMXPath($doc);

        $query = sprintf('//tbody/tr[@class="%s"]', utils::get_plugin_name() );

        $rows = $xpath->query($query);
        $this->assertSame(1, $rows->count());
        $cells = $doc->getElementsByTagName('td');
        $this->assertSame($cells->item(0)->nodeValue, $this->course->fullname); // Course fullname.
        $this->assertSame($cells->item(1)->nodeValue, 'No'); // Is complete.
        $this->assertSame($cells->item(2)->nodeValue, ''); // Time completed.

    }

    public function test_enrolled_and_mixed_completions() {
        global $PAGE;
        $this->resetAfterTest(true);
        $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id);

        $course1completioncriteria = completion_criteria::factory([
            'criteriatype' => COMPLETION_CRITERIA_TYPE_SELF,
        ]);
        $course1completioncompletion = new completion_completion([
            'userid' => $this->user->id,
            'course' => $this->course->id,
        ]);
        $course1completioncompletion->mark_complete();
        $course1completioncriteria->complete( $course1completioncompletion );
        $course1completioninfo = new completion_info($this->course);
        $this->assertSame(COMPLETION_ENABLED, $course1completioninfo->is_enabled());
        $this->assertTrue($course1completioninfo->is_course_complete($this->user->id));

        $course2 = $this->getDataGenerator()->create_course(
            (object)[
                'enablecompletion' => true,
            ]
        );
        $this->getDataGenerator()->enrol_user($this->user->id, $course2->id);

        $course2completioncriteria = completion_criteria::factory([
            'criteriatype' => COMPLETION_CRITERIA_TYPE_SELF,
        ]);
        $course2completioncompletion = new completion_completion([
            'userid' => $this->user->id,
            'course' => $course2->id,
        ]);
        $course2completioncompletion->mark_enrolled();
        $course2completioninfo = new completion_info($course2);
        $this->assertSame(COMPLETION_ENABLED, $course2completioninfo->is_enabled());
        $this->assertFalse($course2completioninfo->is_course_complete($this->user->id));

        $report = system_report_factory::create(course_completions_report::class, context_system::instance());
        $exporter = new system_report_exporter($report->get_report_persistent(), [
            'source' => $report,
            'parameters' => json_encode($report->get_parameters()),
        ]);
        $data = $exporter->export($PAGE->get_renderer('core_reportbuilder'));
        $this->assertStringContainsString($this->course->fullname, $data->table );
        $this->assertStringContainsString($course2->fullname, $data->table );
        $this->assertStringContainsString('No', $data->table );
        $this->assertStringContainsString('Yes', $data->table);
        $this->assertStringContainsString(userdate($course1completioncompletion->timecompleted), $data->table);

        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;

        $doc->loadHTML($data->table);
        $xpath = new DOMXPath($doc);

        $query = sprintf('//tbody/tr[@class="%s"]', utils::get_plugin_name() ); // Find by css class.

        $rows = $xpath->query($query);
        $this->assertSame(2, $rows->count());

        // Course 2.
        $row1 = $rows->item(0);
        $row1cells = $row1->getElementsByTagName('td');
        $this->assertSame($row1cells->item(0)->nodeValue, $course2->fullname); // Course fullname.
        $this->assertSame($row1cells->item(1)->nodeValue, 'No'); // Is complete.
        $this->assertSame($row1cells->item(2)->nodeValue, ''); // Time completed.
        // Course 1.
        $row2 = $rows->item(1);
        $row2cells = $row2->getElementsByTagName('td');
        $this->assertSame($row2cells->item(0)->nodeValue, $this->course->fullname); // Course fullname.
        $this->assertSame($row2cells->item(1)->nodeValue, 'Yes'); // Is complete.
        $this->assertSame(
            $row2cells->item(2)->nodeValue,
            userdate($course1completioncompletion->timecompleted)
        ); // Time completed.

    }

}
