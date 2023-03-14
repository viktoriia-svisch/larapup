<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node\Stmt;
class Use_ extends Stmt
{
    const TYPE_UNKNOWN = 0;
    const TYPE_NORMAL = 1;
    const TYPE_FUNCTION = 2;
    const TYPE_CONSTANT = 3;
    public $type;
    public $uses;
    public function __construct(array $uses, int $type = self::TYPE_NORMAL, array $attributes = []) {
        parent::__construct($attributes);
        $this->type = $type;
        $this->uses = $uses;
    }
    public function getSubNodeNames() : array {
        return ['type', 'uses'];
    }
    public function getType() : string {
        return 'Stmt_Use';
    }
}
