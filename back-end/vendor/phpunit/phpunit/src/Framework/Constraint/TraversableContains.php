<?php
namespace PHPUnit\Framework\Constraint;
use SplObjectStorage;
class TraversableContains extends Constraint
{
    private $checkForObjectIdentity;
    private $checkForNonObjectIdentity;
    private $value;
    public function __construct($value, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false)
    {
        parent::__construct();
        $this->checkForObjectIdentity    = $checkForObjectIdentity;
        $this->checkForNonObjectIdentity = $checkForNonObjectIdentity;
        $this->value                     = $value;
    }
    public function toString(): string
    {
        if (\is_string($this->value) && \strpos($this->value, "\n") !== false) {
            return 'contains "' . $this->value . '"';
        }
        return 'contains ' . $this->exporter->export($this->value);
    }
    protected function matches($other): bool
    {
        if ($other instanceof SplObjectStorage) {
            return $other->contains($this->value);
        }
        if (\is_object($this->value)) {
            foreach ($other as $element) {
                if ($this->checkForObjectIdentity && $element === $this->value) {
                    return true;
                }
                if (!$this->checkForObjectIdentity && $element == $this->value) {
                    return true;
                }
            }
        } else {
            foreach ($other as $element) {
                if ($this->checkForNonObjectIdentity && $element === $this->value) {
                    return true;
                }
                if (!$this->checkForNonObjectIdentity && $element == $this->value) {
                    return true;
                }
            }
        }
        return false;
    }
    protected function failureDescription($other): string
    {
        return \sprintf(
            '%s %s',
            \is_array($other) ? 'an array' : 'a traversable',
            $this->toString()
        );
    }
}
