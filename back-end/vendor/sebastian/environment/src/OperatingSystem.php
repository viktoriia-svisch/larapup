<?php declare(strict_types=1);
namespace SebastianBergmann\Environment;
final class OperatingSystem
{
    public function getFamily(): string
    {
        if (\defined('PHP_OS_FAMILY')) {
            return \PHP_OS_FAMILY;
        }
        if (\DIRECTORY_SEPARATOR === '\\') {
            return 'Windows';
        }
        switch (\PHP_OS) {
            case 'Darwin':
                return 'Darwin';
            case 'DragonFly':
            case 'FreeBSD':
            case 'NetBSD':
            case 'OpenBSD':
                return 'BSD';
            case 'Linux':
                return 'Linux';
            case 'SunOS':
                return 'Solaris';
            default:
                return 'Unknown';
        }
    }
}
