<?php
namespace PHPUnit\Framework\Constraint;
use SebastianBergmann\Diff\Differ;
class StringMatchesFormatDescription extends RegularExpression
{
    private $string;
    public function __construct(string $string)
    {
        parent::__construct(
            $this->createPatternFromFormat(
                $this->convertNewlines($string)
            )
        );
        $this->string = $string;
    }
    protected function matches($other): bool
    {
        return parent::matches(
            $this->convertNewlines($other)
        );
    }
    protected function failureDescription($other): string
    {
        return 'string matches format description';
    }
    protected function additionalFailureDescription($other): string
    {
        $from = \explode("\n", $this->string);
        $to   = \explode("\n", $this->convertNewlines($other));
        foreach ($from as $index => $line) {
            if (isset($to[$index]) && $line !== $to[$index]) {
                $line = $this->createPatternFromFormat($line);
                if (\preg_match($line, $to[$index]) > 0) {
                    $from[$index] = $to[$index];
                }
            }
        }
        $this->string = \implode("\n", $from);
        $other        = \implode("\n", $to);
        $differ = new Differ("--- Expected\n+++ Actual\n");
        return $differ->diff($this->string, $other);
    }
    private function createPatternFromFormat(string $string): string
    {
        $string = \strtr(
            \preg_quote($string, '/'),
            [
                '%%' => '%',
                '%e' => '\\' . \DIRECTORY_SEPARATOR,
                '%s' => '[^\r\n]+',
                '%S' => '[^\r\n]*',
                '%a' => '.+',
                '%A' => '.*',
                '%w' => '\s*',
                '%i' => '[+-]?\d+',
                '%d' => '\d+',
                '%x' => '[0-9a-fA-F]+',
                '%f' => '[+-]?\.?\d+\.?\d*(?:[Ee][+-]?\d+)?',
                '%c' => '.',
            ]
        );
        return '/^' . $string . '$/s';
    }
    private function convertNewlines($text): string
    {
        return \preg_replace('/\r\n/', "\n", $text);
    }
}
