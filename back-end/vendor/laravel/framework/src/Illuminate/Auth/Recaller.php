<?php
namespace Illuminate\Auth;
use Illuminate\Support\Str;
class Recaller
{
    protected $recaller;
    public function __construct($recaller)
    {
        $this->recaller = @unserialize($recaller, ['allowed_classes' => false]) ?: $recaller;
    }
    public function id()
    {
        return explode('|', $this->recaller, 3)[0];
    }
    public function token()
    {
        return explode('|', $this->recaller, 3)[1];
    }
    public function hash()
    {
        return explode('|', $this->recaller, 3)[2];
    }
    public function valid()
    {
        return $this->properString() && $this->hasAllSegments();
    }
    protected function properString()
    {
        return is_string($this->recaller) && Str::contains($this->recaller, '|');
    }
    protected function hasAllSegments()
    {
        $segments = explode('|', $this->recaller);
        return count($segments) === 3 && trim($segments[0]) !== '' && trim($segments[1]) !== '';
    }
}
