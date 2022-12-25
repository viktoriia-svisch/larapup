<?php
namespace Illuminate\Cache;
use Exception;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\InteractsWithTime;
class FileStore implements Store
{
    use InteractsWithTime, RetrievesMultipleKeys;
    protected $files;
    protected $directory;
    public function __construct(Filesystem $files, $directory)
    {
        $this->files = $files;
        $this->directory = $directory;
    }
    public function get($key)
    {
        return $this->getPayload($key)['data'] ?? null;
    }
    public function put($key, $value, $minutes)
    {
        $this->ensureCacheDirectoryExists($path = $this->path($key));
        $this->files->put(
            $path, $this->expiration($minutes).serialize($value), true
        );
    }
    protected function ensureCacheDirectoryExists($path)
    {
        if (! $this->files->exists(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
    }
    public function increment($key, $value = 1)
    {
        $raw = $this->getPayload($key);
        return tap(((int) $raw['data']) + $value, function ($newValue) use ($key, $raw) {
            $this->put($key, $newValue, $raw['time'] ?? 0);
        });
    }
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, $value * -1);
    }
    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
    }
    public function forget($key)
    {
        if ($this->files->exists($file = $this->path($key))) {
            return $this->files->delete($file);
        }
        return false;
    }
    public function flush()
    {
        if (! $this->files->isDirectory($this->directory)) {
            return false;
        }
        foreach ($this->files->directories($this->directory) as $directory) {
            if (! $this->files->deleteDirectory($directory)) {
                return false;
            }
        }
        return true;
    }
    protected function getPayload($key)
    {
        $path = $this->path($key);
        try {
            $expire = substr(
                $contents = $this->files->get($path, true), 0, 10
            );
        } catch (Exception $e) {
            return $this->emptyPayload();
        }
        if ($this->currentTime() >= $expire) {
            $this->forget($key);
            return $this->emptyPayload();
        }
        $data = unserialize(substr($contents, 10));
        $time = ($expire - $this->currentTime()) / 60;
        return compact('data', 'time');
    }
    protected function emptyPayload()
    {
        return ['data' => null, 'time' => null];
    }
    protected function path($key)
    {
        $parts = array_slice(str_split($hash = sha1($key), 2), 0, 2);
        return $this->directory.'/'.implode('/', $parts).'/'.$hash;
    }
    protected function expiration($minutes)
    {
        $time = $this->availableAt((int) ($minutes * 60));
        return $minutes === 0 || $time > 9999999999 ? 9999999999 : (int) $time;
    }
    public function getFilesystem()
    {
        return $this->files;
    }
    public function getDirectory()
    {
        return $this->directory;
    }
    public function getPrefix()
    {
        return '';
    }
}
