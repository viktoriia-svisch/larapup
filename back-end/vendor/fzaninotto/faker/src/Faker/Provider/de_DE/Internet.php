<?php
namespace Faker\Provider\de_DE;
class Internet extends \Faker\Provider\Internet
{
    protected static $freeEmailDomain = array(
        'web.de',
        'gmail.com',
        'hotmail.de',
        'yahoo.de',
        'googlemail.com',
        'aol.de',
        'gmx.de',
        'freenet.de',
        'posteo.de',
        'mail.de',
        'live.de',
        't-online.de'
    );
    protected static $tld = array('com', 'com', 'com', 'net', 'org', 'de', 'de', 'de');
}