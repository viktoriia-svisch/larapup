<?php
namespace Faker\Test\Provider\ms_MY;
use Faker\Generator;
use Faker\Provider\ms_MY\Person;
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
    public function testPersonalIdentityCardNumber()
    {
        $myKadNumber = $this->faker->myKadNumber;
        $yy = substr($myKadNumber, 0, 2);
        $this->assertRegExp("/^[0-9]{2}$/", $yy);
        $mm = substr($myKadNumber, 2, 2);
        $this->assertRegExp("/^0[1-9]|1[0-2]$/", $mm);
        $dd = substr($myKadNumber, 4, 2);
        $this->assertRegExp("/^0[1-9]|1[0-9]|2[0-9]|3[0-1]$/", $dd);
        $pb = substr($myKadNumber, 6, 2);
        $this->assertRegExp("/^(0[1-9]|1[0-6])|(2[1-9]|3[0-9]|4[0-9]|5[0-9])$/", $pb);
        $nnnn = substr($myKadNumber, 8, 4);
        $this->assertRegExp("/^[0-9]{4}$/", $nnnn);
    }
}
