<?php
namespace PHPUnit\Runner\Filter;
use PHPUnit\Framework\TestSuite;
use RecursiveFilterIterator;
use RecursiveIterator;
abstract class GroupFilterIterator extends RecursiveFilterIterator
{
    protected $groupTests = [];
    public function __construct(RecursiveIterator $iterator, array $groups, TestSuite $suite)
    {
        parent::__construct($iterator);
        foreach ($suite->getGroupDetails() as $group => $tests) {
            if (\in_array((string) $group, $groups, true)) {
                $testHashes = \array_map(
                    'spl_object_hash',
                    $tests
                );
                $this->groupTests = \array_merge($this->groupTests, $testHashes);
            }
        }
    }
    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();
        if ($test instanceof TestSuite) {
            return true;
        }
        return $this->doAccept(\spl_object_hash($test));
    }
    abstract protected function doAccept(string $hash);
}
