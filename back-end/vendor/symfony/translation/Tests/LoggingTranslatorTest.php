<?php
namespace Symfony\Component\Translation\Tests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\LoggingTranslator;
use Symfony\Component\Translation\Translator;
class LoggingTranslatorTest extends TestCase
{
    public function testTransWithNoTranslationIsLogged()
    {
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $logger->expects($this->exactly(1))
            ->method('warning')
            ->with('Translation not found.')
        ;
        $translator = new Translator('ar');
        $loggableTranslator = new LoggingTranslator($translator, $logger);
        $loggableTranslator->trans('bar');
    }
    public function testTransChoiceFallbackIsLogged()
    {
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $logger->expects($this->once())
            ->method('debug')
            ->with('Translation use fallback catalogue.')
        ;
        $translator = new Translator('ar');
        $translator->setFallbackLocales(['en']);
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['some_message2' => 'one thing|%count% things'], 'en');
        $loggableTranslator = new LoggingTranslator($translator, $logger);
        $loggableTranslator->transChoice('some_message2', 10, ['%count%' => 10]);
    }
    public function testTransChoiceWithNoTranslationIsLogged()
    {
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $logger->expects($this->exactly(1))
            ->method('warning')
            ->with('Translation not found.')
        ;
        $translator = new Translator('ar');
        $loggableTranslator = new LoggingTranslator($translator, $logger);
        $loggableTranslator->transChoice('some_message2', 10, ['%count%' => 10]);
    }
}
