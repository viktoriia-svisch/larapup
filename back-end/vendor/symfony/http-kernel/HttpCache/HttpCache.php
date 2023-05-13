<?php
namespace Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
class HttpCache implements HttpKernelInterface, TerminableInterface
{
    private $kernel;
    private $store;
    private $request;
    private $surrogate;
    private $surrogateCacheStrategy;
    private $options = [];
    private $traces = [];
    public function __construct(HttpKernelInterface $kernel, StoreInterface $store, SurrogateInterface $surrogate = null, array $options = [])
    {
        $this->store = $store;
        $this->kernel = $kernel;
        $this->surrogate = $surrogate;
        register_shutdown_function([$this->store, 'cleanup']);
        $this->options = array_merge([
            'debug' => false,
            'default_ttl' => 0,
            'private_headers' => ['Authorization', 'Cookie'],
            'allow_reload' => false,
            'allow_revalidate' => false,
            'stale_while_revalidate' => 2,
            'stale_if_error' => 60,
        ], $options);
    }
    public function getStore()
    {
        return $this->store;
    }
    public function getTraces()
    {
        return $this->traces;
    }
    public function getLog()
    {
        $log = [];
        foreach ($this->traces as $request => $traces) {
            $log[] = sprintf('%s: %s', $request, implode(', ', $traces));
        }
        return implode('; ', $log);
    }
    public function getRequest()
    {
        return $this->request;
    }
    public function getKernel()
    {
        return $this->kernel;
    }
    public function getSurrogate()
    {
        return $this->surrogate;
    }
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $type) {
            $this->traces = [];
            $this->request = clone $request;
            if (null !== $this->surrogate) {
                $this->surrogateCacheStrategy = $this->surrogate->createCacheStrategy();
            }
        }
        $this->traces[$this->getTraceKey($request)] = [];
        if (!$request->isMethodSafe(false)) {
            $response = $this->invalidate($request, $catch);
        } elseif ($request->headers->has('expect') || !$request->isMethodCacheable()) {
            $response = $this->pass($request, $catch);
        } elseif ($this->options['allow_reload'] && $request->isNoCache()) {
            $this->record($request, 'reload');
            $response = $this->fetch($request, $catch);
        } else {
            $response = $this->lookup($request, $catch);
        }
        $this->restoreResponseBody($request, $response);
        if (HttpKernelInterface::MASTER_REQUEST === $type && $this->options['debug']) {
            $response->headers->set('X-Symfony-Cache', $this->getLog());
        }
        if (null !== $this->surrogate) {
            if (HttpKernelInterface::MASTER_REQUEST === $type) {
                $this->surrogateCacheStrategy->update($response);
            } else {
                $this->surrogateCacheStrategy->add($response);
            }
        }
        $response->prepare($request);
        $response->isNotModified($request);
        return $response;
    }
    public function terminate(Request $request, Response $response)
    {
        if ($this->getKernel() instanceof TerminableInterface) {
            $this->getKernel()->terminate($request, $response);
        }
    }
    protected function pass(Request $request, $catch = false)
    {
        $this->record($request, 'pass');
        return $this->forward($request, $catch);
    }
    protected function invalidate(Request $request, $catch = false)
    {
        $response = $this->pass($request, $catch);
        if ($response->isSuccessful() || $response->isRedirect()) {
            try {
                $this->store->invalidate($request);
                foreach (['Location', 'Content-Location'] as $header) {
                    if ($uri = $response->headers->get($header)) {
                        $subRequest = Request::create($uri, 'get', [], [], [], $request->server->all());
                        $this->store->invalidate($subRequest);
                    }
                }
                $this->record($request, 'invalidate');
            } catch (\Exception $e) {
                $this->record($request, 'invalidate-failed');
                if ($this->options['debug']) {
                    throw $e;
                }
            }
        }
        return $response;
    }
    protected function lookup(Request $request, $catch = false)
    {
        try {
            $entry = $this->store->lookup($request);
        } catch (\Exception $e) {
            $this->record($request, 'lookup-failed');
            if ($this->options['debug']) {
                throw $e;
            }
            return $this->pass($request, $catch);
        }
        if (null === $entry) {
            $this->record($request, 'miss');
            return $this->fetch($request, $catch);
        }
        if (!$this->isFreshEnough($request, $entry)) {
            $this->record($request, 'stale');
            return $this->validate($request, $entry, $catch);
        }
        $this->record($request, 'fresh');
        $entry->headers->set('Age', $entry->getAge());
        return $entry;
    }
    protected function validate(Request $request, Response $entry, $catch = false)
    {
        $subRequest = clone $request;
        if ('HEAD' === $request->getMethod()) {
            $subRequest->setMethod('GET');
        }
        $subRequest->headers->set('if_modified_since', $entry->headers->get('Last-Modified'));
        $cachedEtags = $entry->getEtag() ? [$entry->getEtag()] : [];
        $requestEtags = $request->getETags();
        if ($etags = array_unique(array_merge($cachedEtags, $requestEtags))) {
            $subRequest->headers->set('if_none_match', implode(', ', $etags));
        }
        $response = $this->forward($subRequest, $catch, $entry);
        if (304 == $response->getStatusCode()) {
            $this->record($request, 'valid');
            $etag = $response->getEtag();
            if ($etag && \in_array($etag, $requestEtags) && !\in_array($etag, $cachedEtags)) {
                return $response;
            }
            $entry = clone $entry;
            $entry->headers->remove('Date');
            foreach (['Date', 'Expires', 'Cache-Control', 'ETag', 'Last-Modified'] as $name) {
                if ($response->headers->has($name)) {
                    $entry->headers->set($name, $response->headers->get($name));
                }
            }
            $response = $entry;
        } else {
            $this->record($request, 'invalid');
        }
        if ($response->isCacheable()) {
            $this->store($request, $response);
        }
        return $response;
    }
    protected function fetch(Request $request, $catch = false)
    {
        $subRequest = clone $request;
        if ('HEAD' === $request->getMethod()) {
            $subRequest->setMethod('GET');
        }
        $subRequest->headers->remove('if_modified_since');
        $subRequest->headers->remove('if_none_match');
        $response = $this->forward($subRequest, $catch);
        if ($response->isCacheable()) {
            $this->store($request, $response);
        }
        return $response;
    }
    protected function forward(Request $request, $catch = false, Response $entry = null)
    {
        if ($this->surrogate) {
            $this->surrogate->addSurrogateCapability($request);
        }
        $response = SubRequestHandler::handle($this->kernel, $request, HttpKernelInterface::MASTER_REQUEST, $catch);
        if (null !== $entry && \in_array($response->getStatusCode(), [500, 502, 503, 504])) {
            if (null === $age = $entry->headers->getCacheControlDirective('stale-if-error')) {
                $age = $this->options['stale_if_error'];
            }
            if (abs($entry->getTtl()) < $age) {
                $this->record($request, 'stale-if-error');
                return $entry;
            }
        }
        if (!$response->headers->has('Date')) {
            $response->setDate(\DateTime::createFromFormat('U', time()));
        }
        $this->processResponseBody($request, $response);
        if ($this->isPrivateRequest($request) && !$response->headers->hasCacheControlDirective('public')) {
            $response->setPrivate();
        } elseif ($this->options['default_ttl'] > 0 && null === $response->getTtl() && !$response->headers->getCacheControlDirective('must-revalidate')) {
            $response->setTtl($this->options['default_ttl']);
        }
        return $response;
    }
    protected function isFreshEnough(Request $request, Response $entry)
    {
        if (!$entry->isFresh()) {
            return $this->lock($request, $entry);
        }
        if ($this->options['allow_revalidate'] && null !== $maxAge = $request->headers->getCacheControlDirective('max-age')) {
            return $maxAge > 0 && $maxAge >= $entry->getAge();
        }
        return true;
    }
    protected function lock(Request $request, Response $entry)
    {
        $lock = $this->store->lock($request);
        if (true === $lock) {
            return false;
        }
        if ($this->mayServeStaleWhileRevalidate($entry)) {
            $this->record($request, 'stale-while-revalidate');
            return true;
        }
        if ($this->waitForLock($request)) {
            $new = $this->lookup($request);
            $entry->headers = $new->headers;
            $entry->setContent($new->getContent());
            $entry->setStatusCode($new->getStatusCode());
            $entry->setProtocolVersion($new->getProtocolVersion());
            foreach ($new->headers->getCookies() as $cookie) {
                $entry->headers->setCookie($cookie);
            }
        } else {
            $entry->setStatusCode(503);
            $entry->setContent('503 Service Unavailable');
            $entry->headers->set('Retry-After', 10);
        }
        return true;
    }
    protected function store(Request $request, Response $response)
    {
        try {
            $this->store->write($request, $response);
            $this->record($request, 'store');
            $response->headers->set('Age', $response->getAge());
        } catch (\Exception $e) {
            $this->record($request, 'store-failed');
            if ($this->options['debug']) {
                throw $e;
            }
        }
        $this->store->unlock($request);
    }
    private function restoreResponseBody(Request $request, Response $response)
    {
        if ($response->headers->has('X-Body-Eval')) {
            ob_start();
            if ($response->headers->has('X-Body-File')) {
                include $response->headers->get('X-Body-File');
            } else {
                eval('; ?>'.$response->getContent().'<?php ;');
            }
            $response->setContent(ob_get_clean());
            $response->headers->remove('X-Body-Eval');
            if (!$response->headers->has('Transfer-Encoding')) {
                $response->headers->set('Content-Length', \strlen($response->getContent()));
            }
        } elseif ($response->headers->has('X-Body-File')) {
            if (!$request->isMethod('HEAD')) {
                $response->setContent(file_get_contents($response->headers->get('X-Body-File')));
            }
        } else {
            return;
        }
        $response->headers->remove('X-Body-File');
    }
    protected function processResponseBody(Request $request, Response $response)
    {
        if (null !== $this->surrogate && $this->surrogate->needsParsing($response)) {
            $this->surrogate->process($request, $response);
        }
    }
    private function isPrivateRequest(Request $request)
    {
        foreach ($this->options['private_headers'] as $key) {
            $key = strtolower(str_replace('HTTP_', '', $key));
            if ('cookie' === $key) {
                if (\count($request->cookies->all())) {
                    return true;
                }
            } elseif ($request->headers->has($key)) {
                return true;
            }
        }
        return false;
    }
    private function record(Request $request, string $event)
    {
        $this->traces[$this->getTraceKey($request)][] = $event;
    }
    private function getTraceKey(Request $request): string
    {
        $path = $request->getPathInfo();
        if ($qs = $request->getQueryString()) {
            $path .= '?'.$qs;
        }
        return $request->getMethod().' '.$path;
    }
    private function mayServeStaleWhileRevalidate(Response $entry): bool
    {
        $timeout = $entry->headers->getCacheControlDirective('stale-while-revalidate');
        if (null === $timeout) {
            $timeout = $this->options['stale_while_revalidate'];
        }
        return abs($entry->getTtl()) < $timeout;
    }
    private function waitForLock(Request $request): bool
    {
        $wait = 0;
        while ($this->store->isLocked($request) && $wait < 100) {
            usleep(50000);
            ++$wait;
        }
        return $wait < 100;
    }
}
