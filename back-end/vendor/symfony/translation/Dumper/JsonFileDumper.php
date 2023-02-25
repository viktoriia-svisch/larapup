<?php
namespace Symfony\Component\Translation\Dumper;
use Symfony\Component\Translation\MessageCatalogue;
class JsonFileDumper extends FileDumper
{
    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = [])
    {
        $flags = $options['json_encoding'] ?? JSON_PRETTY_PRINT;
        return json_encode($messages->all($domain), $flags);
    }
    protected function getExtension()
    {
        return 'json';
    }
}
