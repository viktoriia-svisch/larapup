<?php
namespace Illuminate\Auth\Access;
use Exception;
use ReflectionClass;
use ReflectionFunction;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
class Gate implements GateContract
{
    use HandlesAuthorization;
    protected $container;
    protected $userResolver;
    protected $abilities = [];
    protected $policies = [];
    protected $beforeCallbacks = [];
    protected $afterCallbacks = [];
    public function __construct(Container $container, callable $userResolver, array $abilities = [],
                                array $policies = [], array $beforeCallbacks = [], array $afterCallbacks = [])
    {
        $this->policies = $policies;
        $this->container = $container;
        $this->abilities = $abilities;
        $this->userResolver = $userResolver;
        $this->afterCallbacks = $afterCallbacks;
        $this->beforeCallbacks = $beforeCallbacks;
    }
    public function has($ability)
    {
        $abilities = is_array($ability) ? $ability : func_get_args();
        foreach ($abilities as $ability) {
            if (! isset($this->abilities[$ability])) {
                return false;
            }
        }
        return true;
    }
    public function define($ability, $callback)
    {
        if (is_callable($callback)) {
            $this->abilities[$ability] = $callback;
        } elseif (is_string($callback)) {
            $this->abilities[$ability] = $this->buildAbilityCallback($ability, $callback);
        } else {
            throw new InvalidArgumentException("Callback must be a callable or a 'Class@method' string.");
        }
        return $this;
    }
    public function resource($name, $class, array $abilities = null)
    {
        $abilities = $abilities ?: [
            'view'   => 'view',
            'create' => 'create',
            'update' => 'update',
            'delete' => 'delete',
        ];
        foreach ($abilities as $ability => $method) {
            $this->define($name.'.'.$ability, $class.'@'.$method);
        }
        return $this;
    }
    protected function buildAbilityCallback($ability, $callback)
    {
        return function () use ($ability, $callback) {
            if (Str::contains($callback, '@')) {
                [$class, $method] = Str::parseCallback($callback);
            } else {
                $class = $callback;
            }
            $policy = $this->resolvePolicy($class);
            $arguments = func_get_args();
            $user = array_shift($arguments);
            $result = $this->callPolicyBefore(
                $policy, $user, $ability, $arguments
            );
            if (! is_null($result)) {
                return $result;
            }
            return isset($method)
                    ? $policy->{$method}(...func_get_args())
                    : $policy(...func_get_args());
        };
    }
    public function policy($class, $policy)
    {
        $this->policies[$class] = $policy;
        return $this;
    }
    public function before(callable $callback)
    {
        $this->beforeCallbacks[] = $callback;
        return $this;
    }
    public function after(callable $callback)
    {
        $this->afterCallbacks[] = $callback;
        return $this;
    }
    public function allows($ability, $arguments = [])
    {
        return $this->check($ability, $arguments);
    }
    public function denies($ability, $arguments = [])
    {
        return ! $this->allows($ability, $arguments);
    }
    public function check($abilities, $arguments = [])
    {
        return collect($abilities)->every(function ($ability) use ($arguments) {
            try {
                return (bool) $this->raw($ability, $arguments);
            } catch (AuthorizationException $e) {
                return false;
            }
        });
    }
    public function any($abilities, $arguments = [])
    {
        return collect($abilities)->contains(function ($ability) use ($arguments) {
            return $this->check($ability, $arguments);
        });
    }
    public function authorize($ability, $arguments = [])
    {
        $result = $this->raw($ability, $arguments);
        if ($result instanceof Response) {
            return $result;
        }
        return $result ? $this->allow() : $this->deny();
    }
    public function raw($ability, $arguments = [])
    {
        $arguments = Arr::wrap($arguments);
        $user = $this->resolveUser();
        $result = $this->callBeforeCallbacks(
            $user, $ability, $arguments
        );
        if (is_null($result)) {
            $result = $this->callAuthCallback($user, $ability, $arguments);
        }
        return $this->callAfterCallbacks(
            $user, $ability, $arguments, $result
        );
    }
    protected function canBeCalledWithUser($user, $class, $method = null)
    {
        if (! is_null($user)) {
            return true;
        }
        if (! is_null($method)) {
            return $this->methodAllowsGuests($class, $method);
        }
        if (is_array($class)) {
            $className = is_string($class[0]) ? $class[0] : get_class($class[0]);
            return $this->methodAllowsGuests($className, $class[1]);
        }
        return $this->callbackAllowsGuests($class);
    }
    protected function methodAllowsGuests($class, $method)
    {
        try {
            $reflection = new ReflectionClass($class);
            $method = $reflection->getMethod($method);
        } catch (Exception $e) {
            return false;
        }
        if ($method) {
            $parameters = $method->getParameters();
            return isset($parameters[0]) && $this->parameterAllowsGuests($parameters[0]);
        }
        return false;
    }
    protected function callbackAllowsGuests($callback)
    {
        $parameters = (new ReflectionFunction($callback))->getParameters();
        return isset($parameters[0]) && $this->parameterAllowsGuests($parameters[0]);
    }
    protected function parameterAllowsGuests($parameter)
    {
        return ($parameter->getClass() && $parameter->allowsNull()) ||
               ($parameter->isDefaultValueAvailable() && is_null($parameter->getDefaultValue()));
    }
    protected function callAuthCallback($user, $ability, array $arguments)
    {
        $callback = $this->resolveAuthCallback($user, $ability, $arguments);
        return $callback($user, ...$arguments);
    }
    protected function callBeforeCallbacks($user, $ability, array $arguments)
    {
        $arguments = array_merge([$user, $ability], [$arguments]);
        foreach ($this->beforeCallbacks as $before) {
            if (! $this->canBeCalledWithUser($user, $before)) {
                continue;
            }
            if (! is_null($result = $before(...$arguments))) {
                return $result;
            }
        }
    }
    protected function callAfterCallbacks($user, $ability, array $arguments, $result)
    {
        foreach ($this->afterCallbacks as $after) {
            if (! $this->canBeCalledWithUser($user, $after)) {
                continue;
            }
            $afterResult = $after($user, $ability, $result, $arguments);
            $result = $result ?? $afterResult;
        }
        return $result;
    }
    protected function resolveAuthCallback($user, $ability, array $arguments)
    {
        if (isset($arguments[0]) &&
            ! is_null($policy = $this->getPolicyFor($arguments[0])) &&
            $callback = $this->resolvePolicyCallback($user, $ability, $arguments, $policy)) {
            return $callback;
        }
        if (isset($this->abilities[$ability]) &&
            $this->canBeCalledWithUser($user, $this->abilities[$ability])) {
            return $this->abilities[$ability];
        }
        return function () {
            return null;
        };
    }
    public function getPolicyFor($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        if (! is_string($class)) {
            return;
        }
        if (isset($this->policies[$class])) {
            return $this->resolvePolicy($this->policies[$class]);
        }
        foreach ($this->policies as $expected => $policy) {
            if (is_subclass_of($class, $expected)) {
                return $this->resolvePolicy($policy);
            }
        }
    }
    public function resolvePolicy($class)
    {
        return $this->container->make($class);
    }
    protected function resolvePolicyCallback($user, $ability, array $arguments, $policy)
    {
        if (! is_callable([$policy, $this->formatAbilityToMethod($ability)])) {
            return false;
        }
        return function () use ($user, $ability, $arguments, $policy) {
            $result = $this->callPolicyBefore(
                $policy, $user, $ability, $arguments
            );
            if (! is_null($result)) {
                return $result;
            }
            $method = $this->formatAbilityToMethod($ability);
            return $this->callPolicyMethod($policy, $method, $user, $arguments);
        };
    }
    protected function callPolicyBefore($policy, $user, $ability, $arguments)
    {
        if (! method_exists($policy, 'before')) {
            return null;
        }
        if ($this->canBeCalledWithUser($user, $policy, 'before')) {
            return $policy->before($user, $ability, ...$arguments);
        }
    }
    protected function callPolicyMethod($policy, $method, $user, array $arguments)
    {
        if (isset($arguments[0]) && is_string($arguments[0])) {
            array_shift($arguments);
        }
        if (! is_callable([$policy, $method])) {
            return null;
        }
        if ($this->canBeCalledWithUser($user, $policy, $method)) {
            return $policy->{$method}($user, ...$arguments);
        }
    }
    protected function formatAbilityToMethod($ability)
    {
        return strpos($ability, '-') !== false ? Str::camel($ability) : $ability;
    }
    public function forUser($user)
    {
        $callback = function () use ($user) {
            return $user;
        };
        return new static(
            $this->container, $callback, $this->abilities,
            $this->policies, $this->beforeCallbacks, $this->afterCallbacks
        );
    }
    protected function resolveUser()
    {
        return call_user_func($this->userResolver);
    }
    public function abilities()
    {
        return $this->abilities;
    }
    public function policies()
    {
        return $this->policies;
    }
}
