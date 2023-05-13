<?php
namespace Symfony\Component\Routing\Matcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
class TraceableUrlMatcher extends UrlMatcher
{
    const ROUTE_DOES_NOT_MATCH = 0;
    const ROUTE_ALMOST_MATCHES = 1;
    const ROUTE_MATCHES = 2;
    protected $traces;
    public function getTraces($pathinfo)
    {
        $this->traces = [];
        try {
            $this->match($pathinfo);
        } catch (ExceptionInterface $e) {
        }
        return $this->traces;
    }
    public function getTracesForRequest(Request $request)
    {
        $this->request = $request;
        $traces = $this->getTraces($request->getPathInfo());
        $this->request = null;
        return $traces;
    }
    protected function matchCollection($pathinfo, RouteCollection $routes)
    {
        foreach ($routes as $name => $route) {
            $compiledRoute = $route->compile();
            if (!preg_match($compiledRoute->getRegex(), $pathinfo, $matches)) {
                $r = new Route($route->getPath(), $route->getDefaults(), [], $route->getOptions());
                $cr = $r->compile();
                if (!preg_match($cr->getRegex(), $pathinfo)) {
                    $this->addTrace(sprintf('Path "%s" does not match', $route->getPath()), self::ROUTE_DOES_NOT_MATCH, $name, $route);
                    continue;
                }
                foreach ($route->getRequirements() as $n => $regex) {
                    $r = new Route($route->getPath(), $route->getDefaults(), [$n => $regex], $route->getOptions());
                    $cr = $r->compile();
                    if (\in_array($n, $cr->getVariables()) && !preg_match($cr->getRegex(), $pathinfo)) {
                        $this->addTrace(sprintf('Requirement for "%s" does not match (%s)', $n, $regex), self::ROUTE_ALMOST_MATCHES, $name, $route);
                        continue 2;
                    }
                }
                continue;
            }
            $hostMatches = [];
            if ($compiledRoute->getHostRegex() && !preg_match($compiledRoute->getHostRegex(), $this->context->getHost(), $hostMatches)) {
                $this->addTrace(sprintf('Host "%s" does not match the requirement ("%s")', $this->context->getHost(), $route->getHost()), self::ROUTE_ALMOST_MATCHES, $name, $route);
                continue;
            }
            if ($requiredMethods = $route->getMethods()) {
                if ('HEAD' === $method = $this->context->getMethod()) {
                    $method = 'GET';
                }
                if (!\in_array($method, $requiredMethods)) {
                    $this->allow = array_merge($this->allow, $requiredMethods);
                    $this->addTrace(sprintf('Method "%s" does not match any of the required methods (%s)', $this->context->getMethod(), implode(', ', $requiredMethods)), self::ROUTE_ALMOST_MATCHES, $name, $route);
                    continue;
                }
            }
            if ($condition = $route->getCondition()) {
                if (!$this->getExpressionLanguage()->evaluate($condition, ['context' => $this->context, 'request' => $this->request ?: $this->createRequest($pathinfo)])) {
                    $this->addTrace(sprintf('Condition "%s" does not evaluate to "true"', $condition), self::ROUTE_ALMOST_MATCHES, $name, $route);
                    continue;
                }
            }
            if ($requiredSchemes = $route->getSchemes()) {
                $scheme = $this->context->getScheme();
                if (!$route->hasScheme($scheme)) {
                    $this->addTrace(sprintf('Scheme "%s" does not match any of the required schemes (%s); the user will be redirected to first required scheme', $scheme, implode(', ', $requiredSchemes)), self::ROUTE_ALMOST_MATCHES, $name, $route);
                    return true;
                }
            }
            $this->addTrace('Route matches!', self::ROUTE_MATCHES, $name, $route);
            return true;
        }
    }
    private function addTrace($log, $level = self::ROUTE_DOES_NOT_MATCH, $name = null, $route = null)
    {
        $this->traces[] = [
            'log' => $log,
            'name' => $name,
            'level' => $level,
            'path' => null !== $route ? $route->getPath() : null,
        ];
    }
}
