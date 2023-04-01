<?php
namespace Illuminate\Auth\Passwords;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
interface TokenRepositoryInterface
{
    public function create(CanResetPasswordContract $user);
    public function exists(CanResetPasswordContract $user, $token);
    public function delete(CanResetPasswordContract $user);
    public function deleteExpired();
}
