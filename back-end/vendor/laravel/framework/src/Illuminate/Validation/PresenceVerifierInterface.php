<?php
namespace Illuminate\Validation;
interface PresenceVerifierInterface
{
    public function getCount($collection, $column, $value, $excludeId = null, $idColumn = null, array $extra = []);
    public function getMultiCount($collection, $column, array $values, array $extra = []);
}
