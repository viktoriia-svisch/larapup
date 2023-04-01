<?php
namespace Illuminate\Database\Eloquent\Relations\Concerns;
use Illuminate\Database\Eloquent\Model;
trait SupportsDefaultModels
{
    protected $withDefault;
    abstract protected function newRelatedInstanceFor(Model $parent);
    public function withDefault($callback = true)
    {
        $this->withDefault = $callback;
        return $this;
    }
    protected function getDefaultFor(Model $parent)
    {
        if (! $this->withDefault) {
            return;
        }
        $instance = $this->newRelatedInstanceFor($parent);
        if (is_callable($this->withDefault)) {
            return call_user_func($this->withDefault, $instance, $parent) ?: $instance;
        }
        if (is_array($this->withDefault)) {
            $instance->forceFill($this->withDefault);
        }
        return $instance;
    }
}
