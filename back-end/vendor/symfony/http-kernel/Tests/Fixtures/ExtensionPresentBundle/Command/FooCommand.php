<?php
namespace Symfony\Component\HttpKernel\Tests\Fixtures\ExtensionPresentBundle\Command;
use Symfony\Component\Console\Command\Command;
class FooCommand extends Command
{
    protected function configure()
    {
        $this->setName('foo');
    }
}
