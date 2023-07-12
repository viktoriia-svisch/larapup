<?php
namespace Illuminate\Queue;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Contracts\Queue\QueueableCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
trait SerializesAndRestoresModelIdentifiers
{
    protected function getSerializedPropertyValue($value)
    {
        if ($value instanceof QueueableCollection) {
            return new ModelIdentifier(
                $value->getQueueableClass(),
                $value->getQueueableIds(),
                $value->getQueueableRelations(),
                $value->getQueueableConnection()
            );
        }
        if ($value instanceof QueueableEntity) {
            return new ModelIdentifier(
                get_class($value),
                $value->getQueueableId(),
                $value->getQueueableRelations(),
                $value->getQueueableConnection()
            );
        }
        return $value;
    }
    protected function getRestoredPropertyValue($value)
    {
        if (! $value instanceof ModelIdentifier) {
            return $value;
        }
        return is_array($value->id)
                ? $this->restoreCollection($value)
                : $this->restoreModel($value);
    }
    protected function restoreCollection($value)
    {
        if (! $value->class || count($value->id) === 0) {
            return new EloquentCollection;
        }
        return $this->getQueryForModelRestoration(
            (new $value->class)->setConnection($value->connection), $value->id
        )->useWritePdo()->get();
    }
    public function restoreModel($value)
    {
        return $this->getQueryForModelRestoration(
            (new $value->class)->setConnection($value->connection), $value->id
        )->useWritePdo()->firstOrFail()->load($value->relations ?? []);
    }
    protected function getQueryForModelRestoration($model, $ids)
    {
        return $model->newQueryForRestoration($ids);
    }
}
