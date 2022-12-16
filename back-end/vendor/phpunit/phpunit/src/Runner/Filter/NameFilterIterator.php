<?php
namespace PHPUnit\Runner\Filter;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\WarningTestCase;
use PHPUnit\Util\RegularExpression;
use RecursiveFilterIterator;
use RecursiveIterator;
class NameFilterIterator extends RecursiveFilterIterator
{
    protected $filter;
    protected $filterMin;
    protected $filterMax;
    public function __construct(RecursiveIterator $iterator, string $filter)
    {
        parent::__construct($iterator);
        $this->setFilter($filter);
    }
    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();
        if ($test instanceof TestSuite) {
            return true;
        }
        $tmp = \PHPUnit\Util\Test::describe($test);
        if ($test instanceof WarningTestCase) {
            $name = $test->getMessage();
        } else {
            if ($tmp[0] !== '') {
                $name = \implode('::', $tmp);
            } else {
                $name = $tmp[1];
            }
        }
        $accepted = @\preg_match($this->filter, $name, $matches);
        if ($accepted && isset($this->filterMax)) {
            $set      = \end($matches);
            $accepted = $set >= $this->filterMin && $set <= $this->filterMax;
        }
        return (bool) $accepted;
    }
    protected function setFilter(string $filter): void
    {
        if (RegularExpression::safeMatch($filter, '') === false) {
            if (\preg_match('/^(.*?)#(\d+)(?:-(\d+))?$/', $filter, $matches)) {
                if (isset($matches[3]) && $matches[2] < $matches[3]) {
                    $filter = \sprintf(
                        '%s.*with data set #(\d+)$',
                        $matches[1]
                    );
                    $this->filterMin = $matches[2];
                    $this->filterMax = $matches[3];
                } else {
                    $filter = \sprintf(
                        '%s.*with data set #%s$',
                        $matches[1],
                        $matches[2]
                    );
                }
            } 
            elseif (\preg_match('/^(.*?)@(.+)$/', $filter, $matches)) {
                $filter = \sprintf(
                    '%s.*with data set "%s"$',
                    $matches[1],
                    $matches[2]
                );
            }
            $filter = \sprintf('/%s/i', \str_replace(
                '/',
                '\\/',
                $filter
            ));
        }
        $this->filter = $filter;
    }
}
