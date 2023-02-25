<?php
namespace Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class Store implements StoreInterface
{
    protected $root;
    private $keyCache;
    private $locks;
    public function __construct(string $root)
    {
        $this->root = $root;
        if (!file_exists($this->root) && !@mkdir($this->root, 0777, true) && !is_dir($this->root)) {
            throw new \RuntimeException(sprintf('Unable to create the store directory (%s).', $this->root));
        }
        $this->keyCache = new \SplObjectStorage();
        $this->locks = [];
    }
    public function cleanup()
    {
        foreach ($this->locks as $lock) {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
        $this->locks = [];
    }
    public function lock(Request $request)
    {
        $key = $this->getCacheKey($request);
        if (!isset($this->locks[$key])) {
            $path = $this->getPath($key);
            if (!file_exists(\dirname($path)) && false === @mkdir(\dirname($path), 0777, true) && !is_dir(\dirname($path))) {
                return $path;
            }
            $h = fopen($path, 'cb');
            if (!flock($h, LOCK_EX | LOCK_NB)) {
                fclose($h);
                return $path;
            }
            $this->locks[$key] = $h;
        }
        return true;
    }
    public function unlock(Request $request)
    {
        $key = $this->getCacheKey($request);
        if (isset($this->locks[$key])) {
            flock($this->locks[$key], LOCK_UN);
            fclose($this->locks[$key]);
            unset($this->locks[$key]);
            return true;
        }
        return false;
    }
    public function isLocked(Request $request)
    {
        $key = $this->getCacheKey($request);
        if (isset($this->locks[$key])) {
            return true; 
        }
        if (!file_exists($path = $this->getPath($key))) {
            return false;
        }
        $h = fopen($path, 'rb');
        flock($h, LOCK_EX | LOCK_NB, $wouldBlock);
        flock($h, LOCK_UN); 
        fclose($h);
        return (bool) $wouldBlock;
    }
    public function lookup(Request $request)
    {
        $key = $this->getCacheKey($request);
        if (!$entries = $this->getMetadata($key)) {
            return;
        }
        $match = null;
        foreach ($entries as $entry) {
            if ($this->requestsMatch(isset($entry[1]['vary'][0]) ? implode(', ', $entry[1]['vary']) : '', $request->headers->all(), $entry[0])) {
                $match = $entry;
                break;
            }
        }
        if (null === $match) {
            return;
        }
        $headers = $match[1];
        if (file_exists($body = $this->getPath($headers['x-content-digest'][0]))) {
            return $this->restoreResponse($headers, $body);
        }
    }
    public function write(Request $request, Response $response)
    {
        $key = $this->getCacheKey($request);
        $storedEnv = $this->persistRequest($request);
        if (!$response->headers->has('X-Content-Digest')) {
            $digest = $this->generateContentDigest($response);
            if (false === $this->save($digest, $response->getContent())) {
                throw new \RuntimeException('Unable to store the entity.');
            }
            $response->headers->set('X-Content-Digest', $digest);
            if (!$response->headers->has('Transfer-Encoding')) {
                $response->headers->set('Content-Length', \strlen($response->getContent()));
            }
        }
        $entries = [];
        $vary = $response->headers->get('vary');
        foreach ($this->getMetadata($key) as $entry) {
            if (!isset($entry[1]['vary'][0])) {
                $entry[1]['vary'] = [''];
            }
            if ($entry[1]['vary'][0] != $vary || !$this->requestsMatch($vary, $entry[0], $storedEnv)) {
                $entries[] = $entry;
            }
        }
        $headers = $this->persistResponse($response);
        unset($headers['age']);
        array_unshift($entries, [$storedEnv, $headers]);
        if (false === $this->save($key, serialize($entries))) {
            throw new \RuntimeException('Unable to store the metadata.');
        }
        return $key;
    }
    protected function generateContentDigest(Response $response)
    {
        return 'en'.hash('sha256', $response->getContent());
    }
    public function invalidate(Request $request)
    {
        $modified = false;
        $key = $this->getCacheKey($request);
        $entries = [];
        foreach ($this->getMetadata($key) as $entry) {
            $response = $this->restoreResponse($entry[1]);
            if ($response->isFresh()) {
                $response->expire();
                $modified = true;
                $entries[] = [$entry[0], $this->persistResponse($response)];
            } else {
                $entries[] = $entry;
            }
        }
        if ($modified && false === $this->save($key, serialize($entries))) {
            throw new \RuntimeException('Unable to store the metadata.');
        }
    }
    private function requestsMatch($vary, $env1, $env2)
    {
        if (empty($vary)) {
            return true;
        }
        foreach (preg_split('/[\s,]+/', $vary) as $header) {
            $key = str_replace('_', '-', strtolower($header));
            $v1 = isset($env1[$key]) ? $env1[$key] : null;
            $v2 = isset($env2[$key]) ? $env2[$key] : null;
            if ($v1 !== $v2) {
                return false;
            }
        }
        return true;
    }
    private function getMetadata($key)
    {
        if (!$entries = $this->load($key)) {
            return [];
        }
        return unserialize($entries);
    }
    public function purge($url)
    {
        $http = preg_replace('#^https:#', 'http:', $url);
        $https = preg_replace('#^http:#', 'https:', $url);
        $purgedHttp = $this->doPurge($http);
        $purgedHttps = $this->doPurge($https);
        return $purgedHttp || $purgedHttps;
    }
    private function doPurge($url)
    {
        $key = $this->getCacheKey(Request::create($url));
        if (isset($this->locks[$key])) {
            flock($this->locks[$key], LOCK_UN);
            fclose($this->locks[$key]);
            unset($this->locks[$key]);
        }
        if (file_exists($path = $this->getPath($key))) {
            unlink($path);
            return true;
        }
        return false;
    }
    private function load($key)
    {
        $path = $this->getPath($key);
        return file_exists($path) ? file_get_contents($path) : false;
    }
    private function save($key, $data)
    {
        $path = $this->getPath($key);
        if (isset($this->locks[$key])) {
            $fp = $this->locks[$key];
            @ftruncate($fp, 0);
            @fseek($fp, 0);
            $len = @fwrite($fp, $data);
            if (\strlen($data) !== $len) {
                @ftruncate($fp, 0);
                return false;
            }
        } else {
            if (!file_exists(\dirname($path)) && false === @mkdir(\dirname($path), 0777, true) && !is_dir(\dirname($path))) {
                return false;
            }
            $tmpFile = tempnam(\dirname($path), basename($path));
            if (false === $fp = @fopen($tmpFile, 'wb')) {
                @unlink($tmpFile);
                return false;
            }
            @fwrite($fp, $data);
            @fclose($fp);
            if ($data != file_get_contents($tmpFile)) {
                @unlink($tmpFile);
                return false;
            }
            if (false === @rename($tmpFile, $path)) {
                @unlink($tmpFile);
                return false;
            }
        }
        @chmod($path, 0666 & ~umask());
    }
    public function getPath($key)
    {
        return $this->root.\DIRECTORY_SEPARATOR.substr($key, 0, 2).\DIRECTORY_SEPARATOR.substr($key, 2, 2).\DIRECTORY_SEPARATOR.substr($key, 4, 2).\DIRECTORY_SEPARATOR.substr($key, 6);
    }
    protected function generateCacheKey(Request $request)
    {
        return 'md'.hash('sha256', $request->getUri());
    }
    private function getCacheKey(Request $request)
    {
        if (isset($this->keyCache[$request])) {
            return $this->keyCache[$request];
        }
        return $this->keyCache[$request] = $this->generateCacheKey($request);
    }
    private function persistRequest(Request $request)
    {
        return $request->headers->all();
    }
    private function persistResponse(Response $response)
    {
        $headers = $response->headers->all();
        $headers['X-Status'] = [$response->getStatusCode()];
        return $headers;
    }
    private function restoreResponse($headers, $body = null)
    {
        $status = $headers['X-Status'][0];
        unset($headers['X-Status']);
        if (null !== $body) {
            $headers['X-Body-File'] = [$body];
        }
        return new Response($body, $status, $headers);
    }
}
