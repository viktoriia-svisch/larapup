<?php
namespace Mockery\Adapter\Phpunit;
if (class_exists('PHPUnit_Runner_Version') && version_compare(\PHPUnit_Runner_Version::id(), '6.0.0', '<')) {
    class_alias('Mockery\Adapter\Phpunit\Legacy\TestListenerForV5', 'Mockery\Adapter\Phpunit\TestListener');
} elseif (version_compare(\PHPUnit\Runner\Version::id(), '7.0.0', '<')) {
    class_alias('Mockery\Adapter\Phpunit\Legacy\TestListenerForV6', 'Mockery\Adapter\Phpunit\TestListener');
} else {
    class_alias('Mockery\Adapter\Phpunit\Legacy\TestListenerForV7', 'Mockery\Adapter\Phpunit\TestListener');
}
if (false) {
    class TestListener
    {
    }
}
