<?php
namespace Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
class BinaryFileResponse extends Response
{
    protected static $trustXSendfileTypeHeader = false;
    protected $file;
    protected $offset = 0;
    protected $maxlen = -1;
    protected $deleteFileAfterSend = false;
    public function __construct($file, int $status = 200, array $headers = [], bool $public = true, string $contentDisposition = null, bool $autoEtag = false, bool $autoLastModified = true)
    {
        parent::__construct(null, $status, $headers);
        $this->setFile($file, $contentDisposition, $autoEtag, $autoLastModified);
        if ($public) {
            $this->setPublic();
        }
    }
    public static function create($file = null, $status = 200, $headers = [], $public = true, $contentDisposition = null, $autoEtag = false, $autoLastModified = true)
    {
        return new static($file, $status, $headers, $public, $contentDisposition, $autoEtag, $autoLastModified);
    }
    public function setFile($file, $contentDisposition = null, $autoEtag = false, $autoLastModified = true)
    {
        if (!$file instanceof File) {
            if ($file instanceof \SplFileInfo) {
                $file = new File($file->getPathname());
            } else {
                $file = new File((string) $file);
            }
        }
        if (!$file->isReadable()) {
            throw new FileException('File must be readable.');
        }
        $this->file = $file;
        if ($autoEtag) {
            $this->setAutoEtag();
        }
        if ($autoLastModified) {
            $this->setAutoLastModified();
        }
        if ($contentDisposition) {
            $this->setContentDisposition($contentDisposition);
        }
        return $this;
    }
    public function getFile()
    {
        return $this->file;
    }
    public function setAutoLastModified()
    {
        $this->setLastModified(\DateTime::createFromFormat('U', $this->file->getMTime()));
        return $this;
    }
    public function setAutoEtag()
    {
        $this->setEtag(base64_encode(hash_file('sha256', $this->file->getPathname(), true)));
        return $this;
    }
    public function setContentDisposition($disposition, $filename = '', $filenameFallback = '')
    {
        if ('' === $filename) {
            $filename = $this->file->getFilename();
        }
        if ('' === $filenameFallback && (!preg_match('/^[\x20-\x7e]*$/', $filename) || false !== strpos($filename, '%'))) {
            $encoding = mb_detect_encoding($filename, null, true) ?: '8bit';
            for ($i = 0, $filenameLength = mb_strlen($filename, $encoding); $i < $filenameLength; ++$i) {
                $char = mb_substr($filename, $i, 1, $encoding);
                if ('%' === $char || \ord($char) < 32 || \ord($char) > 126) {
                    $filenameFallback .= '_';
                } else {
                    $filenameFallback .= $char;
                }
            }
        }
        $dispositionHeader = $this->headers->makeDisposition($disposition, $filename, $filenameFallback);
        $this->headers->set('Content-Disposition', $dispositionHeader);
        return $this;
    }
    public function prepare(Request $request)
    {
        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', $this->file->getMimeType() ?: 'application/octet-stream');
        }
        if ('HTTP/1.0' !== $request->server->get('SERVER_PROTOCOL')) {
            $this->setProtocolVersion('1.1');
        }
        $this->ensureIEOverSSLCompatibility($request);
        $this->offset = 0;
        $this->maxlen = -1;
        if (false === $fileSize = $this->file->getSize()) {
            return $this;
        }
        $this->headers->set('Content-Length', $fileSize);
        if (!$this->headers->has('Accept-Ranges')) {
            $this->headers->set('Accept-Ranges', $request->isMethodSafe(false) ? 'bytes' : 'none');
        }
        if (self::$trustXSendfileTypeHeader && $request->headers->has('X-Sendfile-Type')) {
            $type = $request->headers->get('X-Sendfile-Type');
            $path = $this->file->getRealPath();
            if (false === $path) {
                $path = $this->file->getPathname();
            }
            if ('x-accel-redirect' === strtolower($type)) {
                $parts = HeaderUtils::split($request->headers->get('X-Accel-Mapping', ''), ',=');
                foreach ($parts as $part) {
                    list($pathPrefix, $location) = $part;
                    if (substr($path, 0, \strlen($pathPrefix)) === $pathPrefix) {
                        $path = $location.substr($path, \strlen($pathPrefix));
                        break;
                    }
                }
            }
            $this->headers->set($type, $path);
            $this->maxlen = 0;
        } elseif ($request->headers->has('Range')) {
            if (!$request->headers->has('If-Range') || $this->hasValidIfRangeHeader($request->headers->get('If-Range'))) {
                $range = $request->headers->get('Range');
                list($start, $end) = explode('-', substr($range, 6), 2) + [0];
                $end = ('' === $end) ? $fileSize - 1 : (int) $end;
                if ('' === $start) {
                    $start = $fileSize - $end;
                    $end = $fileSize - 1;
                } else {
                    $start = (int) $start;
                }
                if ($start <= $end) {
                    if ($start < 0 || $end > $fileSize - 1) {
                        $this->setStatusCode(416);
                        $this->headers->set('Content-Range', sprintf('bytes */%s', $fileSize));
                    } elseif (0 !== $start || $end !== $fileSize - 1) {
                        $this->maxlen = $end < $fileSize ? $end - $start + 1 : -1;
                        $this->offset = $start;
                        $this->setStatusCode(206);
                        $this->headers->set('Content-Range', sprintf('bytes %s-%s/%s', $start, $end, $fileSize));
                        $this->headers->set('Content-Length', $end - $start + 1);
                    }
                }
            }
        }
        return $this;
    }
    private function hasValidIfRangeHeader($header)
    {
        if ($this->getEtag() === $header) {
            return true;
        }
        if (null === $lastModified = $this->getLastModified()) {
            return false;
        }
        return $lastModified->format('D, d M Y H:i:s').' GMT' === $header;
    }
    public function sendContent()
    {
        if (!$this->isSuccessful()) {
            return parent::sendContent();
        }
        if (0 === $this->maxlen) {
            return $this;
        }
        $out = fopen('php:
        $file = fopen($this->file->getPathname(), 'rb');
        stream_copy_to_stream($file, $out, $this->maxlen, $this->offset);
        fclose($out);
        fclose($file);
        if ($this->deleteFileAfterSend && file_exists($this->file->getPathname())) {
            unlink($this->file->getPathname());
        }
        return $this;
    }
    public function setContent($content)
    {
        if (null !== $content) {
            throw new \LogicException('The content cannot be set on a BinaryFileResponse instance.');
        }
    }
    public function getContent()
    {
        return false;
    }
    public static function trustXSendfileTypeHeader()
    {
        self::$trustXSendfileTypeHeader = true;
    }
    public function deleteFileAfterSend($shouldDelete = true)
    {
        $this->deleteFileAfterSend = $shouldDelete;
        return $this;
    }
}
