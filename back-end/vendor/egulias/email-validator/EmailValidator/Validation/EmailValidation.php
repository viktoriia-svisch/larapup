<?php
namespace Egulias\EmailValidator\Validation;
use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Exception\InvalidEmail;
use Egulias\EmailValidator\Warning\Warning;
interface EmailValidation
{
    public function isValid($email, EmailLexer $emailLexer);
    public function getError();
    public function getWarnings();
}
