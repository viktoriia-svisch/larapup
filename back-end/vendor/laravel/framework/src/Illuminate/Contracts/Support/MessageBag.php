<?php
namespace Illuminate\Contracts\Support;
interface MessageBag extends Arrayable
{
    public function keys();
    public function add($key, $message);
    public function merge($messages);
    public function has($key);
    public function first($key = null, $format = null);
    public function get($key, $format = null);
    public function all($format = null);
    public function getMessages();
    public function getFormat();
    public function setFormat($format = ':message');
    public function isEmpty();
    public function isNotEmpty();
    public function count();
}
