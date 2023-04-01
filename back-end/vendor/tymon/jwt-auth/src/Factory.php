<?php
namespace Tymon\JWTAuth;
use Tymon\JWTAuth\Claims\Claim;
use Tymon\JWTAuth\Claims\Collection;
use Tymon\JWTAuth\Support\RefreshFlow;
use Tymon\JWTAuth\Support\CustomClaims;
use Tymon\JWTAuth\Validators\PayloadValidator;
use Tymon\JWTAuth\Claims\Factory as ClaimFactory;
class Factory
{
    use CustomClaims, RefreshFlow;
    protected $claimFactory;
    protected $validator;
    protected $defaultClaims = [
        'iss',
        'iat',
        'exp',
        'nbf',
        'jti',
    ];
    protected $claims;
    public function __construct(ClaimFactory $claimFactory, PayloadValidator $validator)
    {
        $this->claimFactory = $claimFactory;
        $this->validator = $validator;
        $this->claims = new Collection;
    }
    public function make($resetClaims = false)
    {
        if ($resetClaims) {
            $this->emptyClaims();
        }
        $payload = $this->withClaims($this->buildClaimsCollection());
        return $payload;
    }
    public function emptyClaims()
    {
        $this->claims = new Collection;
        return $this;
    }
    protected function addClaims(array $claims)
    {
        foreach ($claims as $name => $value) {
            $this->addClaim($name, $value);
        }
        return $this;
    }
    protected function addClaim($name, $value)
    {
        $this->claims->put($name, $value);
        return $this;
    }
    protected function buildClaims()
    {
        if ($this->claimFactory->getTTL() === null && $key = array_search('exp', $this->defaultClaims)) {
            unset($this->defaultClaims[$key]);
        }
        foreach ($this->defaultClaims as $claim) {
            $this->addClaim($claim, $this->claimFactory->make($claim));
        }
        return $this->addClaims($this->getCustomClaims());
    }
    protected function resolveClaims()
    {
        return $this->claims->map(function ($value, $name) {
            return $value instanceof Claim ? $value : $this->claimFactory->get($name, $value);
        });
    }
    public function buildClaimsCollection()
    {
        return $this->buildClaims()->resolveClaims();
    }
    public function withClaims(Collection $claims)
    {
        return new Payload($claims, $this->validator, $this->refreshFlow);
    }
    public function setDefaultClaims(array $claims)
    {
        $this->defaultClaims = $claims;
        return $this;
    }
    public function setTTL($ttl)
    {
        $this->claimFactory->setTTL($ttl);
        return $this;
    }
    public function getTTL()
    {
        return $this->claimFactory->getTTL();
    }
    public function getDefaultClaims()
    {
        return $this->defaultClaims;
    }
    public function validator()
    {
        return $this->validator;
    }
    public function __call($method, $parameters)
    {
        $this->addClaim($method, $parameters[0]);
        return $this;
    }
}
