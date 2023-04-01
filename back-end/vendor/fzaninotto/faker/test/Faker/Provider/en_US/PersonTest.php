<?php
namespace Faker\Test\Provider\en_US;
use Faker\Provider\en_US\Person;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
class PersonTest extends TestCase
{
    private $faker;
    public function setUp()
    {
        $faker = new Generator();
        $faker->addProvider(new Person($faker));
        $this->faker = $faker;
    }
    public function testSsn()
    {
        for ($i = 0; $i < 100; $i++) {
            $number = $this->faker->ssn;
            $this->assertRegExp('/^[0-9]{3}-[0-9]{2}-[0-9]{4}$/', $number);
            $parts = explode("-", $number);
            $this->assertNotEquals(666, $parts[0]);
            $this->assertGreaterThan(0, $parts[0]);
            $this->assertLessThan(900, $parts[0]);
            $this->assertGreaterThan(0, $parts[1]);
            $this->assertLessThan(100, $parts[1]);
            $this->assertGreaterThan(0, $parts[2]);
            $this->assertLessThan(10000, $parts[2]);
        }
    }
}
