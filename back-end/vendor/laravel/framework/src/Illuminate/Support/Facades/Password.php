<?php
namespace Illuminate\Support\Facades;
class Password extends Facade
{
    const RESET_LINK_SENT = 'passwords.sent';
    const PASSWORD_RESET = 'passwords.reset';
    const INVALID_USER = 'passwords.user';
    const INVALID_PASSWORD = 'passwords.password';
    const INVALID_TOKEN = 'passwords.token';
    protected static function getFacadeAccessor()
    {
        return 'auth.password';
    }
}
