<?php
namespace Illuminate\Database;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
abstract class Seeder
{
    protected $container;
    protected $command;
    public function call($class, $silent = false)
    {
        $classes = Arr::wrap($class);
        foreach ($classes as $class) {
            if ($silent === false && isset($this->command)) {
                $this->command->getOutput()->writeln("<info>Seeding:</info> $class");
            }
            $this->resolve($class)->__invoke();
        }
        return $this;
    }
    public function callSilent($class)
    {
        $this->call($class, true);
    }
    protected function resolve($class)
    {
        if (isset($this->container)) {
            $instance = $this->container->make($class);
            $instance->setContainer($this->container);
        } else {
            $instance = new $class;
        }
        if (isset($this->command)) {
            $instance->setCommand($this->command);
        }
        return $instance;
    }
    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }
    public function setCommand(Command $command)
    {
        $this->command = $command;
        return $this;
    }
    public function __invoke()
    {
        if (! method_exists($this, 'run')) {
            throw new InvalidArgumentException('Method [run] missing from '.get_class($this));
        }
        return isset($this->container)
                    ? $this->container->call([$this, 'run'])
                    : $this->run();
    }
}
