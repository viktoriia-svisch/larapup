<?php
namespace Egulias\EmailValidator\Validation;
use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Validation\Exception\EmptyValidationList;
class MultipleValidationWithAnd implements EmailValidation
{
    const STOP_ON_ERROR = 0;
    const ALLOW_ALL_ERRORS = 1;
    private $validations = [];
    private $warnings = [];
    private $error;
    private $mode;
    public function __construct(array $validations, $mode = self::ALLOW_ALL_ERRORS)
    {
        if (count($validations) == 0) {
            throw new EmptyValidationList();
        }
        $this->validations = $validations;
        $this->mode = $mode;
    }
    public function isValid($email, EmailLexer $emailLexer)
    {
        $result = true;
        $errors = [];
        foreach ($this->validations as $validation) {
            $emailLexer->reset();
            $validationResult = $validation->isValid($email, $emailLexer);
            $result = $result && $validationResult;
            $this->warnings = array_merge($this->warnings, $validation->getWarnings());
            $errors = $this->addNewError($validation->getError(), $errors);
            if ($this->shouldStop($result)) {
                break;
            }
        }
        if (!empty($errors)) {
            $this->error = new MultipleErrors($errors);
        }
        return $result;
    }
    private function addNewError($possibleError, array $errors)
    {
        if (null !== $possibleError) {
            $errors[] = $possibleError;
        }
        return $errors;
    }
    private function shouldStop($result)
    {
        return !$result && $this->mode === self::STOP_ON_ERROR;
    }
    public function getError()
    {
        return $this->error;
    }
    public function getWarnings()
    {
        return $this->warnings;
    }
}
