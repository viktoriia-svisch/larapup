<?php
namespace Symfony\Component\Translation\Writer;
use Symfony\Component\Translation\Exception\InvalidArgumentException;
use Symfony\Component\Translation\MessageCatalogue;
interface TranslationWriterInterface
{
    public function write(MessageCatalogue $catalogue, $format, $options = []);
}
