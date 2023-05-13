<?php
namespace Illuminate\Auth\Passwords;
use Closure;
use Illuminate\Support\Arr;
use UnexpectedValueException;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
class PasswordBroker implements PasswordBrokerContract
{
    protected $tokens;
    protected $users;
    protected $passwordValidator;
    public function __construct(TokenRepositoryInterface $tokens,
                                UserProvider $users)
    {
        $this->users = $users;
        $this->tokens = $tokens;
    }
    public function sendResetLink(array $credentials)
    {
        $user = $this->getUser($credentials);
        if (is_null($user)) {
            return static::INVALID_USER;
        }
        $user->sendPasswordResetNotification(
            $this->tokens->create($user)
        );
        return static::RESET_LINK_SENT;
    }
    public function reset(array $credentials, Closure $callback)
    {
        $user = $this->validateReset($credentials);
        if (! $user instanceof CanResetPasswordContract) {
            return $user;
        }
        $password = $credentials['password'];
        $callback($user, $password);
        $this->tokens->delete($user);
        return static::PASSWORD_RESET;
    }
    protected function validateReset(array $credentials)
    {
        if (is_null($user = $this->getUser($credentials))) {
            return static::INVALID_USER;
        }
        if (! $this->validateNewPassword($credentials)) {
            return static::INVALID_PASSWORD;
        }
        if (! $this->tokens->exists($user, $credentials['token'])) {
            return static::INVALID_TOKEN;
        }
        return $user;
    }
    public function validator(Closure $callback)
    {
        $this->passwordValidator = $callback;
    }
    public function validateNewPassword(array $credentials)
    {
        if (isset($this->passwordValidator)) {
            [$password, $confirm] = [
                $credentials['password'],
                $credentials['password_confirmation'],
            ];
            return call_user_func(
                $this->passwordValidator, $credentials
            ) && $password === $confirm;
        }
        return $this->validatePasswordWithDefaults($credentials);
    }
    protected function validatePasswordWithDefaults(array $credentials)
    {
        [$password, $confirm] = [
            $credentials['password'],
            $credentials['password_confirmation'],
        ];
        return $password === $confirm && mb_strlen($password) >= 6;
    }
    public function getUser(array $credentials)
    {
        $credentials = Arr::except($credentials, ['token']);
        $user = $this->users->retrieveByCredentials($credentials);
        if ($user && ! $user instanceof CanResetPasswordContract) {
            throw new UnexpectedValueException('User must implement CanResetPassword interface.');
        }
        return $user;
    }
    public function createToken(CanResetPasswordContract $user)
    {
        return $this->tokens->create($user);
    }
    public function deleteToken(CanResetPasswordContract $user)
    {
        $this->tokens->delete($user);
    }
    public function tokenExists(CanResetPasswordContract $user, $token)
    {
        return $this->tokens->exists($user, $token);
    }
    public function getRepository()
    {
        return $this->tokens;
    }
}
