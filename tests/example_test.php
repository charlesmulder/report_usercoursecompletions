<?php
namespace local_example;

use advanced_testcase;

class example_test extends advanced_testcase {

    public function test_user_generator() {
        $this->resetAfterTest(true);
        $user = $this->getDataGenerator()->create_user();
        $this->assertObjectHasAttribute('email', $user);
    }

}
