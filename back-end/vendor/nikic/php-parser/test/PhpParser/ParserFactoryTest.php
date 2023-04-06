<?php declare(strict_types=1);
namespace PhpParser;
class ParserFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate($kind, $lexer, $expected) {
        $this->assertInstanceOf($expected, (new ParserFactory)->create($kind, $lexer));
    }
    public function provideTestCreate() {
        $lexer = new Lexer();
        return [
            [
                ParserFactory::PREFER_PHP7, $lexer,
                Parser\Multiple::class
            ],
            [
                ParserFactory::PREFER_PHP5, null,
                Parser\Multiple::class
            ],
            [
                ParserFactory::ONLY_PHP7, null,
                Parser\Php7::class
            ],
            [
                ParserFactory::ONLY_PHP5, $lexer,
                Parser\Php5::class
            ]
        ];
    }
}
