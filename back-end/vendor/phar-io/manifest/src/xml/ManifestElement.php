<?php
namespace PharIo\Manifest;
use DOMElement;
use DOMNodeList;
class ManifestElement {
    const XMLNS = 'https:
    private $element;
    public function __construct(DOMElement $element) {
        $this->element = $element;
    }
    protected function getAttributeValue($name) {
        if (!$this->element->hasAttribute($name)) {
            throw new ManifestElementException(
                sprintf(
                    'Attribute %s not set on element %s',
                    $name,
                    $this->element->localName
                )
            );
        }
        return $this->element->getAttribute($name);
    }
    protected function getChildByName($elementName) {
        $element = $this->element->getElementsByTagNameNS(self::XMLNS, $elementName)->item(0);
        if (!$element instanceof DOMElement) {
            throw new ManifestElementException(
                sprintf('Element %s missing', $elementName)
            );
        }
        return $element;
    }
    protected function getChildrenByName($elementName) {
        $elementList = $this->element->getElementsByTagNameNS(self::XMLNS, $elementName);
        if ($elementList->length === 0) {
            throw new ManifestElementException(
                sprintf('Element(s) %s missing', $elementName)
            );
        }
        return $elementList;
    }
    protected function hasChild($elementName) {
        return $this->element->getElementsByTagNameNS(self::XMLNS, $elementName)->length !== 0;
    }
}
