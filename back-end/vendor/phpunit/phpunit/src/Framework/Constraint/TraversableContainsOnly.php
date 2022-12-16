<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
class TraversableContainsOnly extends Constraint
{
    private $constraint;
    private $type;
    public function __construct(string $type, bool $isNativeType = true)
    {
        parent::__construct();
        if ($isNativeType) {
            $this->constraint = new IsType($type);
        } else {
            $this->constraint = new IsInstanceOf(
                $type
            );
        }
        $this->type = $type;
    }
    public function evaluate($other, $description = '', $returnResult = false)
    {
        $success = true;
        foreach ($other as $item) {
            if (!$this->constraint->evaluate($item, '', true)) {
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
        return 'contains only values of type "' . $this->type . '"';
    }
}
