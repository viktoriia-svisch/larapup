<?php
namespace Illuminate\Foundation\Http\Exceptions;
use Exception;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
class MaintenanceModeException extends ServiceUnavailableHttpException
{
    public $wentDownAt;
    public $retryAfter;
    public $willBeAvailableAt;
    public function __construct($time, $retryAfter = null, $message = null, Exception $previous = null, $code = 0)
    {
        parent::__construct($retryAfter, $message, $previous, $code);
        $this->wentDownAt = Carbon::createFromTimestamp($time);
        if ($retryAfter) {
            $this->retryAfter = $retryAfter;
            $this->willBeAvailableAt = Carbon::createFromTimestamp($time)->addSeconds($this->retryAfter);
        }
    }
}
