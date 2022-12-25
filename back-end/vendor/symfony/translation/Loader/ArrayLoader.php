<?php
namespace Symfony\Component\Translation\Loader;
use Symfony\Component\Translation\MessageCatalogue;
class ArrayLoader implements LoaderInterface
{
    public function load($resource, $locale, $domain = 'messages')
    {
        $this->flatten($resource);
        $catalogue = new MessageCatalogue($locale);
        $catalogue->add($resource, $domain);
        return $catalogue;
    }
    private function flatten(array &$messages, array $subnode = null, $path = null)
    {
        if (null === $subnode) {
            $subnode = &$messages;
        }
        foreach ($subnode as $key => $value) {
            if (\is_array($value)) {
                $nodePath = $path ? $path.'.'.$key : $key;
                $this->flatten($messages, $value, $nodePath);
                if (null === $path) {
                    unset($messages[$key]);
                }
            } elseif (null !== $path) {
                $messages[$path.'.'.$key] = $value;
            }
        }
    }
}
