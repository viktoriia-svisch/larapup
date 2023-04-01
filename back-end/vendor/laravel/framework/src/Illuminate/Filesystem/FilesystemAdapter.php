<?php
namespace Illuminate\Filesystem;
use RuntimeException;
use Illuminate\Http\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use League\Flysystem\AdapterInterface;
use PHPUnit\Framework\Assert as PHPUnit;
use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Rackspace\RackspaceAdapter;
use League\Flysystem\Adapter\Local as LocalAdapter;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Contracts\Filesystem\Cloud as CloudFilesystemContract;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Contracts\Filesystem\FileExistsException as ContractFileExistsException;
use Illuminate\Contracts\Filesystem\FileNotFoundException as ContractFileNotFoundException;
class FilesystemAdapter implements FilesystemContract, CloudFilesystemContract
{
    protected $driver;
    public function __construct(FilesystemInterface $driver)
    {
        $this->driver = $driver;
    }
    public function assertExists($path)
    {
        $paths = Arr::wrap($path);
        foreach ($paths as $path) {
            PHPUnit::assertTrue(
                $this->exists($path), "Unable to find a file at path [{$path}]."
            );
        }
        return $this;
    }
    public function assertMissing($path)
    {
        $paths = Arr::wrap($path);
        foreach ($paths as $path) {
            PHPUnit::assertFalse(
                $this->exists($path), "Found unexpected file at path [{$path}]."
            );
        }
        return $this;
    }
    public function exists($path)
    {
        return $this->driver->has($path);
    }
    public function path($path)
    {
        return $this->driver->getAdapter()->getPathPrefix().$path;
    }
    public function get($path)
    {
        try {
            return $this->driver->read($path);
        } catch (FileNotFoundException $e) {
            throw new ContractFileNotFoundException($path, $e->getCode(), $e);
        }
    }
    public function response($path, $name = null, array $headers = [], $disposition = 'inline')
    {
        $response = new StreamedResponse;
        $disposition = $response->headers->makeDisposition($disposition, $name ?? basename($path));
        $response->headers->replace($headers + [
            'Content-Type' => $this->mimeType($path),
            'Content-Length' => $this->size($path),
            'Content-Disposition' => $disposition,
        ]);
        $response->setCallback(function () use ($path) {
            $stream = $this->readStream($path);
            fpassthru($stream);
            fclose($stream);
        });
        return $response;
    }
    public function download($path, $name = null, array $headers = [])
    {
        return $this->response($path, $name, $headers, 'attachment');
    }
    public function put($path, $contents, $options = [])
    {
        $options = is_string($options)
                     ? ['visibility' => $options]
                     : (array) $options;
        if ($contents instanceof File ||
            $contents instanceof UploadedFile) {
            return $this->putFile($path, $contents, $options);
        }
        return is_resource($contents)
                ? $this->driver->putStream($path, $contents, $options)
                : $this->driver->put($path, $contents, $options);
    }
    public function putFile($path, $file, $options = [])
    {
        return $this->putFileAs($path, $file, $file->hashName(), $options);
    }
    public function putFileAs($path, $file, $name, $options = [])
    {
        $stream = fopen($file->getRealPath(), 'r');
        $result = $this->put(
            $path = trim($path.'/'.$name, '/'), $stream, $options
        );
        if (is_resource($stream)) {
            fclose($stream);
        }
        return $result ? $path : false;
    }
    public function getVisibility($path)
    {
        if ($this->driver->getVisibility($path) == AdapterInterface::VISIBILITY_PUBLIC) {
            return FilesystemContract::VISIBILITY_PUBLIC;
        }
        return FilesystemContract::VISIBILITY_PRIVATE;
    }
    public function setVisibility($path, $visibility)
    {
        return $this->driver->setVisibility($path, $this->parseVisibility($visibility));
    }
    public function prepend($path, $data, $separator = PHP_EOL)
    {
        if ($this->exists($path)) {
            return $this->put($path, $data.$separator.$this->get($path));
        }
        return $this->put($path, $data);
    }
    public function append($path, $data, $separator = PHP_EOL)
    {
        if ($this->exists($path)) {
            return $this->put($path, $this->get($path).$separator.$data);
        }
        return $this->put($path, $data);
    }
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();
        $success = true;
        foreach ($paths as $path) {
            try {
                if (! $this->driver->delete($path)) {
                    $success = false;
                }
            } catch (FileNotFoundException $e) {
                $success = false;
            }
        }
        return $success;
    }
    public function copy($from, $to)
    {
        return $this->driver->copy($from, $to);
    }
    public function move($from, $to)
    {
        return $this->driver->rename($from, $to);
    }
    public function size($path)
    {
        return $this->driver->getSize($path);
    }
    public function mimeType($path)
    {
        return $this->driver->getMimetype($path);
    }
    public function lastModified($path)
    {
        return $this->driver->getTimestamp($path);
    }
    public function url($path)
    {
        $adapter = $this->driver->getAdapter();
        if ($adapter instanceof CachedAdapter) {
            $adapter = $adapter->getAdapter();
        }
        if (method_exists($adapter, 'getUrl')) {
            return $adapter->getUrl($path);
        } elseif (method_exists($this->driver, 'getUrl')) {
            return $this->driver->getUrl($path);
        } elseif ($adapter instanceof AwsS3Adapter) {
            return $this->getAwsUrl($adapter, $path);
        } elseif ($adapter instanceof RackspaceAdapter) {
            return $this->getRackspaceUrl($adapter, $path);
        } elseif ($adapter instanceof LocalAdapter) {
            return $this->getLocalUrl($path);
        } else {
            throw new RuntimeException('This driver does not support retrieving URLs.');
        }
    }
    public function readStream($path)
    {
        try {
            $resource = $this->driver->readStream($path);
            return $resource ? $resource : null;
        } catch (FileNotFoundException $e) {
            throw new ContractFileNotFoundException($e->getMessage(), $e->getCode(), $e);
        }
    }
    public function writeStream($path, $resource, array $options = [])
    {
        try {
            return $this->driver->writeStream($path, $resource, $options);
        } catch (FileExistsException $e) {
            throw new ContractFileExistsException($e->getMessage(), $e->getCode(), $e);
        }
    }
    protected function getAwsUrl($adapter, $path)
    {
        if (! is_null($url = $this->driver->getConfig()->get('url'))) {
            return $this->concatPathToUrl($url, $adapter->getPathPrefix().$path);
        }
        return $adapter->getClient()->getObjectUrl(
            $adapter->getBucket(), $adapter->getPathPrefix().$path
        );
    }
    protected function getRackspaceUrl($adapter, $path)
    {
        return (string) $adapter->getContainer()->getObject($path)->getPublicUrl();
    }
    protected function getLocalUrl($path)
    {
        $config = $this->driver->getConfig();
        if ($config->has('url')) {
            return $this->concatPathToUrl($config->get('url'), $path);
        }
        $path = '/storage/'.$path;
        if (Str::contains($path, '/storage/public/')) {
            return Str::replaceFirst('/public/', '/', $path);
        }
        return $path;
    }
    public function temporaryUrl($path, $expiration, array $options = [])
    {
        $adapter = $this->driver->getAdapter();
        if ($adapter instanceof CachedAdapter) {
            $adapter = $adapter->getAdapter();
        }
        if (method_exists($adapter, 'getTemporaryUrl')) {
            return $adapter->getTemporaryUrl($path, $expiration, $options);
        } elseif ($adapter instanceof AwsS3Adapter) {
            return $this->getAwsTemporaryUrl($adapter, $path, $expiration, $options);
        } elseif ($adapter instanceof RackspaceAdapter) {
            return $this->getRackspaceTemporaryUrl($adapter, $path, $expiration, $options);
        } else {
            throw new RuntimeException('This driver does not support creating temporary URLs.');
        }
    }
    public function getAwsTemporaryUrl($adapter, $path, $expiration, $options)
    {
        $client = $adapter->getClient();
        $command = $client->getCommand('GetObject', array_merge([
            'Bucket' => $adapter->getBucket(),
            'Key' => $adapter->getPathPrefix().$path,
        ], $options));
        return (string) $client->createPresignedRequest(
            $command, $expiration
        )->getUri();
    }
    public function getRackspaceTemporaryUrl($adapter, $path, $expiration, $options)
    {
        return $adapter->getContainer()->getObject($path)->getTemporaryUrl(
            Carbon::now()->diffInSeconds($expiration),
            $options['method'] ?? 'GET',
            $options['forcePublicUrl'] ?? true
        );
    }
    protected function concatPathToUrl($url, $path)
    {
        return rtrim($url, '/').'/'.ltrim($path, '/');
    }
    public function files($directory = null, $recursive = false)
    {
        $contents = $this->driver->listContents($directory, $recursive);
        return $this->filterContentsByType($contents, 'file');
    }
    public function allFiles($directory = null)
    {
        return $this->files($directory, true);
    }
    public function directories($directory = null, $recursive = false)
    {
        $contents = $this->driver->listContents($directory, $recursive);
        return $this->filterContentsByType($contents, 'dir');
    }
    public function allDirectories($directory = null)
    {
        return $this->directories($directory, true);
    }
    public function makeDirectory($path)
    {
        return $this->driver->createDir($path);
    }
    public function deleteDirectory($directory)
    {
        return $this->driver->deleteDir($directory);
    }
    public function flushCache()
    {
        $adapter = $this->driver->getAdapter();
        if ($adapter instanceof CachedAdapter) {
            $adapter->getCache()->flush();
        }
    }
    public function getDriver()
    {
        return $this->driver;
    }
    protected function filterContentsByType($contents, $type)
    {
        return Collection::make($contents)
            ->where('type', $type)
            ->pluck('path')
            ->values()
            ->all();
    }
    protected function parseVisibility($visibility)
    {
        if (is_null($visibility)) {
            return;
        }
        switch ($visibility) {
            case FilesystemContract::VISIBILITY_PUBLIC:
                return AdapterInterface::VISIBILITY_PUBLIC;
            case FilesystemContract::VISIBILITY_PRIVATE:
                return AdapterInterface::VISIBILITY_PRIVATE;
        }
        throw new InvalidArgumentException("Unknown visibility: {$visibility}");
    }
    public function __call($method, array $parameters)
    {
        return call_user_func_array([$this->driver, $method], $parameters);
    }
}
