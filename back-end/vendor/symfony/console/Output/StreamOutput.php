<?php
namespace Symfony\Component\Console\Output;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
class StreamOutput extends Output
{
    private $stream;
    public function __construct($stream, int $verbosity = self::VERBOSITY_NORMAL, bool $decorated = null, OutputFormatterInterface $formatter = null)
    {
        if (!\is_resource($stream) || 'stream' !== get_resource_type($stream)) {
            throw new InvalidArgumentException('The StreamOutput class needs a stream as its first argument.');
        }
        $this->stream = $stream;
        if (null === $decorated) {
            $decorated = $this->hasColorSupport();
        }
        parent::__construct($verbosity, $decorated, $formatter);
    }
    public function getStream()
    {
        return $this->stream;
    }
    protected function doWrite($message, $newline)
    {
        if ($newline) {
            $message .= PHP_EOL;
        }
        if (false === @fwrite($this->stream, $message)) {
            throw new RuntimeException('Unable to write output.');
        }
        fflush($this->stream);
    }
    protected function hasColorSupport()
    {
        if ('Hyper' === getenv('TERM_PROGRAM')) {
            return true;
        }
        if (\DIRECTORY_SEPARATOR === '\\') {
            return (\function_exists('sapi_windows_vt100_support')
                && @sapi_windows_vt100_support($this->stream))
                || false !== getenv('ANSICON')
                || 'ON' === getenv('ConEmuANSI')
                || 'xterm' === getenv('TERM');
        }
        if (\function_exists('stream_isatty')) {
            return @stream_isatty($this->stream);
        }
        if (\function_exists('posix_isatty')) {
            return @posix_isatty($this->stream);
        }
        $stat = @fstat($this->stream);
        return $stat ? 0020000 === ($stat['mode'] & 0170000) : false;
    }
}
