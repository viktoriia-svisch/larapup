<?php
namespace Symfony\Component\Translation\Dumper;
use Symfony\Component\Translation\MessageCatalogue;
class CsvFileDumper extends FileDumper
{
    private $delimiter = ';';
    private $enclosure = '"';
    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = [])
    {
        $handle = fopen('php:
        foreach ($messages->all($domain) as $source => $target) {
            fputcsv($handle, [$source, $target], $this->delimiter, $this->enclosure);
        }
        rewind($handle);
        $output = stream_get_contents($handle);
        fclose($handle);
        return $output;
    }
    public function setCsvControl($delimiter = ';', $enclosure = '"')
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
    }
    protected function getExtension()
    {
        return 'csv';
    }
}
