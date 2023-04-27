<?php
namespace Symfony\Component\Filesystem\Tests\Fixtures\MockStream;
class MockStream
{
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        return true;
    }
    public function url_stat($path, $flags)
    {
        return [];
    }
}
