<?php
namespace Ramsey\Uuid\Provider\Node;
use Ramsey\Uuid\Provider\NodeProviderInterface;
class RandomNodeProvider implements NodeProviderInterface
{
    public function getNode()
    {
        $node = hexdec(bin2hex(random_bytes(6)));
        $node = $node | 0x010000000000;
        return str_pad(dechex($node), 12, '0', STR_PAD_LEFT);
    }
}
