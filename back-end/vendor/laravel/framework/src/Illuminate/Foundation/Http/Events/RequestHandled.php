<?php
namespace Illuminate\Foundation\Http\Events;
class RequestHandled
{
    public $request;
    public $response;
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
