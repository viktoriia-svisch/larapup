<?php
namespace Symfony\Component\HttpKernel\Fragment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\EventListener\FragmentListener;
abstract class RoutableFragmentRenderer implements FragmentRendererInterface
{
    private $fragmentPath = '/_fragment';
    public function setFragmentPath($path)
    {
        $this->fragmentPath = $path;
    }
    protected function generateFragmentUri(ControllerReference $reference, Request $request, $absolute = false, $strict = true)
    {
        if ($strict) {
            $this->checkNonScalar($reference->attributes);
        }
        if (!isset($reference->attributes['_format'])) {
            $reference->attributes['_format'] = $request->getRequestFormat();
        }
        if (!isset($reference->attributes['_locale'])) {
            $reference->attributes['_locale'] = $request->getLocale();
        }
        $reference->attributes['_controller'] = $reference->controller;
        $reference->query['_path'] = http_build_query($reference->attributes, '', '&');
        $path = $this->fragmentPath.'?'.http_build_query($reference->query, '', '&');
        if ($absolute) {
            return $request->getUriForPath($path);
        }
        return $request->getBaseUrl().$path;
    }
    private function checkNonScalar($values)
    {
        foreach ($values as $key => $value) {
            if (\is_array($value)) {
                $this->checkNonScalar($value);
            } elseif (!is_scalar($value) && null !== $value) {
                throw new \LogicException(sprintf('Controller attributes cannot contain non-scalar/non-null values (value for key "%s" is not a scalar or null).', $key));
            }
        }
    }
}
