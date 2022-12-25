<?php
namespace PHPUnit\Framework\Constraint;
class IsJson extends Constraint
{
    public function toString(): string
    {
        return 'is valid JSON';
    }
    protected function matches($other): bool
    {
        if ($other === '') {
            return false;
        }
        \json_decode($other);
        if (\json_last_error()) {
            return false;
        }
        return true;
    }
    protected function failureDescription($other): string
    {
        if ($other === '') {
            return 'an empty string is valid JSON';
        }
        \json_decode($other);
        $error = JsonMatchesErrorMessageProvider::determineJsonError(
            \json_last_error()
        );
        return \sprintf(
            '%s is valid JSON (%s)',
            $this->exporter->shortenedExport($other),
            $error
        );
    }
}
