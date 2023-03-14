<?php
namespace Zend\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
class SapiEmitter implements EmitterInterface
{
    use SapiEmitterTrait;
    public function emit(ResponseInterface $response)
    {
        $this->assertNoPreviousOutput();
        $this->emitHeaders($response);
        $this->emitStatusLine($response);
        $this->emitBody($response);
    }
    private function emitBody(ResponseInterface $response)
    {
        echo $response->getBody();
    }
}
