<?php
namespace NunoMaduro\Collision;
use Whoops\Handler\Handler as AbstractHandler;
use Symfony\Component\Console\Output\OutputInterface;
use NunoMaduro\Collision\Contracts\Writer as WriterContract;
use NunoMaduro\Collision\Contracts\Handler as HandlerContract;
class Handler extends AbstractHandler implements HandlerContract
{
    protected $writer;
    public function __construct(WriterContract $writer = null)
    {
        $this->writer = $writer ?: new Writer;
    }
    public function handle()
    {
        $this->writer->write($this->getInspector());
        return static::QUIT;
    }
    public function setOutput(OutputInterface $output): HandlerContract
    {
        $this->writer->setOutput($output);
        return $this;
    }
    public function getWriter(): WriterContract
    {
        return $this->writer;
    }
}
