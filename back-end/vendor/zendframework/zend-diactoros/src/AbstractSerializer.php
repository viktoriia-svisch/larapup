<?php
namespace Zend\Diactoros;
use Psr\Http\Message\StreamInterface;
use UnexpectedValueException;
use function array_pop;
use function implode;
use function ltrim;
use function preg_match;
use function sprintf;
use function str_replace;
use function ucwords;
abstract class AbstractSerializer
{
    const CR  = "\r";
    const EOL = "\r\n";
    const LF  = "\n";
    protected static function getLine(StreamInterface $stream)
    {
        $line    = '';
        $crFound = false;
        while (! $stream->eof()) {
            $char = $stream->read(1);
            if ($crFound && $char === self::LF) {
                $crFound = false;
                break;
            }
            if ($crFound && $char !== self::LF) {
                throw new UnexpectedValueException('Unexpected carriage return detected');
            }
            if (! $crFound && $char === self::LF) {
                throw new UnexpectedValueException('Unexpected line feed detected');
            }
            if ($char === self::CR) {
                $crFound = true;
                continue;
            }
            $line .= $char;
        }
        if ($crFound) {
            throw new UnexpectedValueException("Unexpected end of headers");
        }
        return $line;
    }
    protected static function splitStream(StreamInterface $stream)
    {
        $headers       = [];
        $currentHeader = false;
        while ($line = self::getLine($stream)) {
            if (preg_match(';^(?P<name>[!#$%&\'*+.^_`\|~0-9a-zA-Z-]+):(?P<value>.*)$;', $line, $matches)) {
                $currentHeader = $matches['name'];
                if (! isset($headers[$currentHeader])) {
                    $headers[$currentHeader] = [];
                }
                $headers[$currentHeader][] = ltrim($matches['value']);
                continue;
            }
            if (! $currentHeader) {
                throw new UnexpectedValueException('Invalid header detected');
            }
            if (! preg_match('#^[ \t]#', $line)) {
                throw new UnexpectedValueException('Invalid header continuation');
            }
            $value = array_pop($headers[$currentHeader]);
            $headers[$currentHeader][] = $value . ltrim($line);
        }
        return [$headers, new RelativeStream($stream, $stream->tell())];
    }
    protected static function serializeHeaders(array $headers)
    {
        $lines = [];
        foreach ($headers as $header => $values) {
            $normalized = self::filterHeader($header);
            foreach ($values as $value) {
                $lines[] = sprintf('%s: %s', $normalized, $value);
            }
        }
        return implode("\r\n", $lines);
    }
    protected static function filterHeader($header)
    {
        $filtered = str_replace('-', ' ', $header);
        $filtered = ucwords($filtered);
        return str_replace(' ', '-', $filtered);
    }
}
