<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
use Illuminate\Foundation\PackageManifest;
class PackageDiscoverCommand extends Command
{
    protected $signature = 'package:discover';
    protected $description = 'Rebuild the cached package manifest';
    public function handle(PackageManifest $manifest)
    {
        $manifest->build();
        foreach (array_keys($manifest->manifest) as $package) {
            $this->line("Discovered Package: <info>{$package}</info>");
        }
        $this->info('Package manifest generated successfully.');
    }
}
