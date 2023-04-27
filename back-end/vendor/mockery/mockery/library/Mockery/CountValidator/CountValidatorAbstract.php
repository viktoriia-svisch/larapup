<?php
namespace Mockery\CountValidator;
abstract class CountValidatorAbstract
{
    protected $_expectation = null;
    protected $_limit = null;
    public function __construct(\Mockery\Expectation $expectation, $limit)
    {
        $this->_expectation = $expectation;
        $this->_limit = $limit;
    }
    public function isEligible($n)
    {
        return ($n < $this->_limit);
    }
    abstract public function validate($n);
}
