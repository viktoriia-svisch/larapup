<?php
namespace PHPUnit\Framework\MockObject\Builder;
interface NamespaceMatch
{
    public function lookupId($id);
    public function registerId($id, Match $builder);
}
