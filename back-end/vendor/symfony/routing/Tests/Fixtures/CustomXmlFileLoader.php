<?php
namespace Symfony\Component\Routing\Tests\Fixtures;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Routing\Loader\XmlFileLoader;
class CustomXmlFileLoader extends XmlFileLoader
{
    protected function loadFile($file)
    {
        return XmlUtils::loadFile($file, function () { return true; });
    }
}
