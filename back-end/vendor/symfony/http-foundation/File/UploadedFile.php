<?php
namespace Symfony\Component\HttpFoundation\File;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoTmpDirFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
class UploadedFile extends File
{
    private $test = false;
    private $originalName;
    private $mimeType;
    private $error;
    public function __construct(string $path, string $originalName, string $mimeType = null, int $error = null, $test = false)
    {
        $this->originalName = $this->getName($originalName);
        $this->mimeType = $mimeType ?: 'application/octet-stream';
        if (4 < \func_num_args() ? !\is_bool($test) : null !== $error && @filesize($path) === $error) {
            @trigger_error(sprintf('Passing a size as 4th argument to the constructor of "%s" is deprecated since Symfony 4.1.', __CLASS__), E_USER_DEPRECATED);
            $error = $test;
            $test = 5 < \func_num_args() ? func_get_arg(5) : false;
        }
        $this->error = $error ?: UPLOAD_ERR_OK;
        $this->test = $test;
        parent::__construct($path, UPLOAD_ERR_OK === $this->error);
    }
    public function getClientOriginalName()
    {
        return $this->originalName;
    }
    public function getClientOriginalExtension()
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION);
    }
    public function getClientMimeType()
    {
        return $this->mimeType;
    }
    public function guessClientExtension()
    {
        $type = $this->getClientMimeType();
        $guesser = ExtensionGuesser::getInstance();
        return $guesser->guess($type);
    }
    public function getClientSize()
    {
        @trigger_error(sprintf('The "%s()" method is deprecated since Symfony 4.1. Use getSize() instead.', __METHOD__), E_USER_DEPRECATED);
        return $this->getSize();
    }
    public function getError()
    {
        return $this->error;
    }
    public function isValid()
    {
        $isOk = UPLOAD_ERR_OK === $this->error;
        return $this->test ? $isOk : $isOk && is_uploaded_file($this->getPathname());
    }
    public function move($directory, $name = null)
    {
        if ($this->isValid()) {
            if ($this->test) {
                return parent::move($directory, $name);
            }
            $target = $this->getTargetFile($directory, $name);
            set_error_handler(function ($type, $msg) use (&$error) { $error = $msg; });
            $moved = move_uploaded_file($this->getPathname(), $target);
            restore_error_handler();
            if (!$moved) {
                throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error)));
            }
            @chmod($target, 0666 & ~umask());
            return $target;
        }
        switch ($this->error) {
            case UPLOAD_ERR_INI_SIZE:
                throw new IniSizeFileException($this->getErrorMessage());
            case UPLOAD_ERR_FORM_SIZE:
                throw new FormSizeFileException($this->getErrorMessage());
            case UPLOAD_ERR_PARTIAL:
                throw new PartialFileException($this->getErrorMessage());
            case UPLOAD_ERR_NO_FILE:
                throw new NoFileException($this->getErrorMessage());
            case UPLOAD_ERR_CANT_WRITE:
                throw new CannotWriteFileException($this->getErrorMessage());
            case UPLOAD_ERR_NO_TMP_DIR:
                throw new NoTmpDirFileException($this->getErrorMessage());
            case UPLOAD_ERR_EXTENSION:
                throw new ExtensionFileException($this->getErrorMessage());
        }
        throw new FileException($this->getErrorMessage());
    }
    public static function getMaxFilesize()
    {
        $iniMax = strtolower(ini_get('upload_max_filesize'));
        if ('' === $iniMax) {
            return PHP_INT_MAX;
        }
        $max = ltrim($iniMax, '+');
        if (0 === strpos($max, '0x')) {
            $max = \intval($max, 16);
        } elseif (0 === strpos($max, '0')) {
            $max = \intval($max, 8);
        } else {
            $max = (int) $max;
        }
        switch (substr($iniMax, -1)) {
            case 't': $max *= 1024;
            case 'g': $max *= 1024;
            case 'm': $max *= 1024;
            case 'k': $max *= 1024;
        }
        return $max;
    }
    public function getErrorMessage()
    {
        static $errors = [
            UPLOAD_ERR_INI_SIZE => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d KiB).',
            UPLOAD_ERR_FORM_SIZE => 'The file "%s" exceeds the upload limit defined in your form.',
            UPLOAD_ERR_PARTIAL => 'The file "%s" was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
            UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
            UPLOAD_ERR_EXTENSION => 'File upload was stopped by a PHP extension.',
        ];
        $errorCode = $this->error;
        $maxFilesize = UPLOAD_ERR_INI_SIZE === $errorCode ? self::getMaxFilesize() / 1024 : 0;
        $message = isset($errors[$errorCode]) ? $errors[$errorCode] : 'The file "%s" was not uploaded due to an unknown error.';
        return sprintf($message, $this->getClientOriginalName(), $maxFilesize);
    }
}
