<?php
namespace Psy\Test\VersionUpdater;
use Psy\Shell;
class GitHubCheckerTest extends \PHPUnit\Framework\TestCase
{
    public function testExceptionInvocation($input)
    {
        $checker = $this->getMockBuilder('Psy\\VersionUpdater\\GitHubChecker')
            ->setMethods(['fetchLatestRelease'])
            ->getMock();
        $checker->expects($this->once())->method('fetchLatestRelease')->willReturn($input);
        $checker->isLatest();
    }
    public function testDataSetResults($assertion, $input)
    {
        $checker = $this->getMockBuilder('Psy\\VersionUpdater\\GitHubChecker')
            ->setMethods(['fetchLatestRelease'])
            ->getMock();
        $checker->expects($this->once())->method('fetchLatestRelease')->willReturn($input);
        $this->assertSame($assertion, $checker->isLatest());
    }
    public function jsonResults()
    {
        return [
            [false, \json_decode('{"tag_name":"v9.0.0"}')],
            [true, \json_decode('{"tag_name":"v' . Shell::VERSION . '"}')],
            [true, \json_decode('{"tag_name":"v0.0.1"}')],
            [true, \json_decode('{"tag_name":"v0.4.1-alpha"}')],
            [true, \json_decode('{"tag_name":"v0.4.2-beta3"}')],
            [true, \json_decode('{"tag_name":"v0.0.1"}')],
            [true, \json_decode('{"tag_name":""}')],
        ];
    }
    public function malformedResults()
    {
        return [
            [null],
            [false],
            [true],
            [\json_decode('{"foo":"bar"}')],
            [\json_decode('{}')],
            [\json_decode('[]')],
            [[]],
            [\json_decode('{"tag_name":false"}')],
            [\json_decode('{"tag_name":true"}')],
        ];
    }
}
