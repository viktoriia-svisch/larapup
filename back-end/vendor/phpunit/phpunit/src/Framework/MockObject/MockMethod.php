<?php declare(strict_types=1);
namespace PHPUnit\Framework\MockObject;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Text_Template;
final class MockMethod
{
    private static $templates = [];
    private $className;
    private $methodName;
    private $cloneArguments;
    private $modifier;
    private $argumentsForDeclaration;
    private $argumentsForCall;
    private $returnType;
    private $reference;
    private $callOriginalMethod;
    private $static;
    private $deprecation;
    private $allowsReturnNull;
    public static function fromReflection(ReflectionMethod $method, bool $callOriginalMethod, bool $cloneArguments): self
    {
        if ($method->isPrivate()) {
            $modifier = 'private';
        } elseif ($method->isProtected()) {
            $modifier = 'protected';
        } else {
            $modifier = 'public';
        }
        if ($method->isStatic()) {
            $modifier .= ' static';
        }
        if ($method->returnsReference()) {
            $reference = '&';
        } else {
            $reference = '';
        }
        if ($method->hasReturnType()) {
            $returnType = (string) $method->getReturnType();
        } else {
            $returnType = '';
        }
        $docComment = $method->getDocComment();
        if (\is_string($docComment)
            && \preg_match('#\*[ \t]*+@deprecated[ \t]*+(.*?)\r?+\n[ \t]*+\*(?:[ \t]*+@|/$)#s', $docComment, $deprecation)
        ) {
            $deprecation = \trim(\preg_replace('#[ \t]*\r?\n[ \t]*+\*[ \t]*+#', ' ', $deprecation[1]));
        } else {
            $deprecation = null;
        }
        return new self(
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            $cloneArguments,
            $modifier,
            self::getMethodParameters($method),
            self::getMethodParameters($method, true),
            $returnType,
            $reference,
            $callOriginalMethod,
            $method->isStatic(),
            $deprecation,
            $method->hasReturnType() && $method->getReturnType()->allowsNull()
        );
    }
    public static function fromName(string $fullClassName, string $methodName, bool $cloneArguments): self
    {
        return new self(
            $fullClassName,
            $methodName,
            $cloneArguments,
            'public',
            '',
            '',
            '',
            '',
            false,
            false,
            null,
            false
        );
    }
    public function __construct(string $className, string $methodName, bool $cloneArguments, string $modifier, string $argumentsForDeclaration, string $argumentsForCall, string $returnType, string $reference, bool $callOriginalMethod, bool $static, ?string $deprecation, bool $allowsReturnNull)
    {
        $this->className               = $className;
        $this->methodName              = $methodName;
        $this->cloneArguments          = $cloneArguments;
        $this->modifier                = $modifier;
        $this->argumentsForDeclaration = $argumentsForDeclaration;
        $this->argumentsForCall        = $argumentsForCall;
        $this->returnType              = $returnType;
        $this->reference               = $reference;
        $this->callOriginalMethod      = $callOriginalMethod;
        $this->static                  = $static;
        $this->deprecation             = $deprecation;
        $this->allowsReturnNull        = $allowsReturnNull;
    }
    public function getName(): string
    {
        return $this->methodName;
    }
    public function generateCode(): string
    {
        if ($this->static) {
            $templateFile = 'mocked_static_method.tpl';
        } elseif ($this->returnType === 'void') {
            $templateFile = \sprintf(
                '%s_method_void.tpl',
                $this->callOriginalMethod ? 'proxied' : 'mocked'
            );
        } else {
            $templateFile = \sprintf(
                '%s_method.tpl',
                $this->callOriginalMethod ? 'proxied' : 'mocked'
            );
        }
        $returnType = $this->returnType;
        if ($returnType === 'self') {
            $returnType = $this->className;
        }
        if ($returnType === 'parent') {
            $reflector = new ReflectionClass($this->className);
            $parentClass = $reflector->getParentClass();
            if ($parentClass === false) {
                throw new RuntimeException(
                    \sprintf(
                        'Cannot mock %s::%s because "parent" return type declaration is used but %s does not have a parent class',
                        $this->className,
                        $this->methodName,
                        $this->className
                    )
                );
            }
            $returnType = $parentClass->getName();
        }
        $deprecation = $this->deprecation;
        if (null !== $this->deprecation) {
            $deprecation         = "The $this->className::$this->methodName method is deprecated ($this->deprecation).";
            $deprecationTemplate = $this->getTemplate('deprecation.tpl');
            $deprecationTemplate->setVar([
                'deprecation' => \var_export($deprecation, true),
            ]);
            $deprecation = $deprecationTemplate->render();
        }
        $template = $this->getTemplate($templateFile);
        $template->setVar(
            [
                'arguments_decl'  => $this->argumentsForDeclaration,
                'arguments_call'  => $this->argumentsForCall,
                'return_delim'    => $returnType ? ': ' : '',
                'return_type'     => $this->allowsReturnNull ? '?' . $returnType : $returnType,
                'arguments_count' => !empty($this->argumentsForCall) ? \substr_count($this->argumentsForCall, ',') + 1 : 0,
                'class_name'      => $this->className,
                'method_name'     => $this->methodName,
                'modifier'        => $this->modifier,
                'reference'       => $this->reference,
                'clone_arguments' => $this->cloneArguments ? 'true' : 'false',
                'deprecation'     => $deprecation,
            ]
        );
        return $template->render();
    }
    private function getTemplate(string $template): Text_Template
    {
        $filename = __DIR__ . \DIRECTORY_SEPARATOR . 'Generator' . \DIRECTORY_SEPARATOR . $template;
        if (!isset(self::$templates[$filename])) {
            self::$templates[$filename] = new Text_Template($filename);
        }
        return self::$templates[$filename];
    }
    private static function getMethodParameters(ReflectionMethod $method, bool $forCall = false): string
    {
        $parameters = [];
        foreach ($method->getParameters() as $i => $parameter) {
            $name = '$' . $parameter->getName();
            if ($name === '$' || $name === '$...') {
                $name = '$arg' . $i;
            }
            if ($parameter->isVariadic()) {
                if ($forCall) {
                    continue;
                }
                $name = '...' . $name;
            }
            $nullable        = '';
            $default         = '';
            $reference       = '';
            $typeDeclaration = '';
            if (!$forCall) {
                if ($parameter->hasType() && $parameter->allowsNull()) {
                    $nullable = '?';
                }
                if ($parameter->hasType() && (string) $parameter->getType() !== 'self') {
                    $typeDeclaration = $parameter->getType() . ' ';
                } else {
                    try {
                        $class = $parameter->getClass();
                    } catch (ReflectionException $e) {
                        throw new RuntimeException(
                            \sprintf(
                                'Cannot mock %s::%s() because a class or ' .
                                'interface used in the signature is not loaded',
                                $method->getDeclaringClass()->getName(),
                                $method->getName()
                            ),
                            0,
                            $e
                        );
                    }
                    if ($class !== null) {
                        $typeDeclaration = $class->getName() . ' ';
                    }
                }
                if (!$parameter->isVariadic()) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $value = $parameter->getDefaultValueConstantName();
                        if ($value === null) {
                            $value = \var_export($parameter->getDefaultValue(), true);
                        } elseif (!\defined($value)) {
                            $rootValue = \preg_replace('/^.*\\\\/', '', $value);
                            $value     = \defined($rootValue) ? $rootValue : $value;
                        }
                        $default = ' = ' . $value;
                    } elseif ($parameter->isOptional()) {
                        $default = ' = null';
                    }
                }
            }
            if ($parameter->isPassedByReference()) {
                $reference = '&';
            }
            $parameters[] = $nullable . $typeDeclaration . $reference . $name . $default;
        }
        return \implode(', ', $parameters);
    }
}
