<?php
namespace Barryvdh\LaravelIdeHelper\Console;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
class EloquentCommand extends Command
{
    protected $name = 'ide-helper:eloquent';
    protected $files;
    protected $description = 'Add \Eloquent helper to \Eloquent\Model';
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }
    public function handle()
    {
        Eloquent::writeEloquentModelHelper($this, $this->files);
    }
}
