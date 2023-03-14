<?php
namespace Symfony\Component\Process\Tests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
class ProcessFailedExceptionTest extends TestCase
{
    public function testProcessFailedExceptionThrowsException()
    {
        $process = $this->getMockBuilder('Symfony\Component\Process\Process')->setMethods(['isSuccessful'])->setConstructorArgs([['php']])->getMock();
        $process->expects($this->once())
            ->method('isSuccessful')
            ->will($this->returnValue(true));
        if (method_exists($this, 'expectException')) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Expected a failed process, but the given process was successful.');
        } else {
            $this->setExpectedException(\InvalidArgumentException::class, 'Expected a failed process, but the given process was successful.');
        }
        new ProcessFailedException($process);
    }
    public function testProcessFailedExceptionPopulatesInformationFromProcessOutput()
    {
        $cmd = 'php';
        $exitCode = 1;
        $exitText = 'General error';
        $output = 'Command output';
        $errorOutput = 'FATAL: Unexpected error';
        $workingDirectory = getcwd();
        $process = $this->getMockBuilder('Symfony\Component\Process\Process')->setMethods(['isSuccessful', 'getOutput', 'getErrorOutput', 'getExitCode', 'getExitCodeText', 'isOutputDisabled', 'getWorkingDirectory'])->setConstructorArgs([[$cmd]])->getMock();
        $process->expects($this->once())
            ->method('isSuccessful')
            ->will($this->returnValue(false));
        $process->expects($this->once())
            ->method('getOutput')
            ->will($this->returnValue($output));
        $process->expects($this->once())
            ->method('getErrorOutput')
            ->will($this->returnValue($errorOutput));
        $process->expects($this->once())
            ->method('getExitCode')
            ->will($this->returnValue($exitCode));
        $process->expects($this->once())
            ->method('getExitCodeText')
            ->will($this->returnValue($exitText));
        $process->expects($this->once())
            ->method('isOutputDisabled')
            ->will($this->returnValue(false));
        $process->expects($this->once())
            ->method('getWorkingDirectory')
            ->will($this->returnValue($workingDirectory));
        $exception = new ProcessFailedException($process);
        $this->assertEquals(
            "The command \"$cmd\" failed.\n\nExit Code: $exitCode($exitText)\n\nWorking directory: {$workingDirectory}\n\nOutput:\n================\n{$output}\n\nError Output:\n================\n{$errorOutput}",
            str_replace("'php'", 'php', $exception->getMessage())
        );
    }
    public function testDisabledOutputInFailedExceptionDoesNotPopulateOutput()
    {
        $cmd = 'php';
        $exitCode = 1;
        $exitText = 'General error';
        $workingDirectory = getcwd();
        $process = $this->getMockBuilder('Symfony\Component\Process\Process')->setMethods(['isSuccessful', 'isOutputDisabled', 'getExitCode', 'getExitCodeText', 'getOutput', 'getErrorOutput', 'getWorkingDirectory'])->setConstructorArgs([[$cmd]])->getMock();
        $process->expects($this->once())
            ->method('isSuccessful')
            ->will($this->returnValue(false));
        $process->expects($this->never())
            ->method('getOutput');
        $process->expects($this->never())
            ->method('getErrorOutput');
        $process->expects($this->once())
            ->method('getExitCode')
            ->will($this->returnValue($exitCode));
        $process->expects($this->once())
            ->method('getExitCodeText')
            ->will($this->returnValue($exitText));
        $process->expects($this->once())
            ->method('isOutputDisabled')
            ->will($this->returnValue(true));
        $process->expects($this->once())
            ->method('getWorkingDirectory')
            ->will($this->returnValue($workingDirectory));
        $exception = new ProcessFailedException($process);
        $this->assertEquals(
            "The command \"$cmd\" failed.\n\nExit Code: $exitCode($exitText)\n\nWorking directory: {$workingDirectory}",
            str_replace("'php'", 'php', $exception->getMessage())
        );
    }
}
