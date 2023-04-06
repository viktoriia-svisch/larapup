<?php
namespace Mockery\Matcher;
use Mockery\Exception\InvalidArgumentException;
class PHPUnitConstraint extends MatcherAbstract
{
    protected $constraint;
    protected $rethrow;
    public function __construct($constraint, $rethrow = false)
    {
        if (!($constraint instanceof \PHPUnit_Framework_Constraint)
        && !($constraint instanceof \PHPUnit\Framework\Constraint)) {
            throw new InvalidArgumentException(
                'Constraint must be one of \PHPUnit\Framework\Constraint or '.
                '\PHPUnit_Framework_Constraint'
            );
        }
        $this->constraint = $constraint;
        $this->rethrow = $rethrow;
    }
    public function match(&$actual)
    {
        try {
            $this->constraint->evaluate($actual);
            return true;
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            if ($this->rethrow) {
                throw $e;
            }
            return false;
        } catch (\PHPUnit\Framework\AssertionFailedError $e) {
            if ($this->rethrow) {
                throw $e;
            }
            return false;
        }
    }
    public function __toString()
    {
        return '<Constraint>';
    }
}
