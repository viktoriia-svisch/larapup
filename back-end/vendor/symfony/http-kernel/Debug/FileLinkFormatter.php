<?php
namespace Symfony\Component\HttpKernel\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
class FileLinkFormatter implements \Serializable
{
    private $fileLinkFormat;
    private $requestStack;
    private $baseDir;
    private $urlFormat;
    public function __construct($fileLinkFormat = null, RequestStack $requestStack = null, string $baseDir = null, $urlFormat = null)
    {
        $fileLinkFormat = $fileLinkFormat ?: ini_get('xdebug.file_link_format') ?: get_cfg_var('xdebug.file_link_format');
        if ($fileLinkFormat && !\is_array($fileLinkFormat)) {
            $i = strpos($f = $fileLinkFormat, '&', max(strrpos($f, '%f'), strrpos($f, '%l'))) ?: \strlen($f);
            $fileLinkFormat = [substr($f, 0, $i)] + preg_split('/&([^>]++)>/', substr($f, $i), -1, PREG_SPLIT_DELIM_CAPTURE);
        }
        $this->fileLinkFormat = $fileLinkFormat;
        $this->requestStack = $requestStack;
        $this->baseDir = $baseDir;
        $this->urlFormat = $urlFormat;
    }
    public function format($file, $line)
    {
        if ($fmt = $this->getFileLinkFormat()) {
            for ($i = 1; isset($fmt[$i]); ++$i) {
                if (0 === strpos($file, $k = $fmt[$i++])) {
                    $file = substr_replace($file, $fmt[$i], 0, \strlen($k));
                    break;
                }
            }
            return strtr($fmt[0], ['%f' => $file, '%l' => $line]);
        }
        return false;
    }
    public function serialize()
    {
        return serialize($this->getFileLinkFormat());
    }
    public function unserialize($serialized)
    {
        $this->fileLinkFormat = unserialize($serialized, ['allowed_classes' => false]);
    }
    public static function generateUrlFormat(UrlGeneratorInterface $router, $routeName, $queryString)
    {
        try {
            return $router->generate($routeName).$queryString;
        } catch (ExceptionInterface $e) {
            return null;
        }
    }
    private function getFileLinkFormat()
    {
        if ($this->fileLinkFormat) {
            return $this->fileLinkFormat;
        }
        if ($this->requestStack && $this->baseDir && $this->urlFormat) {
            $request = $this->requestStack->getMasterRequest();
            if ($request instanceof Request) {
                if ($this->urlFormat instanceof \Closure && !$this->urlFormat = ($this->urlFormat)()) {
                    return;
                }
                return [
                    $request->getSchemeAndHttpHost().$request->getBasePath().$this->urlFormat,
                    $this->baseDir.\DIRECTORY_SEPARATOR, '',
                ];
            }
        }
    }
}
