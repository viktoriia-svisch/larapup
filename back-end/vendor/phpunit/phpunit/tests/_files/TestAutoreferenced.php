<?php
use PHPUnit\Framework\TestCase;
class TestAutoreferenced extends TestCase
{
    public $myTestData;
    public function testJsonEncodeException($data): void
    {
        $this->myTestData = $data;
    }
}
