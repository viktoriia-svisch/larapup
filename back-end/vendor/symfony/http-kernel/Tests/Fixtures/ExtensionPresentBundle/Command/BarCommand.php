<?php
namespace Symfony\Component\HttpKernel\Tests\Fixtures\ExtensionPresentBundle\Command;
use Symfony\Component\Console\Command\Command;
class BarCommand extends Command
{
    public function __construct($example, $name = 'bar')
    {
    }
}
