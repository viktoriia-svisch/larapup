<?php
namespace Symfony\Component\HttpKernel\Tests\Fixtures\ExtensionNotValidBundle\DependencyInjection;
class ExtensionNotValidExtension
{
    public function getAlias()
    {
        return 'extension_not_valid';
    }
}
