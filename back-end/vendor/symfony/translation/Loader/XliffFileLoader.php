<?php
namespace Symfony\Component\Translation\Loader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Util\XliffUtils;
class XliffFileLoader implements LoaderInterface
{
    public function load($resource, $locale, $domain = 'messages')
    {
        if (!stream_is_local($resource)) {
            throw new InvalidResourceException(sprintf('This is not a local file "%s".', $resource));
        }
        if (!file_exists($resource)) {
            throw new NotFoundResourceException(sprintf('File "%s" not found.', $resource));
        }
        $catalogue = new MessageCatalogue($locale);
        $this->extract($resource, $catalogue, $domain);
        if (class_exists('Symfony\Component\Config\Resource\FileResource')) {
            $catalogue->addResource(new FileResource($resource));
        }
        return $catalogue;
    }
    private function extract($resource, MessageCatalogue $catalogue, $domain)
    {
        try {
            $dom = XmlUtils::loadFile($resource);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidResourceException(sprintf('Unable to load "%s": %s', $resource, $e->getMessage()), $e->getCode(), $e);
        }
        $xliffVersion = XliffUtils::getVersionNumber($dom);
        if ($errors = XliffUtils::validateSchema($dom)) {
            throw new InvalidResourceException(sprintf('Invalid resource provided: "%s"; Errors: %s', $xliffVersion, XliffUtils::getErrorsAsString($errors)));
        }
        if ('1.2' === $xliffVersion) {
            $this->extractXliff1($dom, $catalogue, $domain);
        }
        if ('2.0' === $xliffVersion) {
            $this->extractXliff2($dom, $catalogue, $domain);
        }
    }
    private function extractXliff1(\DOMDocument $dom, MessageCatalogue $catalogue, string $domain)
    {
        $xml = simplexml_import_dom($dom);
        $encoding = strtoupper($dom->encoding);
        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:1.2');
        foreach ($xml->xpath('
            $attributes = $translation->attributes();
            if (!(isset($attributes['resname']) || isset($translation->source))) {
                continue;
            }
            $source = isset($attributes['resname']) && $attributes['resname'] ? $attributes['resname'] : $translation->source;
            $target = $this->utf8ToCharset((string) (isset($translation->target) ? $translation->target : $translation->source), $encoding);
            $catalogue->set((string) $source, $target, $domain);
            $metadata = [];
            if ($notes = $this->parseNotesMetadata($translation->note, $encoding)) {
                $metadata['notes'] = $notes;
            }
            if (isset($translation->target) && $translation->target->attributes()) {
                $metadata['target-attributes'] = [];
                foreach ($translation->target->attributes() as $key => $value) {
                    $metadata['target-attributes'][$key] = (string) $value;
                }
            }
            if (isset($attributes['id'])) {
                $metadata['id'] = (string) $attributes['id'];
            }
            $catalogue->setMetadata((string) $source, $metadata, $domain);
        }
    }
    private function extractXliff2(\DOMDocument $dom, MessageCatalogue $catalogue, string $domain)
    {
        $xml = simplexml_import_dom($dom);
        $encoding = strtoupper($dom->encoding);
        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:2.0');
        foreach ($xml->xpath('
            foreach ($unit->segment as $segment) {
                $source = $segment->source;
                $target = $this->utf8ToCharset((string) (isset($segment->target) ? $segment->target : $source), $encoding);
                $catalogue->set((string) $source, $target, $domain);
                $metadata = [];
                if (isset($segment->target) && $segment->target->attributes()) {
                    $metadata['target-attributes'] = [];
                    foreach ($segment->target->attributes() as $key => $value) {
                        $metadata['target-attributes'][$key] = (string) $value;
                    }
                }
                if (isset($unit->notes)) {
                    $metadata['notes'] = [];
                    foreach ($unit->notes->note as $noteNode) {
                        $note = [];
                        foreach ($noteNode->attributes() as $key => $value) {
                            $note[$key] = (string) $value;
                        }
                        $note['content'] = (string) $noteNode;
                        $metadata['notes'][] = $note;
                    }
                }
                $catalogue->setMetadata((string) $source, $metadata, $domain);
            }
        }
    }
    private function utf8ToCharset(string $content, string $encoding = null): string
    {
        if ('UTF-8' !== $encoding && !empty($encoding)) {
            return mb_convert_encoding($content, $encoding, 'UTF-8');
        }
        return $content;
    }
    private function parseNotesMetadata(\SimpleXMLElement $noteElement = null, string $encoding = null): array
    {
        $notes = [];
        if (null === $noteElement) {
            return $notes;
        }
        foreach ($noteElement as $xmlNote) {
            $noteAttributes = $xmlNote->attributes();
            $note = ['content' => $this->utf8ToCharset((string) $xmlNote, $encoding)];
            if (isset($noteAttributes['priority'])) {
                $note['priority'] = (int) $noteAttributes['priority'];
            }
            if (isset($noteAttributes['from'])) {
                $note['from'] = (string) $noteAttributes['from'];
            }
            $notes[] = $note;
        }
        return $notes;
    }
}
