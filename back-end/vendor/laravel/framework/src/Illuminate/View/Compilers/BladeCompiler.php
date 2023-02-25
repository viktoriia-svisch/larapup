<?php
namespace Illuminate\View\Compilers;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
class BladeCompiler extends Compiler implements CompilerInterface
{
    use Concerns\CompilesAuthorizations,
        Concerns\CompilesComments,
        Concerns\CompilesComponents,
        Concerns\CompilesConditionals,
        Concerns\CompilesEchos,
        Concerns\CompilesHelpers,
        Concerns\CompilesIncludes,
        Concerns\CompilesInjections,
        Concerns\CompilesJson,
        Concerns\CompilesLayouts,
        Concerns\CompilesLoops,
        Concerns\CompilesRawPhp,
        Concerns\CompilesStacks,
        Concerns\CompilesTranslations;
    protected $extensions = [];
    protected $customDirectives = [];
    protected $conditions = [];
    protected $path;
    protected $compilers = [
        'Comments',
        'Extensions',
        'Statements',
        'Echos',
    ];
    protected $rawTags = ['{!!', '!!}'];
    protected $contentTags = ['{{', '}}'];
    protected $escapedTags = ['{{{', '}}}'];
    protected $echoFormat = 'e(%s)';
    protected $footer = [];
    protected $rawBlocks = [];
    public function compile($path = null)
    {
        if ($path) {
            $this->setPath($path);
        }
        if (! is_null($this->cachePath)) {
            $contents = $this->compileString($this->files->get($this->getPath()));
            $this->files->put($this->getCompiledPath($this->getPath()), $contents);
        }
    }
    public function getPath()
    {
        return $this->path;
    }
    public function setPath($path)
    {
        $this->path = $path;
    }
    public function compileString($value)
    {
        if (strpos($value, '@verbatim') !== false) {
            $value = $this->storeVerbatimBlocks($value);
        }
        $this->footer = [];
        if (strpos($value, '@php') !== false) {
            $value = $this->storePhpBlocks($value);
        }
        $result = '';
        foreach (token_get_all($value) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }
        if (! empty($this->rawBlocks)) {
            $result = $this->restoreRawContent($result);
        }
        if (count($this->footer) > 0) {
            $result = $this->addFooters($result);
        }
        return $result;
    }
    protected function storeVerbatimBlocks($value)
    {
        return preg_replace_callback('/(?<!@)@verbatim(.*?)@endverbatim/s', function ($matches) {
            return $this->storeRawBlock($matches[1]);
        }, $value);
    }
    protected function storePhpBlocks($value)
    {
        return preg_replace_callback('/(?<!@)@php(.*?)@endphp/s', function ($matches) {
            return $this->storeRawBlock("<?php{$matches[1]}?>");
        }, $value);
    }
    protected function storeRawBlock($value)
    {
        return $this->getRawPlaceholder(
            array_push($this->rawBlocks, $value) - 1
        );
    }
    protected function restoreRawContent($result)
    {
        $result = preg_replace_callback('/'.$this->getRawPlaceholder('(\d+)').'/', function ($matches) {
            return $this->rawBlocks[$matches[1]];
        }, $result);
        $this->rawBlocks = [];
        return $result;
    }
    protected function getRawPlaceholder($replace)
    {
        return str_replace('#', $replace, '@__raw_block_#__@');
    }
    protected function addFooters($result)
    {
        return ltrim($result, PHP_EOL)
                .PHP_EOL.implode(PHP_EOL, array_reverse($this->footer));
    }
    protected function parseToken($token)
    {
        [$id, $content] = $token;
        if ($id == T_INLINE_HTML) {
            foreach ($this->compilers as $type) {
                $content = $this->{"compile{$type}"}($content);
            }
        }
        return $content;
    }
    protected function compileExtensions($value)
    {
        foreach ($this->extensions as $compiler) {
            $value = call_user_func($compiler, $value, $this);
        }
        return $value;
    }
    protected function compileStatements($value)
    {
        return preg_replace_callback(
            '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', function ($match) {
                return $this->compileStatement($match);
            }, $value
        );
    }
    protected function compileStatement($match)
    {
        if (Str::contains($match[1], '@')) {
            $match[0] = isset($match[3]) ? $match[1].$match[3] : $match[1];
        } elseif (isset($this->customDirectives[$match[1]])) {
            $match[0] = $this->callCustomDirective($match[1], Arr::get($match, 3));
        } elseif (method_exists($this, $method = 'compile'.ucfirst($match[1]))) {
            $match[0] = $this->$method(Arr::get($match, 3));
        }
        return isset($match[3]) ? $match[0] : $match[0].$match[2];
    }
    protected function callCustomDirective($name, $value)
    {
        if (Str::startsWith($value, '(') && Str::endsWith($value, ')')) {
            $value = Str::substr($value, 1, -1);
        }
        return call_user_func($this->customDirectives[$name], trim($value));
    }
    public function stripParentheses($expression)
    {
        if (Str::startsWith($expression, '(')) {
            $expression = substr($expression, 1, -1);
        }
        return $expression;
    }
    public function extend(callable $compiler)
    {
        $this->extensions[] = $compiler;
    }
    public function getExtensions()
    {
        return $this->extensions;
    }
    public function if($name, callable $callback)
    {
        $this->conditions[$name] = $callback;
        $this->directive($name, function ($expression) use ($name) {
            return $expression !== ''
                    ? "<?php if (\Illuminate\Support\Facades\Blade::check('{$name}', {$expression})): ?>"
                    : "<?php if (\Illuminate\Support\Facades\Blade::check('{$name}')): ?>";
        });
        $this->directive('else'.$name, function ($expression) use ($name) {
            return $expression !== ''
                ? "<?php elseif (\Illuminate\Support\Facades\Blade::check('{$name}', {$expression})): ?>"
                : "<?php elseif (\Illuminate\Support\Facades\Blade::check('{$name}')): ?>";
        });
        $this->directive('end'.$name, function () {
            return '<?php endif; ?>';
        });
    }
    public function check($name, ...$parameters)
    {
        return call_user_func($this->conditions[$name], ...$parameters);
    }
    public function component($path, $alias = null)
    {
        $alias = $alias ?: Arr::last(explode('.', $path));
        $this->directive($alias, function ($expression) use ($path) {
            return $expression
                        ? "<?php \$__env->startComponent('{$path}', {$expression}); ?>"
                        : "<?php \$__env->startComponent('{$path}'); ?>";
        });
        $this->directive('end'.$alias, function ($expression) {
            return '<?php echo $__env->renderComponent(); ?>';
        });
    }
    public function include($path, $alias = null)
    {
        $alias = $alias ?: Arr::last(explode('.', $path));
        $this->directive($alias, function ($expression) use ($path) {
            $expression = $this->stripParentheses($expression) ?: '[]';
            return "<?php echo \$__env->make('{$path}', {$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>";
        });
    }
    public function directive($name, callable $handler)
    {
        $this->customDirectives[$name] = $handler;
    }
    public function getCustomDirectives()
    {
        return $this->customDirectives;
    }
    public function setEchoFormat($format)
    {
        $this->echoFormat = $format;
    }
    public function withDoubleEncoding()
    {
        $this->setEchoFormat('e(%s, true)');
    }
    public function withoutDoubleEncoding()
    {
        $this->setEchoFormat('e(%s, false)');
    }
}
