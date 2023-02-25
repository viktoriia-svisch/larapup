<?php
namespace Illuminate\Http\Resources\Json;
class AnonymousResourceCollection extends ResourceCollection
{
    public $collects;
    public function __construct($resource, $collects)
    {
        $this->collects = $collects;
        parent::__construct($resource);
    }
}
