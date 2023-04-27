<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
class GroupUse extends Stmt
{
    public $type;
    public $prefix;
    public $uses;
    public function __construct(Name $prefix, array $uses, int $type = Use_::TYPE_NORMAL, array $attributes = []) {
        parent::__construct($attributes);
        $this->type = $type;
        $this->prefix = $prefix;
        $this->uses = $uses;
    }
    public function getSubNodeNames() : array {
        return ['type', 'prefix', 'uses'];
    }
    public function getType() : string {
        return 'Stmt_GroupUse';
    }
}
