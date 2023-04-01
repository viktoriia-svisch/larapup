<?php
namespace Symfony\Component\Translation\Dumper;
use Symfony\Component\Translation\MessageCatalogue;
class PhpFileDumper extends FileDumper
{
    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = [])
    {
        return "<?php\n\nreturn ".var_export($messages->all($domain), true).";\n";
    }
    protected function getExtension()
    {
        return 'php';
    }
}
