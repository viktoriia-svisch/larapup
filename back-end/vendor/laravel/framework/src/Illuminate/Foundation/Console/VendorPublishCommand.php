<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use League\Flysystem\MountManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
class VendorPublishCommand extends Command
{
    protected $files;
    protected $provider = null;
    protected $tags = [];
    protected $signature = 'vendor:publish {--force : Overwrite any existing files}
                    {--all : Publish assets for all service providers without prompt}
                    {--provider= : The service provider that has assets you want to publish}
                    {--tag=* : One or many tags that have assets you want to publish}';
    protected $description = 'Publish any publishable assets from vendor packages';
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }
    public function handle()
    {
        $this->determineWhatShouldBePublished();
        foreach ($this->tags ?: [null] as $tag) {
            $this->publishTag($tag);
        }
        $this->info('Publishing complete.');
    }
    protected function determineWhatShouldBePublished()
    {
        if ($this->option('all')) {
            return;
        }
        [$this->provider, $this->tags] = [
            $this->option('provider'), (array) $this->option('tag'),
        ];
        if (! $this->provider && ! $this->tags) {
            $this->promptForProviderOrTag();
        }
    }
    protected function promptForProviderOrTag()
    {
        $choice = $this->choice(
            "Which provider or tag's files would you like to publish?",
            $choices = $this->publishableChoices()
        );
        if ($choice == $choices[0] || is_null($choice)) {
            return;
        }
        $this->parseChoice($choice);
    }
    protected function publishableChoices()
    {
        return array_merge(
            ['<comment>Publish files from all providers and tags listed below</comment>'],
            preg_filter('/^/', '<comment>Provider: </comment>', Arr::sort(ServiceProvider::publishableProviders())),
            preg_filter('/^/', '<comment>Tag: </comment>', Arr::sort(ServiceProvider::publishableGroups()))
        );
    }
    protected function parseChoice($choice)
    {
        [$type, $value] = explode(': ', strip_tags($choice));
        if ($type === 'Provider') {
            $this->provider = $value;
        } elseif ($type === 'Tag') {
            $this->tags = [$value];
        }
    }
    protected function publishTag($tag)
    {
        foreach ($this->pathsToPublish($tag) as $from => $to) {
            $this->publishItem($from, $to);
        }
    }
    protected function pathsToPublish($tag)
    {
        return ServiceProvider::pathsToPublish(
            $this->provider, $tag
        );
    }
    protected function publishItem($from, $to)
    {
        if ($this->files->isFile($from)) {
            return $this->publishFile($from, $to);
        } elseif ($this->files->isDirectory($from)) {
            return $this->publishDirectory($from, $to);
        }
        $this->error("Can't locate path: <{$from}>");
    }
    protected function publishFile($from, $to)
    {
        if (! $this->files->exists($to) || $this->option('force')) {
            $this->createParentDirectory(dirname($to));
            $this->files->copy($from, $to);
            $this->status($from, $to, 'File');
        }
    }
    protected function publishDirectory($from, $to)
    {
        $this->moveManagedFiles(new MountManager([
            'from' => new Flysystem(new LocalAdapter($from)),
            'to' => new Flysystem(new LocalAdapter($to)),
        ]));
        $this->status($from, $to, 'Directory');
    }
    protected function moveManagedFiles($manager)
    {
        foreach ($manager->listContents('from:
            if ($file['type'] === 'file' && (! $manager->has('to:
                $manager->put('to:
            }
        }
    }
    protected function createParentDirectory($directory)
    {
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }
    protected function status($from, $to, $type)
    {
        $from = str_replace(base_path(), '', realpath($from));
        $to = str_replace(base_path(), '', realpath($to));
        $this->line('<info>Copied '.$type.'</info> <comment>['.$from.']</comment> <info>To</info> <comment>['.$to.']</comment>');
    }
}
