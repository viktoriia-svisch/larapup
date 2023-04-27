<?php
namespace Monolog\Processor;
use Monolog\Logger;
class MercurialProcessor implements ProcessorInterface
{
    private $level;
    private static $cache;
    public function __construct($level = Logger::DEBUG)
    {
        $this->level = Logger::toMonologLevel($level);
    }
    public function __invoke(array $record)
    {
        if ($record['level'] < $this->level) {
            return $record;
        }
        $record['extra']['hg'] = self::getMercurialInfo();
        return $record;
    }
    private static function getMercurialInfo()
    {
        if (self::$cache) {
            return self::$cache;
        }
        $result = explode(' ', trim(`hg id -nb`));
        if (count($result) >= 3) {
            return self::$cache = array(
                'branch' => $result[1],
                'revision' => $result[2],
            );
        }
        return self::$cache = array();
    }
}
