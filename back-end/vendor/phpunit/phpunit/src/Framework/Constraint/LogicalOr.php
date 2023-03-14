<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
class LogicalOr extends Constraint
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
                $constraint = new IsEqual(
                    $constraint
                );
            }
            $this->constraints[] = $constraint;
        }
    }
    public function evaluate($other, $description = '', $returnResult = false)
    {
        $success = false;
        foreach ($this->constraints as $constraint) {
            if ($constraint->evaluate($other, $description, true)) {
                $success = true;
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
                $text .= ' or ';
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
