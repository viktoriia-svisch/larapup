<?php
class Swift_ByteStream_FileByteStream extends Swift_ByteStream_AbstractFilterableInputStream implements Swift_FileStream
{
    private $offset = 0;
    private $path;
    private $mode;
    private $reader;
    private $writer;
    private $seekable = null;
    public function __construct($path, $writable = false)
    {
        if (empty($path)) {
            throw new Swift_IoException('The path cannot be empty');
        }
        $this->path = $path;
        $this->mode = $writable ? 'w+b' : 'rb';
    }
    public function getPath()
    {
        return $this->path;
    }
    public function read($length)
    {
        $fp = $this->getReadHandle();
        if (!feof($fp)) {
            $bytes = fread($fp, $length);
            $this->offset = ftell($fp);
            if ('' === $bytes && feof($fp)) {
                $this->resetReadHandle();
                return false;
            }
            return $bytes;
        }
        $this->resetReadHandle();
        return false;
    }
    public function setReadPointer($byteOffset)
    {
        if (isset($this->reader)) {
            $this->seekReadStreamToPosition($byteOffset);
        }
        $this->offset = $byteOffset;
    }
    protected function doCommit($bytes)
    {
        fwrite($this->getWriteHandle(), $bytes);
        $this->resetReadHandle();
    }
    protected function flush()
    {
    }
    private function getReadHandle()
    {
        if (!isset($this->reader)) {
            $pointer = @fopen($this->path, 'rb');
            if (!$pointer) {
                throw new Swift_IoException('Unable to open file for reading ['.$this->path.']');
            }
            $this->reader = $pointer;
            if (0 != $this->offset) {
                $this->getReadStreamSeekableStatus();
                $this->seekReadStreamToPosition($this->offset);
            }
        }
        return $this->reader;
    }
    private function getWriteHandle()
    {
        if (!isset($this->writer)) {
            if (!$this->writer = fopen($this->path, $this->mode)) {
                throw new Swift_IoException(
                    'Unable to open file for writing ['.$this->path.']'
                );
            }
        }
        return $this->writer;
    }
    private function resetReadHandle()
    {
        if (isset($this->reader)) {
            fclose($this->reader);
            $this->reader = null;
        }
    }
    private function getReadStreamSeekableStatus()
    {
        $metas = stream_get_meta_data($this->reader);
        $this->seekable = $metas['seekable'];
    }
    private function seekReadStreamToPosition($offset)
    {
        if (null === $this->seekable) {
            $this->getReadStreamSeekableStatus();
        }
        if (false === $this->seekable) {
            $currentPos = ftell($this->reader);
            if ($currentPos < $offset) {
                $toDiscard = $offset - $currentPos;
                fread($this->reader, $toDiscard);
                return;
            }
            $this->copyReadStream();
        }
        fseek($this->reader, $offset, SEEK_SET);
    }
    private function copyReadStream()
    {
        if ($tmpFile = fopen('php:
        } elseif (function_exists('sys_get_temp_dir') && is_writable(sys_get_temp_dir()) && ($tmpFile = tmpfile())) {
        } else {
            throw new Swift_IoException('Unable to copy the file to make it seekable, sys_temp_dir is not writable, php:
        }
        $currentPos = ftell($this->reader);
        fclose($this->reader);
        $source = fopen($this->path, 'rb');
        if (!$source) {
            throw new Swift_IoException('Unable to open file for copying ['.$this->path.']');
        }
        fseek($tmpFile, 0, SEEK_SET);
        while (!feof($source)) {
            fwrite($tmpFile, fread($source, 4096));
        }
        fseek($tmpFile, $currentPos, SEEK_SET);
        fclose($source);
        $this->reader = $tmpFile;
    }
}
