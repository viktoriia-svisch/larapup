<?php
namespace Psy\ExecutionLoop;
use Psy\Exception\ParseErrorException;
use Psy\ParserFactory;
use Psy\Shell;
class RunkitReloader extends AbstractListener
{
    private $parser;
    private $timestamps = [];
    public static function isSupported()
    {
        return \extension_loaded('runkit');
    }
    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->parser = $parserFactory->createParser();
    }
    public function onInput(Shell $shell, $input)
    {
        $this->reload($shell);
    }
    private function reload(Shell $shell)
    {
        \clearstatcache();
        $modified = [];
        foreach (\get_included_files() as $file) {
            $timestamp = \filemtime($file);
            if (!isset($this->timestamps[$file])) {
                $this->timestamps[$file] = $timestamp;
                continue;
            }
            if ($this->timestamps[$file] === $timestamp) {
                continue;
            }
            if (!$this->lintFile($file)) {
                $msg = \sprintf('Modified file "%s" could not be reloaded', $file);
                $shell->writeException(new ParseErrorException($msg));
                continue;
            }
            $modified[] = $file;
            $this->timestamps[$file] = $timestamp;
        }
        foreach ($modified as $file) {
            runkit_import($file, (
                RUNKIT_IMPORT_FUNCTIONS |
                RUNKIT_IMPORT_CLASSES |
                RUNKIT_IMPORT_CLASS_METHODS |
                RUNKIT_IMPORT_CLASS_CONSTS |
                RUNKIT_IMPORT_CLASS_PROPS |
                RUNKIT_IMPORT_OVERRIDE
            ));
        }
    }
    private function lintFile($file)
    {
        try {
            $this->parser->parse(\file_get_contents($file));
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
