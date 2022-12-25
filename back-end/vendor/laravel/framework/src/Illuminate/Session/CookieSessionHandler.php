<?php
namespace Illuminate\Session;
use SessionHandlerInterface;
use Illuminate\Support\InteractsWithTime;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;
class CookieSessionHandler implements SessionHandlerInterface
{
    use InteractsWithTime;
    protected $cookie;
    protected $request;
    protected $minutes;
    public function __construct(CookieJar $cookie, $minutes)
    {
        $this->cookie = $cookie;
        $this->minutes = $minutes;
    }
    public function open($savePath, $sessionName)
    {
        return true;
    }
    public function close()
    {
        return true;
    }
    public function read($sessionId)
    {
        $value = $this->request->cookies->get($sessionId) ?: '';
        if (! is_null($decoded = json_decode($value, true)) && is_array($decoded)) {
            if (isset($decoded['expires']) && $this->currentTime() <= $decoded['expires']) {
                return $decoded['data'];
            }
        }
        return '';
    }
    public function write($sessionId, $data)
    {
        $this->cookie->queue($sessionId, json_encode([
            'data' => $data,
            'expires' => $this->availableAt($this->minutes * 60),
        ]), $this->minutes);
        return true;
    }
    public function destroy($sessionId)
    {
        $this->cookie->queue($this->cookie->forget($sessionId));
        return true;
    }
    public function gc($lifetime)
    {
        return true;
    }
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
}
