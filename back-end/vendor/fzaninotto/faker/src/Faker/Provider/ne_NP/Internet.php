<?php
namespace Faker\Provider\ne_NP;
class Internet extends \Faker\Provider\Internet
{
    protected static $freeEmailDomain = array('gmail.com', 'yahoo.com', 'hotmail.com');
    protected static $tld = array('com', 'com', 'com', 'net', 'org');
    protected static $emailFormats = array(
        '{{userName}}@{{domainName}}',
        '{{userName}}@{{domainName}}',
        '{{userName}}@{{freeEmailDomain}}',
        '{{userName}}@{{domainName}}.np',
        '{{userName}}@{{domainName}}.np',
        '{{userName}}@{{domainName}}.np',
    );
    protected static $urlFormats = array(
        'http:
        'http:
        'http:
        'http:
        'http:
        'http:
        'http:
        'http:
        'http:
        'http:
        'http:
    );
}
