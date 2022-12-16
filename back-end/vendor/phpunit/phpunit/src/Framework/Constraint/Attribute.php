<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
class Attribute extends Composite
{
    private $attributeName;
    public function __construct(Constraint $constraint, string $attributeName)
    {
        parent::__construct($constraint);
        $this->attributeName = $attributeName;
    }
    public function evaluate($other, $description = '', $returnResult = false)
    {
        return parent::evaluate(
            Assert::readAttribute(
                $other,
                $this->attributeName
            ),
            $description,
            $returnResult
        );
    }
    public function toString(): string
    {
        return 'attribute "' . $this->attributeName . '" ' . $this->innerConstraint()->toString();
    }
    protected function failureDescription($other): string
    {
        return $this->toString();
    }
}
