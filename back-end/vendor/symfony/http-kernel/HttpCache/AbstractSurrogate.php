<?php
namespace Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
abstract class AbstractSurrogate implements SurrogateInterface
{
    protected $contentTypes;
    protected $phpEscapeMap = [
        ['<?', '<%', '<s', '<S'],
        ['<?php echo "<?"; ?>', '<?php echo "<%"; ?>', '<?php echo "<s"; ?>', '<?php echo "<S"; ?>'],
    ];
    public function __construct(array $contentTypes = ['text/html', 'text/xml', 'application/xhtml+xml', 'application/xml'])
    {
        $this->contentTypes = $contentTypes;
    }
    public function createCacheStrategy()
    {
        return new ResponseCacheStrategy();
    }
    public function hasSurrogateCapability(Request $request)
    {
        if (null === $value = $request->headers->get('Surrogate-Capability')) {
            return false;
        }
        return false !== strpos($value, sprintf('%s/1.0', strtoupper($this->getName())));
    }
    public function addSurrogateCapability(Request $request)
    {
        $current = $request->headers->get('Surrogate-Capability');
        $new = sprintf('symfony="%s/1.0"', strtoupper($this->getName()));
        $request->headers->set('Surrogate-Capability', $current ? $current.', '.$new : $new);
    }
    public function needsParsing(Response $response)
    {
        if (!$control = $response->headers->get('Surrogate-Control')) {
            return false;
        }
        $pattern = sprintf('#content="[^"]*%s/1.0[^"]*"#', strtoupper($this->getName()));
        return (bool) preg_match($pattern, $control);
    }
    public function handle(HttpCache $cache, $uri, $alt, $ignoreErrors)
    {
        $subRequest = Request::create($uri, Request::METHOD_GET, [], $cache->getRequest()->cookies->all(), [], $cache->getRequest()->server->all());
        try {
            $response = $cache->handle($subRequest, HttpKernelInterface::SUB_REQUEST, true);
            if (!$response->isSuccessful()) {
                throw new \RuntimeException(sprintf('Error when rendering "%s" (Status code is %s).', $subRequest->getUri(), $response->getStatusCode()));
            }
            return $response->getContent();
        } catch (\Exception $e) {
            if ($alt) {
                return $this->handle($cache, $alt, '', $ignoreErrors);
            }
            if (!$ignoreErrors) {
                throw $e;
            }
        }
    }
    protected function removeFromControl(Response $response)
    {
        if (!$response->headers->has('Surrogate-Control')) {
            return;
        }
        $value = $response->headers->get('Surrogate-Control');
        $upperName = strtoupper($this->getName());
        if (sprintf('content="%s/1.0"', $upperName) == $value) {
            $response->headers->remove('Surrogate-Control');
        } elseif (preg_match(sprintf('#,\s*content="%s/1.0"#', $upperName), $value)) {
            $response->headers->set('Surrogate-Control', preg_replace(sprintf('#,\s*content="%s/1.0"#', $upperName), '', $value));
        } elseif (preg_match(sprintf('#content="%s/1.0",\s*#', $upperName), $value)) {
            $response->headers->set('Surrogate-Control', preg_replace(sprintf('#content="%s/1.0",\s*#', $upperName), '', $value));
        }
    }
}
