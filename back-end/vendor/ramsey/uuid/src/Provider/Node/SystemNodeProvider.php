<?php
namespace Ramsey\Uuid\Provider\Node;
use Ramsey\Uuid\Provider\NodeProviderInterface;
class SystemNodeProvider implements NodeProviderInterface
{
    public function getNode()
    {
        static $node = null;
        if ($node !== null) {
            return $node;
        }
        $pattern = '/[^:]([0-9A-Fa-f]{2}([:-])[0-9A-Fa-f]{2}(\2[0-9A-Fa-f]{2}){4})[^:]/';
        $matches = array();
        $node = $this->getSysfs();
        if ($node === false) {
            if (preg_match_all($pattern, $this->getIfconfig(), $matches, PREG_PATTERN_ORDER)) {
                $node = $matches[1][0];
            }
        }
        if ($node !== false) {
            $node = str_replace([':', '-'], '', $node);
        }
        return $node;
    }
    protected function getIfconfig()
    {
        if (strpos(strtolower(ini_get('disable_functions')), 'passthru') !== false) {
            return '';
        }
        ob_start();
        switch (strtoupper(substr(php_uname('a'), 0, 3))) {
            case 'WIN':
                passthru('ipconfig /all 2>&1');
                break;
            case 'DAR':
                passthru('ifconfig 2>&1');
                break;
            case 'FRE':
                passthru('netstat -i -f link 2>&1');
                break;
            case 'LIN':
            default:
                passthru('netstat -ie 2>&1');
                break;
        }
        return ob_get_clean();
    }
    protected function getSysfs()
    {
        $mac = false;
        if (strtoupper(php_uname('s')) === 'LINUX') {
            $addressPaths = glob('/sys/class/net/*/address', GLOB_NOSORT);
            if (empty($addressPaths)) {
                return false;
            }
            array_walk($addressPaths, function ($addressPath) use (&$macs) {
                $macs[] = file_get_contents($addressPath);
            });
            $macs = array_map('trim', $macs);
            $macs = array_filter($macs, function ($mac) {
                return
                    $mac !== '00:00:00:00:00:00' &&
                    preg_match('/^([0-9a-f]{2}:){5}[0-9a-f]{2}$/i', $mac);
            });
            $mac = reset($macs);
        }
        return $mac;
    }
}
