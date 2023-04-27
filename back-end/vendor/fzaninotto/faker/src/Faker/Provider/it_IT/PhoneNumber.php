<?php
namespace Faker\Provider\it_IT;
class PhoneNumber extends \Faker\Provider\PhoneNumber
{
    protected static $formats = array(
        '+## ### ## ## ####',
        '+## ## #######',
        '+## ## ########',
        '+## ### #######',
        '+## ### ########',
        '+## #### #######',
        '+## #### ########',
        '0## ### ####',
        '+39 0## ### ###',
        '3## ### ###',
        '+39 3## ### ###'
    );
}
