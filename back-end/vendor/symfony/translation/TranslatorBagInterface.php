<?php
namespace Symfony\Component\Translation;
use Symfony\Component\Translation\Exception\InvalidArgumentException;
interface TranslatorBagInterface
{
    public function getCatalogue($locale = null);
}
