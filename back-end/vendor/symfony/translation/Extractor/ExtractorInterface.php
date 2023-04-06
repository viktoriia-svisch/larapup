<?php
namespace Symfony\Component\Translation\Extractor;
use Symfony\Component\Translation\MessageCatalogue;
interface ExtractorInterface
{
    public function extract($resource, MessageCatalogue $catalogue);
    public function setPrefix($prefix);
}
