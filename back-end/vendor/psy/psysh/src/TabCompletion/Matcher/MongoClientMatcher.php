<?php
namespace Psy\TabCompletion\Matcher;
class MongoClientMatcher extends AbstractContextAwareMatcher
{
    public function getMatches(array $tokens, array $info = [])
    {
        $input = $this->getInput($tokens);
        $firstToken = \array_pop($tokens);
        if (self::tokenIs($firstToken, self::T_STRING)) {
            \array_pop($tokens);
        }
        $objectToken = \array_pop($tokens);
        $objectName  = \str_replace('$', '', $objectToken[1]);
        $object      = $this->getVariable($objectName);
        if (!$object instanceof \MongoClient) {
            return [];
        }
        $list = $object->listDBs();
        return \array_filter(
            \array_map(function ($info) {
                return $info['name'];
            }, $list['databases']),
            function ($var) use ($input) {
                return AbstractMatcher::startsWith($input, $var);
            }
        );
    }
    public function hasMatched(array $tokens)
    {
        $token     = \array_pop($tokens);
        $prevToken = \array_pop($tokens);
        switch (true) {
            case self::tokenIs($token, self::T_OBJECT_OPERATOR):
            case self::tokenIs($prevToken, self::T_OBJECT_OPERATOR):
                return true;
        }
        return false;
    }
}
