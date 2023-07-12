<?php
namespace Symfony\Component\HttpKernel\Fragment;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
class FragmentHandler
{
    private $debug;
    private $renderers = [];
    private $requestStack;
    public function __construct(RequestStack $requestStack, array $renderers = [], bool $debug = false)
    {
        $this->requestStack = $requestStack;
        foreach ($renderers as $renderer) {
            $this->addRenderer($renderer);
        }
        $this->debug = $debug;
    }
    public function addRenderer(FragmentRendererInterface $renderer)
    {
        $this->renderers[$renderer->getName()] = $renderer;
    }
    public function render($uri, $renderer = 'inline', array $options = [])
    {
        if (!isset($options['ignore_errors'])) {
            $options['ignore_errors'] = !$this->debug;
        }
        if (!isset($this->renderers[$renderer])) {
            throw new \InvalidArgumentException(sprintf('The "%s" renderer does not exist.', $renderer));
        }
        if (!$request = $this->requestStack->getCurrentRequest()) {
            throw new \LogicException('Rendering a fragment can only be done when handling a Request.');
        }
        return $this->deliver($this->renderers[$renderer]->render($uri, $request, $options));
    }
    protected function deliver(Response $response)
    {
        if (!$response->isSuccessful()) {
            throw new \RuntimeException(sprintf('Error when rendering "%s" (Status code is %s).', $this->requestStack->getCurrentRequest()->getUri(), $response->getStatusCode()));
        }
        if (!$response instanceof StreamedResponse) {
            return $response->getContent();
        }
        $response->sendContent();
    }
}
