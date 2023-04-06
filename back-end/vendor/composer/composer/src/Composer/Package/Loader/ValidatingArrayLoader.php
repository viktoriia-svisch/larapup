<?php
namespace Composer\Package\Loader;
use Composer\Package\BasePackage;
use Composer\Semver\Constraint\Constraint;
use Composer\Package\Version\VersionParser;
use Composer\Repository\PlatformRepository;
use Composer\Spdx\SpdxLicenses;
class ValidatingArrayLoader implements LoaderInterface
{
    const CHECK_ALL = 3;
    const CHECK_UNBOUND_CONSTRAINTS = 1;
    const CHECK_STRICT_CONSTRAINTS = 2;
    private $loader;
    private $versionParser;
    private $errors;
    private $warnings;
    private $config;
    private $strictName;
    private $flags;
    public function __construct(LoaderInterface $loader, $strictName = true, VersionParser $parser = null, $flags = 0)
    {
        $this->loader = $loader;
        $this->versionParser = $parser ?: new VersionParser();
        $this->strictName = $strictName;
        $this->flags = $flags;
    }
    public function load(array $config, $class = 'Composer\Package\CompletePackage')
    {
        $this->errors = array();
        $this->warnings = array();
        $this->config = $config;
        if ($this->strictName) {
            $this->validateRegex('name', '[A-Za-z0-9][A-Za-z0-9_.-]*/[A-Za-z0-9][A-Za-z0-9_.-]*', true);
        } else {
            $this->validateString('name', true);
        }
        if (!empty($this->config['version'])) {
            try {
                $this->versionParser->normalize($this->config['version']);
            } catch (\Exception $e) {
                $this->errors[] = 'version : invalid value ('.$this->config['version'].'): '.$e->getMessage();
                unset($this->config['version']);
            }
        }
        if (!empty($this->config['config']['platform'])) {
            foreach ((array) $this->config['config']['platform'] as $key => $platform) {
                try {
                    $this->versionParser->normalize($platform);
                } catch (\Exception $e) {
                    $this->errors[] = 'config.platform.' . $key . ' : invalid value ('.$platform.'): '.$e->getMessage();
                }
            }
        }
        $this->validateRegex('type', '[A-Za-z0-9-]+');
        $this->validateString('target-dir');
        $this->validateArray('extra');
        if (isset($this->config['bin'])) {
            if (is_string($this->config['bin'])) {
                $this->validateString('bin');
            } else {
                $this->validateFlatArray('bin');
            }
        }
        $this->validateArray('scripts'); 
        $this->validateString('description');
        $this->validateUrl('homepage');
        $this->validateFlatArray('keywords', '[\p{N}\p{L} ._-]+');
        $releaseDate = null;
        $this->validateString('time');
        if (!empty($this->config['time'])) {
            try {
                $releaseDate = new \DateTime($this->config['time'], new \DateTimeZone('UTC'));
            } catch (\Exception $e) {
                $this->errors[] = 'time : invalid value ('.$this->config['time'].'): '.$e->getMessage();
                unset($this->config['time']);
            }
        }
        if (isset($this->config['license']) && (!$releaseDate || $releaseDate->getTimestamp() >= strtotime('-8days'))) {
            if (is_array($this->config['license']) || is_string($this->config['license'])) {
                $licenses = (array) $this->config['license'];
                foreach ($licenses as $key => $license) {
                    if ('proprietary' === $license) {
                        unset($licenses[$key]);
                    }
                }
                $licenseValidator = new SpdxLicenses();
                if (count($licenses) === 1 && !$licenseValidator->validate($licenses) && $licenseValidator->validate(trim($licenses[0]))) {
                    $this->warnings[] = sprintf(
                        'License %s must not contain extra spaces, make sure to trim it.',
                        json_encode($this->config['license'])
                    );
                } elseif (array() !== $licenses && !$licenseValidator->validate($licenses)) {
                    $this->warnings[] = sprintf(
                        'License %s is not a valid SPDX license identifier, see https:
                        'If the software is closed-source, you may use "proprietary" as license.',
                        json_encode($this->config['license'])
                    );
                }
            }
        }
        if ($this->validateArray('authors') && !empty($this->config['authors'])) {
            foreach ($this->config['authors'] as $key => $author) {
                if (!is_array($author)) {
                    $this->errors[] = 'authors.'.$key.' : should be an array, '.gettype($author).' given';
                    unset($this->config['authors'][$key]);
                    continue;
                }
                foreach (array('homepage', 'email', 'name', 'role') as $authorData) {
                    if (isset($author[$authorData]) && !is_string($author[$authorData])) {
                        $this->errors[] = 'authors.'.$key.'.'.$authorData.' : invalid value, must be a string';
                        unset($this->config['authors'][$key][$authorData]);
                    }
                }
                if (isset($author['homepage']) && !$this->filterUrl($author['homepage'])) {
                    $this->warnings[] = 'authors.'.$key.'.homepage : invalid value ('.$author['homepage'].'), must be an http/https URL';
                    unset($this->config['authors'][$key]['homepage']);
                }
                if (isset($author['email']) && !filter_var($author['email'], FILTER_VALIDATE_EMAIL)) {
                    $this->warnings[] = 'authors.'.$key.'.email : invalid value ('.$author['email'].'), must be a valid email address';
                    unset($this->config['authors'][$key]['email']);
                }
                if (empty($this->config['authors'][$key])) {
                    unset($this->config['authors'][$key]);
                }
            }
            if (empty($this->config['authors'])) {
                unset($this->config['authors']);
            }
        }
        if ($this->validateArray('support') && !empty($this->config['support'])) {
            foreach (array('issues', 'forum', 'wiki', 'source', 'email', 'irc', 'docs', 'rss', 'chat') as $key) {
                if (isset($this->config['support'][$key]) && !is_string($this->config['support'][$key])) {
                    $this->errors[] = 'support.'.$key.' : invalid value, must be a string';
                    unset($this->config['support'][$key]);
                }
            }
            if (isset($this->config['support']['email']) && !filter_var($this->config['support']['email'], FILTER_VALIDATE_EMAIL)) {
                $this->warnings[] = 'support.email : invalid value ('.$this->config['support']['email'].'), must be a valid email address';
                unset($this->config['support']['email']);
            }
            if (isset($this->config['support']['irc']) && !$this->filterUrl($this->config['support']['irc'], array('irc'))) {
                $this->warnings[] = 'support.irc : invalid value ('.$this->config['support']['irc'].'), must be a irc:
                unset($this->config['support']['irc']);
            }
            foreach (array('issues', 'forum', 'wiki', 'source', 'docs', 'chat') as $key) {
                if (isset($this->config['support'][$key]) && !$this->filterUrl($this->config['support'][$key])) {
                    $this->warnings[] = 'support.'.$key.' : invalid value ('.$this->config['support'][$key].'), must be an http/https URL';
                    unset($this->config['support'][$key]);
                }
            }
            if (empty($this->config['support'])) {
                unset($this->config['support']);
            }
        }
        $unboundConstraint = new Constraint('=', $this->versionParser->normalize('dev-master'));
        $stableConstraint = new Constraint('=', '1.0.0');
        foreach (array_keys(BasePackage::$supportedLinkTypes) as $linkType) {
            if ($this->validateArray($linkType) && isset($this->config[$linkType])) {
                foreach ($this->config[$linkType] as $package => $constraint) {
                    if (!preg_match('{^[A-Za-z0-9_./-]+$}', $package)) {
                        $this->warnings[] = $linkType.'.'.$package.' : invalid key, package names must be strings containing only [A-Za-z0-9_./-]';
                    }
                    if (!is_string($constraint)) {
                        $this->errors[] = $linkType.'.'.$package.' : invalid value, must be a string containing a version constraint';
                        unset($this->config[$linkType][$package]);
                    } elseif ('self.version' !== $constraint) {
                        try {
                            $linkConstraint = $this->versionParser->parseConstraints($constraint);
                        } catch (\Exception $e) {
                            $this->errors[] = $linkType.'.'.$package.' : invalid version constraint ('.$e->getMessage().')';
                            unset($this->config[$linkType][$package]);
                            continue;
                        }
                        if (
                            ($this->flags & self::CHECK_UNBOUND_CONSTRAINTS)
                            && 'require' === $linkType
                            && $linkConstraint->matches($unboundConstraint)
                            && !preg_match(PlatformRepository::PLATFORM_PACKAGE_REGEX, $package)
                        ) {
                            $this->warnings[] = $linkType.'.'.$package.' : unbound version constraints ('.$constraint.') should be avoided';
                        } elseif (
                            ($this->flags & self::CHECK_STRICT_CONSTRAINTS)
                            && 'require' === $linkType
                            && substr($linkConstraint, 0, 1) === '='
                            && $stableConstraint->versionCompare($stableConstraint, $linkConstraint, '<=')
                        ) {
                            $this->warnings[] = $linkType.'.'.$package.' : exact version constraints ('.$constraint.') should be avoided if the package follows semantic versioning';
                        }
                    }
                }
            }
        }
        if ($this->validateArray('suggest') && !empty($this->config['suggest'])) {
            foreach ($this->config['suggest'] as $package => $description) {
                if (!is_string($description)) {
                    $this->errors[] = 'suggest.'.$package.' : invalid value, must be a string describing why the package is suggested';
                    unset($this->config['suggest'][$package]);
                }
            }
        }
        if ($this->validateString('minimum-stability') && !empty($this->config['minimum-stability'])) {
            if (!isset(BasePackage::$stabilities[$this->config['minimum-stability']])) {
                $this->errors[] = 'minimum-stability : invalid value ('.$this->config['minimum-stability'].'), must be one of '.implode(', ', array_keys(BasePackage::$stabilities));
                unset($this->config['minimum-stability']);
            }
        }
        if ($this->validateArray('autoload') && !empty($this->config['autoload'])) {
            $types = array('psr-0', 'psr-4', 'classmap', 'files', 'exclude-from-classmap');
            foreach ($this->config['autoload'] as $type => $typeConfig) {
                if (!in_array($type, $types)) {
                    $this->errors[] = 'autoload : invalid value ('.$type.'), must be one of '.implode(', ', $types);
                    unset($this->config['autoload'][$type]);
                }
                if ($type === 'psr-4') {
                    foreach ($typeConfig as $namespace => $dirs) {
                        if ($namespace !== '' && '\\' !== substr($namespace, -1)) {
                            $this->errors[] = 'autoload.psr-4 : invalid value ('.$namespace.'), namespaces must end with a namespace separator, should be '.$namespace.'\\\\';
                        }
                    }
                }
            }
        }
        if (!empty($this->config['autoload']['psr-4']) && !empty($this->config['target-dir'])) {
            $this->errors[] = 'target-dir : this can not be used together with the autoload.psr-4 setting, remove target-dir to upgrade to psr-4';
            unset($this->config['autoload']['psr-4']);
        }
        $this->validateFlatArray('include-path');
        $this->validateArray('transport-options');
        if (isset($this->config['extra']['branch-alias'])) {
            if (!is_array($this->config['extra']['branch-alias'])) {
                $this->errors[] = 'extra.branch-alias : must be an array of versions => aliases';
            } else {
                foreach ($this->config['extra']['branch-alias'] as $sourceBranch => $targetBranch) {
                    if ('-dev' !== substr($targetBranch, -4)) {
                        $this->warnings[] = 'extra.branch-alias.'.$sourceBranch.' : the target branch ('.$targetBranch.') must end in -dev';
                        unset($this->config['extra']['branch-alias'][$sourceBranch]);
                        continue;
                    }
                    $validatedTargetBranch = $this->versionParser->normalizeBranch(substr($targetBranch, 0, -4));
                    if ('-dev' !== substr($validatedTargetBranch, -4)) {
                        $this->warnings[] = 'extra.branch-alias.'.$sourceBranch.' : the target branch ('.$targetBranch.') must be a parseable number like 2.0-dev';
                        unset($this->config['extra']['branch-alias'][$sourceBranch]);
                        continue;
                    }
                    if (($sourcePrefix = $this->versionParser->parseNumericAliasPrefix($sourceBranch))
                        && ($targetPrefix = $this->versionParser->parseNumericAliasPrefix($targetBranch))
                        && (stripos($targetPrefix, $sourcePrefix) !== 0)
                    ) {
                        $this->warnings[] = 'extra.branch-alias.'.$sourceBranch.' : the target branch ('.$targetBranch.') is not a valid numeric alias for this version';
                        unset($this->config['extra']['branch-alias'][$sourceBranch]);
                    }
                }
            }
        }
        if ($this->errors) {
            throw new InvalidPackageException($this->errors, $this->warnings, $config);
        }
        $package = $this->loader->load($this->config, $class);
        $this->config = null;
        return $package;
    }
    public function getWarnings()
    {
        return $this->warnings;
    }
    public function getErrors()
    {
        return $this->errors;
    }
    public static function hasPackageNamingError($name, $isLink = false)
    {
        if (preg_match(PlatformRepository::PLATFORM_PACKAGE_REGEX, $name)) {
            return;
        }
        if (!preg_match('{^[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9]([_.-]?[a-z0-9]+)*$}iD', $name)) {
            return $name.' is invalid, it should have a vendor name, a forward slash, and a package name. The vendor and package name can be words separated by -, . or _. The complete name should match "[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9]([_.-]?[a-z0-9]+)*".';
        }
        $reservedNames = array('nul', 'con', 'prn', 'aux', 'com1', 'com2', 'com3', 'com4', 'com5', 'com6', 'com7', 'com8', 'com9', 'lpt1', 'lpt2', 'lpt3', 'lpt4', 'lpt5', 'lpt6', 'lpt7', 'lpt8', 'lpt9');
        $bits = explode('/', strtolower($name));
        if (in_array($bits[0], $reservedNames, true) || in_array($bits[1], $reservedNames, true)) {
            return $name.' is reserved, package and vendor names can not match any of: '.implode(', ', $reservedNames).'.';
        }
        if (preg_match('{\.json$}', $name)) {
            return $name.' is invalid, package names can not end in .json, consider renaming it or perhaps using a -json suffix instead.';
        }
        if (preg_match('{[A-Z]}', $name)) {
            if ($isLink) {
                return $name.' is invalid, it should not contain uppercase characters. Please use '.strtolower($name).' instead.';
            }
            $suggestName = preg_replace('{(?:([a-z])([A-Z])|([A-Z])([A-Z][a-z]))}', '\\1\\3-\\2\\4', $name);
            $suggestName = strtolower($suggestName);
            return $name.' is invalid, it should not contain uppercase characters. We suggest using '.$suggestName.' instead.';
        }
    }
    private function validateRegex($property, $regex, $mandatory = false)
    {
        if (!$this->validateString($property, $mandatory)) {
            return false;
        }
        if (!preg_match('{^'.$regex.'$}u', $this->config[$property])) {
            $message = $property.' : invalid value ('.$this->config[$property].'), must match '.$regex;
            if ($mandatory) {
                $this->errors[] = $message;
            } else {
                $this->warnings[] = $message;
            }
            unset($this->config[$property]);
            return false;
        }
        return true;
    }
    private function validateString($property, $mandatory = false)
    {
        if (isset($this->config[$property]) && !is_string($this->config[$property])) {
            $this->errors[] = $property.' : should be a string, '.gettype($this->config[$property]).' given';
            unset($this->config[$property]);
            return false;
        }
        if (!isset($this->config[$property]) || trim($this->config[$property]) === '') {
            if ($mandatory) {
                $this->errors[] = $property.' : must be present';
            }
            unset($this->config[$property]);
            return false;
        }
        return true;
    }
    private function validateArray($property, $mandatory = false)
    {
        if (isset($this->config[$property]) && !is_array($this->config[$property])) {
            $this->errors[] = $property.' : should be an array, '.gettype($this->config[$property]).' given';
            unset($this->config[$property]);
            return false;
        }
        if (!isset($this->config[$property]) || !count($this->config[$property])) {
            if ($mandatory) {
                $this->errors[] = $property.' : must be present and contain at least one element';
            }
            unset($this->config[$property]);
            return false;
        }
        return true;
    }
    private function validateFlatArray($property, $regex = null, $mandatory = false)
    {
        if (!$this->validateArray($property, $mandatory)) {
            return false;
        }
        $pass = true;
        foreach ($this->config[$property] as $key => $value) {
            if (!is_string($value) && !is_numeric($value)) {
                $this->errors[] = $property.'.'.$key.' : must be a string or int, '.gettype($value).' given';
                unset($this->config[$property][$key]);
                $pass = false;
                continue;
            }
            if ($regex && !preg_match('{^'.$regex.'$}u', $value)) {
                $this->warnings[] = $property.'.'.$key.' : invalid value ('.$value.'), must match '.$regex;
                unset($this->config[$property][$key]);
                $pass = false;
            }
        }
        return $pass;
    }
    private function validateUrl($property, $mandatory = false)
    {
        if (!$this->validateString($property, $mandatory)) {
            return false;
        }
        if (!$this->filterUrl($this->config[$property])) {
            $this->warnings[] = $property.' : invalid value ('.$this->config[$property].'), must be an http/https URL';
            unset($this->config[$property]);
            return false;
        }
        return true;
    }
    private function filterUrl($value, array $schemes = array('http', 'https'))
    {
        if ($value === '') {
            return true;
        }
        $bits = parse_url($value);
        if (empty($bits['scheme']) || empty($bits['host'])) {
            return false;
        }
        if (!in_array($bits['scheme'], $schemes, true)) {
            return false;
        }
        return true;
    }
}
