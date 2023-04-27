<?php
namespace Opis\Closure;
function serialize($data)
{
    SerializableClosure::enterContext();
    SerializableClosure::wrapClosures($data);
    $data = \serialize($data);
    SerializableClosure::exitContext();
    return $data;
}
function unserialize($data)
{
    SerializableClosure::enterContext();
    $data = \unserialize($data);
    SerializableClosure::unwrapClosures($data);
    SerializableClosure::exitContext();
    return $data;
}
