<?php
namespace Symfony\Component\Translation\Tests;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Contracts\Tests\Translation\TranslatorTest;
class IdentityTranslatorTest extends TranslatorTest
{
    public function getTranslator()
    {
        return new IdentityTranslator();
    }
}
