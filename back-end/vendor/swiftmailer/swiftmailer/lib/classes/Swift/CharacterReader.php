<?php
interface Swift_CharacterReader
{
    const MAP_TYPE_INVALID = 0x01;
    const MAP_TYPE_FIXED_LEN = 0x02;
    const MAP_TYPE_POSITIONS = 0x03;
    public function getCharPositions($string, $startOffset, &$currentMap, &$ignoredChars);
    public function getMapType();
    public function validateByteSequence($bytes, $size);
    public function getInitialByteSize();
}
