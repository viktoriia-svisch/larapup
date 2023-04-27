<?php
namespace Lcobucci\JWT\Claim;
use Lcobucci\JWT\Claim;
class Factory
{
    private $callbacks;
    public function __construct(array $callbacks = [])
    {
        $this->callbacks = array_merge(
            [
                'iat' => [$this, 'createLesserOrEqualsTo'],
                'nbf' => [$this, 'createLesserOrEqualsTo'],
                'exp' => [$this, 'createGreaterOrEqualsTo'],
                'iss' => [$this, 'createEqualsTo'],
                'aud' => [$this, 'createEqualsTo'],
                'sub' => [$this, 'createEqualsTo'],
                'jti' => [$this, 'createEqualsTo']
            ],
            $callbacks
        );
    }
    public function create($name, $value)
    {
        if (!empty($this->callbacks[$name])) {
            return call_user_func($this->callbacks[$name], $name, $value);
        }
        return $this->createBasic($name, $value);
    }
    private function createGreaterOrEqualsTo($name, $value)
    {
        return new GreaterOrEqualsTo($name, $value);
    }
    private function createLesserOrEqualsTo($name, $value)
    {
        return new LesserOrEqualsTo($name, $value);
    }
    private function createEqualsTo($name, $value)
    {
        return new EqualsTo($name, $value);
    }
    private function createBasic($name, $value)
    {
        return new Basic($name, $value);
    }
}
