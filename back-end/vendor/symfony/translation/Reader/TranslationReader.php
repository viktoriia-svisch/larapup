<?php
namespace Symfony\Component\Translation\Reader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;
class TranslationReader implements TranslationReaderInterface
{
    private $loaders = [];
    public function addLoader($format, LoaderInterface $loader)
    {
        $this->loaders[$format] = $loader;
    }
    public function read($directory, MessageCatalogue $catalogue)
    {
        if (!is_dir($directory)) {
            return;
        }
        foreach ($this->loaders as $format => $loader) {
            $finder = new Finder();
            $extension = $catalogue->getLocale().'.'.$format;
            $files = $finder->files()->name('*.'.$extension)->in($directory);
            foreach ($files as $file) {
                $domain = substr($file->getFilename(), 0, -1 * \strlen($extension) - 1);
                $catalogue->addCatalogue($loader->load($file->getPathname(), $catalogue->getLocale(), $domain));
            }
        }
    }
}
