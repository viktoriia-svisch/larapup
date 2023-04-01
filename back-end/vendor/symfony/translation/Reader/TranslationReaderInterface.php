<?php
namespace Symfony\Component\Translation\Reader;
use Symfony\Component\Translation\MessageCatalogue;
interface TranslationReaderInterface
{
    public function read($directory, MessageCatalogue $catalogue);
}
