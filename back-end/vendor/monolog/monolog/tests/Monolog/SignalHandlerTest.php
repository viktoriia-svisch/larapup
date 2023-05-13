<?php
namespace Monolog;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Psr\Log\LogLevel;
class SignalHandlerTest extends TestCase
{
    private $asyncSignalHandling;
    private $blockedSignals;
    private $signalHandlers;
    protected function setUp()
    {
        $this->signalHandlers = array();
        if (extension_loaded('pcntl')) {
            if (function_exists('pcntl_async_signals')) {
                $this->asyncSignalHandling = pcntl_async_signals();
            }
            if (function_exists('pcntl_sigprocmask')) {
                pcntl_sigprocmask(SIG_BLOCK, array(), $this->blockedSignals);
            }
        }
    }
    protected function tearDown()
    {
        if ($this->asyncSignalHandling !== null) {
            pcntl_async_signals($this->asyncSignalHandling);
        }
        if ($this->blockedSignals !== null) {
            pcntl_sigprocmask(SIG_SETMASK, $this->blockedSignals);
        }
        if ($this->signalHandlers) {
            pcntl_signal_dispatch();
            foreach ($this->signalHandlers as $signo => $handler) {
                pcntl_signal($signo, $handler);
            }
        }
    }
    private function setSignalHandler($signo, $handler = SIG_DFL) {
        if (function_exists('pcntl_signal_get_handler')) {
            $this->signalHandlers[$signo] = pcntl_signal_get_handler($signo);
        } else {
            $this->signalHandlers[$signo] = SIG_DFL;
        }
        $this->assertTrue(pcntl_signal($signo, $handler));
    }
    public function testHandleSignal()
    {
        $logger = new Logger('test', array($handler = new TestHandler));
        $errHandler = new SignalHandler($logger);
        $signo = 2;  
        $siginfo = array('signo' => $signo, 'errno' => 0, 'code' => 0);
        $errHandler->handleSignal($signo, $siginfo);
        $this->assertCount(1, $handler->getRecords());
        $this->assertTrue($handler->hasCriticalRecords());
        $records = $handler->getRecords();
        $this->assertSame($siginfo, $records[0]['context']);
    }
    public function testRegisterSignalHandler()
    {
        if (!defined('SIGCONT') || !defined('SIGURG')) {
            $this->markTestSkipped('This test requires the SIGCONT and SIGURG pcntl constants.');
        }
        $this->setSignalHandler(SIGCONT, SIG_IGN);
        $this->setSignalHandler(SIGURG, SIG_IGN);
        $logger = new Logger('test', array($handler = new TestHandler));
        $errHandler = new SignalHandler($logger);
        $pid = posix_getpid();
        $this->assertTrue(posix_kill($pid, SIGURG));
        $this->assertTrue(pcntl_signal_dispatch());
        $this->assertCount(0, $handler->getRecords());
        $errHandler->registerSignalHandler(SIGURG, LogLevel::INFO, false, false, false);
        $this->assertTrue(posix_kill($pid, SIGCONT));
        $this->assertTrue(pcntl_signal_dispatch());
        $this->assertCount(0, $handler->getRecords());
        $this->assertTrue(posix_kill($pid, SIGURG));
        $this->assertTrue(pcntl_signal_dispatch());
        $this->assertCount(1, $handler->getRecords());
        $this->assertTrue($handler->hasInfoThatContains('SIGURG'));
    }
    public function testRegisterDefaultPreviousSignalHandler($signo, $callPrevious, $expected)
    {
        $this->setSignalHandler($signo, SIG_DFL);
        $path = tempnam(sys_get_temp_dir(), 'monolog-');
        $this->assertNotFalse($path);
        $pid = pcntl_fork();
        if ($pid === 0) {  
            $streamHandler = new StreamHandler($path);
            $streamHandler->setFormatter($this->getIdentityFormatter());
            $logger = new Logger('test', array($streamHandler));
            $errHandler = new SignalHandler($logger);
            $errHandler->registerSignalHandler($signo, LogLevel::INFO, $callPrevious, false, false);
            pcntl_sigprocmask(SIG_SETMASK, array(SIGCONT));
            posix_kill(posix_getpid(), $signo);
            pcntl_signal_dispatch();
            pcntl_sigprocmask(SIG_BLOCK, array(), $oldset);
            file_put_contents($path, implode(' ', $oldset), FILE_APPEND);
            posix_kill(posix_getpid(), $signo);
            pcntl_signal_dispatch();
            exit();
        }
        $this->assertNotSame(-1, $pid);
        $this->assertNotSame(-1, pcntl_waitpid($pid, $status));
        $this->assertNotSame(-1, $status);
        $this->assertSame($expected, file_get_contents($path));
    }
    public function defaultPreviousProvider()
    {
        if (!defined('SIGCONT') || !defined('SIGINT') || !defined('SIGURG')) {
            return array();
        }
        return array(
            array(SIGINT, false, 'Program received signal SIGINT'.SIGCONT.'Program received signal SIGINT'),
            array(SIGINT, true, 'Program received signal SIGINT'),
            array(SIGURG, false, 'Program received signal SIGURG'.SIGCONT.'Program received signal SIGURG'),
            array(SIGURG, true, 'Program received signal SIGURG'.SIGCONT.'Program received signal SIGURG'),
        );
    }
    public function testRegisterCallablePreviousSignalHandler($callPrevious)
    {
        $this->setSignalHandler(SIGURG, SIG_IGN);
        $logger = new Logger('test', array($handler = new TestHandler));
        $errHandler = new SignalHandler($logger);
        $previousCalled = 0;
        pcntl_signal(SIGURG, function ($signo, array $siginfo = null) use (&$previousCalled) {
            ++$previousCalled;
        });
        $errHandler->registerSignalHandler(SIGURG, LogLevel::INFO, $callPrevious, false, false);
        $this->assertTrue(posix_kill(posix_getpid(), SIGURG));
        $this->assertTrue(pcntl_signal_dispatch());
        $this->assertCount(1, $handler->getRecords());
        $this->assertTrue($handler->hasInfoThatContains('SIGURG'));
        $this->assertSame($callPrevious ? 1 : 0, $previousCalled);
    }
    public function callablePreviousProvider()
    {
        return array(
            array(false),
            array(true),
        );
    }
    public function testRegisterSyscallRestartingSignalHandler($restartSyscalls)
    {
        $this->setSignalHandler(SIGURG, SIG_IGN);
        $parentPid = posix_getpid();
        $microtime = microtime(true);
        $pid = pcntl_fork();
        if ($pid === 0) {  
            usleep(100000);
            posix_kill($parentPid, SIGURG);
            usleep(100000);
            exit();
        }
        $this->assertNotSame(-1, $pid);
        $logger = new Logger('test', array($handler = new TestHandler));
        $errHandler = new SignalHandler($logger);
        $errHandler->registerSignalHandler(SIGURG, LogLevel::INFO, false, $restartSyscalls, false);
        if ($restartSyscalls) {
            $this->assertNotSame(-1, pcntl_waitpid($pid, $status));
        } else {
            $this->assertSame(-1, pcntl_waitpid($pid, $status));
        }
        $this->assertSame($restartSyscalls, microtime(true) - $microtime > 0.15);
        $this->assertTrue(pcntl_signal_dispatch());
        $this->assertCount(1, $handler->getRecords());
        if ($restartSyscalls) {
            $this->assertSame(-1, pcntl_waitpid($pid, $status));
        } else {
            $this->assertNotSame(-1, pcntl_waitpid($pid, $status));
        }
    }
    public function restartSyscallsProvider()
    {
        return array(
            array(false),
            array(true),
            array(false),
            array(true),
        );
    }
    public function testRegisterAsyncSignalHandler($initialAsync, $desiredAsync, $expectedBefore, $expectedAfter)
    {
        $this->setSignalHandler(SIGURG, SIG_IGN);
        pcntl_async_signals($initialAsync);
        $logger = new Logger('test', array($handler = new TestHandler));
        $errHandler = new SignalHandler($logger);
        $errHandler->registerSignalHandler(SIGURG, LogLevel::INFO, false, false, $desiredAsync);
        $this->assertTrue(posix_kill(posix_getpid(), SIGURG));
        $this->assertCount($expectedBefore, $handler->getRecords());
        $this->assertTrue(pcntl_signal_dispatch());
        $this->assertCount($expectedAfter, $handler->getRecords());
    }
    public function asyncProvider()
    {
        return array(
            array(false, false, 0, 1),
            array(false, null, 0, 1),
            array(false, true, 1, 1),
            array(true, false, 0, 1),
            array(true, null, 1, 1),
            array(true, true, 1, 1),
        );
    }
}
