<?php
namespace Symfony\Component\Translation\Dumper;
use Symfony\Component\Translation\MessageCatalogue;
interface DumperInterface
{
    public function dump(MessageCatalogue $messages, $options = []);
}
