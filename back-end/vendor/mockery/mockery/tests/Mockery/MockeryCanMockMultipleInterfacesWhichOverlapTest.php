<?php
namespace Mockery\Tests;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class GeneratorTest extends MockeryTestCase
{
    public function shouldNotDuplicateDoublyInheritedMethods()
    {
        $container = new \Mockery\Container;
        $mock = $container->mock('Mockery\Tests\Evenement_EventEmitter', 'Mockery\Tests\Chatroulette_ConnectionInterface');
    }
}
interface Evenement_EventEmitterInterface
{
    public function on($name, $callback);
}
class Evenement_EventEmitter implements Evenement_EventEmitterInterface
{
    public function on($name, $callback)
    {
    }
}
interface React_StreamInterface extends Evenement_EventEmitterInterface
{
    public function close();
}
interface React_ReadableStreamInterface extends React_StreamInterface
{
    public function pause();
}
interface React_WritableStreamInterface extends React_StreamInterface
{
    public function write($data);
}
interface Chatroulette_ConnectionInterface extends React_ReadableStreamInterface, React_WritableStreamInterface
{
}
