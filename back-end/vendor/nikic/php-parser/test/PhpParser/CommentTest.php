<?php declare(strict_types=1);
namespace PhpParser;
class CommentTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSet() {
        $comment = new Comment('', 1, 10, 2);
        $this->assertSame('', $comment->getText());
        $this->assertSame('', (string) $comment);
        $this->assertSame(1, $comment->getLine());
        $this->assertSame(10, $comment->getFilePos());
        $this->assertSame(2, $comment->getTokenPos());
    }
    public function testReformatting($commentText, $reformattedText) {
        $comment = new Comment($commentText);
        $this->assertSame($reformattedText, $comment->getReformattedText());
    }
    public function provideTestReformatting() {
        return [
            ['
            ['', ''],
            [
                '',
                ''
            ],
            [
                '',
                ''
            ],
            [
                '',
                ''
            ],
            [
                '',
                '',
            ],
            [
                'hallo
    world',
                'hallo
    world',
            ],
        ];
    }
}
