<?php
namespace Symfony\Component\Translation\Loader;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\MessageCatalogue;
class IcuResFileLoader implements LoaderInterface
{
    public function load($resource, $locale, $domain = 'messages')
    {
        if (!stream_is_local($resource)) {
            throw new InvalidResourceException(sprintf('This is not a local file "%s".', $resource));
        }
        if (!is_dir($resource)) {
            throw new NotFoundResourceException(sprintf('File "%s" not found.', $resource));
        }
        try {
            $rb = new \ResourceBundle($locale, $resource);
        } catch (\Exception $e) {
            $rb = null;
        }
        if (!$rb) {
            throw new InvalidResourceException(sprintf('Cannot load resource "%s"', $resource));
        } elseif (intl_is_failure($rb->getErrorCode())) {
            throw new InvalidResourceException($rb->getErrorMessage(), $rb->getErrorCode());
        }
        $messages = $this->flatten($rb);
        $catalogue = new MessageCatalogue($locale);
        $catalogue->add($messages, $domain);
        if (class_exists('Symfony\Component\Config\Resource\DirectoryResource')) {
            $catalogue->addResource(new DirectoryResource($resource));
        }
        return $catalogue;
    }
    protected function flatten(\ResourceBundle $rb, array &$messages = [], $path = null)
    {
        foreach ($rb as $key => $value) {
            $nodePath = $path ? $path.'.'.$key : $key;
            if ($value instanceof \ResourceBundle) {
                $this->flatten($value, $messages, $nodePath);
            } else {
                $messages[$nodePath] = $value;
            }
        }
        return $messages;
    }
}
