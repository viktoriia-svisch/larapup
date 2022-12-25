<?php
namespace Psy\Exception;
class ErrorException extends \ErrorException implements Exception
{
    private $rawMessage;
    public function __construct($message = '', $code = 0, $severity = 1, $filename = null, $lineno = null, $previous = null)
    {
        $this->rawMessage = $message;
        if (!empty($filename) && \preg_match('{Psy[/\\\\]ExecutionLoop}', $filename)) {
            $filename = '';
        }
        switch ($severity) {
            case E_STRICT:
                $type = 'Strict error';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $type = 'Notice';
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                $type = 'Warning';
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $type = 'Deprecated';
                break;
            case E_RECOVERABLE_ERROR:
                $type = 'Recoverable fatal error';
                break;
            default:
                $type = 'Error';
                break;
        }
        $message = \sprintf('PHP %s:  %s%s on line %d', $type, $message, $filename ? ' in ' . $filename : '', $lineno);
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
    }
    public function getRawMessage()
    {
        return $this->rawMessage;
    }
    public static function throwException($errno, $errstr, $errfile, $errline)
    {
        throw new self($errstr, 0, $errno, $errfile, $errline);
    }
    public static function fromError(\Error $e)
    {
        return new self($e->getMessage(), $e->getCode(), 1, $e->getFile(), $e->getLine(), $e);
    }
}
