<?php
namespace Faker\Test\Provider\zh_TW;
use Faker\Generator;
use Faker\Provider\zh_TW\Person;
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
    public function testPersonalIdentityNumber()
    {
        $id = $this->faker->personalIdentityNumber;
        $firstChar = substr($id, 0, 1);
        $codesString = Person::$idBirthplaceCode[$firstChar] . substr($id, 1);
        $this->assertRegExp("/^[0-9]{11}$/", $codesString);
        $total = 0;
        $codesArray = str_split($codesString);
        foreach ($codesArray as $key => $code) {
            $total += $code * Person::$idDigitValidator[$key];
        }
        $this->assertEquals(0, ($total % 10));
    }
}
