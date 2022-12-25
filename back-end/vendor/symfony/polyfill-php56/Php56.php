<?php
namespace Symfony\Polyfill\Php56;
use Symfony\Polyfill\Util\Binary;
final class Php56
{
    const LDAP_ESCAPE_FILTER = 1;
    const LDAP_ESCAPE_DN = 2;
    public static function hash_equals($knownString, $userInput)
    {
        if (!\is_string($knownString)) {
            trigger_error('Expected known_string to be a string, '.\gettype($knownString).' given', E_USER_WARNING);
            return false;
        }
        if (!\is_string($userInput)) {
            trigger_error('Expected user_input to be a string, '.\gettype($userInput).' given', E_USER_WARNING);
            return false;
        }
        $knownLen = Binary::strlen($knownString);
        $userLen = Binary::strlen($userInput);
        if ($knownLen !== $userLen) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < $knownLen; ++$i) {
            $result |= \ord($knownString[$i]) ^ \ord($userInput[$i]);
        }
        return 0 === $result;
    }
    public static function ldap_escape($subject, $ignore = '', $flags = 0)
    {
        static $charMaps = null;
        if (null === $charMaps) {
            $charMaps = array(
                self::LDAP_ESCAPE_FILTER => array('\\', '*', '(', ')', "\x00"),
                self::LDAP_ESCAPE_DN => array('\\', ',', '=', '+', '<', '>', ';', '"', '#', "\r"),
            );
            $charMaps[0] = array();
            for ($i = 0; $i < 256; ++$i) {
                $charMaps[0][\chr($i)] = sprintf('\\%02x', $i);
            }
            for ($i = 0, $l = \count($charMaps[self::LDAP_ESCAPE_FILTER]); $i < $l; ++$i) {
                $chr = $charMaps[self::LDAP_ESCAPE_FILTER][$i];
                unset($charMaps[self::LDAP_ESCAPE_FILTER][$i]);
                $charMaps[self::LDAP_ESCAPE_FILTER][$chr] = $charMaps[0][$chr];
            }
            for ($i = 0, $l = \count($charMaps[self::LDAP_ESCAPE_DN]); $i < $l; ++$i) {
                $chr = $charMaps[self::LDAP_ESCAPE_DN][$i];
                unset($charMaps[self::LDAP_ESCAPE_DN][$i]);
                $charMaps[self::LDAP_ESCAPE_DN][$chr] = $charMaps[0][$chr];
            }
        }
        $flags = (int) $flags;
        $charMap = array();
        if ($flags & self::LDAP_ESCAPE_FILTER) {
            $charMap += $charMaps[self::LDAP_ESCAPE_FILTER];
        }
        if ($flags & self::LDAP_ESCAPE_DN) {
            $charMap += $charMaps[self::LDAP_ESCAPE_DN];
        }
        if (!$charMap) {
            $charMap = $charMaps[0];
        }
        $ignore = (string) $ignore;
        for ($i = 0, $l = \strlen($ignore); $i < $l; ++$i) {
            unset($charMap[$ignore[$i]]);
        }
        $result = strtr($subject, $charMap);
        if ($flags & self::LDAP_ESCAPE_DN) {
            if (' ' === $result[0]) {
                $result = '\\20'.substr($result, 1);
            }
            if (' ' === $result[\strlen($result) - 1]) {
                $result = substr($result, 0, -1).'\\20';
            }
        }
        return $result;
    }
}
