<?php
namespace NunoMaduro\Collision\Contracts;
use Whoops\Handler\HandlerInterface;
use Symfony\Component\Console\Output\OutputInterface;
interface Handler extends HandlerInterface
{
    public function setOutput(OutputInterface $output): Handler;
    public function getWriter(): Writer;
}
