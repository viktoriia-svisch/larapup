<?php
namespace Symfony\Component\Console\Tests\Fixtures;
use Symfony\Component\Console\Application;
class DescriptorApplicationMbString extends Application
{
    public function __construct()
    {
        parent::__construct('MbString åpplicätion');
        $this->add(new DescriptorCommandMbString());
    }
}
