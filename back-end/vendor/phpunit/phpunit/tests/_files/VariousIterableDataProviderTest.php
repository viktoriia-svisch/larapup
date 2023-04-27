<?php
class VariousIterableDataProviderTest
{
    public static function asArrayProvider()
    {
        return [
            ['A'],
            ['B'],
            ['C'],
        ];
    }
    public static function asIteratorProvider()
    {
        yield ['D'];
        yield ['E'];
        yield ['F'];
    }
    public static function asTraversableProvider()
    {
        return new WrapperIteratorAggregate([
            ['G'],
            ['H'],
            ['I'],
        ]);
    }
    public function test(): void
    {
    }
}
