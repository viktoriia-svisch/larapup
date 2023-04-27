<?php
namespace Symfony\Component\Translation\Dumper;
use Symfony\Component\Translation\Loader\MoFileLoader;
use Symfony\Component\Translation\MessageCatalogue;
class MoFileDumper extends FileDumper
{
    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = [])
    {
        $sources = $targets = $sourceOffsets = $targetOffsets = '';
        $offsets = [];
        $size = 0;
        foreach ($messages->all($domain) as $source => $target) {
            $offsets[] = array_map('strlen', [$sources, $source, $targets, $target]);
            $sources .= "\0".$source;
            $targets .= "\0".$target;
            ++$size;
        }
        $header = [
            'magicNumber' => MoFileLoader::MO_LITTLE_ENDIAN_MAGIC,
            'formatRevision' => 0,
            'count' => $size,
            'offsetId' => MoFileLoader::MO_HEADER_SIZE,
            'offsetTranslated' => MoFileLoader::MO_HEADER_SIZE + (8 * $size),
            'sizeHashes' => 0,
            'offsetHashes' => MoFileLoader::MO_HEADER_SIZE + (16 * $size),
        ];
        $sourcesSize = \strlen($sources);
        $sourcesStart = $header['offsetHashes'] + 1;
        foreach ($offsets as $offset) {
            $sourceOffsets .= $this->writeLong($offset[1])
                          .$this->writeLong($offset[0] + $sourcesStart);
            $targetOffsets .= $this->writeLong($offset[3])
                          .$this->writeLong($offset[2] + $sourcesStart + $sourcesSize);
        }
        $output = implode('', array_map([$this, 'writeLong'], $header))
               .$sourceOffsets
               .$targetOffsets
               .$sources
               .$targets
                ;
        return $output;
    }
    protected function getExtension()
    {
        return 'mo';
    }
    private function writeLong($str)
    {
        return pack('V*', $str);
    }
}
