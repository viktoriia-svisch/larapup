<?php
namespace Composer;
use Composer\Json\JsonFile;
use Composer\Spdx\SpdxLicenses;
use Composer\CaBundle\CaBundle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Seld\PharUtils\Timestamps;
class Compiler
{
    private $version;
    private $branchAliasVersion = '';
    private $versionDate;
    public function compile($pharFile = 'composer.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }
        $process = new Process('git log --pretty="%H" -n1 HEAD', __DIR__);
        if ($process->run() != 0) {
            throw new \RuntimeException('Can\'t run git log. You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        $this->version = trim($process->getOutput());
        $process = new Process('git log -n1 --pretty=%ci HEAD', __DIR__);
        if ($process->run() != 0) {
            throw new \RuntimeException('Can\'t run git log. You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        $this->versionDate = new \DateTime(trim($process->getOutput()));
        $this->versionDate->setTimezone(new \DateTimeZone('UTC'));
        $process = new Process('git describe --tags --exact-match HEAD');
        if ($process->run() == 0) {
            $this->version = trim($process->getOutput());
        } else {
            $localConfig = __DIR__.'/../../composer.json';
            $file = new JsonFile($localConfig);
            $localConfig = $file->read();
            if (isset($localConfig['extra']['branch-alias']['dev-master'])) {
                $this->branchAliasVersion = $localConfig['extra']['branch-alias']['dev-master'];
            }
        }
        $phar = new \Phar($pharFile, 0, 'composer.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);
        $phar->startBuffering();
        $finderSort = function ($a, $b) {
            return strcmp(strtr($a->getRealPath(), '\\', '/'), strtr($b->getRealPath(), '\\', '/'));
        };
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Compiler.php')
            ->notName('ClassLoader.php')
            ->in(__DIR__.'/..')
            ->sort($finderSort)
        ;
        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/Autoload/ClassLoader.php'), false);
        $finder = new Finder();
        $finder->files()
            ->name('*.json')
            ->in(__DIR__.'/../../res')
            ->in(SpdxLicenses::getResourcesDir())
            ->sort($finderSort)
        ;
        foreach ($finder as $file) {
            $this->addFile($phar, $file, false);
        }
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../vendor/symfony/console/Resources/bin/hiddeninput.exe'), false);
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('LICENSE')
            ->exclude('Tests')
            ->exclude('tests')
            ->exclude('docs')
            ->in(__DIR__.'/../../vendor/symfony/')
            ->in(__DIR__.'/../../vendor/seld/jsonlint/')
            ->in(__DIR__.'/../../vendor/justinrainbow/json-schema/')
            ->in(__DIR__.'/../../vendor/composer/spdx-licenses/')
            ->in(__DIR__.'/../../vendor/composer/semver/')
            ->in(__DIR__.'/../../vendor/composer/ca-bundle/')
            ->in(__DIR__.'/../../vendor/composer/xdebug-handler/')
            ->in(__DIR__.'/../../vendor/psr/')
            ->sort($finderSort)
        ;
        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/autoload.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/composer/autoload_namespaces.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/composer/autoload_psr4.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/composer/autoload_classmap.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/composer/autoload_files.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/composer/autoload_real.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/composer/autoload_static.php'));
        if (file_exists(__DIR__.'/../../vendor/composer/include_paths.php')) {
            $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/composer/include_paths.php'));
        }
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/composer/ClassLoader.php'));
        $this->addFile($phar, new \SplFileInfo(CaBundle::getBundledCaBundlePath()), false);
        $this->addComposerBin($phar);
        $phar->setStub($this->getStub());
        $phar->stopBuffering();
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../LICENSE'), false);
        unset($phar);
        $util = new Timestamps($pharFile);
        $util->updateTimestamps($this->versionDate);
        $util->save($pharFile, \Phar::SHA1);
    }
    private function getRelativeFilePath($file)
    {
        $realPath = $file->getRealPath();
        $pathPrefix = dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR;
        $pos = strpos($realPath, $pathPrefix);
        $relativePath = ($pos !== false) ? substr_replace($realPath, '', $pos, strlen($pathPrefix)) : $realPath;
        return strtr($relativePath, '\\', '/');
    }
    private function addFile($phar, $file, $strip = true)
    {
        $path = $this->getRelativeFilePath($file);
        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        } elseif ('LICENSE' === basename($file)) {
            $content = "\n".$content."\n";
        }
        if ($path === 'src/Composer/Composer.php') {
            $content = str_replace('@package_version@', $this->version, $content);
            $content = str_replace('@package_branch_alias_version@', $this->branchAliasVersion, $content);
            $content = str_replace('@release_date@', $this->versionDate->format('Y-m-d H:i:s'), $content);
        }
        $phar->addFromString($path, $content);
    }
    private function addComposerBin($phar)
    {
        $content = file_get_contents(__DIR__.'/../../bin/composer');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/composer', $content);
    }
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }
        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }
        return $output;
    }
    private function getStub()
    {
        $stub = <<<'EOF'
<?php
if (extension_loaded('apc') && filter_var(ini_get('apc.enable_cli'), FILTER_VALIDATE_BOOLEAN) && filter_var(ini_get('apc.cache_by_default'), FILTER_VALIDATE_BOOLEAN)) {
    if (version_compare(phpversion('apc'), '3.0.12', '>=')) {
        ini_set('apc.cache_by_default', 0);
    } else {
        fwrite(STDERR, 'Warning: APC <= 3.0.12 may cause fatal errors when running composer commands.'.PHP_EOL);
        fwrite(STDERR, 'Update APC, or set apc.enable_cli or apc.cache_by_default to 0 in your php.ini.'.PHP_EOL);
    }
}
Phar::mapPhar('composer.phar');
EOF;
        if (preg_match('{^[a-f0-9]+$}', $this->version)) {
            $warningTime = $this->versionDate->format('U') + 60 * 86400;
            $stub .= "define('COMPOSER_DEV_WARNING_TIME', $warningTime);\n";
        }
        return $stub . <<<'EOF'
require 'phar:
__HALT_COMPILER();
EOF;
    }
}
