<?php
namespace Symfony\Component\VarDumper\Cloner;
use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Exception\ThrowingCasterException;
abstract class AbstractCloner implements ClonerInterface
{
    public static $defaultCasters = [
        '__PHP_Incomplete_Class' => ['Symfony\Component\VarDumper\Caster\Caster', 'castPhpIncompleteClass'],
        'Symfony\Component\VarDumper\Caster\CutStub' => ['Symfony\Component\VarDumper\Caster\StubCaster', 'castStub'],
        'Symfony\Component\VarDumper\Caster\CutArrayStub' => ['Symfony\Component\VarDumper\Caster\StubCaster', 'castCutArray'],
        'Symfony\Component\VarDumper\Caster\ConstStub' => ['Symfony\Component\VarDumper\Caster\StubCaster', 'castStub'],
        'Symfony\Component\VarDumper\Caster\EnumStub' => ['Symfony\Component\VarDumper\Caster\StubCaster', 'castEnum'],
        'Closure' => ['Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClosure'],
        'Generator' => ['Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castGenerator'],
        'ReflectionType' => ['Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castType'],
        'ReflectionGenerator' => ['Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castReflectionGenerator'],
        'ReflectionClass' => ['Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClass'],
        'ReflectionFunctionAbstract' => ['Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castFunctionAbstract'],
        'ReflectionMethod' => ['Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castMethod'],
        'ReflectionParameter' => ['Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castParameter'],
        'ReflectionProperty' => ['Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castProperty'],
        'ReflectionExtension' => ['Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castExtension'],
        'ReflectionZendExtension' => ['Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castZendExtension'],
        'Doctrine\Common\Persistence\ObjectManager' => ['Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Doctrine\Common\Proxy\Proxy' => ['Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castCommonProxy'],
        'Doctrine\ORM\Proxy\Proxy' => ['Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castOrmProxy'],
        'Doctrine\ORM\PersistentCollection' => ['Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castPersistentCollection'],
        'DOMException' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castException'],
        'DOMStringList' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNameList' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMImplementation' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castImplementation'],
        'DOMImplementationList' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNode' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castNode'],
        'DOMNameSpaceNode' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castNameSpaceNode'],
        'DOMDocument' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castDocument'],
        'DOMNodeList' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNamedNodeMap' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMCharacterData' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castCharacterData'],
        'DOMAttr' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castAttr'],
        'DOMElement' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castElement'],
        'DOMText' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castText'],
        'DOMTypeinfo' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castTypeinfo'],
        'DOMDomError' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castDomError'],
        'DOMLocator' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castLocator'],
        'DOMDocumentType' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castDocumentType'],
        'DOMNotation' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castNotation'],
        'DOMEntity' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castEntity'],
        'DOMProcessingInstruction' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castProcessingInstruction'],
        'DOMXPath' => ['Symfony\Component\VarDumper\Caster\DOMCaster', 'castXPath'],
        'XmlReader' => ['Symfony\Component\VarDumper\Caster\XmlReaderCaster', 'castXmlReader'],
        'ErrorException' => ['Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castErrorException'],
        'Exception' => ['Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castException'],
        'Error' => ['Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castError'],
        'Symfony\Component\DependencyInjection\ContainerInterface' => ['Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Symfony\Component\HttpFoundation\Request' => ['Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castRequest'],
        'Symfony\Component\VarDumper\Exception\ThrowingCasterException' => ['Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castThrowingCasterException'],
        'Symfony\Component\VarDumper\Caster\TraceStub' => ['Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castTraceStub'],
        'Symfony\Component\VarDumper\Caster\FrameStub' => ['Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castFrameStub'],
        'Symfony\Component\Debug\Exception\SilencedErrorContext' => ['Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castSilencedErrorContext'],
        'ProxyManager\Proxy\ProxyInterface' => ['Symfony\Component\VarDumper\Caster\ProxyManagerCaster', 'castProxy'],
        'PHPUnit_Framework_MockObject_MockObject' => ['Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Prophecy\Prophecy\ProphecySubjectInterface' => ['Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Mockery\MockInterface' => ['Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'PDO' => ['Symfony\Component\VarDumper\Caster\PdoCaster', 'castPdo'],
        'PDOStatement' => ['Symfony\Component\VarDumper\Caster\PdoCaster', 'castPdoStatement'],
        'AMQPConnection' => ['Symfony\Component\VarDumper\Caster\AmqpCaster', 'castConnection'],
        'AMQPChannel' => ['Symfony\Component\VarDumper\Caster\AmqpCaster', 'castChannel'],
        'AMQPQueue' => ['Symfony\Component\VarDumper\Caster\AmqpCaster', 'castQueue'],
        'AMQPExchange' => ['Symfony\Component\VarDumper\Caster\AmqpCaster', 'castExchange'],
        'AMQPEnvelope' => ['Symfony\Component\VarDumper\Caster\AmqpCaster', 'castEnvelope'],
        'ArrayObject' => ['Symfony\Component\VarDumper\Caster\SplCaster', 'castArrayObject'],
        'ArrayIterator' => ['Symfony\Component\VarDumper\Caster\SplCaster', 'castArrayIterator'],
        'SplDoublyLinkedList' => ['Symfony\Component\VarDumper\Caster\SplCaster', 'castDoublyLinkedList'],
        'SplFileInfo' => ['Symfony\Component\VarDumper\Caster\SplCaster', 'castFileInfo'],
        'SplFileObject' => ['Symfony\Component\VarDumper\Caster\SplCaster', 'castFileObject'],
        'SplFixedArray' => ['Symfony\Component\VarDumper\Caster\SplCaster', 'castFixedArray'],
        'SplHeap' => ['Symfony\Component\VarDumper\Caster\SplCaster', 'castHeap'],
        'SplObjectStorage' => ['Symfony\Component\VarDumper\Caster\SplCaster', 'castObjectStorage'],
        'SplPriorityQueue' => ['Symfony\Component\VarDumper\Caster\SplCaster', 'castHeap'],
        'OuterIterator' => ['Symfony\Component\VarDumper\Caster\SplCaster', 'castOuterIterator'],
        'Redis' => ['Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedis'],
        'RedisArray' => ['Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedisArray'],
        'RedisCluster' => ['Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedisCluster'],
        'DateTimeInterface' => ['Symfony\Component\VarDumper\Caster\DateCaster', 'castDateTime'],
        'DateInterval' => ['Symfony\Component\VarDumper\Caster\DateCaster', 'castInterval'],
        'DateTimeZone' => ['Symfony\Component\VarDumper\Caster\DateCaster', 'castTimeZone'],
        'DatePeriod' => ['Symfony\Component\VarDumper\Caster\DateCaster', 'castPeriod'],
        'GMP' => ['Symfony\Component\VarDumper\Caster\GmpCaster', 'castGmp'],
        'MessageFormatter' => ['Symfony\Component\VarDumper\Caster\IntlCaster', 'castMessageFormatter'],
        'NumberFormatter' => ['Symfony\Component\VarDumper\Caster\IntlCaster', 'castNumberFormatter'],
        'IntlTimeZone' => ['Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlTimeZone'],
        'IntlCalendar' => ['Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlCalendar'],
        'IntlDateFormatter' => ['Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlDateFormatter'],
        'Memcached' => ['Symfony\Component\VarDumper\Caster\MemcachedCaster', 'castMemcached'],
        ':curl' => ['Symfony\Component\VarDumper\Caster\ResourceCaster', 'castCurl'],
        ':dba' => ['Symfony\Component\VarDumper\Caster\ResourceCaster', 'castDba'],
        ':dba persistent' => ['Symfony\Component\VarDumper\Caster\ResourceCaster', 'castDba'],
        ':gd' => ['Symfony\Component\VarDumper\Caster\ResourceCaster', 'castGd'],
        ':mysql link' => ['Symfony\Component\VarDumper\Caster\ResourceCaster', 'castMysqlLink'],
        ':pgsql large object' => ['Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLargeObject'],
        ':pgsql link' => ['Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLink'],
        ':pgsql link persistent' => ['Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLink'],
        ':pgsql result' => ['Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castResult'],
        ':process' => ['Symfony\Component\VarDumper\Caster\ResourceCaster', 'castProcess'],
        ':stream' => ['Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStream'],
        ':persistent stream' => ['Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStream'],
        ':stream-context' => ['Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStreamContext'],
        ':xml' => ['Symfony\Component\VarDumper\Caster\XmlResourceCaster', 'castXml'],
    ];
    protected $maxItems = 2500;
    protected $maxString = -1;
    protected $minDepth = 1;
    private $casters = [];
    private $prevErrorHandler;
    private $classInfo = [];
    private $filter = 0;
    public function __construct(array $casters = null)
    {
        if (null === $casters) {
            $casters = static::$defaultCasters;
        }
        $this->addCasters($casters);
    }
    public function addCasters(array $casters)
    {
        foreach ($casters as $type => $callback) {
            $this->casters[strtolower($type)][] = \is_string($callback) && false !== strpos($callback, '::') ? explode('::', $callback, 2) : $callback;
        }
    }
    public function setMaxItems($maxItems)
    {
        $this->maxItems = (int) $maxItems;
    }
    public function setMaxString($maxString)
    {
        $this->maxString = (int) $maxString;
    }
    public function setMinDepth($minDepth)
    {
        $this->minDepth = (int) $minDepth;
    }
    public function cloneVar($var, $filter = 0)
    {
        $this->prevErrorHandler = set_error_handler(function ($type, $msg, $file, $line, $context = []) {
            if (E_RECOVERABLE_ERROR === $type || E_USER_ERROR === $type) {
                throw new \ErrorException($msg, 0, $type, $file, $line);
            }
            if ($this->prevErrorHandler) {
                return ($this->prevErrorHandler)($type, $msg, $file, $line, $context);
            }
            return false;
        });
        $this->filter = $filter;
        if ($gc = gc_enabled()) {
            gc_disable();
        }
        try {
            return new Data($this->doClone($var));
        } finally {
            if ($gc) {
                gc_enable();
            }
            restore_error_handler();
            $this->prevErrorHandler = null;
        }
    }
    abstract protected function doClone($var);
    protected function castObject(Stub $stub, $isNested)
    {
        $obj = $stub->value;
        $class = $stub->class;
        if (isset($class[15]) && "\0" === $class[15] && 0 === strpos($class, "class@anonymous\x00")) {
            $stub->class = get_parent_class($class).'@anonymous';
        }
        if (isset($this->classInfo[$class])) {
            list($i, $parents, $hasDebugInfo) = $this->classInfo[$class];
        } else {
            $i = 2;
            $parents = [strtolower($class)];
            $hasDebugInfo = method_exists($class, '__debugInfo');
            foreach (class_parents($class) as $p) {
                $parents[] = strtolower($p);
                ++$i;
            }
            foreach (class_implements($class) as $p) {
                $parents[] = strtolower($p);
                ++$i;
            }
            $parents[] = '*';
            $this->classInfo[$class] = [$i, $parents, $hasDebugInfo];
        }
        $a = Caster::castObject($obj, $class, $hasDebugInfo);
        try {
            while ($i--) {
                if (!empty($this->casters[$p = $parents[$i]])) {
                    foreach ($this->casters[$p] as $callback) {
                        $a = $callback($obj, $a, $stub, $isNested, $this->filter);
                    }
                }
            }
        } catch (\Exception $e) {
            $a = [(Stub::TYPE_OBJECT === $stub->type ? Caster::PREFIX_VIRTUAL : '').'⚠' => new ThrowingCasterException($e)] + $a;
        }
        return $a;
    }
    protected function castResource(Stub $stub, $isNested)
    {
        $a = [];
        $res = $stub->value;
        $type = $stub->class;
        try {
            if (!empty($this->casters[':'.$type])) {
                foreach ($this->casters[':'.$type] as $callback) {
                    $a = $callback($res, $a, $stub, $isNested, $this->filter);
                }
            }
        } catch (\Exception $e) {
            $a = [(Stub::TYPE_OBJECT === $stub->type ? Caster::PREFIX_VIRTUAL : '').'⚠' => new ThrowingCasterException($e)] + $a;
        }
        return $a;
    }
}
