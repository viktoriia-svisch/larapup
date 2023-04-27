<?php
namespace PHPUnit\Util\PHP;
use PHPUnit\Framework\Exception;
class WindowsPhpProcess extends DefaultPhpProcess
{
    public function getCommand(array $settings, string $file = null): string
    {
        return '"' . parent::getCommand($settings, $file) . '"';
    }
    protected function getHandles(): array
    {
        if (false === $stdout_handle = \tmpfile()) {
            throw new Exception(
                'A temporary file could not be created; verify that your TEMP environment variable is writable'
            );
        }
        return [
            1 => $stdout_handle,
        ];
    }
    protected function useTemporaryFile(): bool
    {
        return true;
    }
}
