<?php
namespace Nexmo\Client;
use Nexmo\Client\Exception\Exception;
class Signature
{
    protected $params;
    protected $signed;
    public function __construct(array $params, $secret, $signatureMethod)
    {
        $this->params = $params;
        $this->signed = $params;
        if(!isset($this->signed['timestamp'])){
            $this->signed['timestamp'] = time();
        }
        unset($this->signed['sig']);
        ksort($this->signed);
        $signed = [];
        foreach ($this->signed as $key => $value) {
            $signed[$key] = str_replace(array("&", "="), "_", $value);
        }
        $base = '&'.urldecode(http_build_query($signed));
        $this->signed['sig'] = $this->sign($signatureMethod, $base, $secret);
    }
    protected function sign($signatureMethod, $data, $secret) {
       switch($signatureMethod) {
            case 'md5hash':
                $data .= $secret;
                return md5($data);
                break;
            case 'md5':
            case 'sha1':
            case 'sha256':
            case 'sha512':
                return strtoupper(hash_hmac($signatureMethod, $data, $secret));
                break;
            default:
                throw new Exception('Unknown signature algorithm: '.$signatureMethod.'. Expected: md5hash, md5, sha1, sha256, or sha512');
        }
    }
    public function getParams()
    {
        return $this->params;
    }
    public function getSignature()
    {
        return $this->signed['sig'];
    }
    public function getSignedParams()
    {
        return $this->signed;
    }
    public function check($signature)
    {
        if(is_array($signature) AND isset($signature['sig'])){
            $signature = $signature['sig'];
        }
        if(!is_string($signature)){
            throw new \InvalidArgumentException('signature must be string, or present in array or parameters');
        }
        return strtolower($signature) == strtolower($this->signed['sig']);
    }
    public function __toString()
    {
        return $this->getSignature();
    }
}
