<?php
namespace Dotenv;
use Dotenv\Exception\InvalidPathException;
class Dotenv
{
    protected $filePath;
    protected $loader;
    public function __construct($path, $file = '.env')
    {
        $this->filePath = $this->getFilePath($path, $file);
        $this->loader = new Loader($this->filePath, true);
    }
    public function load()
    {
        return $this->loadData();
    }
    public function safeLoad()
    {
        try {
            return $this->loadData();
        } catch (InvalidPathException $e) {
            return array();
        }
    }
    public function overload()
    {
        return $this->loadData(true);
    }
    protected function getFilePath($path, $file)
    {
        if (!is_string($file)) {
            $file = '.env';
        }
        $filePath = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file;
        return $filePath;
    }
    protected function loadData($overload = false)
    {
        return $this->loader->setImmutable(!$overload)->load();
    }
    public function required($variable)
    {
        return new Validator((array) $variable, $this->loader);
    }
    public function getEnvironmentVariableNames()
    {
        return $this->loader->variableNames;
    }
}
