<?php
namespace Symfony\Component\HttpFoundation\Session\Storage;
class MockFileSessionStorage extends MockArraySessionStorage
{
    private $savePath;
    public function __construct(string $savePath = null, string $name = 'MOCKSESSID', MetadataBag $metaBag = null)
    {
        if (null === $savePath) {
            $savePath = sys_get_temp_dir();
        }
        if (!is_dir($savePath) && !@mkdir($savePath, 0777, true) && !is_dir($savePath)) {
            throw new \RuntimeException(sprintf('Session Storage was not able to create directory "%s"', $savePath));
        }
        $this->savePath = $savePath;
        parent::__construct($name, $metaBag);
    }
    public function start()
    {
        if ($this->started) {
            return true;
        }
        if (!$this->id) {
            $this->id = $this->generateId();
        }
        $this->read();
        $this->started = true;
        return true;
    }
    public function regenerate($destroy = false, $lifetime = null)
    {
        if (!$this->started) {
            $this->start();
        }
        if ($destroy) {
            $this->destroy();
        }
        return parent::regenerate($destroy, $lifetime);
    }
    public function save()
    {
        if (!$this->started) {
            throw new \RuntimeException('Trying to save a session that was not started yet or was already closed');
        }
        $data = $this->data;
        foreach ($this->bags as $bag) {
            if (empty($data[$key = $bag->getStorageKey()])) {
                unset($data[$key]);
            }
        }
        if ([$key = $this->metadataBag->getStorageKey()] === array_keys($data)) {
            unset($data[$key]);
        }
        try {
            if ($data) {
                file_put_contents($this->getFilePath(), serialize($data));
            } else {
                $this->destroy();
            }
        } finally {
            $this->data = $data;
        }
        $this->started = false;
    }
    private function destroy()
    {
        if (is_file($this->getFilePath())) {
            unlink($this->getFilePath());
        }
    }
    private function getFilePath()
    {
        return $this->savePath.'/'.$this->id.'.mocksess';
    }
    private function read()
    {
        $filePath = $this->getFilePath();
        $this->data = is_readable($filePath) && is_file($filePath) ? unserialize(file_get_contents($filePath)) : [];
        $this->loadSession();
    }
}
