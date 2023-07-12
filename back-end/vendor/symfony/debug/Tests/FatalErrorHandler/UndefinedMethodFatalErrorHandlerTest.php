<?php
namespace Symfony\Component\Debug\Tests\FatalErrorHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\FatalErrorHandler\UndefinedMethodFatalErrorHandler;
class UndefinedMethodFatalErrorHandlerTest extends TestCase
{
    public function testUndefinedMethod($error, $translatedMessage)
    {
        $handler = new UndefinedMethodFatalErrorHandler();
        $exception = $handler->handleError($error, new FatalErrorException('', 0, $error['type'], $error['file'], $error['line']));
        $this->assertInstanceOf('Symfony\Component\Debug\Exception\UndefinedMethodException', $exception);
        $this->assertSame($translatedMessage, $exception->getMessage());
        $this->assertSame($error['type'], $exception->getSeverity());
        $this->assertSame($error['file'], $exception->getFile());
        $this->assertSame($error['line'], $exception->getLine());
    }
    public function provideUndefinedMethodData()
    {
        return [
            [
                [
                    'type' => 1,
                    'line' => 12,
                    'file' => 'foo.php',
                    'message' => 'Call to undefined method SplObjectStorage::what()',
                ],
                'Attempted to call an undefined method named "what" of class "SplObjectStorage".',
            ],
            [
                [
                    'type' => 1,
                    'line' => 12,
                    'file' => 'foo.php',
                    'message' => 'Call to undefined method SplObjectStorage::walid()',
                ],
                "Attempted to call an undefined method named \"walid\" of class \"SplObjectStorage\".\nDid you mean to call \"valid\"?",
            ],
            [
                [
                    'type' => 1,
                    'line' => 12,
                    'file' => 'foo.php',
                    'message' => 'Call to undefined method SplObjectStorage::offsetFet()',
                ],
                "Attempted to call an undefined method named \"offsetFet\" of class \"SplObjectStorage\".\nDid you mean to call e.g. \"offsetGet\", \"offsetSet\" or \"offsetUnset\"?",
            ],
            [
                [
                    'type' => 1,
                    'message' => 'Call to undefined method class@anonymous::test()',
                    'file' => '/home/possum/work/symfony/test.php',
                    'line' => 11,
                ],
                'Attempted to call an undefined method named "test" of class "class@anonymous".',
            ],
        ];
    }
}
