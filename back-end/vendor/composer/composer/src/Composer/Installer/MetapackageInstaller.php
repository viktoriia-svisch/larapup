<?php
namespace Composer\Installer;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Package\PackageInterface;
use Composer\Package\Version\VersionParser;
use Composer\IO\IOInterface;
class MetapackageInstaller implements InstallerInterface
{
    private $io;
    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }
    public function supports($packageType)
    {
        return $packageType === 'metapackage';
    }
    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        return $repo->hasPackage($package);
    }
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->io->writeError("  - Installing <info>" . $package->getName() . "</info> (<comment>" . $package->getFullPrettyVersion() . "</comment>)");
        $repo->addPackage(clone $package);
    }
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        if (!$repo->hasPackage($initial)) {
            throw new \InvalidArgumentException('Package is not installed: '.$initial);
        }
        $name = $target->getName();
        $from = $initial->getFullPrettyVersion();
        $to = $target->getFullPrettyVersion();
        $actionName = VersionParser::isUpgrade($initial->getVersion(), $target->getVersion()) ? 'Updating' : 'Downgrading';
        $this->io->writeError("  - " . $actionName . " <info>" . $name . "</info> (<comment>" . $from . "</comment> => <comment>" . $to . "</comment>)");
        $repo->removePackage($initial);
        $repo->addPackage(clone $target);
    }
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        if (!$repo->hasPackage($package)) {
            throw new \InvalidArgumentException('Package is not installed: '.$package);
        }
        $this->io->writeError("  - Removing <info>" . $package->getName() . "</info> (<comment>" . $package->getFullPrettyVersion() . "</comment>)");
        $repo->removePackage($package);
    }
    public function getInstallPath(PackageInterface $package)
    {
        return '';
    }
}
