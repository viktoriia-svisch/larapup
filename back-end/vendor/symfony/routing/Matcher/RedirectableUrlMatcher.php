<?php
namespace Symfony\Component\Routing\Matcher;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
abstract class RedirectableUrlMatcher extends UrlMatcher implements RedirectableUrlMatcherInterface
{
    public function match($pathinfo)
    {
        try {
            return parent::match($pathinfo);
        } catch (ResourceNotFoundException $e) {
            if (!\in_array($this->context->getMethod(), ['HEAD', 'GET'], true)) {
                throw $e;
            }
            if ($this->allowSchemes) {
                redirect_scheme:
                $scheme = $this->context->getScheme();
                $this->context->setScheme(current($this->allowSchemes));
                try {
                    $ret = parent::match($pathinfo);
                    return $this->redirect($pathinfo, $ret['_route'] ?? null, $this->context->getScheme()) + $ret;
                } catch (ExceptionInterface $e2) {
                    throw $e;
                } finally {
                    $this->context->setScheme($scheme);
                }
            } elseif ('/' === $trimmedPathinfo = rtrim($pathinfo, '/') ?: '/') {
                throw $e;
            } else {
                try {
                    $pathinfo = $trimmedPathinfo === $pathinfo ? $pathinfo.'/' : $trimmedPathinfo;
                    $ret = parent::match($pathinfo);
                    return $this->redirect($pathinfo, $ret['_route'] ?? null) + $ret;
                } catch (ExceptionInterface $e2) {
                    if ($this->allowSchemes) {
                        goto redirect_scheme;
                    }
                    throw $e;
                }
            }
        }
    }
}
