<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
abstract class Composite extends Constraint
{
    private $innerConstraint;
    public function __construct(Constraint $innerConstraint)
    {
        parent::__construct();
        $this->innerConstraint = $innerConstraint;
    }
    public function evaluate($other, $description = '', $returnResult = false)
    {
        try {
            return $this->innerConstraint->evaluate(
                $other,
                $description,
                $returnResult
            );
        } catch (ExpectationFailedException $e) {
            $this->fail($other, $description, $e->getComparisonFailure());
        }
    }
    public function count(): int
    {
        return \count($this->innerConstraint);
    }
    protected function innerConstraint(): Constraint
    {
        return $this->innerConstraint;
    }
}
