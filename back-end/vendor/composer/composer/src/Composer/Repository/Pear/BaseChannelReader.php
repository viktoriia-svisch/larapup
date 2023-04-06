<?php
namespace Composer\Repository\Pear;
use Composer\Util\RemoteFilesystem;
abstract class BaseChannelReader
{
    const CHANNEL_NS = 'http:
    const ALL_CATEGORIES_NS = 'http:
    const CATEGORY_PACKAGES_INFO_NS = 'http:
    const ALL_PACKAGES_NS = 'http:
    const ALL_RELEASES_NS = 'http:
    const PACKAGE_INFO_NS = 'http:
    private $rfs;
    protected function __construct(RemoteFilesystem $rfs)
    {
        $this->rfs = $rfs;
    }
    protected function requestContent($origin, $path)
    {
        $url = rtrim($origin, '/') . '/' . ltrim($path, '/');
        $content = $this->rfs->getContents($origin, $url, false);
        if (!$content) {
            throw new \UnexpectedValueException('The PEAR channel at ' . $url . ' did not respond.');
        }
        return str_replace('http:
    }
    protected function requestXml($origin, $path)
    {
        $xml = simplexml_load_string($this->requestContent($origin, $path), "SimpleXMLElement", LIBXML_NOERROR);
        if (false === $xml) {
            throw new \UnexpectedValueException(sprintf('The PEAR channel at ' . $origin . ' is broken. (Invalid XML at file `%s`)', $path));
        }
        return $xml;
    }
}
