<?php
namespace SebastianBergmann\CodeCoverage;
final class UnintentionallyCoveredCodeException extends RuntimeException
{
    private $unintentionallyCoveredUnits = [];
    public function __construct(array $unintentionallyCoveredUnits)
    {
        $this->unintentionallyCoveredUnits = $unintentionallyCoveredUnits;
        parent::__construct($this->toString());
    }
    public function getUnintentionallyCoveredUnits(): array
    {
        return $this->unintentionallyCoveredUnits;
    }
    private function toString(): string
    {
        $message = '';
        foreach ($this->unintentionallyCoveredUnits as $unit) {
            $message .= '- ' . $unit . "\n";
        }
        return $message;
    }
}
