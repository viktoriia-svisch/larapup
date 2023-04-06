<?php
namespace Illuminate\Foundation\Testing;
use Faker\Factory;
trait WithFaker
{
    protected $faker;
    protected function setUpFaker()
    {
        $this->faker = $this->makeFaker();
    }
    protected function faker($locale = null)
    {
        return is_null($locale) ? $this->faker : $this->makeFaker($locale);
    }
    protected function makeFaker($locale = null)
    {
        return Factory::create($locale ?? Factory::DEFAULT_LOCALE);
    }
}
