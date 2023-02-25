<?php
namespace Symfony\Component\HttpKernel\Fragment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\ExistsLoaderInterface;
class HIncludeFragmentRenderer extends RoutableFragmentRenderer
{
    private $globalDefaultTemplate;
    private $signer;
    private $templating;
    private $charset;
    public function __construct($templating = null, UriSigner $signer = null, string $globalDefaultTemplate = null, string $charset = 'utf-8')
    {
        $this->setTemplating($templating);
        $this->globalDefaultTemplate = $globalDefaultTemplate;
        $this->signer = $signer;
        $this->charset = $charset;
    }
    public function setTemplating($templating)
    {
        if (null !== $templating && !$templating instanceof EngineInterface && !$templating instanceof Environment) {
            throw new \InvalidArgumentException('The hinclude rendering strategy needs an instance of Twig\Environment or Symfony\Component\Templating\EngineInterface');
        }
        $this->templating = $templating;
    }
    public function hasTemplating()
    {
        return null !== $this->templating;
    }
    public function render($uri, Request $request, array $options = [])
    {
        if ($uri instanceof ControllerReference) {
            if (null === $this->signer) {
                throw new \LogicException('You must use a proper URI when using the Hinclude rendering strategy or set a URL signer.');
            }
            $uri = substr($this->signer->sign($this->generateFragmentUri($uri, $request, true)), \strlen($request->getSchemeAndHttpHost()));
        }
        $uri = str_replace('&', '&amp;', $uri);
        $template = isset($options['default']) ? $options['default'] : $this->globalDefaultTemplate;
        if (null !== $this->templating && $template && $this->templateExists($template)) {
            $content = $this->templating->render($template);
        } else {
            $content = $template;
        }
        $attributes = isset($options['attributes']) && \is_array($options['attributes']) ? $options['attributes'] : [];
        if (isset($options['id']) && $options['id']) {
            $attributes['id'] = $options['id'];
        }
        $renderedAttributes = '';
        if (\count($attributes) > 0) {
            $flags = ENT_QUOTES | ENT_SUBSTITUTE;
            foreach ($attributes as $attribute => $value) {
                $renderedAttributes .= sprintf(
                    ' %s="%s"',
                    htmlspecialchars($attribute, $flags, $this->charset, false),
                    htmlspecialchars($value, $flags, $this->charset, false)
                );
            }
        }
        return new Response(sprintf('<hx:include src="%s"%s>%s</hx:include>', $uri, $renderedAttributes, $content));
    }
    private function templateExists(string $template): bool
    {
        if ($this->templating instanceof EngineInterface) {
            try {
                return $this->templating->exists($template);
            } catch (\Exception $e) {
                return false;
            }
        }
        $loader = $this->templating->getLoader();
        if ($loader instanceof ExistsLoaderInterface || method_exists($loader, 'exists')) {
            return $loader->exists($template);
        }
        try {
            if (method_exists($loader, 'getSourceContext')) {
                $loader->getSourceContext($template);
            } else {
                $loader->getSource($template);
            }
            return true;
        } catch (LoaderError $e) {
        }
        return false;
    }
    public function getName()
    {
        return 'hinclude';
    }
}
