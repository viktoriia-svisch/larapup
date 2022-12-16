<?php
namespace Psy\VarDumper;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Cloner\Stub;
class Presenter
{
    const VERBOSE = 1;
    private $cloner;
    private $dumper;
    private $exceptionsImportants = [
        "\0*\0message",
        "\0*\0code",
        "\0*\0file",
        "\0*\0line",
        "\0Exception\0previous",
    ];
    private $styles = [
        'num'       => 'number',
        'const'     => 'const',
        'str'       => 'string',
        'cchr'      => 'default',
        'note'      => 'class',
        'ref'       => 'default',
        'public'    => 'public',
        'protected' => 'protected',
        'private'   => 'private',
        'meta'      => 'comment',
        'key'       => 'comment',
        'index'     => 'number',
    ];
    public function __construct(OutputFormatter $formatter, $forceArrayIndexes = false)
    {
        $oldLocale = \setlocale(LC_NUMERIC, 0);
        \setlocale(LC_NUMERIC, 'C');
        $this->dumper = new Dumper($formatter, $forceArrayIndexes);
        $this->dumper->setStyles($this->styles);
        \setlocale(LC_NUMERIC, $oldLocale);
        $this->cloner = new Cloner();
        $this->cloner->addCasters(['*' => function ($obj, array $a, Stub $stub, $isNested, $filter = 0) {
            if ($filter || $isNested) {
                if ($obj instanceof \Exception) {
                    $a = Caster::filter($a, Caster::EXCLUDE_NOT_IMPORTANT | Caster::EXCLUDE_EMPTY, $this->exceptionsImportants);
                } else {
                    $a = Caster::filter($a, Caster::EXCLUDE_PROTECTED | Caster::EXCLUDE_PRIVATE);
                }
            }
            return $a;
        }]);
    }
    public function addCasters(array $casters)
    {
        $this->cloner->addCasters($casters);
    }
    public function presentRef($value)
    {
        return $this->present($value, 0);
    }
    public function present($value, $depth = null, $options = 0)
    {
        $data = $this->cloner->cloneVar($value, !($options & self::VERBOSE) ? Caster::EXCLUDE_VERBOSE : 0);
        if (null !== $depth) {
            $data = $data->withMaxDepth($depth);
        }
        $oldLocale = \setlocale(LC_NUMERIC, 0);
        \setlocale(LC_NUMERIC, 'C');
        $output = '';
        $this->dumper->dump($data, function ($line, $depth) use (&$output) {
            if ($depth >= 0) {
                if ('' !== $output) {
                    $output .= PHP_EOL;
                }
                $output .= \str_repeat('  ', $depth) . $line;
            }
        });
        \setlocale(LC_NUMERIC, $oldLocale);
        return OutputFormatter::escape($output);
    }
}
