<?php
namespace Psy\Command;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedName;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\PrettyPrinter\Standard as Printer;
use Psy\Context;
use Psy\ContextAware;
use Psy\Input\CodeArgument;
use Psy\ParserFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class ThrowUpCommand extends Command implements ContextAware
{
    const THROW_CLASS = 'Psy\Exception\ThrowUpException';
    private $parser;
    private $printer;
    protected $context;
    public function __construct($name = null)
    {
        $parserFactory = new ParserFactory();
        $this->parser  = $parserFactory->createParser();
        $this->printer = new Printer();
        parent::__construct($name);
    }
    public function setContext(Context $context)
    {
        $this->context = $context;
    }
    protected function configure()
    {
        $this
            ->setName('throw-up')
            ->setDefinition([
                new CodeArgument('exception', CodeArgument::OPTIONAL, 'Exception or Error to throw.'),
            ])
            ->setDescription('Throw an exception or error out of the Psy Shell.')
            ->setHelp(
                <<<'HELP'
Throws an exception or error out of the current the Psy Shell instance.
By default it throws the most recent exception.
e.g.
<return>>>> throw-up</return>
<return>>>> throw-up $e</return>
<return>>>> throw-up new Exception('WHEEEEEE!')</return>
<return>>>> throw-up "bye!"</return>
HELP
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $args = $this->prepareArgs($input->getArgument('exception'));
        $throwStmt = new Throw_(new StaticCall(new FullyQualifiedName(self::THROW_CLASS), 'fromThrowable', $args));
        $throwCode = $this->printer->prettyPrint([$throwStmt]);
        $shell = $this->getApplication();
        $shell->addCode($throwCode, !$shell->hasCode());
    }
    private function prepareArgs($code = null)
    {
        if (!$code) {
            return [new Arg(new Variable('_e'))];
        }
        if (\strpos('<?', $code) === false) {
            $code = '<?php ' . $code;
        }
        $nodes = $this->parse($code);
        if (\count($nodes) !== 1) {
            throw new \InvalidArgumentException('No idea how to throw this');
        }
        $node = $nodes[0];
        $expr = isset($node->expr) ? $node->expr : $node;
        $args = [new Arg($expr, false, false, $node->getAttributes())];
        if ($expr instanceof String_) {
            return [new New_(new FullyQualifiedName('Exception'), $args)];
        }
        return $args;
    }
    private function parse($code)
    {
        try {
            return $this->parser->parse($code);
        } catch (\PhpParser\Error $e) {
            if (\strpos($e->getMessage(), 'unexpected EOF') === false) {
                throw $e;
            }
            return $this->parser->parse($code . ';');
        }
    }
}
