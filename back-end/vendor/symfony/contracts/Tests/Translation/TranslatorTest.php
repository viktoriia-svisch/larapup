<?php
namespace Symfony\Contracts\Tests\Translation;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorTrait;
class TranslatorTest extends TestCase
{
    public function getTranslator()
    {
        return new class() implements TranslatorInterface {
            use TranslatorTrait;
        };
    }
    public function testTrans($expected, $id, $parameters)
    {
        $translator = $this->getTranslator();
        $this->assertEquals($expected, $translator->trans($id, $parameters));
    }
    public function testTransChoiceWithExplicitLocale($expected, $id, $number)
    {
        $translator = $this->getTranslator();
        $translator->setLocale('en');
        $this->assertEquals($expected, $translator->trans($id, array('%count%' => $number)));
    }
    public function testTransChoiceWithDefaultLocale($expected, $id, $number)
    {
        \Locale::setDefault('en');
        $translator = $this->getTranslator();
        $this->assertEquals($expected, $translator->trans($id, array('%count%' => $number)));
    }
    public function testGetSetLocale()
    {
        $translator = $this->getTranslator();
        $translator->setLocale('en');
        $this->assertEquals('en', $translator->getLocale());
    }
    public function testGetLocaleReturnsDefaultLocaleIfNotSet()
    {
        $translator = $this->getTranslator();
        \Locale::setDefault('pt_BR');
        $this->assertEquals('pt_BR', $translator->getLocale());
        \Locale::setDefault('en');
        $this->assertEquals('en', $translator->getLocale());
    }
    public function getTransTests()
    {
        return array(
            array('Symfony is great!', 'Symfony is great!', array()),
            array('Symfony is awesome!', 'Symfony is %what%!', array('%what%' => 'awesome')),
        );
    }
    public function getTransChoiceTests()
    {
        return array(
            array('There are no apples', '{0} There are no apples|{1} There is one apple|]1,Inf] There are %count% apples', 0),
            array('There is one apple', '{0} There are no apples|{1} There is one apple|]1,Inf] There are %count% apples', 1),
            array('There are 10 apples', '{0} There are no apples|{1} There is one apple|]1,Inf] There are %count% apples', 10),
            array('There are 0 apples', 'There is 1 apple|There are %count% apples', 0),
            array('There is 1 apple', 'There is 1 apple|There are %count% apples', 1),
            array('There are 10 apples', 'There is 1 apple|There are %count% apples', 10),
            array('There are 2 apples', 'There are 2 apples', 2),
        );
    }
    public function testInterval($expected, $number, $interval)
    {
        $translator = $this->getTranslator();
        $this->assertEquals($expected, $translator->trans($interval.' foo|[1,Inf[ bar', array('%count%' => $number)));
    }
    public function getInternal()
    {
        return array(
            array('foo', 3, '{1,2, 3 ,4}'),
            array('bar', 10, '{1,2, 3 ,4}'),
            array('bar', 3, '[1,2]'),
            array('foo', 1, '[1,2]'),
            array('foo', 2, '[1,2]'),
            array('bar', 1, ']1,2['),
            array('bar', 2, ']1,2['),
            array('foo', log(0), '[-Inf,2['),
            array('foo', -log(0), '[-2,+Inf]'),
        );
    }
    public function testChoose($expected, $id, $number)
    {
        $translator = $this->getTranslator();
        $this->assertEquals($expected, $translator->trans($id, array('%count%' => $number)));
    }
    public function testReturnMessageIfExactlyOneStandardRuleIsGiven()
    {
        $translator = $this->getTranslator();
        $this->assertEquals('There are two apples', $translator->trans('There are two apples', array('%count%' => 2)));
    }
    public function testThrowExceptionIfMatchingMessageCannotBeFound($id, $number)
    {
        $translator = $this->getTranslator();
        $translator->trans($id, array('%count%' => $number));
    }
    public function getNonMatchingMessages()
    {
        return array(
            array('{0} There are no apples|{1} There is one apple', 2),
            array('{1} There is one apple|]1,Inf] There are %count% apples', 0),
            array('{1} There is one apple|]2,Inf] There are %count% apples', 2),
            array('{0} There are no apples|There is one apple', 2),
        );
    }
    public function getChooseTests()
    {
        return array(
            array('There are no apples', '{0} There are no apples|{1} There is one apple|]1,Inf] There are %count% apples', 0),
            array('There are no apples', '{0}     There are no apples|{1} There is one apple|]1,Inf] There are %count% apples', 0),
            array('There are no apples', '{0}There are no apples|{1} There is one apple|]1,Inf] There are %count% apples', 0),
            array('There is one apple', '{0} There are no apples|{1} There is one apple|]1,Inf] There are %count% apples', 1),
            array('There are 10 apples', '{0} There are no apples|{1} There is one apple|]1,Inf] There are %count% apples', 10),
            array('There are 10 apples', '{0} There are no apples|{1} There is one apple|]1,Inf]There are %count% apples', 10),
            array('There are 10 apples', '{0} There are no apples|{1} There is one apple|]1,Inf]     There are %count% apples', 10),
            array('There are 0 apples', 'There is one apple|There are %count% apples', 0),
            array('There is one apple', 'There is one apple|There are %count% apples', 1),
            array('There are 10 apples', 'There is one apple|There are %count% apples', 10),
            array('There are 0 apples', 'one: There is one apple|more: There are %count% apples', 0),
            array('There is one apple', 'one: There is one apple|more: There are %count% apples', 1),
            array('There are 10 apples', 'one: There is one apple|more: There are %count% apples', 10),
            array('There are no apples', '{0} There are no apples|one: There is one apple|more: There are %count% apples', 0),
            array('There is one apple', '{0} There are no apples|one: There is one apple|more: There are %count% apples', 1),
            array('There are 10 apples', '{0} There are no apples|one: There is one apple|more: There are %count% apples', 10),
            array('', '{0}|{1} There is one apple|]1,Inf] There are %count% apples', 0),
            array('', '{0} There are no apples|{1}|]1,Inf] There are %count% apples', 1),
            array('There are 0 apples', 'There is one apple|There are %count% apples', 0),
            array('There is one apple', 'There is one apple|There are %count% apples', 1),
            array('There are 2 apples', 'There is one apple|There are %count% apples', 2),
            array('There is almost one apple', '{0} There are no apples|]0,1[ There is almost one apple|{1} There is one apple|[1,Inf] There is more than one apple', 0.7),
            array('There is one apple', '{0} There are no apples|]0,1[There are %count% apples|{1} There is one apple|[1,Inf] There is more than one apple', 1),
            array('There is more than one apple', '{0} There are no apples|]0,1[There are %count% apples|{1} There is one apple|[1,Inf] There is more than one apple', 1.7),
            array('There are no apples', '{0} There are no apples|]0,1[There are %count% apples|{1} There is one apple|[1,Inf] There is more than one apple', 0),
            array('There are no apples', '{0} There are no apples|]0,1[There are %count% apples|{1} There is one apple|[1,Inf] There is more than one apple', 0.0),
            array('There are no apples', '{0.0} There are no apples|]0,1[There are %count% apples|{1} There is one apple|[1,Inf] There is more than one apple', 0),
            array("This is a text with a\n            new-line in it. Selector = 0.", '{0}This is a text with a
            new-line in it. Selector = 0.|{1}This is a text with a
            new-line in it. Selector = 1.|[1,Inf]This is a text with a
            new-line in it. Selector > 1.', 0),
            array("This is a text with a\n            new-line in it. Selector = 1.", '{0}This is a text with a
            new-line in it. Selector = 0.|{1}This is a text with a
            new-line in it. Selector = 1.|[1,Inf]This is a text with a
            new-line in it. Selector > 1.', 1),
            array("This is a text with a\n            new-line in it. Selector > 1.", '{0}This is a text with a
            new-line in it. Selector = 0.|{1}This is a text with a
            new-line in it. Selector = 1.|[1,Inf]This is a text with a
            new-line in it. Selector > 1.', 5),
            array('This is a text with a
            new-line in it. Selector = 1.', '{0}This is a text with a
            new-line in it. Selector = 0.|{1}This is a text with a
            new-line in it. Selector = 1.|[1,Inf]This is a text with a
            new-line in it. Selector > 1.', 1),
            array('This is a text with a
            new-line in it. Selector > 1.', '{0}This is a text with a
            new-line in it. Selector = 0.|{1}This is a text with a
            new-line in it. Selector = 1.|[1,Inf]This is a text with a
            new-line in it. Selector > 1.', 5),
            array('This is a text with a\nnew-line in it. Selector = 0.', '{0}This is a text with a\nnew-line in it. Selector = 0.|{1}This is a text with a\nnew-line in it. Selector = 1.|[1,Inf]This is a text with a\nnew-line in it. Selector > 1.', 0),
            array("This is a text with a\nnew-line in it. Selector = 1.", "{0}This is a text with a\nnew-line in it. Selector = 0.|{1}This is a text with a\nnew-line in it. Selector = 1.|[1,Inf]This is a text with a\nnew-line in it. Selector > 1.", 1),
            array('This is a text with | in it. Selector = 0.', '{0}This is a text with || in it. Selector = 0.|{1}This is a text with || in it. Selector = 1.', 0),
            array('', '|', 1),
            array('', '||', 1),
        );
    }
    public function testFailedLangcodes($nplural, $langCodes)
    {
        $matrix = $this->generateTestData($langCodes);
        $this->validateMatrix($nplural, $matrix, false);
    }
    public function testLangcodes($nplural, $langCodes)
    {
        $matrix = $this->generateTestData($langCodes);
        $this->validateMatrix($nplural, $matrix);
    }
    public function successLangcodes()
    {
        return array(
            array('1', array('ay', 'bo', 'cgg', 'dz', 'id', 'ja', 'jbo', 'ka', 'kk', 'km', 'ko', 'ky')),
            array('2', array('nl', 'fr', 'en', 'de', 'de_GE', 'hy', 'hy_AM')),
            array('3', array('be', 'bs', 'cs', 'hr')),
            array('4', array('cy', 'mt', 'sl')),
            array('6', array('ar')),
        );
    }
    public function failingLangcodes()
    {
        return array(
            array('1', array('fa')),
            array('2', array('jbo')),
            array('3', array('cbs')),
            array('4', array('gd', 'kw')),
            array('5', array('ga')),
        );
    }
    protected function validateMatrix($nplural, $matrix, $expectSuccess = true)
    {
        foreach ($matrix as $langCode => $data) {
            $indexes = array_flip($data);
            if ($expectSuccess) {
                $this->assertEquals($nplural, \count($indexes), "Langcode '$langCode' has '$nplural' plural forms.");
            } else {
                $this->assertNotEquals((int) $nplural, \count($indexes), "Langcode '$langCode' has '$nplural' plural forms.");
            }
        }
    }
    protected function generateTestData($langCodes)
    {
        $translator = new class() {
            use TranslatorTrait {
                getPluralizationRule as public;
            }
        };
        $matrix = array();
        foreach ($langCodes as $langCode) {
            for ($count = 0; $count < 200; ++$count) {
                $plural = $translator->getPluralizationRule($count, $langCode);
                $matrix[$langCode][$count] = $plural;
            }
        }
        return $matrix;
    }
}
