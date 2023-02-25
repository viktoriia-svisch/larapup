<?php
namespace Psy\Command;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard as Printer;
use Psy\Command\TimeitCommand\TimeitVisitor;
use Psy\Input\CodeArgument;
use Psy\ParserFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class TimeitCommand extends Command
{
    const RESULT_MSG     = '<info>Command took %.6f seconds to complete.</info>';
    const AVG_RESULT_MSG = '<info>Command took %.6f seconds on average (%.6f median; %.6f total) to complete.</info>';
    private static $start = null;
    private static $times = [];
    private $parser;
    private $traverser;
    private $printer;
    public function __construct($name = null)
    {
        $parserFactory = new ParserFactory();
        $this->parser = $parserFactory->createParser();
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new TimeitVisitor());
        $this->printer = new Printer();
        parent::__construct($name);
    }
    protected function configure()
    {
        $this
            ->setName('timeit')
            ->setDefinition([
                new InputOption('num', 'n', InputOption::VALUE_REQUIRED, 'Number of iterations.'),
                new CodeArgument('code', CodeArgument::REQUIRED, 'Code to execute.'),
            ])
            ->setDescription('Profiles with a timer.')
            ->setHelp(
                <<<'HELP'
Time profiling for functions and commands.
e.g.
<return>>>> timeit sleep(1)</return>
<return>>>> timeit -n1000 $closure()</return>
HELP
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        $num = $input->getOption('num') ?: 1;
        $shell = $this->getApplication();
        $instrumentedCode = $this->instrumentCode($code);
        self::$times = [];
        for ($i = 0; $i < $num; $i++) {
            $_ = $shell->execute($instrumentedCode);
            $this->ensureEndMarked();
        }
        $shell->writeReturnValue($_);
        $times = self::$times;
        self::$times = [];
        if ($num === 1) {
            $output->writeln(\sprintf(self::RESULT_MSG, $times[0]));
        } else {
            $total = \array_sum($times);
            \rsort($times);
            $median = $times[\round($num / 2)];
            $output->writeln(\sprintf(self::AVG_RESULT_MSG, $total / $num, $median, $total));
        }
    }
    public static function markStart()
    {
        self::$start = \microtime(true);
    }
    public static function markEnd($ret = null)
    {
        self::$times[] = \microtime(true) - self::$start;
        self::$start = null;
        return $ret;
    }
    private function ensureEndMarked()
    {
        if (self::$start !== null) {
            self::markEnd();
        }
    }
    private function instrumentCode($code)
    {
        return $this->printer->prettyPrint($this->traverser->traverse($this->parse($code)));
    }
    private function parse($code)
    {
        $code = '<?php ' . $code;
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
