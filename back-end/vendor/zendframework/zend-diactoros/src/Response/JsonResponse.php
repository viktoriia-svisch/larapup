<?php
namespace Zend\Diactoros\Response;
use InvalidArgumentException;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;
use function is_object;
use function is_resource;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function sprintf;
use const JSON_ERROR_NONE;
class JsonResponse extends Response
{
    use InjectContentTypeTrait;
    const DEFAULT_JSON_FLAGS = 79;
    private $payload;
    private $encodingOptions;
    public function __construct(
        $data,
        $status = 200,
        array $headers = [],
        $encodingOptions = self::DEFAULT_JSON_FLAGS
    ) {
        $this->setPayload($data);
        $this->encodingOptions = $encodingOptions;
        $json = $this->jsonEncode($data, $this->encodingOptions);
        $body = $this->createBodyFromJson($json);
        $headers = $this->injectContentType('application/json', $headers);
        parent::__construct($body, $status, $headers);
    }
    public function getPayload()
    {
        return $this->payload;
    }
    public function withPayload($data)
    {
        $new = clone $this;
        $new->setPayload($data);
        return $this->updateBodyFor($new);
    }
    public function getEncodingOptions()
    {
        return $this->encodingOptions;
    }
    public function withEncodingOptions($encodingOptions)
    {
        $new = clone $this;
        $new->encodingOptions = $encodingOptions;
        return $this->updateBodyFor($new);
    }
    private function createBodyFromJson($json)
    {
        $body = new Stream('php:
        $body->write($json);
        $body->rewind();
        return $body;
    }
    private function jsonEncode($data, $encodingOptions)
    {
        if (is_resource($data)) {
            throw new InvalidArgumentException('Cannot JSON encode resources');
        }
        json_encode(null);
        $json = json_encode($data, $encodingOptions);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(sprintf(
                'Unable to encode data to JSON in %s: %s',
                __CLASS__,
                json_last_error_msg()
            ));
        }
        return $json;
    }
    private function setPayload($data)
    {
        if (is_object($data)) {
            $data = clone $data;
        }
        $this->payload = $data;
    }
    private function updateBodyFor(self $toUpdate)
    {
        $json = $this->jsonEncode($toUpdate->payload, $toUpdate->encodingOptions);
        $body = $this->createBodyFromJson($json);
        return $toUpdate->withBody($body);
    }
}
