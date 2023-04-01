<?php
namespace Psy\Test\TabCompletion;
use Psy\Command\ListCommand;
use Psy\Command\ShowCommand;
use Psy\Configuration;
use Psy\Context;
use Psy\ContextAware;
use Psy\TabCompletion\Matcher;
class AutoCompleterTest extends \PHPUnit\Framework\TestCase
{
    public function testClassesCompletion($line, $mustContain, $mustNotContain)
    {
        $context = new Context();
        $commands = [
            new ShowCommand(),
            new ListCommand(),
        ];
        $matchers = [
            new Matcher\VariablesMatcher(),
            new Matcher\ClassNamesMatcher(),
            new Matcher\ConstantsMatcher(),
            new Matcher\FunctionsMatcher(),
            new Matcher\ObjectMethodsMatcher(),
            new Matcher\ObjectAttributesMatcher(),
            new Matcher\KeywordsMatcher(),
            new Matcher\ClassAttributesMatcher(),
            new Matcher\ClassMethodsMatcher(),
            new Matcher\CommandsMatcher($commands),
        ];
        $config = new Configuration();
        $tabCompletion = $config->getAutoCompleter();
        foreach ($matchers as $matcher) {
            if ($matcher instanceof ContextAware) {
                $matcher->setContext($context);
            }
            $tabCompletion->addMatcher($matcher);
        }
        $context->setAll(['foo' => 12, 'bar' => new \DOMDocument()]);
        $code = $tabCompletion->processCallback('', 0, [
           'line_buffer' => $line,
           'point'       => 0,
           'end'         => \strlen($line),
        ]);
        foreach ($mustContain as $mc) {
            $this->assertContains($mc, $code);
        }
        foreach ($mustNotContain as $mnc) {
            $this->assertNotContains($mnc, $code);
        }
    }
    public function classesInput()
    {
        return [
            ['T_OPE', ['T_OPEN_TAG'], []],
            ['st', ['stdClass'], []],
            ['stdCla', ['stdClass'], []],
            ['new s', ['stdClass'], []],
            [
                'new ',
                ['stdClass', 'Psy\\Context', 'Psy\\Configuration'],
                ['require', 'array_search', 'T_OPEN_TAG', '$foo'],
            ],
            ['new Psy\\C', ['Context'], ['CASE_LOWER']],
            ['\s', ['stdClass'], []],
            ['array_', ['array_search', 'array_map', 'array_merge'], []],
            ['$bar->', ['load'], []],
            ['$b', ['bar'], []],
            ['6 + $b', ['bar'], []],
            ['$f', ['foo'], []],
            ['l', ['ls'], []],
            ['ls ', [], ['ls']],
            ['sho', ['show'], []],
            ['12 + clone $', ['foo'], []],
            ['$', ['foo', 'bar'], ['require', 'array_search', 'T_OPEN_TAG', 'Psy']],
            [
                'Psy\\',
                ['Context', 'TabCompletion\\Matcher\\AbstractMatcher'],
                ['require', 'array_search'],
            ],
            [
                'Psy\Test\TabCompletion\StaticSample::CO',
                ['StaticSample::CONSTANT_VALUE'],
                [],
            ],
            [
                'Psy\Test\TabCompletion\StaticSample::',
                ['StaticSample::$staticVariable'],
                [],
            ],
            [
                'Psy\Test\TabCompletion\StaticSample::',
                ['StaticSample::staticFunction'],
                [],
            ],
        ];
    }
}
