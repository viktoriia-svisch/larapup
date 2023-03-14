<?php
namespace Psy\TabCompletion\Matcher;
class KeywordsMatcher extends AbstractMatcher
{
    protected $keywords = [
        'array', 'clone', 'declare', 'die', 'echo', 'empty', 'eval', 'exit', 'include',
        'include_once', 'isset', 'list', 'print',  'require', 'require_once', 'unset',
    ];
    protected $mandatoryStartKeywords = [
        'die', 'echo', 'print', 'unset',
    ];
    public function getKeywords()
    {
        return $this->keywords;
    }
    public function isKeyword($keyword)
    {
        return \in_array($keyword, $this->keywords);
    }
    public function getMatches(array $tokens, array $info = [])
    {
        $input = $this->getInput($tokens);
        return \array_filter($this->keywords, function ($keyword) use ($input) {
            return AbstractMatcher::startsWith($input, $keyword);
        });
    }
    public function hasMatched(array $tokens)
    {
        $token     = \array_pop($tokens);
        $prevToken = \array_pop($tokens);
        switch (true) {
            case self::hasToken([self::T_OPEN_TAG, self::T_VARIABLE], $token):
            case self::hasToken([self::T_OPEN_TAG, self::T_VARIABLE], $prevToken) &&
                self::tokenIs($token, self::T_STRING):
            case self::isOperator($token):
                return true;
        }
        return false;
    }
}
