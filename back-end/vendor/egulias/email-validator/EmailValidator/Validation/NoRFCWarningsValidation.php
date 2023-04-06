<?php
namespace Egulias\EmailValidator\Validation;
use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Exception\InvalidEmail;
use Egulias\EmailValidator\Validation\Error\RFCWarnings;
class NoRFCWarningsValidation extends RFCValidation
{
    private $error;
    public function isValid($email, EmailLexer $emailLexer)
    {
        if (!parent::isValid($email, $emailLexer)) {
            return false;
        }
        if (empty($this->getWarnings())) {
            return true;
        }
        $this->error = new RFCWarnings();
        return false;
    }
    public function getError()
    {
        return $this->error ?: parent::getError();
    }
}
