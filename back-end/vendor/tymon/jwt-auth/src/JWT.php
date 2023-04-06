<?php
namespace Tymon\JWTAuth;
use BadMethodCallException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Parser\Parser;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Support\CustomClaims;
use Tymon\JWTAuth\Exceptions\JWTException;
class JWT
{
    use CustomClaims;
    protected $manager;
    protected $parser;
    protected $token;
    protected $lockSubject = true;
    public function __construct(Manager $manager, Parser $parser)
    {
        $this->manager = $manager;
        $this->parser = $parser;
    }
    public function fromSubject(JWTSubject $subject)
    {
        $payload = $this->makePayload($subject);
        return $this->manager->encode($payload)->get();
    }
    public function fromUser(JWTSubject $user)
    {
        return $this->fromSubject($user);
    }
    public function refresh($forceForever = false, $resetClaims = false)
    {
        $this->requireToken();
        return $this->manager->customClaims($this->getCustomClaims())
                             ->refresh($this->token, $forceForever, $resetClaims)
                             ->get();
    }
    public function invalidate($forceForever = false)
    {
        $this->requireToken();
        $this->manager->invalidate($this->token, $forceForever);
        return $this;
    }
    public function checkOrFail()
    {
        return $this->getPayload();
    }
    public function check($getPayload = false)
    {
        try {
            $payload = $this->checkOrFail();
        } catch (JWTException $e) {
            return false;
        }
        return $getPayload ? $payload : true;
    }
    public function getToken()
    {
        if ($this->token === null) {
            try {
                $this->parseToken();
            } catch (JWTException $e) {
                $this->token = null;
            }
        }
        return $this->token;
    }
    public function parseToken()
    {
        if (! $token = $this->parser->parseToken()) {
            throw new JWTException('The token could not be parsed from the request');
        }
        return $this->setToken($token);
    }
    public function getPayload()
    {
        $this->requireToken();
        return $this->manager->decode($this->token);
    }
    public function payload()
    {
        return $this->getPayload();
    }
    public function getClaim($claim)
    {
        return $this->payload()->get($claim);
    }
    public function makePayload(JWTSubject $subject)
    {
        return $this->factory()->customClaims($this->getClaimsArray($subject))->make();
    }
    protected function getClaimsArray(JWTSubject $subject)
    {
        return array_merge(
            $this->getClaimsForSubject($subject),
            $subject->getJWTCustomClaims(), 
            $this->customClaims 
        );
    }
    protected function getClaimsForSubject(JWTSubject $subject)
    {
        return array_merge([
            'sub' => $subject->getJWTIdentifier(),
        ], $this->lockSubject ? ['prv' => $this->hashSubjectModel($subject)] : []);
    }
    protected function hashSubjectModel($model)
    {
        return sha1(is_object($model) ? get_class($model) : $model);
    }
    public function checkSubjectModel($model)
    {
        if (($prv = $this->payload()->get('prv')) === null) {
            return true;
        }
        return $this->hashSubjectModel($model) === $prv;
    }
    public function setToken($token)
    {
        $this->token = $token instanceof Token ? $token : new Token($token);
        return $this;
    }
    public function unsetToken()
    {
        $this->token = null;
        return $this;
    }
    protected function requireToken()
    {
        if (! $this->token) {
            throw new JWTException('A token is required');
        }
    }
    public function setRequest(Request $request)
    {
        $this->parser->setRequest($request);
        return $this;
    }
    public function lockSubject($lock)
    {
        $this->lockSubject = $lock;
        return $this;
    }
    public function manager()
    {
        return $this->manager;
    }
    public function parser()
    {
        return $this->parser;
    }
    public function factory()
    {
        return $this->manager->getPayloadFactory();
    }
    public function blacklist()
    {
        return $this->manager->getBlacklist();
    }
    public function __call($method, $parameters)
    {
        if (method_exists($this->manager, $method)) {
            return call_user_func_array([$this->manager, $method], $parameters);
        }
        throw new BadMethodCallException("Method [$method] does not exist.");
    }
}
