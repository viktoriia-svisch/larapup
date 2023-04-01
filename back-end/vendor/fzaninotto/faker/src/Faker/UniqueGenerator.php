<?php
namespace Faker;
class UniqueGenerator
{
    protected $generator;
    protected $maxRetries;
    protected $uniques = array();
    public function __construct(Generator $generator, $maxRetries = 10000)
    {
        $this->generator = $generator;
        $this->maxRetries = $maxRetries;
    }
    public function __get($attribute)
    {
        return $this->__call($attribute, array());
    }
    public function __call($name, $arguments)
    {
        if (!isset($this->uniques[$name])) {
            $this->uniques[$name] = array();
        }
        $i = 0;
        do {
            $res = call_user_func_array(array($this->generator, $name), $arguments);
            $i++;
            if ($i > $this->maxRetries) {
                throw new \OverflowException(sprintf('Maximum retries of %d reached without finding a unique value', $this->maxRetries));
            }
        } while (array_key_exists(serialize($res), $this->uniques[$name]));
        $this->uniques[$name][serialize($res)]= null;
        return $res;
    }
}
