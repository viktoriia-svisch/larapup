<?php
namespace NunoMaduro\Collision;
use Whoops\Exception\Frame;
use Whoops\Exception\Inspector;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use NunoMaduro\Collision\Contracts\Writer as WriterContract;
use NunoMaduro\Collision\Contracts\Highlighter as HighlighterContract;
use NunoMaduro\Collision\Contracts\ArgumentFormatter as ArgumentFormatterContract;
class Writer implements WriterContract
{
    const VERBOSITY_NORMAL_FRAMES = 1;
    protected $output;
    protected $argumentFormatter;
    protected $highlighter;
    protected $ignore = [];
    protected $showTrace = true;
    protected $showEditor = true;
    public function __construct(
        OutputInterface $output = null,
        ArgumentFormatterContract $argumentFormatter = null,
        HighlighterContract $highlighter = null
    ) {
        $this->output = $output ?: new ConsoleOutput;
        $this->argumentFormatter = $argumentFormatter ?: new ArgumentFormatter;
        $this->highlighter = $highlighter ?: new Highlighter;
    }
    public function write(Inspector $inspector): void
    {
        $this->renderTitle($inspector);
        $frames = $this->getFrames($inspector);
        $editorFrame = array_shift($frames);
        if ($this->showEditor && $editorFrame !== null) {
            $this->renderEditor($editorFrame);
        }
        if ($this->showTrace && ! empty($frames)) {
            $this->renderTrace($frames);
        } else {
            $this->output->writeln('');
        }
    }
    public function ignoreFilesIn(array $ignore): WriterContract
    {
        $this->ignore = $ignore;
        return $this;
    }
    public function showTrace(bool $show): WriterContract
    {
        $this->showTrace = $show;
        return $this;
    }
    public function showEditor(bool $show): WriterContract
    {
        $this->showEditor = $show;
        return $this;
    }
    public function setOutput(OutputInterface $output): WriterContract
    {
        $this->output = $output;
        return $this;
    }
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
    protected function getFrames(Inspector $inspector): array
    {
        return $inspector->getFrames()
            ->filter(
                function ($frame) {
                    foreach ($this->ignore as $ignore) {
                        if (preg_match($ignore, $frame->getFile())) {
                            return false;
                        }
                    }
                    return true;
                }
            )
            ->getArray();
    }
    protected function renderTitle(Inspector $inspector): WriterContract
    {
        $exception = $inspector->getException();
        $message = $exception->getMessage();
        $class = $inspector->getExceptionName();
        $this->render("<bg=red;options=bold> $class </> : <comment>$message</>");
        return $this;
    }
    protected function renderEditor(Frame $frame): WriterContract
    {
        $this->render('at <fg=green>'.$frame->getFile().'</>'.':<fg=green>'.$frame->getLine().'</>');
        $content = $this->highlighter->highlight((string) $frame->getFileContents(), (int) $frame->getLine());
        $this->output->writeln($content);
        return $this;
    }
    protected function renderTrace(array $frames): WriterContract
    {
        $this->render('<comment>Exception trace:</comment>');
        foreach ($frames as $i => $frame) {
            if ($i > static::VERBOSITY_NORMAL_FRAMES && $this->output->getVerbosity(
                ) < OutputInterface::VERBOSITY_VERBOSE) {
                $this->render('<info>Please use the argument <fg=red>-v</> to see more details.</info>');
                break;
            }
            $file = $frame->getFile();
            $line = $frame->getLine();
            $class = empty($frame->getClass()) ? '' : $frame->getClass().'::';
            $function = $frame->getFunction();
            $args = $this->argumentFormatter->format($frame->getArgs());
            $pos = str_pad((int) $i + 1, 4, ' ');
            $this->render("<comment><fg=cyan>$pos</>$class$function($args)</comment>");
            $this->render("    <fg=green>$file</>:<fg=green>$line</>", false);
        }
        return $this;
    }
    protected function render(string $message, bool $break = true): WriterContract
    {
        if ($break) {
            $this->output->writeln('');
        }
        $this->output->writeln("  $message");
        return $this;
    }
}
