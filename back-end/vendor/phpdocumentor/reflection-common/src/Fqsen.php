<?php
namespace phpDocumentor\Reflection;
final class Fqsen
{
    private $fqsen;
    private $name;
    public function __construct($fqsen)
    {
        $matches = array();
        $result = preg_match(
            '/^\\\\([a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff\\\\]*)?(?:[:]{2}\\$?([a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*))?(?:\\(\\))?$/',
                $fqsen,
                $matches
        );
        if ($result === 0) {
            throw new \InvalidArgumentException(
                sprintf('"%s" is not a valid Fqsen.', $fqsen)
            );
        }
        $this->fqsen = $fqsen;
        if (isset($matches[2])) {
            $this->name = $matches[2];
        } else {
            $matches = explode('\\', $fqsen);
            $this->name = trim(end($matches), '()');
        }
    }
    public function __toString()
    {
        return $this->fqsen;
    }
    public function getName()
    {
        return $this->name;
    }
}
