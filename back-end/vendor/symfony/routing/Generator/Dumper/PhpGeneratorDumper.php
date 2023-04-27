<?php
namespace Symfony\Component\Routing\Generator\Dumper;
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;
class PhpGeneratorDumper extends GeneratorDumper
{
    public function dump(array $options = [])
    {
        $options = array_merge([
            'class' => 'ProjectUrlGenerator',
            'base_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
        ], $options);
        return <<<EOF
<?php
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Psr\Log\LoggerInterface;
class {$options['class']} extends {$options['base_class']}
{
    private static \$declaredRoutes;
    private \$defaultLocale;
    public function __construct(RequestContext \$context, LoggerInterface \$logger = null, string \$defaultLocale = null)
    {
        \$this->context = \$context;
        \$this->logger = \$logger;
        \$this->defaultLocale = \$defaultLocale;
        if (null === self::\$declaredRoutes) {
            self::\$declaredRoutes = {$this->generateDeclaredRoutes()};
        }
    }
{$this->generateGenerateMethod()}
}
EOF;
    }
    private function generateDeclaredRoutes()
    {
        $routes = "[\n";
        foreach ($this->getRoutes()->all() as $name => $route) {
            $compiledRoute = $route->compile();
            $properties = [];
            $properties[] = $compiledRoute->getVariables();
            $properties[] = $route->getDefaults();
            $properties[] = $route->getRequirements();
            $properties[] = $compiledRoute->getTokens();
            $properties[] = $compiledRoute->getHostTokens();
            $properties[] = $route->getSchemes();
            $routes .= sprintf("        '%s' => %s,\n", $name, PhpMatcherDumper::export($properties));
        }
        $routes .= '    ]';
        return $routes;
    }
    private function generateGenerateMethod()
    {
        return <<<'EOF'
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        $locale = $parameters['_locale']
            ?? $this->context->getParameter('_locale')
            ?: $this->defaultLocale;
        if (null !== $locale && null !== $name) {
            do {
                if ((self::$declaredRoutes[$name.'.'.$locale][1]['_canonical_route'] ?? null) === $name) {
                    unset($parameters['_locale']);
                    $name .= '.'.$locale;
                    break;
                }
            } while (false !== $locale = strstr($locale, '_', true));
        }
        if (!isset(self::$declaredRoutes[$name])) {
            throw new RouteNotFoundException(sprintf('Unable to generate a URL for the named route "%s" as such route does not exist.', $name));
        }
        list($variables, $defaults, $requirements, $tokens, $hostTokens, $requiredSchemes) = self::$declaredRoutes[$name];
        return $this->doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes);
    }
EOF;
    }
}
