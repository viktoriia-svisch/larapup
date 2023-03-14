<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Util\Filter;
use Throwable;
class Exception extends Constraint
{
    private $className;
    public function __construct(string $className)
    {
        parent::__construct();
        $this->className = $className;
    }
    public function toString(): string
    {
        return \sprintf(
            'exception of type "%s"',
            $this->className
        );
    }
    protected function matches($other): bool
    {
        return $other instanceof $this->className;
    }
    protected function failureDescription($other): string
    {
        if ($other !== null) {
            $message = '';
            if ($other instanceof Throwable) {
                $message = '. Message was: "' . $other->getMessage() . '" at'
                    . "\n" . Filter::getFilteredStacktrace($other);
            }
            return \sprintf(
                'exception of type "%s" matches expected exception "%s"%s',
                \get_class($other),
                $this->className,
                $message
            );
        }
        return \sprintf(
            'exception of type "%s" is thrown',
            $this->className
        );
    }
}
