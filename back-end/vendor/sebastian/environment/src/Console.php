<?php declare(strict_types=1);
namespace SebastianBergmann\Environment;
final class Console
{
    public const STDIN  = 0;
    public const STDOUT = 1;
    public const STDERR = 2;
    public function hasColorSupport(): bool
    {
        if ('Hyper' === \getenv('TERM_PROGRAM')) {
            return true;
        }
        if ($this->isWindows()) {
            return (\defined('STDOUT') && \function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support(\STDOUT))
                || false !== \getenv('ANSICON')
                || 'ON' === \getenv('ConEmuANSI')
                || 'xterm' === \getenv('TERM');
        }
        if (!\defined('STDOUT')) {
            return false;
        }
        if ($this->isInteractive(\STDOUT)) {
            return true;
        }
        $stat = @\fstat(\STDOUT);
        return $stat ? 0020000 === ($stat['mode'] & 0170000) : false;
    }
    public function getNumberOfColumns(): int
    {
        if ($this->isWindows()) {
            return $this->getNumberOfColumnsWindows();
        }
        if (!$this->isInteractive(\defined('STDIN') ? \STDIN : self::STDIN)) {
            return 80;
        }
        return $this->getNumberOfColumnsInteractive();
    }
    public function isInteractive($fileDescriptor = self::STDOUT): bool
    {
        return (\is_resource($fileDescriptor) && \function_exists('stream_isatty') && @\stream_isatty($fileDescriptor)) 
            || (\function_exists('posix_isatty') && @\posix_isatty($fileDescriptor));
    }
    private function isWindows(): bool
    {
        return \DIRECTORY_SEPARATOR === '\\';
    }
    private function getNumberOfColumnsInteractive(): int
    {
        if (\function_exists('shell_exec') && \preg_match('#\d+ (\d+)#', \shell_exec('stty size') ?? '', $match) === 1) {
            if ((int) $match[1] > 0) {
                return (int) $match[1];
            }
        }
        if (\function_exists('shell_exec') && \preg_match('#columns = (\d+);#', \shell_exec('stty') ?? '', $match) === 1) {
            if ((int) $match[1] > 0) {
                return (int) $match[1];
            }
        }
        return 80;
    }
    private function getNumberOfColumnsWindows(): int
    {
        $ansicon = \getenv('ANSICON');
        $columns = 80;
        if (\is_string($ansicon) && \preg_match('/^(\d+)x\d+ \(\d+x(\d+)\)$/', \trim($ansicon), $matches)) {
            $columns = $matches[1];
        } elseif (\function_exists('proc_open')) {
            $process = \proc_open(
                'mode CON',
                [
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes,
                null,
                null,
                ['suppress_errors' => true]
            );
            if (\is_resource($process)) {
                $info = \stream_get_contents($pipes[1]);
                \fclose($pipes[1]);
                \fclose($pipes[2]);
                \proc_close($process);
                if (\preg_match('/--------+\r?\n.+?(\d+)\r?\n.+?(\d+)\r?\n/', $info, $matches)) {
                    $columns = $matches[2];
                }
            }
        }
        return $columns - 1;
    }
}
