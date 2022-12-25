<?php
namespace Illuminate\Support\Facades;
use Illuminate\Support\Testing\Fakes\MailFake;
class Mail extends Facade
{
    public static function fake()
    {
        static::swap(new MailFake);
    }
    protected static function getFacadeAccessor()
    {
        return 'mailer';
    }
}
