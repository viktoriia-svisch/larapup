<?php
namespace Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\HttpFoundation\Response;
class ResponseCacheStrategy implements ResponseCacheStrategyInterface
{
    private static $overrideDirectives = ['private', 'no-cache', 'no-store', 'no-transform', 'must-revalidate', 'proxy-revalidate'];
    private static $inheritDirectives = ['public', 'immutable'];
    private $embeddedResponses = 0;
    private $isNotCacheableResponseEmbedded = false;
    private $age = 0;
    private $flagDirectives = [
        'no-cache' => null,
        'no-store' => null,
        'no-transform' => null,
        'must-revalidate' => null,
        'proxy-revalidate' => null,
        'public' => null,
        'private' => null,
        'immutable' => null,
    ];
    private $ageDirectives = [
        'max-age' => null,
        's-maxage' => null,
        'expires' => null,
    ];
    public function add(Response $response)
    {
        ++$this->embeddedResponses;
        foreach (self::$overrideDirectives as $directive) {
            if ($response->headers->hasCacheControlDirective($directive)) {
                $this->flagDirectives[$directive] = true;
            }
        }
        foreach (self::$inheritDirectives as $directive) {
            if (false !== $this->flagDirectives[$directive]) {
                $this->flagDirectives[$directive] = $response->headers->hasCacheControlDirective($directive);
            }
        }
        $age = $response->getAge();
        $this->age = max($this->age, $age);
        if ($this->willMakeFinalResponseUncacheable($response)) {
            $this->isNotCacheableResponseEmbedded = true;
            return;
        }
        $this->storeRelativeAgeDirective('max-age', $response->headers->getCacheControlDirective('max-age'), $age);
        $this->storeRelativeAgeDirective('s-maxage', $response->headers->getCacheControlDirective('s-maxage') ?: $response->headers->getCacheControlDirective('max-age'), $age);
        $expires = $response->getExpires();
        $expires = null !== $expires ? $expires->format('U') - $response->getDate()->format('U') : null;
        $this->storeRelativeAgeDirective('expires', $expires >= 0 ? $expires : null, 0);
    }
    public function update(Response $response)
    {
        if (0 === $this->embeddedResponses) {
            return;
        }
        $response->setEtag(null);
        $response->setLastModified(null);
        $this->add($response);
        $response->headers->set('Age', $this->age);
        if ($this->isNotCacheableResponseEmbedded) {
            $response->setExpires($response->getDate());
            if ($this->flagDirectives['no-store']) {
                $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            } else {
                $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
            }
            return;
        }
        $flags = array_filter($this->flagDirectives);
        if (isset($flags['must-revalidate'])) {
            $flags['no-cache'] = true;
        }
        $response->headers->set('Cache-Control', implode(', ', array_keys($flags)));
        $maxAge = null;
        $sMaxage = null;
        if (\is_numeric($this->ageDirectives['max-age'])) {
            $maxAge = $this->ageDirectives['max-age'] + $this->age;
            $response->headers->addCacheControlDirective('max-age', $maxAge);
        }
        if (\is_numeric($this->ageDirectives['s-maxage'])) {
            $sMaxage = $this->ageDirectives['s-maxage'] + $this->age;
            if ($maxAge !== $sMaxage) {
                $response->headers->addCacheControlDirective('s-maxage', $sMaxage);
            }
        }
        if (\is_numeric($this->ageDirectives['expires'])) {
            $date = clone $response->getDate();
            $date->modify('+'.($this->ageDirectives['expires'] + $this->age).' seconds');
            $response->setExpires($date);
        }
    }
    private function willMakeFinalResponseUncacheable(Response $response)
    {
        if ($response->headers->hasCacheControlDirective('no-cache')
            || $response->headers->getCacheControlDirective('no-store')
        ) {
            return true;
        }
        if (\in_array($response->getStatusCode(), [200, 203, 300, 301, 410])
            && null === $response->getLastModified()
            && null === $response->getEtag()
        ) {
            return false;
        }
        $cacheControl = ['max-age', 's-maxage', 'must-revalidate', 'proxy-revalidate', 'public', 'private'];
        foreach ($cacheControl as $key) {
            if ($response->headers->hasCacheControlDirective($key)) {
                return false;
            }
        }
        if ($response->headers->has('Expires')) {
            return false;
        }
        return true;
    }
    private function storeRelativeAgeDirective($directive, $value, $age)
    {
        if (null === $value) {
            $this->ageDirectives[$directive] = false;
        }
        if (false !== $this->ageDirectives[$directive]) {
            $value = $value - $age;
            $this->ageDirectives[$directive] = null !== $this->ageDirectives[$directive] ? min($this->ageDirectives[$directive], $value) : $value;
        }
    }
}
