<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
class ViewCacheCommand extends Command
{
    protected $signature = 'view:cache';
    protected $description = "Compile all of the application's Blade templates";
    public function handle()
    {
        $this->paths()->each(function ($path) {
            $this->compileViews($this->bladeFilesIn([$path]));
        });
        $this->info('Blade templates cached successfully!');
    }
    protected function compileViews(Collection $views)
    {
        $compiler = $this->laravel['view']->getEngineResolver()->resolve('blade')->getCompiler();
        $views->map(function (SplFileInfo $file) use ($compiler) {
            $compiler->compile($file->getRealPath());
        });
    }
    protected function bladeFilesIn(array $paths)
    {
        return collect(
            Finder::create()
                ->in($paths)
                ->exclude('vendor')
                ->name('*.blade.php')
                ->files()
        );
    }
    protected function paths()
    {
        $finder = $this->laravel['view']->getFinder();
        return collect($finder->getPaths())->merge(
            collect($finder->getHints())->flatten()
        );
    }
}
