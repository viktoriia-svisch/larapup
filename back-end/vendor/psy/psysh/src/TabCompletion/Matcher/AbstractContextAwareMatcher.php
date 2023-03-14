<?php
namespace Psy\TabCompletion\Matcher;
use Psy\Context;
use Psy\ContextAware;
abstract class AbstractContextAwareMatcher extends AbstractMatcher implements ContextAware
{
    protected $context;
    public function setContext(Context $context)
    {
        $this->context = $context;
    }
    protected function getVariable($var)
    {
        return $this->context->get($var);
    }
    protected function getVariables()
    {
        return $this->context->getAll();
    }
}
