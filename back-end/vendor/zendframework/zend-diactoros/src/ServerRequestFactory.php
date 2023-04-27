<?php
namespace Zend\Diactoros;
use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;
use stdClass;
use UnexpectedValueException;
use function array_change_key_case;
use function array_key_exists;
use function explode;
use function implode;
use function is_array;
use function is_callable;
use function strtolower;
use const CASE_LOWER;
abstract class ServerRequestFactory
{
    private static $apacheRequestHeaders = 'apache_request_headers';
    public static function fromGlobals(
        array $server = null,
        array $query = null,
        array $body = null,
        array $cookies = null,
        array $files = null
    ) {
        $server = normalizeServer(
            $server ?: $_SERVER,
            is_callable(self::$apacheRequestHeaders) ? self::$apacheRequestHeaders : null
        );
        $files   = normalizeUploadedFiles($files ?: $_FILES);
        $headers = marshalHeadersFromSapi($server);
        if (null === $cookies && array_key_exists('cookie', $headers)) {
            $cookies = parseCookieHeader($headers['cookie']);
        }
        return new ServerRequest(
            $server,
            $files,
            marshalUriFromSapi($server, $headers),
            marshalMethodFromSapi($server),
            'php:
            $headers,
            $cookies ?: $_COOKIE,
            $query ?: $_GET,
            $body ?: $_POST,
            marshalProtocolVersionFromSapi($server)
        );
    }
    public static function get($key, array $values, $default = null)
    {
        if (array_key_exists($key, $values)) {
            return $values[$key];
        }
        return $default;
    }
    public static function getHeader($header, array $headers, $default = null)
    {
        $header  = strtolower($header);
        $headers = array_change_key_case($headers, CASE_LOWER);
        if (array_key_exists($header, $headers)) {
            $value = is_array($headers[$header]) ? implode(', ', $headers[$header]) : $headers[$header];
            return $value;
        }
        return $default;
    }
    public static function normalizeServer(array $server)
    {
        return normalizeServer(
            $server ?: $_SERVER,
            is_callable(self::$apacheRequestHeaders) ? self::$apacheRequestHeaders : null
        );
    }
    public static function normalizeFiles(array $files)
    {
        return normalizeUploadedFiles($files);
    }
    public static function marshalHeaders(array $server)
    {
        return marshalHeadersFromSapi($server);
    }
    public static function marshalUriFromServer(array $server, array $headers)
    {
        return marshalUriFromSapi($server, $headers);
    }
    public static function marshalHostAndPortFromHeaders(stdClass $accumulator, array $server, array $headers)
    {
        $uri = marshalUriFromSapi($server, $headers);
        $accumulator->host = $uri->getHost();
        $accumulator->port = $uri->getPort();
    }
    public static function marshalRequestUri(array $server)
    {
        $uri = marshalUriFromSapi($server, []);
        return $uri->getPath();
    }
    public static function stripQueryString($path)
    {
        return explode('?', $path, 2)[0];
    }
}
