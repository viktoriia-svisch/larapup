<?php declare(strict_types=1);
namespace SebastianBergmann\Environment;
final class Runtime
{
    private static $binary;
    public function canCollectCodeCoverage(): bool
    {
        return $this->hasXdebug() || $this->hasPCOV() || $this->hasPHPDBGCodeCoverage();
    }
    public function discardsComments(): bool
    {
        if (!\extension_loaded('Zend OPcache')) {
            return false;
        }
        if (\ini_get('opcache.save_comments') !== '0') {
            return false;
        }
        if (\PHP_SAPI === 'cli' && \ini_get('opcache.enable_cli') === '1') {
            return true;
        }
        if (\PHP_SAPI !== 'cli' && \ini_get('opcache.enable') === '1') {
            return true;
        }
        return false;
    }
    public function getBinary(): string
    {
        if (self::$binary === null && $this->isHHVM()) {
            if ((self::$binary = \getenv('PHP_BINARY')) === false) {
                self::$binary = \PHP_BINARY;
            }
            self::$binary = \escapeshellarg(self::$binary) . ' --php' .
                ' -d hhvm.php7.all=1';
        }
        if (self::$binary === null && \PHP_BINARY !== '') {
            self::$binary = \escapeshellarg(\PHP_BINARY);
        }
        if (self::$binary === null) {
            $possibleBinaryLocations = [
                \PHP_BINDIR . '/php',
                \PHP_BINDIR . '/php-cli.exe',
                \PHP_BINDIR . '/php.exe',
            ];
            foreach ($possibleBinaryLocations as $binary) {
                if (\is_readable($binary)) {
                    self::$binary = \escapeshellarg($binary);
                    break;
                }
            }
        }
        if (self::$binary === null) {
            self::$binary = 'php';
        }
        return self::$binary;
    }
    public function getNameWithVersion(): string
    {
        return $this->getName() . ' ' . $this->getVersion();
    }
    public function getNameWithVersionAndCodeCoverageDriver(): string
    {
        if (!$this->canCollectCodeCoverage() || $this->hasPHPDBGCodeCoverage()) {
            return $this->getNameWithVersion();
        }
        if ($this->hasXdebug()) {
            return \sprintf(
                '%s with Xdebug %s',
                $this->getNameWithVersion(),
                \phpversion('xdebug')
            );
        }
        if ($this->hasPCOV()) {
            return \sprintf(
                '%s with PCOV %s',
                $this->getNameWithVersion(),
                \phpversion('pcov')
            );
        }
    }
    public function getName(): string
    {
        if ($this->isHHVM()) {
            return 'HHVM';
        }
        if ($this->isPHPDBG()) {
            return 'PHPDBG';
        }
        return 'PHP';
    }
    public function getVendorUrl(): string
    {
        if ($this->isHHVM()) {
            return 'http:
        }
        return 'https:
    }
    public function getVersion(): string
    {
        if ($this->isHHVM()) {
            return HHVM_VERSION;
        }
        return \PHP_VERSION;
    }
    public function hasXdebug(): bool
    {
        return ($this->isPHP() || $this->isHHVM()) && \extension_loaded('xdebug');
    }
    public function isHHVM(): bool
    {
        return \defined('HHVM_VERSION');
    }
    public function isPHP(): bool
    {
        return !$this->isHHVM() && !$this->isPHPDBG();
    }
    public function isPHPDBG(): bool
    {
        return \PHP_SAPI === 'phpdbg' && !$this->isHHVM();
    }
    public function hasPHPDBGCodeCoverage(): bool
    {
        return $this->isPHPDBG();
    }
    public function hasPCOV(): bool
    {
        return $this->isPHP() && \extension_loaded('pcov') && \ini_get('pcov.enabled');
    }
}
