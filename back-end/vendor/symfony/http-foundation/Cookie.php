<?php
namespace Symfony\Component\HttpFoundation;
class Cookie
{
    protected $name;
    protected $value;
    protected $domain;
    protected $expire;
    protected $path;
    protected $secure;
    protected $httpOnly;
    private $raw;
    private $sameSite;
    private $secureDefault = false;
    const SAMESITE_LAX = 'lax';
    const SAMESITE_STRICT = 'strict';
    public static function fromString($cookie, $decode = false)
    {
        $data = [
            'expires' => 0,
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'httponly' => false,
            'raw' => !$decode,
            'samesite' => null,
        ];
        $parts = HeaderUtils::split($cookie, ';=');
        $part = array_shift($parts);
        $name = $decode ? urldecode($part[0]) : $part[0];
        $value = isset($part[1]) ? ($decode ? urldecode($part[1]) : $part[1]) : null;
        $data = HeaderUtils::combine($parts) + $data;
        if (isset($data['max-age'])) {
            $data['expires'] = time() + (int) $data['max-age'];
        }
        return new static($name, $value, $data['expires'], $data['path'], $data['domain'], $data['secure'], $data['httponly'], $data['raw'], $data['samesite']);
    }
    public static function create(string $name, string $value = null, $expire = 0, ?string $path = '/', string $domain = null, bool $secure = null, bool $httpOnly = true, bool $raw = false, ?string $sameSite = self::SAMESITE_LAX): self
    {
        return new self($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
    public function __construct(string $name, string $value = null, $expire = 0, ?string $path = '/', string $domain = null, ?bool $secure = false, bool $httpOnly = true, bool $raw = false, string $sameSite = null)
    {
        if (9 > \func_num_args()) {
            @trigger_error(sprintf('The default value of the "$secure" and "$samesite" arguments of "%s"\'s constructor will respectively change from "false" to "null" and from "null" to "lax" in Symfony 5.0, you should define their values explicitly or use "Cookie::create()" instead.', __METHOD__), E_USER_DEPRECATED);
        }
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('The cookie name cannot be empty.');
        }
        if ($expire instanceof \DateTimeInterface) {
            $expire = $expire->format('U');
        } elseif (!is_numeric($expire)) {
            $expire = strtotime($expire);
            if (false === $expire) {
                throw new \InvalidArgumentException('The cookie expiration time is not valid.');
            }
        }
        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->expire = 0 < $expire ? (int) $expire : 0;
        $this->path = empty($path) ? '/' : $path;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->raw = $raw;
        if ('' === $sameSite) {
            $sameSite = null;
        } elseif (null !== $sameSite) {
            $sameSite = strtolower($sameSite);
        }
        if (!\in_array($sameSite, [self::SAMESITE_LAX, self::SAMESITE_STRICT, null], true)) {
            throw new \InvalidArgumentException('The "sameSite" parameter value is not valid.');
        }
        $this->sameSite = $sameSite;
    }
    public function __toString()
    {
        $str = ($this->isRaw() ? $this->getName() : urlencode($this->getName())).'=';
        if ('' === (string) $this->getValue()) {
            $str .= 'deleted; expires='.gmdate('D, d-M-Y H:i:s T', time() - 31536001).'; Max-Age=0';
        } else {
            $str .= $this->isRaw() ? $this->getValue() : rawurlencode($this->getValue());
            if (0 !== $this->getExpiresTime()) {
                $str .= '; expires='.gmdate('D, d-M-Y H:i:s T', $this->getExpiresTime()).'; Max-Age='.$this->getMaxAge();
            }
        }
        if ($this->getPath()) {
            $str .= '; path='.$this->getPath();
        }
        if ($this->getDomain()) {
            $str .= '; domain='.$this->getDomain();
        }
        if (true === $this->isSecure()) {
            $str .= '; secure';
        }
        if (true === $this->isHttpOnly()) {
            $str .= '; httponly';
        }
        if (null !== $this->getSameSite()) {
            $str .= '; samesite='.$this->getSameSite();
        }
        return $str;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function getDomain()
    {
        return $this->domain;
    }
    public function getExpiresTime()
    {
        return $this->expire;
    }
    public function getMaxAge()
    {
        $maxAge = $this->expire - time();
        return 0 >= $maxAge ? 0 : $maxAge;
    }
    public function getPath()
    {
        return $this->path;
    }
    public function isSecure()
    {
        return $this->secure ?? $this->secureDefault;
    }
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }
    public function isCleared()
    {
        return 0 !== $this->expire && $this->expire < time();
    }
    public function isRaw()
    {
        return $this->raw;
    }
    public function getSameSite()
    {
        return $this->sameSite;
    }
    public function setSecureDefault(bool $default): void
    {
        $this->secureDefault = $default;
    }
}
