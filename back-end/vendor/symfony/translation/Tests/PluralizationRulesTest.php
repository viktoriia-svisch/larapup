<?php
namespace Symfony\Component\Translation\Tests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\PluralizationRules;
class PluralizationRulesTest extends TestCase
{
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
        return [
            ['1', ['ay', 'bo', 'cgg', 'dz', 'id', 'ja', 'jbo', 'ka', 'kk', 'km', 'ko', 'ky']],
            ['2', ['nl', 'fr', 'en', 'de', 'de_GE', 'hy', 'hy_AM']],
            ['3', ['be', 'bs', 'cs', 'hr']],
            ['4', ['cy', 'mt', 'sl']],
            ['6', ['ar']],
        ];
    }
    public function failingLangcodes()
    {
        return [
            ['1', ['fa']],
            ['2', ['jbo']],
            ['3', ['cbs']],
            ['4', ['gd', 'kw']],
            ['5', ['ga']],
        ];
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
        $matrix = [];
        foreach ($langCodes as $langCode) {
            for ($count = 0; $count < 200; ++$count) {
                $plural = PluralizationRules::get($count, $langCode);
                $matrix[$langCode][$count] = $plural;
            }
        }
        return $matrix;
    }
}
