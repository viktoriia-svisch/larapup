<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Util\Json;
use SebastianBergmann\Comparator\ComparisonFailure;
class JsonMatches extends Constraint
{
    private $value;
    public function __construct(string $value)
    {
        parent::__construct();
        $this->value = $value;
    }
    public function toString(): string
    {
        return \sprintf(
            'matches JSON string "%s"',
            $this->value
        );
    }
    protected function matches($other): bool
    {
        [$error, $recodedOther] = Json::canonicalize($other);
        if ($error) {
            return false;
        }
        [$error, $recodedValue] = Json::canonicalize($this->value);
        if ($error) {
            return false;
        }
        return $recodedOther == $recodedValue;
    }
    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null): void
    {
        if ($comparisonFailure === null) {
            [$error] = Json::canonicalize($other);
            if ($error) {
                parent::fail($other, $description);
                return;
            }
            [$error] = Json::canonicalize($this->value);
            if ($error) {
                parent::fail($other, $description);
                return;
            }
            $comparisonFailure = new ComparisonFailure(
                \json_decode($this->value),
                \json_decode($other),
                Json::prettify($this->value),
                Json::prettify($other),
                false,
                'Failed asserting that two json values are equal.'
            );
        }
        parent::fail($other, $description, $comparisonFailure);
    }
}
