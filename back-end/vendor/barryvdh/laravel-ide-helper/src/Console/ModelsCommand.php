<?php
namespace Barryvdh\LaravelIdeHelper\Console;
use Composer\Autoload\ClassMapGenerator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Context;
use Barryvdh\Reflection\DocBlock\Tag;
use Barryvdh\Reflection\DocBlock\Serializer as DocBlockSerializer;
class ModelsCommand extends Command
{
    protected $files;
    protected $name = 'ide-helper:models';
    protected $filename = '_ide_helper_models.php';
    protected $description = 'Generate autocompletion for models';
    protected $write_model_magic_where;
    protected $properties = array();
    protected $methods = array();
    protected $write = false;
    protected $dirs = array();
    protected $reset;
    protected $keep_text;
    protected $nullableColumns = [];
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }
    public function handle()
    {
        $filename = $this->option('filename');
        $this->write = $this->option('write');
        $this->dirs = array_merge(
            $this->laravel['config']->get('ide-helper.model_locations'),
            $this->option('dir')
        );
        $model = $this->argument('model');
        $ignore = $this->option('ignore');
        $this->reset = $this->option('reset');
        if ($this->option('smart-reset')) {
            $this->keep_text = $this->reset = true;
        }
        $this->write_model_magic_where = $this->laravel['config']->get('ide-helper.write_model_magic_where', true);
        if (!$this->write && $filename === $this->filename && !$this->option('nowrite')) {
            if ($this->confirm(
                "Do you want to overwrite the existing model files? Choose no to write to $filename instead?"
            )
            ) {
                $this->write = true;
            }
        }
        $content = $this->generateDocs($model, $ignore);
        if (!$this->write) {
            $written = $this->files->put($filename, $content);
            if ($written !== false) {
                $this->info("Model information was written to $filename");
            } else {
                $this->error("Failed to write model information to $filename");
            }
        }
    }
    protected function getArguments()
    {
        return array(
          array('model', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Which models to include', array()),
        );
    }
    protected function getOptions()
    {
        return array(
          array('filename', 'F', InputOption::VALUE_OPTIONAL, 'The path to the helper file', $this->filename),
          array('dir', 'D', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The model dir', array()),
          array('write', 'W', InputOption::VALUE_NONE, 'Write to Model file'),
          array('nowrite', 'N', InputOption::VALUE_NONE, 'Don\'t write to Model file'),
          array('reset', 'R', InputOption::VALUE_NONE, 'Remove the original phpdocs instead of appending'),
          array('smart-reset', 'r', InputOption::VALUE_NONE, 'Refresh the properties/methods list, but keep the text'),
          array('ignore', 'I', InputOption::VALUE_OPTIONAL, 'Which models to ignore', ''),
        );
    }
    protected function generateDocs($loadModels, $ignore = '')
    {
        $output = "<?php
\n\n";
        $hasDoctrine = interface_exists('Doctrine\DBAL\Driver');
        if (empty($loadModels)) {
            $models = $this->loadModels();
        } else {
            $models = array();
            foreach ($loadModels as $model) {
                $models = array_merge($models, explode(',', $model));
            }
        }
        $ignore = explode(',', $ignore);
        foreach ($models as $name) {
            if (in_array($name, $ignore)) {
                if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->comment("Ignoring model '$name'");
                }
                continue;
            }
            $this->properties = array();
            $this->methods = array();
            if (class_exists($name)) {
                try {
                    $reflectionClass = new ReflectionClass($name);
                    if (!$reflectionClass->isSubclassOf('Illuminate\Database\Eloquent\Model')) {
                        continue;
                    }
                    if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        $this->comment("Loading model '$name'");
                    }
                    if (!$reflectionClass->IsInstantiable()) {
                        continue;
                    }
                    $model = $this->laravel->make($name);
                    if ($hasDoctrine) {
                        $this->getPropertiesFromTable($model);
                    }
                    if (method_exists($model, 'getCasts')) {
                        $this->castPropertiesType($model);
                    }
                    $this->getPropertiesFromMethods($model);
                    $this->getSoftDeleteMethods($model);
                    $output                .= $this->createPhpDocs($name);
                    $ignore[]              = $name;
                    $this->nullableColumns = [];
                } catch (\Exception $e) {
                    $this->error("Exception: " . $e->getMessage() . "\nCould not analyze class $name.");
                }
            }
        }
        if (!$hasDoctrine) {
            $this->error(
                'Warning: `"doctrine/dbal": "~2.3"` is required to load database information. '.
                'Please require that in your composer.json and run `composer update`.'
            );
        }
        return $output;
    }
    protected function loadModels()
    {
        $models = array();
        foreach ($this->dirs as $dir) {
            $dir = base_path() . '/' . $dir;
            if (file_exists($dir)) {
                foreach (ClassMapGenerator::createMap($dir) as $model => $path) {
                    $models[] = $model;
                }
            }
        }
        return $models;
    }
    protected function castPropertiesType($model)
    {
        $casts = $model->getCasts();
        foreach ($casts as $name => $type) {
            switch ($type) {
                case 'boolean':
                case 'bool':
                    $realType = 'boolean';
                    break;
                case 'string':
                    $realType = 'string';
                    break;
                case 'array':
                case 'json':
                    $realType = 'array';
                    break;
                case 'object':
                    $realType = 'object';
                    break;
                case 'int':
                case 'integer':
                case 'timestamp':
                    $realType = 'integer';
                    break;
                case 'real':
                case 'double':
                case 'float':
                    $realType = 'float';
                    break;
                case 'date':
                case 'datetime':
                    $realType = '\Illuminate\Support\Carbon';
                    break;
                case 'collection':
                    $realType = '\Illuminate\Support\Collection';
                    break;
                default:
                    $realType = 'mixed';
                    break;
            }
            if (!isset($this->properties[$name])) {
                continue;
            } else {
                $this->properties[$name]['type'] = $this->getTypeOverride($realType);
                if (isset($this->nullableColumns[$name])) {
                    $this->properties[$name]['type'] .= '|null';
                }
            }
        }
    }
    protected function getTypeOverride($type)
    {
        $typeOverrides = $this->laravel['config']->get('ide-helper.type_overrides', array());
        return isset($typeOverrides[$type]) ? $typeOverrides[$type] : $type;
    }
    protected function getPropertiesFromTable($model)
    {
        $table = $model->getConnection()->getTablePrefix() . $model->getTable();
        $schema = $model->getConnection()->getDoctrineSchemaManager($table);
        $databasePlatform = $schema->getDatabasePlatform();
        $databasePlatform->registerDoctrineTypeMapping('enum', 'string');
        $platformName = $databasePlatform->getName();
        $customTypes = $this->laravel['config']->get("ide-helper.custom_db_types.{$platformName}", array());
        foreach ($customTypes as $yourTypeName => $doctrineTypeName) {
            $databasePlatform->registerDoctrineTypeMapping($yourTypeName, $doctrineTypeName);
        }
        $database = null;
        if (strpos($table, '.')) {
            list($database, $table) = explode('.', $table);
        }
        $columns = $schema->listTableColumns($table, $database);
        if ($columns) {
            foreach ($columns as $column) {
                $name = $column->getName();
                if (in_array($name, $model->getDates())) {
                    $type = '\Illuminate\Support\Carbon';
                } else {
                    $type = $column->getType()->getName();
                    switch ($type) {
                        case 'string':
                        case 'text':
                        case 'date':
                        case 'time':
                        case 'guid':
                        case 'datetimetz':
                        case 'datetime':
                            $type = 'string';
                            break;
                        case 'integer':
                        case 'bigint':
                        case 'smallint':
                            $type = 'integer';
                            break;
                        case 'boolean':
                            switch (config('database.default')) {
                                case 'sqlite':
                                case 'mysql':
                                    $type = 'integer';
                                    break;
                                default:
                                    $type = 'boolean';
                                    break;
                            }
                            break;
                        case 'decimal':
                        case 'float':
                            $type = 'float';
                            break;
                        default:
                            $type = 'mixed';
                            break;
                    }
                }
                $comment = $column->getComment();
                if (!$column->getNotnull()) {
                    $this->nullableColumns[$name] = true;
                }
                $this->setProperty($name, $type, true, true, $comment, !$column->getNotnull());
                if ($this->write_model_magic_where) {
                    $this->setMethod(
                        Str::camel("where_" . $name),
                        '\Illuminate\Database\Eloquent\Builder|\\' . get_class($model),
                        array('$value')
                    );
                }
            }
        }
    }
    protected function getPropertiesFromMethods($model)
    {
        $methods = get_class_methods($model);
        if ($methods) {
            sort($methods);
            foreach ($methods as $method) {
                if (Str::startsWith($method, 'get') && Str::endsWith(
                    $method,
                    'Attribute'
                ) && $method !== 'getAttribute'
                ) {
                    $name = Str::snake(substr($method, 3, -9));
                    if (!empty($name)) {
                        $reflection = new \ReflectionMethod($model, $method);
                        $type = $this->getReturnTypeFromDocBlock($reflection);
                        $this->setProperty($name, $type, true, null);
                    }
                } elseif (Str::startsWith($method, 'set') && Str::endsWith(
                    $method,
                    'Attribute'
                ) && $method !== 'setAttribute'
                ) {
                    $name = Str::snake(substr($method, 3, -9));
                    if (!empty($name)) {
                        $this->setProperty($name, null, null, true);
                    }
                } elseif (Str::startsWith($method, 'scope') && $method !== 'scopeQuery') {
                    $name = Str::camel(substr($method, 5));
                    if (!empty($name)) {
                        $reflection = new \ReflectionMethod($model, $method);
                        $args = $this->getParameters($reflection);
                        array_shift($args);
                        $this->setMethod($name, '\Illuminate\Database\Eloquent\Builder|\\' . $reflection->class, $args);
                    }
                } elseif (in_array($method, ['query', 'newQuery', 'newModelQuery'])) {
                    $reflection = new \ReflectionClass($model);
                    $this->setMethod($method, '\Illuminate\Database\Eloquent\Builder|\\' . $reflection->getName());
                } elseif (!method_exists('Illuminate\Database\Eloquent\Model', $method)
                    && !Str::startsWith($method, 'get')
                ) {
                    $reflection = new \ReflectionMethod($model, $method);
                    $type = (string) $reflection->getReturnType() ?: (string)$this->getReturnTypeFromDocBlock($reflection);
                    $file = new \SplFileObject($reflection->getFileName());
                    $file->seek($reflection->getStartLine() - 1);
                    $code = '';
                    while ($file->key() < $reflection->getEndLine()) {
                        $code .= $file->current();
                        $file->next();
                    }
                    $code = trim(preg_replace('/\s\s+/', '', $code));
                    $begin = strpos($code, 'function(');
                    $code = substr($code, $begin, strrpos($code, '}') - $begin + 1);
                    foreach (array(
                               'hasMany' => '\Illuminate\Database\Eloquent\Relations\HasMany',
                               'hasManyThrough' => '\Illuminate\Database\Eloquent\Relations\HasManyThrough',
                               'belongsToMany' => '\Illuminate\Database\Eloquent\Relations\BelongsToMany',
                               'hasOne' => '\Illuminate\Database\Eloquent\Relations\HasOne',
                               'belongsTo' => '\Illuminate\Database\Eloquent\Relations\BelongsTo',
                               'morphOne' => '\Illuminate\Database\Eloquent\Relations\MorphOne',
                               'morphTo' => '\Illuminate\Database\Eloquent\Relations\MorphTo',
                               'morphMany' => '\Illuminate\Database\Eloquent\Relations\MorphMany',
                               'morphToMany' => '\Illuminate\Database\Eloquent\Relations\MorphToMany',
                               'morphedByMany' => '\Illuminate\Database\Eloquent\Relations\MorphToMany'
                             ) as $relation => $impl) {
                        $search = '$this->' . $relation . '(';
                        if (stripos($code, $search) || stripos($impl, (string)$type) !== false) {
                            $methodReflection = new \ReflectionMethod($model, $method);
                            if ($methodReflection->getNumberOfParameters()) {
                                continue;
                            }
                            $relationObj = $model->$method();
                            if ($relationObj instanceof Relation) {
                                $relatedModel = '\\' . get_class($relationObj->getRelated());
                                $relations = [
                                    'hasManyThrough',
                                    'belongsToMany',
                                    'hasMany',
                                    'morphMany',
                                    'morphToMany',
                                    'morphedByMany',
                                ];
                                if (in_array($relation, $relations)) {
                                    $this->setProperty(
                                        $method,
                                        $this->getCollectionClass($relatedModel) . '|' . $relatedModel . '[]',
                                        true,
                                        null
                                    );
                                } elseif ($relation === "morphTo") {
                                    $this->setProperty(
                                        $method,
                                        '\Illuminate\Database\Eloquent\Model|\Eloquent',
                                        true,
                                        null
                                    );
                                } else {
                                    $this->setProperty(
                                        $method,
                                        $relatedModel,
                                        true,
                                        null,
                                        '',
                                        $this->isRelationForeignKeyNullable($relationObj)
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    private function isRelationForeignKeyNullable(Relation $relation)
    {
        $reflectionObj = new \ReflectionObject($relation);
        if (!$reflectionObj->hasProperty('foreignKey')) {
            return false;
        }
        $fkProp = $reflectionObj->getProperty('foreignKey');
        $fkProp->setAccessible(true);
        return isset($this->nullableColumns[$fkProp->getValue($relation)]);
    }
    protected function setProperty($name, $type = null, $read = null, $write = null, $comment = '', $nullable = false)
    {
        if (!isset($this->properties[$name])) {
            $this->properties[$name] = array();
            $this->properties[$name]['type'] = 'mixed';
            $this->properties[$name]['read'] = false;
            $this->properties[$name]['write'] = false;
            $this->properties[$name]['comment'] = (string) $comment;
        }
        if ($type !== null) {
            $newType = $this->getTypeOverride($type);
            if ($nullable) {
                $newType .='|null';
            }
            $this->properties[$name]['type'] = $newType;
        }
        if ($read !== null) {
            $this->properties[$name]['read'] = $read;
        }
        if ($write !== null) {
            $this->properties[$name]['write'] = $write;
        }
    }
    protected function setMethod($name, $type = '', $arguments = array())
    {
        $methods = array_change_key_case($this->methods, CASE_LOWER);
        if (!isset($methods[strtolower($name)])) {
            $this->methods[$name] = array();
            $this->methods[$name]['type'] = $type;
            $this->methods[$name]['arguments'] = $arguments;
        }
    }
    protected function createPhpDocs($class)
    {
        $reflection = new ReflectionClass($class);
        $namespace = $reflection->getNamespaceName();
        $classname = $reflection->getShortName();
        $originalDoc = $reflection->getDocComment();
        $keyword = $this->getClassKeyword($reflection);
        if ($this->reset) {
            $phpdoc = new DocBlock('', new Context($namespace));
            if ($this->keep_text) {
                $phpdoc->setText(
                    (new DocBlock($reflection, new Context($namespace)))->getText()
                );
            }
        } else {
            $phpdoc = new DocBlock($reflection, new Context($namespace));
        }
        if (!$phpdoc->getText()) {
            $phpdoc->setText($class);
        }
        $properties = array();
        $methods = array();
        foreach ($phpdoc->getTags() as $tag) {
            $name = $tag->getName();
            if ($name == "property" || $name == "property-read" || $name == "property-write") {
                $properties[] = $tag->getVariableName();
            } elseif ($name == "method") {
                $methods[] = $tag->getMethodName();
            }
        }
        foreach ($this->properties as $name => $property) {
            $name = "\$$name";
            if (in_array($name, $properties)) {
                continue;
            }
            if ($property['read'] && $property['write']) {
                $attr = 'property';
            } elseif ($property['write']) {
                $attr = 'property-write';
            } else {
                $attr = 'property-read';
            }
            if ($this->hasCamelCaseModelProperties()) {
                $name = Str::camel($name);
            }
            $tagLine = trim("@{$attr} {$property['type']} {$name} {$property['comment']}");
            $tag = Tag::createInstance($tagLine, $phpdoc);
            $phpdoc->appendTag($tag);
        }
        ksort($this->methods);
        foreach ($this->methods as $name => $method) {
            if (in_array($name, $methods)) {
                continue;
            }
            $arguments = implode(', ', $method['arguments']);
            $tag = Tag::createInstance("@method static {$method['type']} {$name}({$arguments})", $phpdoc);
            $phpdoc->appendTag($tag);
        }
        if ($this->write && ! $phpdoc->getTagsByName('mixin')) {
            $phpdoc->appendTag(Tag::createInstance("@mixin \\Eloquent", $phpdoc));
        }
        $serializer = new DocBlockSerializer();
        $serializer->getDocComment($phpdoc);
        $docComment = $serializer->getDocComment($phpdoc);
        if ($this->write) {
            $filename = $reflection->getFileName();
            $contents = $this->files->get($filename);
            if ($originalDoc) {
                $contents = str_replace($originalDoc, $docComment, $contents);
            } else {
                $needle = "class {$classname}";
                $replace = "{$docComment}\nclass {$classname}";
                $pos = strpos($contents, $needle);
                if ($pos !== false) {
                    $contents = substr_replace($contents, $replace, $pos, strlen($needle));
                }
            }
            if ($this->files->put($filename, $contents)) {
                $this->info('Written new phpDocBlock to ' . $filename);
            }
        }
        $output = "namespace {$namespace}{\n{$docComment}\n\t{$keyword}class {$classname} extends \Eloquent {}\n}\n\n";
        return $output;
    }
    public function getParameters($method)
    {
        $params = array();
        $paramsWithDefault = array();
        foreach ($method->getParameters() as $param) {
            $paramClass = $param->getClass();
            $paramStr = (!is_null($paramClass) ? '\\' . $paramClass->getName() . ' ' : '') . '$' . $param->getName();
            $params[] = $paramStr;
            if ($param->isOptional() && $param->isDefaultValueAvailable()) {
                $default = $param->getDefaultValue();
                if (is_bool($default)) {
                    $default = $default ? 'true' : 'false';
                } elseif (is_array($default)) {
                    $default = 'array()';
                } elseif (is_null($default)) {
                    $default = 'null';
                } elseif (is_int($default)) {
                } else {
                    $default = "'" . trim($default) . "'";
                }
                $paramStr .= " = $default";
            }
            $paramsWithDefault[] = $paramStr;
        }
        return $paramsWithDefault;
    }
    private function getCollectionClass($className)
    {
        if (!method_exists($className, 'newCollection')) {
            return '\Illuminate\Database\Eloquent\Collection';
        }
        $model = new $className;
        return '\\' . get_class($model->newCollection());
    }
    protected function hasCamelCaseModelProperties()
    {
        return $this->laravel['config']->get('ide-helper.model_camel_case_properties', false);
    }
    protected function getReturnTypeFromDocBlock(\ReflectionMethod $reflection)
    {
        $type = null;
        $phpdoc = new DocBlock($reflection);
        if ($phpdoc->hasTag('return')) {
            $type = $phpdoc->getTagsByName('return')[0]->getType();
        }
        return $type;
    }
    protected function getSoftDeleteMethods($model)
    {
        $traits = class_uses(get_class($model), true);
        if (in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', $traits)) {
            $this->setMethod('forceDelete', 'bool|null', []);
            $this->setMethod('restore', 'bool|null', []);
            $this->setMethod('withTrashed', '\Illuminate\Database\Query\Builder|\\' . get_class($model), []);
            $this->setMethod('withoutTrashed', '\Illuminate\Database\Query\Builder|\\' . get_class($model), []);
            $this->setMethod('onlyTrashed', '\Illuminate\Database\Query\Builder|\\' . get_class($model), []);
        }
    }
    private function getClassKeyword(ReflectionClass $reflection)
    {
        if ($reflection->isFinal()) {
            $keyword = 'final ';
        } elseif ($reflection->isAbstract()) {
            $keyword = 'abstract ';
        } else {
            $keyword = '';
        }
        return $keyword;
    }
}
