<?php
class Author
{
    public $books = [];
    private $name = '';
    public function __construct($name)
    {
        $this->name = $name;
    }
}
