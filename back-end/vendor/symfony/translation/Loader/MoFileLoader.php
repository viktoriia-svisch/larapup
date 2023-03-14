<?php
namespace Symfony\Component\Translation\Loader;
use Symfony\Component\Translation\Exception\InvalidResourceException;
class MoFileLoader extends FileLoader
{
    const MO_LITTLE_ENDIAN_MAGIC = 0x950412de;
    const MO_BIG_ENDIAN_MAGIC = 0xde120495;
    const MO_HEADER_SIZE = 28;
    protected function loadResource($resource)
    {
        $stream = fopen($resource, 'r');
        $stat = fstat($stream);
        if ($stat['size'] < self::MO_HEADER_SIZE) {
            throw new InvalidResourceException('MO stream content has an invalid format.');
        }
        $magic = unpack('V1', fread($stream, 4));
        $magic = hexdec(substr(dechex(current($magic)), -8));
        if (self::MO_LITTLE_ENDIAN_MAGIC == $magic) {
            $isBigEndian = false;
        } elseif (self::MO_BIG_ENDIAN_MAGIC == $magic) {
            $isBigEndian = true;
        } else {
            throw new InvalidResourceException('MO stream content has an invalid format.');
        }
        $this->readLong($stream, $isBigEndian);
        $count = $this->readLong($stream, $isBigEndian);
        $offsetId = $this->readLong($stream, $isBigEndian);
        $offsetTranslated = $this->readLong($stream, $isBigEndian);
        $this->readLong($stream, $isBigEndian);
        $this->readLong($stream, $isBigEndian);
        $messages = [];
        for ($i = 0; $i < $count; ++$i) {
            $pluralId = null;
            $translated = null;
            fseek($stream, $offsetId + $i * 8);
            $length = $this->readLong($stream, $isBigEndian);
            $offset = $this->readLong($stream, $isBigEndian);
            if ($length < 1) {
                continue;
            }
            fseek($stream, $offset);
            $singularId = fread($stream, $length);
            if (false !== strpos($singularId, "\000")) {
                list($singularId, $pluralId) = explode("\000", $singularId);
            }
            fseek($stream, $offsetTranslated + $i * 8);
            $length = $this->readLong($stream, $isBigEndian);
            $offset = $this->readLong($stream, $isBigEndian);
            if ($length < 1) {
                continue;
            }
            fseek($stream, $offset);
            $translated = fread($stream, $length);
            if (false !== strpos($translated, "\000")) {
                $translated = explode("\000", $translated);
            }
            $ids = ['singular' => $singularId, 'plural' => $pluralId];
            $item = compact('ids', 'translated');
            if (\is_array($item['translated'])) {
                $messages[$item['ids']['singular']] = stripcslashes($item['translated'][0]);
                if (isset($item['ids']['plural'])) {
                    $plurals = [];
                    foreach ($item['translated'] as $plural => $translated) {
                        $plurals[] = sprintf('{%d} %s', $plural, $translated);
                    }
                    $messages[$item['ids']['plural']] = stripcslashes(implode('|', $plurals));
                }
            } elseif (!empty($item['ids']['singular'])) {
                $messages[$item['ids']['singular']] = stripcslashes($item['translated']);
            }
        }
        fclose($stream);
        return array_filter($messages);
    }
    private function readLong($stream, bool $isBigEndian): int
    {
        $result = unpack($isBigEndian ? 'N1' : 'V1', fread($stream, 4));
        $result = current($result);
        return (int) substr($result, -8);
    }
}
