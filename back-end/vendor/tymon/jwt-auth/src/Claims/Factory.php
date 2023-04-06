<?php
namespace Tymon\JWTAuth\Claims;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Support\Utils;
class Factory
{
    protected $request;
    protected $ttl = 60;
    protected $leeway = 0;
    private $classMap = [
        'aud' => Audience::class,
        'exp' => Expiration::class,
        'iat' => IssuedAt::class,
        'iss' => Issuer::class,
        'jti' => JwtId::class,
        'nbf' => NotBefore::class,
        'sub' => Subject::class,
    ];
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function get($name, $value)
    {
        if ($this->has($name)) {
            $claim = new $this->classMap[$name]($value);
            return method_exists($claim, 'setLeeway') ?
                $claim->setLeeway($this->leeway) :
                $claim;
        }
        return new Custom($name, $value);
    }
    public function has($name)
    {
        return array_key_exists($name, $this->classMap);
    }
    public function make($name)
    {
        return $this->get($name, $this->$name());
    }
    public function iss()
    {
        return $this->request->url();
    }
    public function iat()
    {
        return Utils::now()->getTimestamp();
    }
    public function exp()
    {
        return Utils::now()->addMinutes($this->ttl)->getTimestamp();
    }
    public function nbf()
    {
        return Utils::now()->getTimestamp();
    }
    public function jti()
    {
        return Str::random();
    }
    public function extend($name, $classPath)
    {
        $this->classMap[$name] = $classPath;
        return $this;
    }
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
    public function setTTL($ttl)
    {
        $this->ttl = $ttl;
        return $this;
    }
    public function getTTL()
    {
        return $this->ttl;
    }
    public function setLeeway($leeway)
    {
        $this->leeway = $leeway;
        return $this;
    }
}
