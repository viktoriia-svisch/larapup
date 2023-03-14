<?php
namespace Egulias\EmailValidator;
use Egulias\EmailValidator\Exception\InvalidEmail;
use Egulias\EmailValidator\Validation\EmailValidation;
class EmailValidator
{
    private $lexer;
    protected $warnings;
    protected $error;
    public function __construct()
    {
        $this->lexer = new EmailLexer();
    }
    public function isValid($email, EmailValidation $emailValidation)
    {
        $isValid = $emailValidation->isValid($email, $this->lexer);
        $this->warnings = $emailValidation->getWarnings();
        $this->error = $emailValidation->getError();
        return $isValid;
    }
    public function hasWarnings()
    {
        return !empty($this->warnings);
    }
    public function getWarnings()
    {
        return $this->warnings;
    }
    public function getError()
    {
        return $this->error;
    }
}
