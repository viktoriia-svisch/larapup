<?php
namespace Symfony\Component\Routing\Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
class UrlGenerator implements UrlGeneratorInterface, ConfigurableRequirementsInterface
{
    protected $routes;
    protected $context;
    protected $strictRequirements = true;
    protected $logger;
    private $defaultLocale;
    protected $decodedChars = [
        '%2F' => '/',
        '%40' => '@',
        '%3A' => ':',
        '%3B' => ';',
        '%2C' => ',',
        '%3D' => '=',
        '%2B' => '+',
        '%21' => '!',
        '%2A' => '*',
        '%7C' => '|',
    ];
    public function __construct(RouteCollection $routes, RequestContext $context, LoggerInterface $logger = null, string $defaultLocale = null)
    {
        $this->routes = $routes;
        $this->context = $context;
        $this->logger = $logger;
        $this->defaultLocale = $defaultLocale;
    }
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }
    public function getContext()
    {
        return $this->context;
    }
    public function setStrictRequirements($enabled)
    {
        $this->strictRequirements = null === $enabled ? null : (bool) $enabled;
    }
    public function isStrictRequirements()
    {
        return $this->strictRequirements;
    }
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        $route = null;
        $locale = $parameters['_locale']
            ?? $this->context->getParameter('_locale')
            ?: $this->defaultLocale;
        if (null !== $locale) {
            do {
                if (null !== ($route = $this->routes->get($name.'.'.$locale)) && $route->getDefault('_canonical_route') === $name) {
                    unset($parameters['_locale']);
                    break;
                }
            } while (false !== $locale = strstr($locale, '_', true));
        }
        if (null === $route = $route ?? $this->routes->get($name)) {
            throw new RouteNotFoundException(sprintf('Unable to generate a URL for the named route "%s" as such route does not exist.', $name));
        }
        $compiledRoute = $route->compile();
        return $this->doGenerate($compiledRoute->getVariables(), $route->getDefaults(), $route->getRequirements(), $compiledRoute->getTokens(), $parameters, $name, $referenceType, $compiledRoute->getHostTokens(), $route->getSchemes());
    }
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, array $requiredSchemes = [])
    {
        $variables = array_flip($variables);
        $mergedParams = array_replace($defaults, $this->context->getParameters(), $parameters);
        if ($diff = array_diff_key($variables, $mergedParams)) {
            throw new MissingMandatoryParametersException(sprintf('Some mandatory parameters are missing ("%s") to generate a URL for route "%s".', implode('", "', array_keys($diff)), $name));
        }
        $url = '';
        $optional = true;
        $message = 'Parameter "{parameter}" for route "{route}" must match "{expected}" ("{given}" given) to generate a corresponding URL.';
        foreach ($tokens as $token) {
            if ('variable' === $token[0]) {
                if (!$optional || !\array_key_exists($token[3], $defaults) || null !== $mergedParams[$token[3]] && (string) $mergedParams[$token[3]] !== (string) $defaults[$token[3]]) {
                    if (null !== $this->strictRequirements && !preg_match('#^'.preg_replace('/\(\?(?:=|<=|!|<!)((?:[^()\\\\]+|\\\\.|\((?1)\))*)\)/', '', $token[2]).'$#'.(empty($token[4]) ? '' : 'u'), $mergedParams[$token[3]])) {
                        if ($this->strictRequirements) {
                            throw new InvalidParameterException(strtr($message, ['{parameter}' => $token[3], '{route}' => $name, '{expected}' => $token[2], '{given}' => $mergedParams[$token[3]]]));
                        }
                        if ($this->logger) {
                            $this->logger->error($message, ['parameter' => $token[3], 'route' => $name, 'expected' => $token[2], 'given' => $mergedParams[$token[3]]]);
                        }
                        return;
                    }
                    $url = $token[1].$mergedParams[$token[3]].$url;
                    $optional = false;
                }
            } else {
                $url = $token[1].$url;
                $optional = false;
            }
        }
        if ('' === $url) {
            $url = '/';
        }
        $url = strtr(rawurlencode($url), $this->decodedChars);
        $url = strtr($url, ['/../' => '/%2E%2E/', '/./' => '/%2E/']);
        if ('/..' === substr($url, -3)) {
            $url = substr($url, 0, -2).'%2E%2E';
        } elseif ('/.' === substr($url, -2)) {
            $url = substr($url, 0, -1).'%2E';
        }
        $schemeAuthority = '';
        $host = $this->context->getHost();
        $scheme = $this->context->getScheme();
        if ($requiredSchemes) {
            if (!\in_array($scheme, $requiredSchemes, true)) {
                $referenceType = self::ABSOLUTE_URL;
                $scheme = current($requiredSchemes);
            }
        }
        if ($hostTokens) {
            $routeHost = '';
            foreach ($hostTokens as $token) {
                if ('variable' === $token[0]) {
                    if (null !== $this->strictRequirements && !preg_match('#^'.preg_replace('/\(\?(?:=|<=|!|<!)((?:[^()\\\\]+|\\\\.|\((?1)\))*)\)/', '', $token[2]).'$#i'.(empty($token[4]) ? '' : 'u'), $mergedParams[$token[3]])) {
                        if ($this->strictRequirements) {
                            throw new InvalidParameterException(strtr($message, ['{parameter}' => $token[3], '{route}' => $name, '{expected}' => $token[2], '{given}' => $mergedParams[$token[3]]]));
                        }
                        if ($this->logger) {
                            $this->logger->error($message, ['parameter' => $token[3], 'route' => $name, 'expected' => $token[2], 'given' => $mergedParams[$token[3]]]);
                        }
                        return;
                    }
                    $routeHost = $token[1].$mergedParams[$token[3]].$routeHost;
                } else {
                    $routeHost = $token[1].$routeHost;
                }
            }
            if ($routeHost !== $host) {
                $host = $routeHost;
                if (self::ABSOLUTE_URL !== $referenceType) {
                    $referenceType = self::NETWORK_PATH;
                }
            }
        }
        if ((self::ABSOLUTE_URL === $referenceType || self::NETWORK_PATH === $referenceType) && !empty($host)) {
            $port = '';
            if ('http' === $scheme && 80 != $this->context->getHttpPort()) {
                $port = ':'.$this->context->getHttpPort();
            } elseif ('https' === $scheme && 443 != $this->context->getHttpsPort()) {
                $port = ':'.$this->context->getHttpsPort();
            }
            $schemeAuthority = self::NETWORK_PATH === $referenceType ? '
            $schemeAuthority .= $host.$port;
        }
        if (self::RELATIVE_PATH === $referenceType) {
            $url = self::getRelativePath($this->context->getPathInfo(), $url);
        } else {
            $url = $schemeAuthority.$this->context->getBaseUrl().$url;
        }
        $extra = array_udiff_assoc(array_diff_key($parameters, $variables), $defaults, function ($a, $b) {
            return $a == $b ? 0 : 1;
        });
        $fragment = $defaults['_fragment'] ?? '';
        if (isset($extra['_fragment'])) {
            $fragment = $extra['_fragment'];
            unset($extra['_fragment']);
        }
        if ($extra && $query = http_build_query($extra, '', '&', PHP_QUERY_RFC3986)) {
            $url .= '?'.strtr($query, ['%2F' => '/']);
        }
        if ('' !== $fragment) {
            $url .= '#'.strtr(rawurlencode($fragment), ['%2F' => '/', '%3F' => '?']);
        }
        return $url;
    }
    public static function getRelativePath($basePath, $targetPath)
    {
        if ($basePath === $targetPath) {
            return '';
        }
        $sourceDirs = explode('/', isset($basePath[0]) && '/' === $basePath[0] ? substr($basePath, 1) : $basePath);
        $targetDirs = explode('/', isset($targetPath[0]) && '/' === $targetPath[0] ? substr($targetPath, 1) : $targetPath);
        array_pop($sourceDirs);
        $targetFile = array_pop($targetDirs);
        foreach ($sourceDirs as $i => $dir) {
            if (isset($targetDirs[$i]) && $dir === $targetDirs[$i]) {
                unset($sourceDirs[$i], $targetDirs[$i]);
            } else {
                break;
            }
        }
        $targetDirs[] = $targetFile;
        $path = str_repeat('../', \count($sourceDirs)).implode('/', $targetDirs);
        return '' === $path || '/' === $path[0]
            || false !== ($colonPos = strpos($path, ':')) && ($colonPos < ($slashPos = strpos($path, '/')) || false === $slashPos)
            ? "./$path" : $path;
    }
}
