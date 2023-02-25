<?php
namespace Zend\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use function is_array;
use function preg_match;
use function strlen;
use function substr;
class SapiStreamEmitter implements EmitterInterface
{
    use SapiEmitterTrait;
    public function emit(ResponseInterface $response, $maxBufferLength = 8192)
    {
        $this->assertNoPreviousOutput();
        $this->emitHeaders($response);
        $this->emitStatusLine($response);
        $range = $this->parseContentRange($response->getHeaderLine('Content-Range'));
        if (is_array($range) && $range[0] === 'bytes') {
            $this->emitBodyRange($range, $response, $maxBufferLength);
            return;
        }
        $this->emitBody($response, $maxBufferLength);
    }
    private function emitBody(ResponseInterface $response, $maxBufferLength)
    {
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }
        if (! $body->isReadable()) {
            echo $body;
            return;
        }
        while (! $body->eof()) {
            echo $body->read($maxBufferLength);
        }
    }
    private function emitBodyRange(array $range, ResponseInterface $response, $maxBufferLength)
    {
        list($unit, $first, $last, $length) = $range;
        $body = $response->getBody();
        $length = $last - $first + 1;
        if ($body->isSeekable()) {
            $body->seek($first);
            $first = 0;
        }
        if (! $body->isReadable()) {
            echo substr($body->getContents(), $first, $length);
            return;
        }
        $remaining = $length;
        while ($remaining >= $maxBufferLength && ! $body->eof()) {
            $contents   = $body->read($maxBufferLength);
            $remaining -= strlen($contents);
            echo $contents;
        }
        if ($remaining > 0 && ! $body->eof()) {
            echo $body->read($remaining);
        }
    }
    private function parseContentRange($header)
    {
        if (preg_match('/(?P<unit>[\w]+)\s+(?P<first>\d+)-(?P<last>\d+)\/(?P<length>\d+|\*)/', $header, $matches)) {
            return [
                $matches['unit'],
                (int) $matches['first'],
                (int) $matches['last'],
                $matches['length'] === '*' ? '*' : (int) $matches['length'],
            ];
        }
        return false;
    }
}
