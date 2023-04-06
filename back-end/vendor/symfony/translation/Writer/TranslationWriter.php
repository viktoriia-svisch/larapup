<?php
namespace Symfony\Component\Translation\Writer;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\Exception\InvalidArgumentException;
use Symfony\Component\Translation\Exception\RuntimeException;
use Symfony\Component\Translation\MessageCatalogue;
class TranslationWriter implements TranslationWriterInterface
{
    private $dumpers = [];
    public function addDumper($format, DumperInterface $dumper)
    {
        $this->dumpers[$format] = $dumper;
    }
    public function disableBackup()
    {
        @trigger_error(sprintf('The "%s()" method is deprecated since Symfony 4.1.', __METHOD__), E_USER_DEPRECATED);
        foreach ($this->dumpers as $dumper) {
            if (method_exists($dumper, 'setBackup')) {
                $dumper->setBackup(false);
            }
        }
    }
    public function getFormats()
    {
        return array_keys($this->dumpers);
    }
    public function write(MessageCatalogue $catalogue, $format, $options = [])
    {
        if (!isset($this->dumpers[$format])) {
            throw new InvalidArgumentException(sprintf('There is no dumper associated with format "%s".', $format));
        }
        $dumper = $this->dumpers[$format];
        if (isset($options['path']) && !is_dir($options['path']) && !@mkdir($options['path'], 0777, true) && !is_dir($options['path'])) {
            throw new RuntimeException(sprintf('Translation Writer was not able to create directory "%s"', $options['path']));
        }
        $dumper->dump($catalogue, $options);
    }
}
