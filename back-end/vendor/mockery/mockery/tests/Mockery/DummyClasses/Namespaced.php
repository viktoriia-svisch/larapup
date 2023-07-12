<?php
namespace Nature
{
    class Plant
    {
    }
}
namespace
{
    abstract class Gardener
    {
        abstract public function water(Nature\Plant $plant);
    }
}
