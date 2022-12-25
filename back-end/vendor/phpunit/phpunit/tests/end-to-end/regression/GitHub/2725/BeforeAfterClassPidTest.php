<?php
namespace Issue2725;
use PHPUnit\Framework\TestCase;
class BeforeAfterClassPidTest extends TestCase
{
    public const PID_VARIABLE = 'current_pid';
    public static function showPidBefore(): void
    {
        $GLOBALS[static::PID_VARIABLE] = \getmypid();
    }
    public static function showPidAfter(): void
    {
        if ($GLOBALS[static::PID_VARIABLE] - \getmypid() !== 0) {
            print "\n@afterClass output - PID difference should be zero!";
        }
        unset($GLOBALS[static::PID_VARIABLE]);
    }
    public function testMethod1WithItsBeforeAndAfter(): void
    {
        $this->assertEquals($GLOBALS[static::PID_VARIABLE], \getmypid());
    }
    public function testMethod2WithItsBeforeAndAfter(): void
    {
        $this->assertEquals($GLOBALS[static::PID_VARIABLE], \getmypid());
    }
}
