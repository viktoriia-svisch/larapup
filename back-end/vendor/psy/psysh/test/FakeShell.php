<?php
namespace Psy\Test;
use Psy\Shell;
class FakeShell extends Shell
{
    public $matchers;
    public function __construct(Configuration $config = null)
    {
    }
    public function addMatchers(array $matchers)
    {
        $this->matchers = $matchers;
    }
}
