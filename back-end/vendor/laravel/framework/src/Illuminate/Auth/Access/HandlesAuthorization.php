<?php
namespace Illuminate\Auth\Access;
trait HandlesAuthorization
{
    protected function allow($message = null)
    {
        return new Response($message);
    }
    protected function deny($message = 'This action is unauthorized.')
    {
        throw new AuthorizationException($message);
    }
}
