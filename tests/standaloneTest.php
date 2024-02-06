<?php
namespace local_example;

use PHPUnit\Framework\TestCase;
use Faker;

class mod_unit_test extends TestCase
{

    protected function setUp(): void {
        $this->faker = Faker\Factory::create();
    }

    protected function tearDown(): void {
    }

    public function test_standalone() {
        $this->assertIsString($this->faker->name());
    }

}
