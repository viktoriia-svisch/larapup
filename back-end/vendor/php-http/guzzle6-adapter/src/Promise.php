<?php
namespace Http\Adapter\Guzzle6;
use GuzzleHttp\Exception as GuzzleExceptions;
use GuzzleHttp\Promise\PromiseInterface;
use Http\Client\Exception as HttplugException;
use Http\Promise\Promise as HttpPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
class Promise implements HttpPromise
{
    private $promise;
    private $state;
    private $response;
    private $exception;
    private $request;
    public function __construct(PromiseInterface $promise, RequestInterface $request)
    {
        $this->request = $request;
        $this->state = self::PENDING;
        $this->promise = $promise->then(function ($response) {
            $this->response = $response;
            $this->state = self::FULFILLED;
            return $response;
        }, function ($reason) use ($request) {
            $this->state = self::REJECTED;
            if ($reason instanceof HttplugException) {
                $this->exception = $reason;
            } elseif ($reason instanceof GuzzleExceptions\GuzzleException) {
                $this->exception = $this->handleException($reason, $request);
            } elseif ($reason instanceof \Exception) {
                $this->exception = new \RuntimeException('Invalid exception returned from Guzzle6', 0, $reason);
            } else {
                $this->exception = new \UnexpectedValueException('Reason returned from Guzzle6 must be an Exception', 0, $reason);
            }
            throw $this->exception;
        });
    }
    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        return new static($this->promise->then($onFulfilled, $onRejected), $this->request);
    }
    public function getState()
    {
        return $this->state;
    }
    public function wait($unwrap = true)
    {
        $this->promise->wait(false);
        if ($unwrap) {
            if ($this->getState() == self::REJECTED) {
                throw $this->exception;
            }
            return $this->response;
        }
    }
    private function handleException(GuzzleExceptions\GuzzleException $exception, RequestInterface $request)
    {
        if ($exception instanceof GuzzleExceptions\SeekException) {
            return new HttplugException\RequestException($exception->getMessage(), $request, $exception);
        }
        if ($exception instanceof GuzzleExceptions\ConnectException) {
            return new HttplugException\NetworkException($exception->getMessage(), $exception->getRequest(), $exception);
        }
        if ($exception instanceof GuzzleExceptions\RequestException) {
            if ($exception->hasResponse()) {
                return new HttplugException\HttpException(
                    $exception->getMessage(),
                    $exception->getRequest(),
                    $exception->getResponse(),
                    $exception
                );
            }
            return new HttplugException\RequestException($exception->getMessage(), $exception->getRequest(), $exception);
        }
        return new HttplugException\TransferException($exception->getMessage(), 0, $exception);
    }
}
