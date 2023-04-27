<?php
namespace Symfony\Component\HttpFoundation;
class IpUtils
{
    private static $checkedIps = [];
    private function __construct()
    {
    }
    public static function checkIp($requestIp, $ips)
    {
        if (!\is_array($ips)) {
            $ips = [$ips];
        }
        $method = substr_count($requestIp, ':') > 1 ? 'checkIp6' : 'checkIp4';
        foreach ($ips as $ip) {
            if (self::$method($requestIp, $ip)) {
                return true;
            }
        }
        return false;
    }
    public static function checkIp4($requestIp, $ip)
    {
        $cacheKey = $requestIp.'-'.$ip;
        if (isset(self::$checkedIps[$cacheKey])) {
            return self::$checkedIps[$cacheKey];
        }
        if (!filter_var($requestIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return self::$checkedIps[$cacheKey] = false;
        }
        if (false !== strpos($ip, '/')) {
            list($address, $netmask) = explode('/', $ip, 2);
            if ('0' === $netmask) {
                return self::$checkedIps[$cacheKey] = filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
            }
            if ($netmask < 0 || $netmask > 32) {
                return self::$checkedIps[$cacheKey] = false;
            }
        } else {
            $address = $ip;
            $netmask = 32;
        }
        if (false === ip2long($address)) {
            return self::$checkedIps[$cacheKey] = false;
        }
        return self::$checkedIps[$cacheKey] = 0 === substr_compare(sprintf('%032b', ip2long($requestIp)), sprintf('%032b', ip2long($address)), 0, $netmask);
    }
    public static function checkIp6($requestIp, $ip)
    {
        $cacheKey = $requestIp.'-'.$ip;
        if (isset(self::$checkedIps[$cacheKey])) {
            return self::$checkedIps[$cacheKey];
        }
        if (!((\extension_loaded('sockets') && \defined('AF_INET6')) || @inet_pton('::1'))) {
            throw new \RuntimeException('Unable to check Ipv6. Check that PHP was not compiled with option "disable-ipv6".');
        }
        if (false !== strpos($ip, '/')) {
            list($address, $netmask) = explode('/', $ip, 2);
            if ('0' === $netmask) {
                return (bool) unpack('n*', @inet_pton($address));
            }
            if ($netmask < 1 || $netmask > 128) {
                return self::$checkedIps[$cacheKey] = false;
            }
        } else {
            $address = $ip;
            $netmask = 128;
        }
        $bytesAddr = unpack('n*', @inet_pton($address));
        $bytesTest = unpack('n*', @inet_pton($requestIp));
        if (!$bytesAddr || !$bytesTest) {
            return self::$checkedIps[$cacheKey] = false;
        }
        for ($i = 1, $ceil = ceil($netmask / 16); $i <= $ceil; ++$i) {
            $left = $netmask - 16 * ($i - 1);
            $left = ($left <= 16) ? $left : 16;
            $mask = ~(0xffff >> $left) & 0xffff;
            if (($bytesAddr[$i] & $mask) != ($bytesTest[$i] & $mask)) {
                return self::$checkedIps[$cacheKey] = false;
            }
        }
        return self::$checkedIps[$cacheKey] = true;
    }
}
