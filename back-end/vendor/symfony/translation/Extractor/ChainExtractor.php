<?php
namespace Symfony\Component\Translation\Extractor;
use Symfony\Component\Translation\MessageCatalogue;
class ChainExtractor implements ExtractorInterface
{
    private $extractors = [];
    public function addExtractor($format, ExtractorInterface $extractor)
    {
        $this->extractors[$format] = $extractor;
    }
    public function setPrefix($prefix)
    {
        foreach ($this->extractors as $extractor) {
            $extractor->setPrefix($prefix);
        }
    }
    public function extract($directory, MessageCatalogue $catalogue)
    {
        foreach ($this->extractors as $extractor) {
            $extractor->extract($directory, $catalogue);
        }
    }
}
