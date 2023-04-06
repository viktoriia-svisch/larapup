<?php
namespace Symfony\Component\Filesystem;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Exception\IOException;
class Filesystem
{
    private static $lastError;
    public function copy($originFile, $targetFile, $overwriteNewerFiles = false)
    {
        $originIsLocal = stream_is_local($originFile) || 0 === stripos($originFile, 'file:
        if ($originIsLocal && !is_file($originFile)) {
            throw new FileNotFoundException(sprintf('Failed to copy "%s" because file does not exist.', $originFile), 0, null, $originFile);
        }
        $this->mkdir(\dirname($targetFile));
        $doCopy = true;
        if (!$overwriteNewerFiles && null === parse_url($originFile, PHP_URL_HOST) && is_file($targetFile)) {
            $doCopy = filemtime($originFile) > filemtime($targetFile);
        }
        if ($doCopy) {
            if (false === $source = @fopen($originFile, 'r')) {
                throw new IOException(sprintf('Failed to copy "%s" to "%s" because source file could not be opened for reading.', $originFile, $targetFile), 0, null, $originFile);
            }
            if (false === $target = @fopen($targetFile, 'w', null, stream_context_create(['ftp' => ['overwrite' => true]]))) {
                throw new IOException(sprintf('Failed to copy "%s" to "%s" because target file could not be opened for writing.', $originFile, $targetFile), 0, null, $originFile);
            }
            $bytesCopied = stream_copy_to_stream($source, $target);
            fclose($source);
            fclose($target);
            unset($source, $target);
            if (!is_file($targetFile)) {
                throw new IOException(sprintf('Failed to copy "%s" to "%s".', $originFile, $targetFile), 0, null, $originFile);
            }
            if ($originIsLocal) {
                @chmod($targetFile, fileperms($targetFile) | (fileperms($originFile) & 0111));
                if ($bytesCopied !== $bytesOrigin = filesize($originFile)) {
                    throw new IOException(sprintf('Failed to copy the whole content of "%s" to "%s" (%g of %g bytes copied).', $originFile, $targetFile, $bytesCopied, $bytesOrigin), 0, null, $originFile);
                }
            }
        }
    }
    public function mkdir($dirs, $mode = 0777)
    {
        foreach ($this->toIterable($dirs) as $dir) {
            if (is_dir($dir)) {
                continue;
            }
            if (!self::box('mkdir', $dir, $mode, true)) {
                if (!is_dir($dir)) {
                    if (self::$lastError) {
                        throw new IOException(sprintf('Failed to create "%s": %s.', $dir, self::$lastError), 0, null, $dir);
                    }
                    throw new IOException(sprintf('Failed to create "%s"', $dir), 0, null, $dir);
                }
            }
        }
    }
    public function exists($files)
    {
        $maxPathLength = PHP_MAXPATHLEN - 2;
        foreach ($this->toIterable($files) as $file) {
            if (\strlen($file) > $maxPathLength) {
                throw new IOException(sprintf('Could not check if file exist because path length exceeds %d characters.', $maxPathLength), 0, null, $file);
            }
            if (!file_exists($file)) {
                return false;
            }
        }
        return true;
    }
    public function touch($files, $time = null, $atime = null)
    {
        foreach ($this->toIterable($files) as $file) {
            $touch = $time ? @touch($file, $time, $atime) : @touch($file);
            if (true !== $touch) {
                throw new IOException(sprintf('Failed to touch "%s".', $file), 0, null, $file);
            }
        }
    }
    public function remove($files)
    {
        if ($files instanceof \Traversable) {
            $files = iterator_to_array($files, false);
        } elseif (!\is_array($files)) {
            $files = [$files];
        }
        $files = array_reverse($files);
        foreach ($files as $file) {
            if (is_link($file)) {
                if (!(self::box('unlink', $file) || '\\' !== \DIRECTORY_SEPARATOR || self::box('rmdir', $file)) && file_exists($file)) {
                    throw new IOException(sprintf('Failed to remove symlink "%s": %s.', $file, self::$lastError));
                }
            } elseif (is_dir($file)) {
                $this->remove(new \FilesystemIterator($file, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS));
                if (!self::box('rmdir', $file) && file_exists($file)) {
                    throw new IOException(sprintf('Failed to remove directory "%s": %s.', $file, self::$lastError));
                }
            } elseif (!self::box('unlink', $file) && file_exists($file)) {
                throw new IOException(sprintf('Failed to remove file "%s": %s.', $file, self::$lastError));
            }
        }
    }
    public function chmod($files, $mode, $umask = 0000, $recursive = false)
    {
        foreach ($this->toIterable($files) as $file) {
            if (true !== @chmod($file, $mode & ~$umask)) {
                throw new IOException(sprintf('Failed to chmod file "%s".', $file), 0, null, $file);
            }
            if ($recursive && is_dir($file) && !is_link($file)) {
                $this->chmod(new \FilesystemIterator($file), $mode, $umask, true);
            }
        }
    }
    public function chown($files, $user, $recursive = false)
    {
        foreach ($this->toIterable($files) as $file) {
            if ($recursive && is_dir($file) && !is_link($file)) {
                $this->chown(new \FilesystemIterator($file), $user, true);
            }
            if (is_link($file) && \function_exists('lchown')) {
                if (true !== @lchown($file, $user)) {
                    throw new IOException(sprintf('Failed to chown file "%s".', $file), 0, null, $file);
                }
            } else {
                if (true !== @chown($file, $user)) {
                    throw new IOException(sprintf('Failed to chown file "%s".', $file), 0, null, $file);
                }
            }
        }
    }
    public function chgrp($files, $group, $recursive = false)
    {
        foreach ($this->toIterable($files) as $file) {
            if ($recursive && is_dir($file) && !is_link($file)) {
                $this->chgrp(new \FilesystemIterator($file), $group, true);
            }
            if (is_link($file) && \function_exists('lchgrp')) {
                if (true !== @lchgrp($file, $group)) {
                    throw new IOException(sprintf('Failed to chgrp file "%s".', $file), 0, null, $file);
                }
            } else {
                if (true !== @chgrp($file, $group)) {
                    throw new IOException(sprintf('Failed to chgrp file "%s".', $file), 0, null, $file);
                }
            }
        }
    }
    public function rename($origin, $target, $overwrite = false)
    {
        if (!$overwrite && $this->isReadable($target)) {
            throw new IOException(sprintf('Cannot rename because the target "%s" already exists.', $target), 0, null, $target);
        }
        if (true !== @rename($origin, $target)) {
            if (is_dir($origin)) {
                $this->mirror($origin, $target, null, ['override' => $overwrite, 'delete' => $overwrite]);
                $this->remove($origin);
                return;
            }
            throw new IOException(sprintf('Cannot rename "%s" to "%s".', $origin, $target), 0, null, $target);
        }
    }
    private function isReadable($filename)
    {
        $maxPathLength = PHP_MAXPATHLEN - 2;
        if (\strlen($filename) > $maxPathLength) {
            throw new IOException(sprintf('Could not check if file is readable because path length exceeds %d characters.', $maxPathLength), 0, null, $filename);
        }
        return is_readable($filename);
    }
    public function symlink($originDir, $targetDir, $copyOnWindows = false)
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $originDir = strtr($originDir, '/', '\\');
            $targetDir = strtr($targetDir, '/', '\\');
            if ($copyOnWindows) {
                $this->mirror($originDir, $targetDir);
                return;
            }
        }
        $this->mkdir(\dirname($targetDir));
        if (is_link($targetDir)) {
            if (readlink($targetDir) === $originDir) {
                return;
            }
            $this->remove($targetDir);
        }
        if (!self::box('symlink', $originDir, $targetDir)) {
            $this->linkException($originDir, $targetDir, 'symbolic');
        }
    }
    public function hardlink($originFile, $targetFiles)
    {
        if (!$this->exists($originFile)) {
            throw new FileNotFoundException(null, 0, null, $originFile);
        }
        if (!is_file($originFile)) {
            throw new FileNotFoundException(sprintf('Origin file "%s" is not a file', $originFile));
        }
        foreach ($this->toIterable($targetFiles) as $targetFile) {
            if (is_file($targetFile)) {
                if (fileinode($originFile) === fileinode($targetFile)) {
                    continue;
                }
                $this->remove($targetFile);
            }
            if (!self::box('link', $originFile, $targetFile)) {
                $this->linkException($originFile, $targetFile, 'hard');
            }
        }
    }
    private function linkException($origin, $target, $linkType)
    {
        if (self::$lastError) {
            if ('\\' === \DIRECTORY_SEPARATOR && false !== strpos(self::$lastError, 'error code(1314)')) {
                throw new IOException(sprintf('Unable to create %s link due to error code 1314: \'A required privilege is not held by the client\'. Do you have the required Administrator-rights?', $linkType), 0, null, $target);
            }
        }
        throw new IOException(sprintf('Failed to create %s link from "%s" to "%s".', $linkType, $origin, $target), 0, null, $target);
    }
    public function readlink($path, $canonicalize = false)
    {
        if (!$canonicalize && !is_link($path)) {
            return;
        }
        if ($canonicalize) {
            if (!$this->exists($path)) {
                return;
            }
            if ('\\' === \DIRECTORY_SEPARATOR) {
                $path = readlink($path);
            }
            return realpath($path);
        }
        if ('\\' === \DIRECTORY_SEPARATOR) {
            return realpath($path);
        }
        return readlink($path);
    }
    public function makePathRelative($endPath, $startPath)
    {
        if (!$this->isAbsolutePath($startPath)) {
            throw new InvalidArgumentException(sprintf('The start path "%s" is not absolute.', $startPath));
        }
        if (!$this->isAbsolutePath($endPath)) {
            throw new InvalidArgumentException(sprintf('The end path "%s" is not absolute.', $endPath));
        }
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $endPath = str_replace('\\', '/', $endPath);
            $startPath = str_replace('\\', '/', $startPath);
        }
        $stripDriveLetter = function ($path) {
            if (\strlen($path) > 2 && ':' === $path[1] && '/' === $path[2] && ctype_alpha($path[0])) {
                return substr($path, 2);
            }
            return $path;
        };
        $endPath = $stripDriveLetter($endPath);
        $startPath = $stripDriveLetter($startPath);
        $startPathArr = explode('/', trim($startPath, '/'));
        $endPathArr = explode('/', trim($endPath, '/'));
        $normalizePathArray = function ($pathSegments) {
            $result = [];
            foreach ($pathSegments as $segment) {
                if ('..' === $segment) {
                    array_pop($result);
                } elseif ('.' !== $segment) {
                    $result[] = $segment;
                }
            }
            return $result;
        };
        $startPathArr = $normalizePathArray($startPathArr);
        $endPathArr = $normalizePathArray($endPathArr);
        $index = 0;
        while (isset($startPathArr[$index]) && isset($endPathArr[$index]) && $startPathArr[$index] === $endPathArr[$index]) {
            ++$index;
        }
        if (1 === \count($startPathArr) && '' === $startPathArr[0]) {
            $depth = 0;
        } else {
            $depth = \count($startPathArr) - $index;
        }
        $traverser = str_repeat('../', $depth);
        $endPathRemainder = implode('/', \array_slice($endPathArr, $index));
        $relativePath = $traverser.('' !== $endPathRemainder ? $endPathRemainder.'/' : '');
        return '' === $relativePath ? './' : $relativePath;
    }
    public function mirror($originDir, $targetDir, \Traversable $iterator = null, $options = [])
    {
        $targetDir = rtrim($targetDir, '/\\');
        $originDir = rtrim($originDir, '/\\');
        $originDirLen = \strlen($originDir);
        if ($this->exists($targetDir) && isset($options['delete']) && $options['delete']) {
            $deleteIterator = $iterator;
            if (null === $deleteIterator) {
                $flags = \FilesystemIterator::SKIP_DOTS;
                $deleteIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($targetDir, $flags), \RecursiveIteratorIterator::CHILD_FIRST);
            }
            $targetDirLen = \strlen($targetDir);
            foreach ($deleteIterator as $file) {
                $origin = $originDir.substr($file->getPathname(), $targetDirLen);
                if (!$this->exists($origin)) {
                    $this->remove($file);
                }
            }
        }
        $copyOnWindows = $options['copy_on_windows'] ?? false;
        if (null === $iterator) {
            $flags = $copyOnWindows ? \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS : \FilesystemIterator::SKIP_DOTS;
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($originDir, $flags), \RecursiveIteratorIterator::SELF_FIRST);
        }
        if ($this->exists($originDir)) {
            $this->mkdir($targetDir);
        }
        foreach ($iterator as $file) {
            if (false === strpos($file->getPath(), $originDir)) {
                throw new IOException(sprintf('Unable to mirror "%s" directory. If the origin directory is relative, try using "realpath" before calling the mirror method.', $originDir), 0, null, $originDir);
            }
            $target = $targetDir.substr($file->getPathname(), $originDirLen);
            if ($copyOnWindows) {
                if (is_file($file)) {
                    $this->copy($file, $target, isset($options['override']) ? $options['override'] : false);
                } elseif (is_dir($file)) {
                    $this->mkdir($target);
                } else {
                    throw new IOException(sprintf('Unable to guess "%s" file type.', $file), 0, null, $file);
                }
            } else {
                if (is_link($file)) {
                    $this->symlink($file->getLinkTarget(), $target);
                } elseif (is_dir($file)) {
                    $this->mkdir($target);
                } elseif (is_file($file)) {
                    $this->copy($file, $target, isset($options['override']) ? $options['override'] : false);
                } else {
                    throw new IOException(sprintf('Unable to guess "%s" file type.', $file), 0, null, $file);
                }
            }
        }
    }
    public function isAbsolutePath($file)
    {
        return strspn($file, '/\\', 0, 1)
            || (\strlen($file) > 3 && ctype_alpha($file[0])
                && ':' === $file[1]
                && strspn($file, '/\\', 2, 1)
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        ;
    }
    public function tempnam($dir, $prefix)
    {
        list($scheme, $hierarchy) = $this->getSchemeAndHierarchy($dir);
        if (null === $scheme || 'file' === $scheme || 'gs' === $scheme) {
            $tmpFile = @tempnam($hierarchy, $prefix);
            if (false !== $tmpFile) {
                if (null !== $scheme && 'gs' !== $scheme) {
                    return $scheme.':
                }
                return $tmpFile;
            }
            throw new IOException('A temporary file could not be created.');
        }
        for ($i = 0; $i < 10; ++$i) {
            $tmpFile = $dir.'/'.$prefix.uniqid(mt_rand(), true);
            $handle = @fopen($tmpFile, 'x+');
            if (false === $handle) {
                continue;
            }
            @fclose($handle);
            return $tmpFile;
        }
        throw new IOException('A temporary file could not be created.');
    }
    public function dumpFile($filename, $content)
    {
        $dir = \dirname($filename);
        if (!is_dir($dir)) {
            $this->mkdir($dir);
        }
        if (!is_writable($dir)) {
            throw new IOException(sprintf('Unable to write to the "%s" directory.', $dir), 0, null, $dir);
        }
        $tmpFile = $this->tempnam($dir, basename($filename));
        if (false === @file_put_contents($tmpFile, $content)) {
            throw new IOException(sprintf('Failed to write file "%s".', $filename), 0, null, $filename);
        }
        @chmod($tmpFile, file_exists($filename) ? fileperms($filename) : 0666 & ~umask());
        $this->rename($tmpFile, $filename, true);
    }
    public function appendToFile($filename, $content)
    {
        $dir = \dirname($filename);
        if (!is_dir($dir)) {
            $this->mkdir($dir);
        }
        if (!is_writable($dir)) {
            throw new IOException(sprintf('Unable to write to the "%s" directory.', $dir), 0, null, $dir);
        }
        if (false === @file_put_contents($filename, $content, FILE_APPEND)) {
            throw new IOException(sprintf('Failed to write file "%s".', $filename), 0, null, $filename);
        }
    }
    private function toIterable($files): iterable
    {
        return \is_array($files) || $files instanceof \Traversable ? $files : [$files];
    }
    private function getSchemeAndHierarchy(string $filename): array
    {
        $components = explode(':
        return 2 === \count($components) ? [$components[0], $components[1]] : [null, $components[0]];
    }
    private static function box($func)
    {
        self::$lastError = null;
        \set_error_handler(__CLASS__.'::handleError');
        try {
            $result = $func(...\array_slice(\func_get_args(), 1));
            \restore_error_handler();
            return $result;
        } catch (\Throwable $e) {
        } catch (\Exception $e) {
        }
        \restore_error_handler();
        throw $e;
    }
    public static function handleError($type, $msg)
    {
        self::$lastError = $msg;
    }
}
