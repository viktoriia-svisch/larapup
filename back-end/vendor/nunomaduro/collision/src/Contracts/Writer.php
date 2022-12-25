<?php
namespace NunoMaduro\Collision\Contracts;
use Whoops\Exception\Inspector;
use Symfony\Component\Console\Output\OutputInterface;
interface Writer
{
    public function ignoreFilesIn(array $ignore): Writer;
    public function showTrace(bool $show): Writer;
    public function showEditor(bool $show): Writer;
    public function write(Inspector $inspector): void;
    public function setOutput(OutputInterface $output): Writer;
    public function getOutput(): OutputInterface;
}
