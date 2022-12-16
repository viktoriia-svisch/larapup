<?php
namespace Symfony\Component\Console\Tests\Helper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StreamableInputInterface;
abstract class AbstractQuestionHelperTest extends TestCase
{
    protected function createStreamableInputInterfaceMock($stream = null, $interactive = true)
    {
        $mock = $this->getMockBuilder(StreamableInputInterface::class)->getMock();
        $mock->expects($this->any())
            ->method('isInteractive')
            ->will($this->returnValue($interactive));
        if ($stream) {
            $mock->expects($this->any())
                ->method('getStream')
                ->willReturn($stream);
        }
        return $mock;
    }
}
