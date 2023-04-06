<?php
namespace Faker\Provider\ng_NG;
use Faker\Generator;
use Faker\Provider\en_NG\Address;
use PHPUnit\Framework\TestCase;
class AddressTest extends TestCase
{
    private $faker;
    public function setUp()
    {
        $faker = new Generator();
        $faker->addProvider(new Address($faker));
        $this->faker = $faker;
    }
    public function testPostcodeIsNotEmptyAndIsValid()
    {
        $postcode = $this->faker->postcode();
        $this->assertNotEmpty($postcode);
        $this->assertInternalType('string', $postcode);
    }
    public function testCountyIsAValidString()
    {
        $county = $this->faker->county;
        $this->assertNotEmpty($county);
        $this->assertInternalType('string', $county);
    }
    public function testRegionIsAValidString()
    {
        $region = $this->faker->region;
        $this->assertNotEmpty($region);
        $this->assertInternalType('string', $region);
    }
}
