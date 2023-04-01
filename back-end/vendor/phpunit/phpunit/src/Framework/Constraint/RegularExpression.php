<?php
namespace PHPUnit\Framework\Constraint;
class RegularExpression extends Constraint
{
    private $pattern;
    public function __construct(string $pattern)
    {
        parent::__construct();
        $this->pattern = $pattern;
    }
    public function toString(): string
    {
        return \sprintf(
            'matches PCRE pattern "%s"',
            $this->pattern
        );
    }
    protected function matches($other): bool
    {
        return \preg_match($this->pattern, $other) > 0;
    }
}
