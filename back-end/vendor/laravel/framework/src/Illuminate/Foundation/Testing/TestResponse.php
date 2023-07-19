<?php
namespace Illuminate\Foundation\Testing;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;
use PHPUnit\Framework\Assert as PHPUnit;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Foundation\Testing\Constraints\SeeInOrder;
class TestResponse
{
    use Macroable {
        __call as macroCall;
    }
    public $baseResponse;
    protected $streamedContent;
    public function __construct($response)
    {
        $this->baseResponse = $response;
    }
    public static function fromBaseResponse($response)
    {
        return new static($response);
    }
    public function assertSuccessful()
    {
        PHPUnit::assertTrue(
            $this->isSuccessful(),
            'Response status code ['.$this->getStatusCode().'] is not a successful status code.'
        );
        return $this;
    }
    public function assertOk()
    {
        PHPUnit::assertTrue(
            $this->isOk(),
            'Response status code ['.$this->getStatusCode().'] does not match expected 200 status code.'
        );
        return $this;
    }
    public function assertNotFound()
    {
        PHPUnit::assertTrue(
            $this->isNotFound(),
            'Response status code ['.$this->getStatusCode().'] is not a not found status code.'
        );
        return $this;
    }
    public function assertForbidden()
    {
        PHPUnit::assertTrue(
            $this->isForbidden(),
            'Response status code ['.$this->getStatusCode().'] is not a forbidden status code.'
        );
        return $this;
    }
    public function assertStatus($status)
    {
        $actual = $this->getStatusCode();
        PHPUnit::assertTrue(
            $actual === $status,
            "Expected status code {$status} but received {$actual}."
        );
        return $this;
    }
    public function assertRedirect($uri = null)
    {
        PHPUnit::assertTrue(
            $this->isRedirect(), 'Response status code ['.$this->getStatusCode().'] is not a redirect status code.'
        );
        if (! is_null($uri)) {
            $this->assertLocation($uri);
        }
        return $this;
    }
    public function assertHeader($headerName, $value = null)
    {
        PHPUnit::assertTrue(
            $this->headers->has($headerName), "Header [{$headerName}] not present on response."
        );
        $actual = $this->headers->get($headerName);
        if (! is_null($value)) {
            PHPUnit::assertEquals(
                $value, $this->headers->get($headerName),
                "Header [{$headerName}] was found, but value [{$actual}] does not match [{$value}]."
            );
        }
        return $this;
    }
    public function assertHeaderMissing($headerName)
    {
        PHPUnit::assertFalse(
            $this->headers->has($headerName), "Unexpected header [{$headerName}] is present on response."
        );
        return $this;
    }
    public function assertLocation($uri)
    {
        PHPUnit::assertEquals(
            app('url')->to($uri), app('url')->to($this->headers->get('Location'))
        );
        return $this;
    }
    public function assertPlainCookie($cookieName, $value = null)
    {
        $this->assertCookie($cookieName, $value, false);
        return $this;
    }
    public function assertCookie($cookieName, $value = null, $encrypted = true, $unserialize = false)
    {
        PHPUnit::assertNotNull(
            $cookie = $this->getCookie($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );
        if (! $cookie || is_null($value)) {
            return $this;
        }
        $cookieValue = $cookie->getValue();
        $actual = $encrypted
            ? app('encrypter')->decrypt($cookieValue, $unserialize) : $cookieValue;
        PHPUnit::assertEquals(
            $value, $actual,
            "Cookie [{$cookieName}] was found, but value [{$actual}] does not match [{$value}]."
        );
        return $this;
    }
    public function assertCookieExpired($cookieName)
    {
        PHPUnit::assertNotNull(
            $cookie = $this->getCookie($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );
        $expiresAt = Carbon::createFromTimestamp($cookie->getExpiresTime());
        PHPUnit::assertTrue(
            $expiresAt->lessThan(Carbon::now()),
            "Cookie [{$cookieName}] is not expired, it expires at [{$expiresAt}]."
        );
        return $this;
    }
    public function assertCookieNotExpired($cookieName)
    {
        PHPUnit::assertNotNull(
            $cookie = $this->getCookie($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );
        $expiresAt = Carbon::createFromTimestamp($cookie->getExpiresTime());
        PHPUnit::assertTrue(
            $expiresAt->greaterThan(Carbon::now()),
            "Cookie [{$cookieName}] is expired, it expired at [{$expiresAt}]."
        );
        return $this;
    }
    public function assertCookieMissing($cookieName)
    {
        PHPUnit::assertNull(
            $this->getCookie($cookieName),
            "Cookie [{$cookieName}] is present on response."
        );
        return $this;
    }
    protected function getCookie($cookieName)
    {
        foreach ($this->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $cookieName) {
                return $cookie;
            }
        }
    }
    public function assertSee($value)
    {
        PHPUnit::assertContains((string) $value, $this->getContent());
        return $this;
    }
    public function assertSeeInOrder(array $values)
    {
        PHPUnit::assertThat($values, new SeeInOrder($this->getContent()));
        return $this;
    }
    public function assertSeeText($value)
    {
        PHPUnit::assertContains((string) $value, strip_tags($this->getContent()));
        return $this;
    }
    public function assertSeeTextInOrder(array $values)
    {
        PHPUnit::assertThat($values, new SeeInOrder(strip_tags($this->getContent())));
        return $this;
    }
    public function assertDontSee($value)
    {
        PHPUnit::assertNotContains((string) $value, $this->getContent());
        return $this;
    }
    public function assertDontSeeText($value)
    {
        PHPUnit::assertNotContains((string) $value, strip_tags($this->getContent()));
        return $this;
    }
    public function assertJson(array $data, $strict = false)
    {
        PHPUnit::assertArraySubset(
            $data, $this->decodeResponseJson(), $strict, $this->assertJsonMessage($data)
        );
        return $this;
    }
    protected function assertJsonMessage(array $data)
    {
        $expected = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $actual = json_encode($this->decodeResponseJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return 'Unable to find JSON: '.PHP_EOL.PHP_EOL.
            "[{$expected}]".PHP_EOL.PHP_EOL.
            'within response JSON:'.PHP_EOL.PHP_EOL.
            "[{$actual}].".PHP_EOL.PHP_EOL;
    }
    public function assertExactJson(array $data)
    {
        $actual = json_encode(Arr::sortRecursive(
            (array) $this->decodeResponseJson()
        ));
        PHPUnit::assertEquals(json_encode(Arr::sortRecursive($data)), $actual);
        return $this;
    }
    public function assertJsonFragment(array $data)
    {
        $actual = json_encode(Arr::sortRecursive(
            (array) $this->decodeResponseJson()
        ));
        foreach (Arr::sortRecursive($data) as $key => $value) {
            $expected = $this->jsonSearchStrings($key, $value);
            PHPUnit::assertTrue(
                Str::contains($actual, $expected),
                'Unable to find JSON fragment: '.PHP_EOL.PHP_EOL.
                '['.json_encode([$key => $value]).']'.PHP_EOL.PHP_EOL.
                'within'.PHP_EOL.PHP_EOL.
                "[{$actual}]."
            );
        }
        return $this;
    }
    public function assertJsonMissing(array $data, $exact = false)
    {
        if ($exact) {
            return $this->assertJsonMissingExact($data);
        }
        $actual = json_encode(Arr::sortRecursive(
            (array) $this->decodeResponseJson()
        ));
        foreach (Arr::sortRecursive($data) as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);
            PHPUnit::assertFalse(
                Str::contains($actual, $unexpected),
                'Found unexpected JSON fragment: '.PHP_EOL.PHP_EOL.
                '['.json_encode([$key => $value]).']'.PHP_EOL.PHP_EOL.
                'within'.PHP_EOL.PHP_EOL.
                "[{$actual}]."
            );
        }
        return $this;
    }
    public function assertJsonMissingExact(array $data)
    {
        $actual = json_encode(Arr::sortRecursive(
            (array) $this->decodeResponseJson()
        ));
        foreach (Arr::sortRecursive($data) as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);
            if (! Str::contains($actual, $unexpected)) {
                return $this;
            }
        }
        PHPUnit::fail(
            'Found unexpected JSON fragment: '.PHP_EOL.PHP_EOL.
            '['.json_encode($data).']'.PHP_EOL.PHP_EOL.
            'within'.PHP_EOL.PHP_EOL.
            "[{$actual}]."
        );
    }
    protected function jsonSearchStrings($key, $value)
    {
        $needle = substr(json_encode([$key => $value]), 1, -1);
        return [
            $needle.']',
            $needle.'}',
            $needle.',',
        ];
    }
    public function assertJsonStructure(array $structure = null, $responseData = null)
    {
        if (is_null($structure)) {
            return $this->assertExactJson($this->json());
        }
        if (is_null($responseData)) {
            $responseData = $this->decodeResponseJson();
        }
        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                PHPUnit::assertInternalType('array', $responseData);
                foreach ($responseData as $responseDataItem) {
                    $this->assertJsonStructure($structure['*'], $responseDataItem);
                }
            } elseif (is_array($value)) {
                PHPUnit::assertArrayHasKey($key, $responseData);
                $this->assertJsonStructure($structure[$key], $responseData[$key]);
            } else {
                PHPUnit::assertArrayHasKey($value, $responseData);
            }
        }
        return $this;
    }
    public function assertJsonCount(int $count, $key = null)
    {
        if ($key) {
            PHPUnit::assertCount(
                $count, data_get($this->json(), $key),
                "Failed to assert that the response count matched the expected {$count}"
            );
            return $this;
        }
        PHPUnit::assertCount($count,
            $this->json(),
            "Failed to assert that the response count matched the expected {$count}"
        );
        return $this;
    }
    public function assertJsonValidationErrors($keys)
    {
        $keys = Arr::wrap($keys);
        PHPUnit::assertNotEmpty($keys, 'No keys were provided.');
        $errors = $this->json()['errors'] ?? [];
        $errorMessage = $errors
                ? 'Response has the following JSON validation errors:'.
                        PHP_EOL.PHP_EOL.json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
                : 'Response does not have JSON validation errors.';
        foreach ($keys as $key) {
            PHPUnit::assertArrayHasKey(
                $key,
                $errors,
                "Failed to find a validation error in the response for key: '{$key}'".PHP_EOL.PHP_EOL.$errorMessage
            );
        }
        return $this;
    }
    public function assertJsonMissingValidationErrors($keys = null)
    {
        $json = $this->json();
        if (! array_key_exists('errors', $json)) {
            PHPUnit::assertArrayNotHasKey('errors', $json);
            return $this;
        }
        $errors = $json['errors'];
        if (is_null($keys) && count($errors) > 0) {
            PHPUnit::fail(
                'Response has unexpected validation errors: '.PHP_EOL.PHP_EOL.
                json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
        }
        foreach (Arr::wrap($keys) as $key) {
            PHPUnit::assertFalse(
                isset($errors[$key]),
                "Found unexpected validation error for key: '{$key}'"
            );
        }
        return $this;
    }
    public function decodeResponseJson($key = null)
    {
        $decodedResponse = json_decode($this->getContent(), true);
        if (is_null($decodedResponse) || $decodedResponse === false) {
            if ($this->exception) {
                throw $this->exception;
            } else {
                PHPUnit::fail('Invalid JSON was returned from the route.');
            }
        }
        return data_get($decodedResponse, $key);
    }
    public function json($key = null)
    {
        return $this->decodeResponseJson($key);
    }
    public function assertViewIs($value)
    {
        $this->ensureResponseHasView();
        PHPUnit::assertEquals($value, $this->original->getName());
        return $this;
    }
    public function assertViewHas($key, $value = null)
    {
        if (is_array($key)) {
            return $this->assertViewHasAll($key);
        }
        $this->ensureResponseHasView();
        if (is_null($value)) {
            PHPUnit::assertArrayHasKey($key, $this->original->getData());
        } elseif ($value instanceof Closure) {
            PHPUnit::assertTrue($value($this->original->$key));
        } elseif ($value instanceof Model) {
            PHPUnit::assertTrue($value->is($this->original->$key));
        } else {
            PHPUnit::assertEquals($value, $this->original->$key);
        }
        return $this;
    }
    public function assertViewHasAll(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $this->assertViewHas($value);
            } else {
                $this->assertViewHas($key, $value);
            }
        }
        return $this;
    }
    public function viewData($key)
    {
        $this->ensureResponseHasView();
        return $this->original->$key;
    }
    public function assertViewMissing($key)
    {
        $this->ensureResponseHasView();
        PHPUnit::assertArrayNotHasKey($key, $this->original->getData());
        return $this;
    }
    protected function ensureResponseHasView()
    {
        if (! isset($this->original) || ! $this->original instanceof View) {
            return PHPUnit::fail('The response is not a view.');
        }
        return $this;
    }
    public function assertSessionHas($key, $value = null)
    {
        if (is_array($key)) {
            return $this->assertSessionHasAll($key);
        }
        if (is_null($value)) {
            PHPUnit::assertTrue(
                $this->session()->has($key),
                "Session is missing expected key [{$key}]."
            );
        } else {
            PHPUnit::assertEquals($value, $this->session()->get($key));
        }
        return $this;
    }
    public function assertSessionHasAll(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $this->assertSessionHas($value);
            } else {
                $this->assertSessionHas($key, $value);
            }
        }
        return $this;
    }
    public function assertSessionHasErrors($keys = [], $format = null, $errorBag = 'default')
    {
        $this->assertSessionHas('errors');
        $keys = (array) $keys;
        $errors = $this->session()->get('errors')->getBag($errorBag);
        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                PHPUnit::assertTrue($errors->has($value), "Session missing error: $value");
            } else {
                PHPUnit::assertContains($value, $errors->get($key, $format));
            }
        }
        return $this;
    }
    public function assertSessionDoesntHaveErrors($keys = [], $format = null, $errorBag = 'default')
    {
        $keys = (array) $keys;
        if (empty($keys)) {
            return $this->assertSessionMissing('errors');
        }
        if (is_null($this->session()->get('errors'))) {
            PHPUnit::assertTrue(true);
            return $this;
        }
        $errors = $this->session()->get('errors')->getBag($errorBag);
        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                PHPUnit::assertFalse($errors->has($value), "Session has unexpected error: $value");
            } else {
                PHPUnit::assertNotContains($value, $errors->get($key, $format));
            }
        }
        return $this;
    }
    public function assertSessionHasNoErrors()
    {
        $hasErrors = $this->session()->has('errors');
        $errors = $hasErrors ? $this->session()->get('errors')->all() : [];
        PHPUnit::assertFalse(
            $hasErrors,
            'Session has unexpected errors: '.PHP_EOL.PHP_EOL.
            json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
        return $this;
    }
    public function assertSessionHasErrorsIn($errorBag, $keys = [], $format = null)
    {
        return $this->assertSessionHasErrors($keys, $format, $errorBag);
    }
    public function assertSessionMissing($key)
    {
        if (is_array($key)) {
            foreach ($key as $value) {
                $this->assertSessionMissing($value);
            }
        } else {
            PHPUnit::assertFalse(
                $this->session()->has($key),
                "Session has unexpected key [{$key}]."
            );
        }
        return $this;
    }
    protected function session()
    {
        return app('session.store');
    }
    public function dump()
    {
        $content = $this->getContent();
        $json = json_decode($content);
        if (json_last_error() === JSON_ERROR_NONE) {
            $content = $json;
        }
        dd($content);
    }
    public function streamedContent()
    {
        if (! is_null($this->streamedContent)) {
            return $this->streamedContent;
        }
        if (! $this->baseResponse instanceof StreamedResponse) {
            PHPUnit::fail('The response is not a streamed response.');
        }
        ob_start();
        $this->sendContent();
        return $this->streamedContent = ob_get_clean();
    }
    public function __get($key)
    {
        return $this->baseResponse->{$key};
    }
    public function __isset($key)
    {
        return isset($this->baseResponse->{$key});
    }
    public function __call($method, $args)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $args);
        }
        return $this->baseResponse->{$method}(...$args);
    }
}