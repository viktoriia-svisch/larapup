<?php
namespace Illuminate\Http\Concerns;
use stdClass;
use SplFileInfo;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
trait InteractsWithInput
{
    public function server($key = null, $default = null)
    {
        return $this->retrieveItem('server', $key, $default);
    }
    public function hasHeader($key)
    {
        return ! is_null($this->header($key));
    }
    public function header($key = null, $default = null)
    {
        return $this->retrieveItem('headers', $key, $default);
    }
    public function bearerToken()
    {
        $header = $this->header('Authorization', '');
        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }
    }
    public function exists($key)
    {
        return $this->has($key);
    }
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();
        $input = $this->all();
        foreach ($keys as $value) {
            if (! Arr::has($input, $value)) {
                return false;
            }
        }
        return true;
    }
    public function hasAny($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $input = $this->all();
        foreach ($keys as $key) {
            if (Arr::has($input, $key)) {
                return true;
            }
        }
        return false;
    }
    public function filled($key)
    {
        $keys = is_array($key) ? $key : func_get_args();
        foreach ($keys as $value) {
            if ($this->isEmptyString($value)) {
                return false;
            }
        }
        return true;
    }
    public function anyFilled($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        foreach ($keys as $key) {
            if ($this->filled($key)) {
                return true;
            }
        }
        return false;
    }
    protected function isEmptyString($key)
    {
        $value = $this->input($key);
        return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
    }
    public function keys()
    {
        return array_merge(array_keys($this->input()), $this->files->keys());
    }
    public function all($keys = null)
    {
        $input = array_replace_recursive($this->input(), $this->allFiles());
        if (! $keys) {
            return $input;
        }
        $results = [];
        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            Arr::set($results, $key, Arr::get($input, $key));
        }
        return $results;
    }
    public function input($key = null, $default = null)
    {
        return data_get(
            $this->getInputSource()->all() + $this->query->all(), $key, $default
        );
    }
    public function only($keys)
    {
        $results = [];
        $input = $this->all();
        $placeholder = new stdClass;
        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            $value = data_get($input, $key, $placeholder);
            if ($value !== $placeholder) {
                Arr::set($results, $key, $value);
            }
        }
        return $results;
    }
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $results = $this->all();
        Arr::forget($results, $keys);
        return $results;
    }
    public function query($key = null, $default = null)
    {
        return $this->retrieveItem('query', $key, $default);
    }
    public function post($key = null, $default = null)
    {
        return $this->retrieveItem('request', $key, $default);
    }
    public function hasCookie($key)
    {
        return ! is_null($this->cookie($key));
    }
    public function cookie($key = null, $default = null)
    {
        return $this->retrieveItem('cookies', $key, $default);
    }
    public function allFiles()
    {
        $files = $this->files->all();
        return $this->convertedFiles
                    ? $this->convertedFiles
                    : $this->convertedFiles = $this->convertUploadedFiles($files);
    }
    protected function convertUploadedFiles(array $files)
    {
        return array_map(function ($file) {
            if (is_null($file) || (is_array($file) && empty(array_filter($file)))) {
                return $file;
            }
            return is_array($file)
                        ? $this->convertUploadedFiles($file)
                        : UploadedFile::createFromBase($file);
        }, $files);
    }
    public function hasFile($key)
    {
        if (! is_array($files = $this->file($key))) {
            $files = [$files];
        }
        foreach ($files as $file) {
            if ($this->isValidFile($file)) {
                return true;
            }
        }
        return false;
    }
    protected function isValidFile($file)
    {
        return $file instanceof SplFileInfo && $file->getPath() !== '';
    }
    public function file($key = null, $default = null)
    {
        return data_get($this->allFiles(), $key, $default);
    }
    protected function retrieveItem($source, $key, $default)
    {
        if (is_null($key)) {
            return $this->$source->all();
        }
        return $this->$source->get($key, $default);
    }
}
