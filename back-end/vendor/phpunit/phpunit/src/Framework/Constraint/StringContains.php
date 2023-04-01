<?php
namespace PHPUnit\Framework\Constraint;
class StringContains extends Constraint
{
    private $string;
    private $ignoreCase;
    public function __construct(string $string, bool $ignoreCase = false)
    {
        parent::__construct();
        $this->string     = $string;
        $this->ignoreCase = $ignoreCase;
    }
    public function toString(): string
    {
        if ($this->ignoreCase) {
            $string = \mb_strtolower($this->string);
        } else {
            $string = $this->string;
        }
        return \sprintf(
            'contains "%s"',
            $string
        );
    }
    protected function matches($other): bool
    {
        if ('' === $this->string) {
            return true;
        }
        if ($this->ignoreCase) {
            return \mb_stripos($other, $this->string) !== false;
        }
        return \mb_strpos($other, $this->string) !== false;
    }
}
