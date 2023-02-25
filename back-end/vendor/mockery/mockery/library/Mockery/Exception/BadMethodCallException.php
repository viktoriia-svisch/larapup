<?php
namespace Mockery\Exception;
class BadMethodCallException extends \BadMethodCallException
{
    private $dismissed = false;
    public function dismiss()
    {
        $this->dismissed = true;
        if ($this->getPrevious() && $this->getPrevious() instanceof BadMethodCallException) {
            $this->getPrevious()->dismiss();
        }
    }
    public function dismissed()
    {
        return $this->dismissed;
    }
}
