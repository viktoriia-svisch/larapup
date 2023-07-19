<?php
namespace Symfony\Component\HttpKernel\EventListener;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
class ExceptionListener implements EventSubscriberInterface
{
    protected $controller;
    protected $logger;
    protected $debug;
    private $charset;
    private $fileLinkFormat;
    private $isTerminating = false;
    public function __construct($controller, LoggerInterface $logger = null, $debug = false, string $charset = null, $fileLinkFormat = null)
    {
        $this->controller = $controller;
        $this->logger = $logger;
        $this->debug = $debug;
        $this->charset = $charset;
        $this->fileLinkFormat = $fileLinkFormat;
    }
    public function logKernelException(GetResponseForExceptionEvent $event)
    {
        $e = FlattenException::create($event->getException());
        $this->logException($event->getException(), sprintf('Uncaught PHP Exception %s: "%s" at %s line %s', $e->getClass(), $e->getMessage(), $e->getFile(), $e->getLine()));
    }
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (null === $this->controller) {
            if (!$event->isMasterRequest()) {
                return;
            }
            if (!$this->isTerminating) {
                $this->isTerminating = true;
                return;
            }
            $this->isTerminating = false;
        }
        $exception = $event->getException();
        $request = $this->duplicateRequest($exception, $event->getRequest());
        $eventDispatcher = \func_num_args() > 2 ? func_get_arg(2) : null;
        try {
            $response = $event->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST, false);
        } catch (\Exception $e) {
            $f = FlattenException::create($e);
            $this->logException($e, sprintf('Exception thrown when handling an exception (%s: %s at %s line %s)', $f->getClass(), $f->getMessage(), $e->getFile(), $e->getLine()));
            $prev = $e;
            do {
                if ($exception === $wrapper = $prev) {
                    throw $e;
                }
            } while ($prev = $wrapper->getPrevious());
            $prev = new \ReflectionProperty($wrapper instanceof \Exception ? \Exception::class : \Error::class, 'previous');
            $prev->setAccessible(true);
            $prev->setValue($wrapper, $exception);
            throw $e;
        }
        $event->setResponse($response);
        if ($this->debug && $eventDispatcher instanceof EventDispatcherInterface) {
            $cspRemovalListener = function (FilterResponseEvent $event) use (&$cspRemovalListener, $eventDispatcher) {
                $event->getResponse()->headers->remove('Content-Security-Policy');
                $eventDispatcher->removeListener(KernelEvents::RESPONSE, $cspRemovalListener);
            };
            $eventDispatcher->addListener(KernelEvents::RESPONSE, $cspRemovalListener, -128);
        }
    }
    public function reset()
    {
        $this->isTerminating = false;
    }
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [
                ['logKernelException', 0],
                ['onKernelException', -128],
            ],
        ];
    }
    protected function logException(\Exception $exception, $message)
    {
        if (null !== $this->logger) {
            if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                $this->logger->critical($message, ['exception' => $exception]);
            } else {
                $this->logger->error($message, ['exception' => $exception]);
            }
        }
    }
    protected function duplicateRequest(\Exception $exception, Request $request)
    {
        $attributes = [
            'exception' => $exception = FlattenException::create($exception),
            '_controller' => $this->controller ?: function () use ($exception) {
                $handler = new ExceptionHandler($this->debug, $this->charset, $this->fileLinkFormat);
                return new Response($handler->getHtml($exception), $exception->getStatusCode(), $exception->getHeaders());
            },
            'logger' => $this->logger instanceof DebugLoggerInterface ? $this->logger : null,
        ];
        $request = $request->duplicate(null, null, $attributes);
        $request->setMethod('GET');
        return $request;
    }
}