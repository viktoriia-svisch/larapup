<?php
namespace Symfony\Component\HttpFoundation;
class JsonResponse extends Response
{
    protected $data;
    protected $callback;
    const DEFAULT_ENCODING_OPTIONS = 15;
    protected $encodingOptions = self::DEFAULT_ENCODING_OPTIONS;
    public function __construct($data = null, int $status = 200, array $headers = [], bool $json = false)
    {
        parent::__construct('', $status, $headers);
        if (null === $data) {
            $data = new \ArrayObject();
        }
        $json ? $this->setJson($data) : $this->setData($data);
    }
    public static function create($data = null, $status = 200, $headers = [])
    {
        return new static($data, $status, $headers);
    }
    public static function fromJsonString($data = null, $status = 200, $headers = [])
    {
        return new static($data, $status, $headers, true);
    }
    public function setCallback($callback = null)
    {
        if (null !== $callback) {
            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*(?:\[(?:"(?:\\\.|[^"\\\])*"|\'(?:\\\.|[^\'\\\])*\'|\d+)\])*?$/u';
            $reserved = [
                'break', 'do', 'instanceof', 'typeof', 'case', 'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 'for', 'switch', 'while',
                'debugger', 'function', 'this', 'with', 'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 'extends', 'super',  'const', 'export',
                'import', 'implements', 'let', 'private', 'public', 'yield', 'interface', 'package', 'protected', 'static', 'null', 'true', 'false',
            ];
            $parts = explode('.', $callback);
            foreach ($parts as $part) {
                if (!preg_match($pattern, $part) || \in_array($part, $reserved, true)) {
                    throw new \InvalidArgumentException('The callback name is not valid.');
                }
            }
        }
        $this->callback = $callback;
        return $this->update();
    }
    public function setJson($json)
    {
        $this->data = $json;
        return $this->update();
    }
    public function setData($data = [])
    {
        try {
            $data = json_encode($data, $this->encodingOptions);
        } catch (\Exception $e) {
            if ('Exception' === \get_class($e) && 0 === strpos($e->getMessage(), 'Failed calling ')) {
                throw $e->getPrevious() ?: $e;
            }
            throw $e;
        }
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }
        return $this->setJson($data);
    }
    public function getEncodingOptions()
    {
        return $this->encodingOptions;
    }
    public function setEncodingOptions($encodingOptions)
    {
        $this->encodingOptions = (int) $encodingOptions;
        return $this->setData(json_decode($this->data));
    }
    protected function update()
    {
        if (null !== $this->callback) {
            $this->headers->set('Content-Type', 'text/javascript');
            return $this->setContent(sprintf('%s(%s);', $this->callback, $this->data));
        }
        if (!$this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            $this->headers->set('Content-Type', 'application/json');
        }
        return $this->setContent($this->data);
    }
}
