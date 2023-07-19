<?php
namespace Zend\Diactoros;
use function preg_match_all;
use function urldecode;
function parseCookieHeader($cookieHeader)
{
    preg_match_all('(
        (?:^\\n?[ \t]*|;[ ])
        (?P<name>[!#$%&\'*+-.0-9A-Z^_`a-z|~]+)
        =
        (?P<DQUOTE>"?)
            (?P<value>[\x21\x23-\x2b\x2d-\x3a\x3c-\x5b\x5d-\x7e]*)
        (?P=DQUOTE)
        (?=\\n?[ \t]*$|;[ ])
    )x', $cookieHeader, $matches, PREG_SET_ORDER);
    $cookies = [];
    foreach ($matches as $match) {
        $cookies[$match['name']] = urldecode($match['value']);
    }
    return $cookies;
}