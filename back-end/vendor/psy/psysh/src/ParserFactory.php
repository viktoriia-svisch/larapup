<?php
namespace Psy;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory as OriginalParserFactory;
class ParserFactory
{
    const ONLY_PHP5   = 'ONLY_PHP5';
    const ONLY_PHP7   = 'ONLY_PHP7';
    const PREFER_PHP5 = 'PREFER_PHP5';
    const PREFER_PHP7 = 'PREFER_PHP7';
    public static function getPossibleKinds()
    {
        return ['ONLY_PHP5', 'ONLY_PHP7', 'PREFER_PHP5', 'PREFER_PHP7'];
    }
    public function hasKindsSupport()
    {
        return \class_exists('PhpParser\ParserFactory');
    }
    public function getDefaultKind()
    {
        if ($this->hasKindsSupport()) {
            return \version_compare(PHP_VERSION, '7.0', '>=') ? static::ONLY_PHP7 : static::ONLY_PHP5;
        }
    }
    public function createParser($kind = null)
    {
        if ($this->hasKindsSupport()) {
            $originalFactory = new OriginalParserFactory();
            $kind = $kind ?: $this->getDefaultKind();
            if (!\in_array($kind, static::getPossibleKinds())) {
                throw new \InvalidArgumentException('Unknown parser kind');
            }
            $parser = $originalFactory->create(\constant('PhpParser\ParserFactory::' . $kind));
        } else {
            if ($kind !== null) {
                throw new \InvalidArgumentException('Install PHP Parser v2.x to specify parser kind');
            }
            $parser = new Parser(new Lexer());
        }
        return $parser;
    }
}
