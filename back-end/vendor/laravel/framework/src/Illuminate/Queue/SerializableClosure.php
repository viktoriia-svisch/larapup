<?php
namespace Illuminate\Queue;
use Opis\Closure\SerializableClosure as OpisSerializableClosure;
class SerializableClosure extends OpisSerializableClosure
{
    use SerializesAndRestoresModelIdentifiers;
    protected function transformUseVariables($data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->getSerializedPropertyValue($value);
        }
        return $data;
    }
    protected function resolveUseVariables($data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->getRestoredPropertyValue($value);
        }
        return $data;
    }
}
