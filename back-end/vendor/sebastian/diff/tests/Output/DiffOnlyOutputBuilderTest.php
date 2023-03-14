<?php declare(strict_types=1);
namespace SebastianBergmann\Diff\Output;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ;
final class DiffOnlyOutputBuilderTest extends TestCase
{
    public function testDiffDoNotShowNonDiffLines(string $expected, string $from, string $to, string $header = ''): void
    {
        $differ = new Differ(new DiffOnlyOutputBuilder($header));
        $this->assertSame($expected, $differ->diff($from, $to));
    }
    public function textForNoNonDiffLinesProvider(): array
    {
        return [
            [
                " #Warning: Strings contain different line endings!\n-A\r\n+B\n",
                "A\r\n",
                "B\n",
            ],
            [
                "-A\n+B\n",
                "\nA",
                "\nB",
            ],
            [
                '',
                'a',
                'a',
            ],
            [
                "-A\n+C\n",
                "A\n\n\nB",
                "C\n\n\nB",
            ],
            [
                "header\n",
                'a',
                'a',
                'header',
            ],
            [
                "header\n",
                'a',
                'a',
                "header\n",
            ],
        ];
    }
}
