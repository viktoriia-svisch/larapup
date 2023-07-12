<?php declare(strict_types=1);
namespace PhpParser;
use PhpParser\Parser\Tokens;
class LexerTest extends \PHPUnit\Framework\TestCase
{
    protected function getLexer(array $options = []) {
        return new Lexer($options);
    }
    public function testError($code, $messages) {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM does not throw warnings from token_get_all()');
        }
        $errorHandler = new ErrorHandler\Collecting();
        $lexer = $this->getLexer(['usedAttributes' => [
            'comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'
        ]]);
        $lexer->startLexing($code, $errorHandler);
        $errors = $errorHandler->getErrors();
        $this->assertCount(count($messages), $errors);
        for ($i = 0; $i < count($messages); $i++) {
            $this->assertSame($messages[$i], $errors[$i]->getMessageWithColumnInfo($code));
        }
    }
    public function provideTestError() {
        return [
            ["<?php 
    public function testLex($code, $options, $tokens) {
        $lexer = $this->getLexer($options);
        $lexer->startLexing($code);
        while ($id = $lexer->getNextToken($value, $startAttributes, $endAttributes)) {
            $token = array_shift($tokens);
            $this->assertSame($token[0], $id);
            $this->assertSame($token[1], $value);
            $this->assertEquals($token[2], $startAttributes);
            $this->assertEquals($token[3], $endAttributes);
        }
    }
    public function provideTestLex() {
        return [
            [
                '<?php tokens ?>plaintext',
                [],
                [
                    [
                        Tokens::T_STRING, 'tokens',
                        ['startLine' => 1], ['endLine' => 1]
                    ],
                    [
                        ord(';'), '?>',
                        ['startLine' => 1], ['endLine' => 1]
                    ],
                    [
                        Tokens::T_INLINE_HTML, 'plaintext',
                        ['startLine' => 1, 'hasLeadingNewline' => false],
                        ['endLine' => 1]
                    ],
                ]
            ],
            [
                '<?php' . "\n" . '$ token  $',
                [],
                [
                    [
                        ord('$'), '$',
                        ['startLine' => 2], ['endLine' => 2]
                    ],
                    [
                        Tokens::T_STRING, 'token',
                        ['startLine' => 2], ['endLine' => 2]
                    ],
                    [
                        ord('$'), '$',
                        [
                            'startLine' => 3,
                            'comments' => [
                                new Comment\Doc('', 2, 14, 5),
                            ]
                        ],
                        ['endLine' => 3]
                    ],
                ]
            ],
            [
                '<?php  
                [],
                [
                    [
                        Tokens::T_STRING, 'token',
                        [
                            'startLine' => 2,
                            'comments' => [
                                new Comment('', 1, 6, 1),
                                new Comment('
                                new Comment\Doc('', 2, 31, 4),
                                new Comment\Doc('', 2, 50, 5),
                            ],
                        ],
                        ['endLine' => 2]
                    ],
                ]
            ],
            [
                '<?php "foo' . "\n" . 'bar"',
                [],
                [
                    [
                        Tokens::T_CONSTANT_ENCAPSED_STRING, '"foo' . "\n" . 'bar"',
                        ['startLine' => 1], ['endLine' => 2]
                    ],
                ]
            ],
            [
                '<?php "a";' . "\n" . '
                ['usedAttributes' => ['startFilePos', 'endFilePos']],
                [
                    [
                        Tokens::T_CONSTANT_ENCAPSED_STRING, '"a"',
                        ['startFilePos' => 6], ['endFilePos' => 8]
                    ],
                    [
                        ord(';'), ';',
                        ['startFilePos' => 9], ['endFilePos' => 9]
                    ],
                    [
                        Tokens::T_CONSTANT_ENCAPSED_STRING, '"b"',
                        ['startFilePos' => 18], ['endFilePos' => 20]
                    ],
                    [
                        ord(';'), ';',
                        ['startFilePos' => 21], ['endFilePos' => 21]
                    ],
                ]
            ],
            [
                '<?php "a";' . "\n" . '
                ['usedAttributes' => ['startTokenPos', 'endTokenPos']],
                [
                    [
                        Tokens::T_CONSTANT_ENCAPSED_STRING, '"a"',
                        ['startTokenPos' => 1], ['endTokenPos' => 1]
                    ],
                    [
                        ord(';'), ';',
                        ['startTokenPos' => 2], ['endTokenPos' => 2]
                    ],
                    [
                        Tokens::T_CONSTANT_ENCAPSED_STRING, '"b"',
                        ['startTokenPos' => 5], ['endTokenPos' => 5]
                    ],
                    [
                        ord(';'), ';',
                        ['startTokenPos' => 6], ['endTokenPos' => 6]
                    ],
                ]
            ],
            [
                '<?php  $bar;',
                ['usedAttributes' => []],
                [
                    [
                        Tokens::T_VARIABLE, '$bar',
                        [], []
                    ],
                    [
                        ord(';'), ';',
                        [], []
                    ]
                ]
            ],
            [
                '',
                [],
                []
            ],
        ];
    }
    public function testHandleHaltCompiler($code, $remaining) {
        $lexer = $this->getLexer();
        $lexer->startLexing($code);
        while (Tokens::T_HALT_COMPILER !== $lexer->getNextToken());
        $this->assertSame($remaining, $lexer->handleHaltCompiler());
        $this->assertSame(0, $lexer->getNextToken());
    }
    public function provideTestHaltCompiler() {
        return [
            ['<?php ... __halt_compiler();Remaining Text', 'Remaining Text'],
            ['<?php ... __halt_compiler ( ) ;Remaining Text', 'Remaining Text'],
            ['<?php ... __halt_compiler() ?>Remaining Text', 'Remaining Text'],
        ];
    }
    public function testHandleHaltCompilerError() {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('__HALT_COMPILER must be followed by "();"');
        $lexer = $this->getLexer();
        $lexer->startLexing('<?php ... __halt_compiler invalid ();');
        while (Tokens::T_HALT_COMPILER !== $lexer->getNextToken());
        $lexer->handleHaltCompiler();
    }
    public function testGetTokens() {
        $code = '<?php "a";' . "\n" . '
        $expectedTokens = [
            [T_OPEN_TAG, '<?php ', 1],
            [T_CONSTANT_ENCAPSED_STRING, '"a"', 1],
            ';',
            [T_WHITESPACE, "\n", 1],
            [T_COMMENT, '
            [T_CONSTANT_ENCAPSED_STRING, '"b"', 3],
            ';',
        ];
        $lexer = $this->getLexer();
        $lexer->startLexing($code);
        $this->assertSame($expectedTokens, $lexer->getTokens());
    }
}
