<?php
namespace Illuminate\Cache;
trait RetrievesMultipleKeys
{
    public function many(array $keys)
    {
        $return = [];
        foreach ($keys as $key) {
            $return[$key] = $this->get($key);
        }
        return $return;
    }
    public function putMany(array $values, $minutes)
    {
        foreach ($values as $key => $value) {
            $this->put($key, $value, $minutes);
        }
    }
}
