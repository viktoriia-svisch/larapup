<?php
namespace Tymon\JWTAuth;
use Tymon\JWTAuth\Support\RefreshFlow;
use Tymon\JWTAuth\Support\CustomClaims;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Contracts\Providers\JWT as JWTContract;
class Manager
{
    use CustomClaims, RefreshFlow;
    protected $provider;
    protected $blacklist;
    protected $payloadFactory;
    protected $blacklistEnabled = true;
    protected $persistentClaims = [];
    public function __construct(JWTContract $provider, Blacklist $blacklist, Factory $payloadFactory)
    {
        $this->provider = $provider;
        $this->blacklist = $blacklist;
        $this->payloadFactory = $payloadFactory;
    }
    public function encode(Payload $payload)
    {
        $token = $this->provider->encode($payload->get());
        return new Token($token);
    }
    public function decode(Token $token, $checkBlacklist = true)
    {
        $payloadArray = $this->provider->decode($token->get());
        $payload = $this->payloadFactory
                        ->setRefreshFlow($this->refreshFlow)
                        ->customClaims($payloadArray)
                        ->make();
        if ($checkBlacklist && $this->blacklistEnabled && $this->blacklist->has($payload)) {
            throw new TokenBlacklistedException('The token has been blacklisted');
        }
        return $payload;
    }
    public function refresh(Token $token, $forceForever = false, $resetClaims = false)
    {
        $this->setRefreshFlow();
        $claims = $this->buildRefreshClaims($this->decode($token));
        if ($this->blacklistEnabled) {
            $this->invalidate($token, $forceForever);
        }
        return $this->encode(
            $this->payloadFactory->customClaims($claims)->make($resetClaims)
        );
    }
    public function invalidate(Token $token, $forceForever = false)
    {
        if (! $this->blacklistEnabled) {
            throw new JWTException('You must have the blacklist enabled to invalidate a token.');
        }
        return call_user_func(
            [$this->blacklist, $forceForever ? 'addForever' : 'add'],
            $this->decode($token, false)
        );
    }
    protected function buildRefreshClaims(Payload $payload)
    {
        extract($payload->toArray());
        return array_merge(
            $this->customClaims,
            compact($this->persistentClaims, 'sub', 'iat')
        );
    }
    public function getPayloadFactory()
    {
        return $this->payloadFactory;
    }
    public function getJWTProvider()
    {
        return $this->provider;
    }
    public function getBlacklist()
    {
        return $this->blacklist;
    }
    public function setBlacklistEnabled($enabled)
    {
        $this->blacklistEnabled = $enabled;
        return $this;
    }
    public function setPersistentClaims(array $claims)
    {
        $this->persistentClaims = $claims;
        return $this;
    }
}
