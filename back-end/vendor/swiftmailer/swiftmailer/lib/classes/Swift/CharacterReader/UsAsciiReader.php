<?php
class Swift_CharacterReader_UsAsciiReader implements Swift_CharacterReader
{
    public function getCharPositions($string, $startOffset, &$currentMap, &$ignoredChars)
    {
        $strlen = strlen($string);
        $ignoredChars = '';
        for ($i = 0; $i < $strlen; ++$i) {
            if ($string[$i] > "\x07F") {
                $currentMap[$i + $startOffset] = $string[$i];
            }
        }
        return $strlen;
    }
    public function getMapType()
    {
        return self::MAP_TYPE_INVALID;
    }
    public function validateByteSequence($bytes, $size)
    {
        $byte = reset($bytes);
        if (1 == count($bytes) && $byte >= 0x00 && $byte <= 0x7F) {
            return 0;
        }
        return -1;
    }
    public function getInitialByteSize()
    {
        return 1;
    }
}
