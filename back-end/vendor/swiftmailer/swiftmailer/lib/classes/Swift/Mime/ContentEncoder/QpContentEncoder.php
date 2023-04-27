<?php
class Swift_Mime_ContentEncoder_QpContentEncoder extends Swift_Encoder_QpEncoder implements Swift_Mime_ContentEncoder
{
    protected $dotEscape;
    public function __construct(Swift_CharacterStream $charStream, Swift_StreamFilter $filter = null, $dotEscape = false)
    {
        $this->dotEscape = $dotEscape;
        parent::__construct($charStream, $filter);
    }
    public function __sleep()
    {
        return ['charStream', 'filter', 'dotEscape'];
    }
    protected function getSafeMapShareId()
    {
        return get_class($this).($this->dotEscape ? '.dotEscape' : '');
    }
    protected function initSafeMap()
    {
        parent::initSafeMap();
        if ($this->dotEscape) {
            unset($this->safeMap[0x2e]);
        }
    }
    public function encodeByteStream(Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0, $maxLineLength = 0)
    {
        if ($maxLineLength > 76 || $maxLineLength <= 0) {
            $maxLineLength = 76;
        }
        $thisLineLength = $maxLineLength - $firstLineOffset;
        $this->charStream->flushContents();
        $this->charStream->importByteStream($os);
        $currentLine = '';
        $prepend = '';
        $size = $lineLen = 0;
        while (false !== $bytes = $this->nextSequence()) {
            if (isset($this->filter)) {
                while ($this->filter->shouldBuffer($bytes)) {
                    if (false === $moreBytes = $this->nextSequence(1)) {
                        break;
                    }
                    foreach ($moreBytes as $b) {
                        $bytes[] = $b;
                    }
                }
                $bytes = $this->filter->filter($bytes);
            }
            $enc = $this->encodeByteSequence($bytes, $size);
            $i = strpos($enc, '=0D=0A');
            $newLineLength = $lineLen + (false === $i ? $size : $i);
            if ($currentLine && $newLineLength >= $thisLineLength) {
                $is->write($prepend.$this->standardize($currentLine));
                $currentLine = '';
                $prepend = "=\r\n";
                $thisLineLength = $maxLineLength;
                $lineLen = 0;
            }
            $currentLine .= $enc;
            if (false === $i) {
                $lineLen += $size;
            } else {
                $lineLen = $size - strrpos($enc, '=0D=0A') - 6;
            }
        }
        if (strlen($currentLine)) {
            $is->write($prepend.$this->standardize($currentLine));
        }
    }
    public function getName()
    {
        return 'quoted-printable';
    }
}
