<?php
namespace Psy\CodeCleaner;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use Psy\Exception\FatalErrorException;
class StrictTypesPass extends CodeCleanerPass
{
    const EXCEPTION_MESSAGE = 'strict_types declaration must have 0 or 1 as its value';
    private $strictTypes = false;
    private $atLeastPhp7;
    public function __construct()
    {
        $this->atLeastPhp7 = \version_compare(PHP_VERSION, '7.0', '>=');
    }
    public function beforeTraverse(array $nodes)
    {
        if (!$this->atLeastPhp7) {
            return; 
        }
        $prependStrictTypes = $this->strictTypes;
        foreach ($nodes as $key => $node) {
            if ($node instanceof Declare_) {
                foreach ($node->declares as $declare) {
                    $declareKey = $declare->key instanceof Identifier ? $declare->key->toString() : $declare->key;
                    if ($declareKey === 'strict_types') {
                        $value = $declare->value;
                        if (!$value instanceof LNumber || ($value->value !== 0 && $value->value !== 1)) {
                            throw new FatalErrorException(self::EXCEPTION_MESSAGE, 0, E_ERROR, null, $node->getLine());
                        }
                        $this->strictTypes = $value->value === 1;
                    }
                }
            }
        }
        if ($prependStrictTypes) {
            $first = \reset($nodes);
            if (!$first instanceof Declare_) {
                $declare = new Declare_([new DeclareDeclare('strict_types', new LNumber(1))]);
                \array_unshift($nodes, $declare);
            }
        }
        return $nodes;
    }
}
