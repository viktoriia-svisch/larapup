<?php
namespace PharIo\Manifest;
use PharIo\Version\Version;
use PharIo\Version\Exception as VersionException;
use PharIo\Version\VersionConstraintParser;
class ManifestDocumentMapper {
    public function map(ManifestDocument $document) {
        try {
            $contains          = $document->getContainsElement();
            $type              = $this->mapType($contains);
            $copyright         = $this->mapCopyright($document->getCopyrightElement());
            $requirements      = $this->mapRequirements($document->getRequiresElement());
            $bundledComponents = $this->mapBundledComponents($document);
            return new Manifest(
                new ApplicationName($contains->getName()),
                new Version($contains->getVersion()),
                $type,
                $copyright,
                $requirements,
                $bundledComponents
            );
        } catch (VersionException $e) {
            throw new ManifestDocumentMapperException($e->getMessage(), $e->getCode(), $e);
        } catch (Exception $e) {
            throw new ManifestDocumentMapperException($e->getMessage(), $e->getCode(), $e);
        }
    }
    private function mapType(ContainsElement $contains) {
        switch ($contains->getType()) {
            case 'application':
                return Type::application();
            case 'library':
                return Type::library();
            case 'extension':
                return $this->mapExtension($contains->getExtensionElement());
        }
        throw new ManifestDocumentMapperException(
            sprintf('Unsupported type %s', $contains->getType())
        );
    }
    private function mapCopyright(CopyrightElement $copyright) {
        $authors = new AuthorCollection();
        foreach($copyright->getAuthorElements() as $authorElement) {
            $authors->add(
                new Author(
                    $authorElement->getName(),
                    new Email($authorElement->getEmail())
                )
            );
        }
        $licenseElement = $copyright->getLicenseElement();
        $license        = new License(
            $licenseElement->getType(),
            new Url($licenseElement->getUrl())
        );
        return new CopyrightInformation(
            $authors,
            $license
        );
    }
    private function mapRequirements(RequiresElement $requires) {
        $collection = new RequirementCollection();
        $phpElement = $requires->getPHPElement();
        $parser     = new VersionConstraintParser;
        try {
            $versionConstraint = $parser->parse($phpElement->getVersion());
        } catch (VersionException $e) {
            throw new ManifestDocumentMapperException(
                sprintf('Unsupported version constraint - %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
        $collection->add(
            new PhpVersionRequirement(
                $versionConstraint
            )
        );
        if (!$phpElement->hasExtElements()) {
            return $collection;
        }
        foreach($phpElement->getExtElements() as $extElement) {
            $collection->add(
                new PhpExtensionRequirement($extElement->getName())
            );
        }
        return $collection;
    }
    private function mapBundledComponents(ManifestDocument $document) {
        $collection = new BundledComponentCollection();
        if (!$document->hasBundlesElement()) {
            return $collection;
        }
        foreach($document->getBundlesElement()->getComponentElements() as $componentElement) {
            $collection->add(
                new BundledComponent(
                    $componentElement->getName(),
                    new Version(
                        $componentElement->getVersion()
                    )
                )
            );
        }
        return $collection;
    }
    private function mapExtension(ExtensionElement $extension) {
        try {
            $parser            = new VersionConstraintParser;
            $versionConstraint = $parser->parse($extension->getCompatible());
            return Type::extension(
                new ApplicationName($extension->getFor()),
                $versionConstraint
            );
        } catch (VersionException $e) {
            throw new ManifestDocumentMapperException(
                sprintf('Unsupported version constraint - %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
