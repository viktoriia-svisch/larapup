<?php
namespace Illuminate\Http;
use Illuminate\Support\Arr;
use Illuminate\Container\Container;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
class UploadedFile extends SymfonyUploadedFile
{
    use FileHelpers, Macroable;
    public static function fake()
    {
        return new Testing\FileFactory;
    }
    public function store($path, $options = [])
    {
        return $this->storeAs($path, $this->hashName(), $this->parseOptions($options));
    }
    public function storePublicly($path, $options = [])
    {
        $options = $this->parseOptions($options);
        $options['visibility'] = 'public';
        return $this->storeAs($path, $this->hashName(), $options);
    }
    public function storePubliclyAs($path, $name, $options = [])
    {
        $options = $this->parseOptions($options);
        $options['visibility'] = 'public';
        return $this->storeAs($path, $name, $options);
    }
    public function storeAs($path, $name, $options = [])
    {
        $options = $this->parseOptions($options);
        $disk = Arr::pull($options, 'disk');
        return Container::getInstance()->make(FilesystemFactory::class)->disk($disk)->putFileAs(
            $path, $this, $name, $options
        );
    }
    public function get()
    {
        if (! $this->isValid()) {
            throw new FileNotFoundException("File does not exist at path {$this->getPathname()}");
        }
        return file_get_contents($this->getPathname());
    }
    public static function createFromBase(SymfonyUploadedFile $file, $test = false)
    {
        return $file instanceof static ? $file : new static(
            $file->getPathname(),
            $file->getClientOriginalName(),
            $file->getClientMimeType(),
            $file->getError(),
            $test
        );
    }
    protected function parseOptions($options)
    {
        if (is_string($options)) {
            $options = ['disk' => $options];
        }
        return $options;
    }
}
