<?php
namespace Symfony\Component\VarDumper\Dumper;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Cloner\DumperInterface;
abstract class AbstractDumper implements DataDumperInterface, DumperInterface
{
    const DUMP_LIGHT_ARRAY = 1;
    const DUMP_STRING_LENGTH = 2;
    const DUMP_COMMA_SEPARATOR = 4;
    const DUMP_TRAILING_COMMA = 8;
    public static $defaultOutput = 'php:
    protected $line = '';
    protected $lineDumper;
    protected $outputStream;
    protected $decimalPoint; 
    protected $indentPad = '  ';
    protected $flags;
    private $charset;
    public function __construct($output = null, string $charset = null, int $flags = 0)
    {
        $this->flags = $flags;
        $this->setCharset($charset ?: ini_get('php.output_encoding') ?: ini_get('default_charset') ?: 'UTF-8');
        $this->decimalPoint = localeconv();
        $this->decimalPoint = $this->decimalPoint['decimal_point'];
        $this->setOutput($output ?: static::$defaultOutput);
        if (!$output && \is_string(static::$defaultOutput)) {
            static::$defaultOutput = $this->outputStream;
        }
    }
    public function setOutput($output)
    {
        $prev = null !== $this->outputStream ? $this->outputStream : $this->lineDumper;
        if (\is_callable($output)) {
            $this->outputStream = null;
            $this->lineDumper = $output;
        } else {
            if (\is_string($output)) {
                $output = fopen($output, 'wb');
            }
            $this->outputStream = $output;
            $this->lineDumper = [$this, 'echoLine'];
        }
        return $prev;
    }
    public function setCharset($charset)
    {
        $prev = $this->charset;
        $charset = strtoupper($charset);
        $charset = null === $charset || 'UTF-8' === $charset || 'UTF8' === $charset ? 'CP1252' : $charset;
        $this->charset = $charset;
        return $prev;
    }
    public function setIndentPad($pad)
    {
        $prev = $this->indentPad;
        $this->indentPad = $pad;
        return $prev;
    }
    public function dump(Data $data, $output = null)
    {
        $this->decimalPoint = localeconv();
        $this->decimalPoint = $this->decimalPoint['decimal_point'];
        if ($locale = $this->flags & (self::DUMP_COMMA_SEPARATOR | self::DUMP_TRAILING_COMMA) ? setlocale(LC_NUMERIC, 0) : null) {
            setlocale(LC_NUMERIC, 'C');
        }
        if ($returnDump = true === $output) {
            $output = fopen('php:
        }
        if ($output) {
            $prevOutput = $this->setOutput($output);
        }
        try {
            $data->dump($this);
            $this->dumpLine(-1);
            if ($returnDump) {
                $result = stream_get_contents($output, -1, 0);
                fclose($output);
                return $result;
            }
        } finally {
            if ($output) {
                $this->setOutput($prevOutput);
            }
            if ($locale) {
                setlocale(LC_NUMERIC, $locale);
            }
        }
    }
    protected function dumpLine($depth)
    {
        ($this->lineDumper)($this->line, $depth, $this->indentPad);
        $this->line = '';
    }
    protected function echoLine($line, $depth, $indentPad)
    {
        if (-1 !== $depth) {
            fwrite($this->outputStream, str_repeat($indentPad, $depth).$line."\n");
        }
    }
    protected function utf8Encode($s)
    {
        if (preg_match('
            return $s;
        }
        if (!\function_exists('iconv')) {
            throw new \RuntimeException('Unable to convert a non-UTF-8 string to UTF-8: required function iconv() does not exist. You should install ext-iconv or symfony/polyfill-iconv.');
        }
        if (false !== $c = @iconv($this->charset, 'UTF-8', $s)) {
            return $c;
        }
        if ('CP1252' !== $this->charset && false !== $c = @iconv('CP1252', 'UTF-8', $s)) {
            return $c;
        }
        return iconv('CP850', 'UTF-8', $s);
    }
}
