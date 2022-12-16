<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\GeneratorCommand;
class ChannelMakeCommand extends GeneratorCommand
{
    protected $name = 'make:channel';
    protected $description = 'Create a new channel class';
    protected $type = 'Channel';
    protected function buildClass($name)
    {
        return str_replace(
            'DummyUser',
            class_basename($this->userProviderModel()),
            parent::buildClass($name)
        );
    }
    protected function getStub()
    {
        return __DIR__.'/stubs/channel.stub';
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Broadcasting';
    }
}
