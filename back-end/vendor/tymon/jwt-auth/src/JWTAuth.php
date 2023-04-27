<?php
namespace Tymon\JWTAuth;
use Tymon\JWTAuth\Http\Parser\Parser;
use Tymon\JWTAuth\Contracts\Providers\Auth;
class JWTAuth extends JWT
{
    protected $auth;
    public function __construct(Manager $manager, Auth $auth, Parser $parser)
    {
        parent::__construct($manager, $parser);
        $this->auth = $auth;
    }
    public function attempt(array $credentials)
    {
        if (! $this->auth->byCredentials($credentials)) {
            return false;
        }
        return $this->fromUser($this->user());
    }
    public function authenticate()
    {
        $id = $this->getPayload()->get('sub');
        if (! $this->auth->byId($id)) {
            return false;
        }
        return $this->user();
    }
    public function toUser()
    {
        return $this->authenticate();
    }
    public function user()
    {
        return $this->auth->user();
    }
}
