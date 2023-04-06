<?php
namespace Psy;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard as Printer;
use Psy\CodeCleaner\AbstractClassPass;
use Psy\CodeCleaner\AssignThisVariablePass;
use Psy\CodeCleaner\CalledClassPass;
use Psy\CodeCleaner\CallTimePassByReferencePass;
use Psy\CodeCleaner\ExitPass;
use Psy\CodeCleaner\FinalClassPass;
use Psy\CodeCleaner\FunctionContextPass;
use Psy\CodeCleaner\FunctionReturnInWriteContextPass;
use Psy\CodeCleaner\ImplicitReturnPass;
use Psy\CodeCleaner\InstanceOfPass;
use Psy\CodeCleaner\LeavePsyshAlonePass;
use Psy\CodeCleaner\LegacyEmptyPass;
use Psy\CodeCleaner\ListPass;
use Psy\CodeCleaner\LoopContextPass;
use Psy\CodeCleaner\MagicConstantsPass;
use Psy\CodeCleaner\NamespacePass;
use Psy\CodeCleaner\PassableByReferencePass;
use Psy\CodeCleaner\RequirePass;
use Psy\CodeCleaner\StrictTypesPass;
use Psy\CodeCleaner\UseStatementPass;
use Psy\CodeCleaner\ValidClassNamePass;
use Psy\CodeCleaner\ValidConstantPass;
use Psy\CodeCleaner\ValidConstructorPass;
use Psy\CodeCleaner\ValidFunctionNamePass;
use Psy\Exception\ParseErrorException;
class CodeCleaner
{
    private $parser;
    private $printer;
    private $traverser;
    private $namespace;
    public function __construct(Parser $parser = null, Printer $printer = null, NodeTraverser $traverser = null)
    {
        if ($parser === null) {
            $parserFactory = new ParserFactory();
            $parser        = $parserFactory->createParser();
        }
        $this->parser    = $parser;
        $this->printer   = $printer ?: new Printer();
        $this->traverser = $traverser ?: new NodeTraverser();
        foreach ($this->getDefaultPasses() as $pass) {
            $this->traverser->addVisitor($pass);
        }
    }
    private function getDefaultPasses()
    {
        $useStatementPass = new UseStatementPass();
        $namespacePass    = new NamespacePass($this);
        $this->addImplicitDebugContext([$useStatementPass, $namespacePass]);
        return [
            new AbstractClassPass(),
            new AssignThisVariablePass(),
            new CalledClassPass(),
            new CallTimePassByReferencePass(),
            new FinalClassPass(),
            new FunctionContextPass(),
            new FunctionReturnInWriteContextPass(),
            new InstanceOfPass(),
            new LeavePsyshAlonePass(),
            new LegacyEmptyPass(),
            new ListPass(),
            new LoopContextPass(),
            new PassableByReferencePass(),
            new ValidConstructorPass(),
            $useStatementPass,        
            new ExitPass(),
            new ImplicitReturnPass(),
            new MagicConstantsPass(),
            $namespacePass,           
            new RequirePass(),
            new StrictTypesPass(),
            new ValidClassNamePass(),
            new ValidConstantPass(),
            new ValidFunctionNamePass(),
        ];
    }
    private function addImplicitDebugContext(array $passes)
    {
        $file = $this->getDebugFile();
        if ($file === null) {
            return;
        }
        try {
            $code = @\file_get_contents($file);
            if (!$code) {
                return;
            }
            $stmts = $this->parse($code, true);
            if ($stmts === false) {
                return;
            }
            $traverser = new NodeTraverser();
            foreach ($passes as $pass) {
                $traverser->addVisitor($pass);
            }
            $traverser->traverse($stmts);
        } catch (\Throwable $e) {
        } catch (\Exception $e) {
        }
    }
    private static function getDebugFile()
    {
        $trace = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach (\array_reverse($trace) as $stackFrame) {
            if (!self::isDebugCall($stackFrame)) {
                continue;
            }
            if (\preg_match('/eval\(/', $stackFrame['file'])) {
                \preg_match_all('/([^\(]+)\((\d+)/', $stackFrame['file'], $matches);
                return $matches[1][0];
            }
            return $stackFrame['file'];
        }
    }
    private static function isDebugCall(array $stackFrame)
    {
        $class    = isset($stackFrame['class']) ? $stackFrame['class'] : null;
        $function = isset($stackFrame['function']) ? $stackFrame['function'] : null;
        return ($class === null && $function === 'Psy\debug') ||
            ($class === 'Psy\Shell' && $function === 'debug');
    }
    public function clean(array $codeLines, $requireSemicolons = false)
    {
        $stmts = $this->parse('<?php ' . \implode(PHP_EOL, $codeLines) . PHP_EOL, $requireSemicolons);
        if ($stmts === false) {
            return false;
        }
        $stmts = $this->traverser->traverse($stmts);
        $oldLocale = \setlocale(LC_NUMERIC, 0);
        \setlocale(LC_NUMERIC, 'C');
        $code = $this->printer->prettyPrint($stmts);
        \setlocale(LC_NUMERIC, $oldLocale);
        return $code;
    }
    public function setNamespace(array $namespace = null)
    {
        $this->namespace = $namespace;
    }
    public function getNamespace()
    {
        return $this->namespace;
    }
    protected function parse($code, $requireSemicolons = false)
    {
        try {
            return $this->parser->parse($code);
        } catch (\PhpParser\Error $e) {
            if ($this->parseErrorIsUnclosedString($e, $code)) {
                return false;
            }
            if ($this->parseErrorIsUnterminatedComment($e, $code)) {
                return false;
            }
            if ($this->parseErrorIsTrailingComma($e, $code)) {
                return false;
            }
            if (!$this->parseErrorIsEOF($e)) {
                throw ParseErrorException::fromParseError($e);
            }
            if ($requireSemicolons) {
                return false;
            }
            try {
                return $this->parser->parse($code . ';');
            } catch (\PhpParser\Error $e) {
                return false;
            }
        }
    }
    private function parseErrorIsEOF(\PhpParser\Error $e)
    {
        $msg = $e->getRawMessage();
        return ($msg === 'Unexpected token EOF') || (\strpos($msg, 'Syntax error, unexpected EOF') !== false);
    }
    private function parseErrorIsUnclosedString(\PhpParser\Error $e, $code)
    {
        if ($e->getRawMessage() !== 'Syntax error, unexpected T_ENCAPSED_AND_WHITESPACE') {
            return false;
        }
        try {
            $this->parser->parse($code . "';");
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
    private function parseErrorIsUnterminatedComment(\PhpParser\Error $e, $code)
    {
        return $e->getRawMessage() === 'Unterminated comment';
    }
    private function parseErrorIsTrailingComma(\PhpParser\Error $e, $code)
    {
        return ($e->getRawMessage() === 'A trailing comma is not allowed here') && (\substr(\rtrim($code), -1) === ',');
    }
}
