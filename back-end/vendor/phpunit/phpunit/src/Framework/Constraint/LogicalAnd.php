<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
class LogicalAnd extends Constraint
{
    private $constraints = [];
    public static function fromConstraints(Constraint ...$constraints): self
    {
        $constraint = new self;
        $constraint->constraints = \array_values($constraints);
        return $constraint;
    }
    public function setConstraints(array $constraints): void
    {
        $this->constraints = [];
        foreach ($constraints as $constraint) {
            if (!($constraint instanceof Constraint)) {
                throw new \PHPUnit\Framework\Exception(
                    'All parameters to ' . __CLASS__ .
                    ' must be a constraint object.'
                );
            }
            $this->constraints[] = $constraint;
        }
    }
    public function evaluate($other, $description = '', $returnResult = false)
    {
        $success = true;
        foreach ($this->constraints as $constraint) {
            if (!$constraint->evaluate($other, $description, true)) {
                $success = false;
                break;
            }
        }
        if ($returnResult) {
            return $success;
        }
        if (!$success) {
            $this->fail($other, $description);
        }
    }
    public function toString(): string
    {
        $text = '';
        foreach ($this->constraints as $key => $constraint) {
            if ($key > 0) {
                $text .= ' and ';
            }
            $text .= $constraint->toString();
        }
        return $text;
    }
    public function count(): int
    {
        $count = 0;
        foreach ($this->constraints as $constraint) {
            $count += \count($constraint);
        }
        return $count;
    }
}
